<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\AttributeOptionResource;
use App\Http\Resources\AttributeResource;
use App\Http\Resources\CategoryAttributeResource;
use App\Http\Resources\ProductAttributeValueResource;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DynamicAttributeController extends BaseApiController
{
    /**
     * GET /api/v2/categories/{categoryId}/attributes
     */
    public function categoryAttributes($categoryId): JsonResponse
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return $this->sendError('Category not found.', [], 404);
        }

        $mappings = CategoryAttribute::with(['attribute.options'])
            ->where('category_id', $categoryId)
            ->where('status', 'Active')
            ->orderBy('sort_order', 'asc')
            ->get();

        return $this->sendResponse(
            CategoryAttributeResource::collection($mappings),
            'Category attributes retrieved successfully.'
        );
    }

    /**
     * GET /api/v2/attributes
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attribute::with('options');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');

        if ($request->boolean('paginate', true)) {
            $perPage = min((int) $request->get('per_page', 20), 100);
            return $this->sendPaginated($query->paginate($perPage), AttributeResource::class);
        }

        return $this->sendResponse(AttributeResource::collection($query->get()), 'Attributes retrieved successfully.');
    }

    /**
     * GET /api/v2/attributes/{id}
     */
    public function show($id): JsonResponse
    {
        $attribute = Attribute::with('options')->find($id);
        if (!$attribute) {
            return $this->sendError('Attribute not found.', [], 404);
        }

        return $this->sendResponse(new AttributeResource($attribute), 'Attribute details retrieved successfully.');
    }

    /**
     * GET /api/v2/attributes/{id}/options
     */
    public function options($id): JsonResponse
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return $this->sendError('Attribute not found.', [], 404);
        }

        $options = $attribute->options()->where('status', 'Active')->orderBy('sort_order')->get();
        return $this->sendResponse(AttributeOptionResource::collection($options), 'Attribute options retrieved successfully.');
    }

    /**
     * GET /api/v2/products/{productId}/attributes
     */
    public function productAttributes($productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        $values = ProductAttributeValue::with(['attribute', 'option'])
            ->where('product_id', $productId)
            ->whereNull('product_variant_id')
            ->get();

        return $this->sendResponse(
            ProductAttributeValueResource::collection($values),
            'Product attributes retrieved successfully.'
        );
    }

    /**
     * POST /api/v2/attributes/validate
     * Validates an attribute-value payload against category configuration.
     */
    public function validateValues(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'values' => 'required|array',
            'values.*.attribute_id' => 'required|exists:attributes,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $categoryId = $request->category_id;
        $categoryMappings = CategoryAttribute::with('attribute')
            ->where('category_id', $categoryId)
            ->where('status', 'Active')
            ->get()
            ->keyBy('attribute_id');

        $errors = [];
        $submittedAttrs = collect($request->values)->keyBy('attribute_id');

        // Check required category attributes
        foreach ($categoryMappings as $attrId => $mapping) {
            if ($mapping->is_required && !$submittedAttrs->has($attrId)) {
                $attrName = $mapping->attribute->name ?? "Attribute #{$attrId}";
                $errors[] = "Field '{$attrName}' is mandatory for this category.";
            }
        }

        // Validate attribute data types
        foreach ($submittedAttrs as $attrId => $item) {
            if (!isset($categoryMappings[$attrId])) {
                continue; // Descriptive attribute not constrained by category mapping
            }

            $attr = $categoryMappings[$attrId]->attribute;
            $val = $item['value'] ?? null;

            if ($attr->type === 'number' && !is_numeric($val)) {
                $errors[] = "Field '{$attr->name}' must be a valid number.";
            } elseif ($attr->type === 'boolean' && !is_bool($val) && !in_array($val, [0, 1, '0', '1', 'true', 'false'])) {
                $errors[] = "Field '{$attr->name}' must be a boolean true/false.";
            } elseif ($attr->type === 'single_select') {
                $validOption = AttributeOption::where('attribute_id', $attrId)
                    ->where(function($q) use ($val, $item) {
                        $q->where('id', $item['attribute_option_id'] ?? null)
                          ->orWhere('value', $val);
                    })->exists();
                if (!$validOption) {
                    $errors[] = "Field '{$attr->name}' value is not an allowed option.";
                }
            }
        }

        if (!empty($errors)) {
            return $this->sendError('Attribute payload failed validation.', $errors, 422);
        }

        return $this->sendResponse(['valid' => true], 'All attribute values conform to category constraints.');
    }

    /**
     * POST /api/v2/admin/attributes
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:attributes,code',
            'type' => 'required|in:text,number,boolean,single_select,multi_select,date,measurement',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:Active,Inactive,active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $attribute = Attribute::create([
            'name' => $request->name,
            'code' => Str::slug($request->code, '_'),
            'type' => $request->type,
            'unit' => $request->unit,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?: (Attribute::max('sort_order') + 1),
            'status' => $request->status ?: 'Active',
        ]);

        return $this->sendResponse(new AttributeResource($attribute), 'Attribute created successfully.', 201);
    }

    /**
     * PUT /api/v2/admin/attributes/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return $this->sendError('Attribute not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => "sometimes|required|string|max:100|unique:attributes,code,{$id}",
            'type' => 'sometimes|required|in:text,number,boolean,single_select,multi_select,date,measurement',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:Active,Inactive,active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $attribute->update($request->only(['name', 'code', 'type', 'unit', 'description', 'sort_order', 'status']));

        return $this->sendResponse(new AttributeResource($attribute), 'Attribute updated successfully.');
    }

    /**
     * POST /api/v2/admin/attributes/{id}/options
     */
    public function storeOption(Request $request, $id): JsonResponse
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return $this->sendError('Attribute not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:255',
            'color_hex' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $value = $request->value ?: Str::slug($request->label);

        $option = AttributeOption::updateOrCreate(
            ['attribute_id' => $id, 'value' => $value],
            [
                'label' => $request->label,
                'color_hex' => $request->color_hex,
                'sort_order' => $request->sort_order ?: ($attribute->options()->max('sort_order') + 1),
                'is_default' => $request->boolean('is_default', false),
                'status' => 'Active',
            ]
        );

        return $this->sendResponse(new AttributeOptionResource($option), 'Attribute option saved successfully.', 201);
    }

    /**
     * POST /api/v2/admin/categories/{categoryId}/attributes
     */
    public function mapToCategory(Request $request, $categoryId): JsonResponse
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return $this->sendError('Category not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'attribute_id' => 'required|exists:attributes,id',
            'is_required' => 'nullable|boolean',
            'is_filterable' => 'nullable|boolean',
            'is_searchable' => 'nullable|boolean',
            'is_variant_generating' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $mapping = CategoryAttribute::updateOrCreate(
            ['category_id' => $categoryId, 'attribute_id' => $request->attribute_id],
            [
                'is_required' => $request->boolean('is_required', false),
                'is_filterable' => $request->boolean('is_filterable', true),
                'is_searchable' => $request->boolean('is_searchable', false),
                'is_variant_generating' => $request->boolean('is_variant_generating', false),
                'sort_order' => $request->sort_order ?: 1,
                'status' => 'Active',
            ]
        );

        return $this->sendResponse(new CategoryAttributeResource($mapping), 'Attribute mapped to category successfully.');
    }

    /**
     * DELETE /api/v2/admin/categories/{categoryId}/attributes/{attributeId}
     */
    public function unmapFromCategory($categoryId, $attributeId): JsonResponse
    {
        $mapping = CategoryAttribute::where('category_id', $categoryId)
            ->where('attribute_id', $attributeId)
            ->first();

        if (!$mapping) {
            return $this->sendError('Category attribute mapping not found.', [], 404);
        }

        $mapping->delete();
        return $this->sendResponse([], 'Attribute mapping removed successfully.');
    }
}
