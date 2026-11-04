<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductVariantController extends BaseApiController
{
    /**
     * GET /api/v2/products/{productId}/variants
     */
    public function productVariants($productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        $variants = ProductVariant::with([
            'primaryListing.seller',
            'attributeValues.attribute',
            'attributeValues.option'
        ])
        ->where('product_id', $productId)
        ->where('status', 'Active')
        ->get();

        return $this->sendResponse(
            ProductVariantResource::collection($variants),
            'Product variants retrieved successfully.'
        );
    }

    /**
     * GET /api/v2/variants/{id}
     */
    public function show($id): JsonResponse
    {
        $variant = ProductVariant::with([
            'product',
            'primaryListing.seller',
            'attributeValues.attribute',
            'attributeValues.option',
            'listings' => function($q) {
                $q->where('status', 'Active')->orderBy('selling_price', 'asc');
            }
        ])->find($id);

        if (!$variant) {
            return $this->sendError('Variant not found.', [], 404);
        }

        return $this->sendResponse(new ProductVariantResource($variant), 'Variant details retrieved successfully.');
    }

    /**
     * POST /api/v2/products/{productId}/variants/find-by-attributes
     */
    public function findByAttributes(Request $request, $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        $shadeCardId = $request->get('shade_card_id');
        $boxPackingId = $request->get('box_packing_id');
        $packLitres = $request->get('pack_litres');

        $query = ProductVariant::with(['primaryListing.seller', 'attributeValues.attribute', 'attributeValues.option'])
            ->where('product_id', $productId)
            ->where('status', 'Active');

        if ($shadeCardId !== null) {
            $query->where('shade_card_id', $shadeCardId);
        }

        if ($boxPackingId !== null) {
            $query->where('box_packing_id', $boxPackingId);
        }

        if ($packLitres !== null) {
            $query->where('pack_litres', $packLitres);
        }

        $variant = $query->first();
        if (!$variant) {
            return $this->sendError('No variant matching this attribute combination was found.', [], 404);
        }

        return $this->sendResponse(new ProductVariantResource($variant), 'Matching variant found.');
    }

    /**
     * POST /api/v2/admin/products/{productId}/variants
     * Creates a product variant while strictly preventing duplicate attribute combinations.
     */
    public function store(Request $request, $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'variant_name' => 'nullable|string|max:255',
            'shade_card_id' => 'nullable|integer',
            'box_packing_id' => 'nullable|integer',
            'pack_litres' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'barcode' => 'nullable|string|max:100',
            'status' => 'nullable|in:Active,Inactive,Archived,active,inactive',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required_with:attributes|exists:attributes,id',
            'attributes.*.value' => 'nullable',
            'attributes.*.attribute_option_id' => 'nullable|exists:attribute_options,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        // Check duplicate variant combination
        $duplicate = ProductVariant::where('product_id', $productId)
            ->where('shade_card_id', $request->shade_card_id)
            ->where('box_packing_id', $request->box_packing_id)
            ->exists();

        if ($duplicate) {
            return $this->sendError('A variant with this attribute combination already exists for this product.', [], 409);
        }

        return DB::transaction(function () use ($request, $product, $productId) {
            $variantSku = "VAR-P{$productId}-" . Str::upper(Str::random(6));

            $variant = ProductVariant::create([
                'product_id' => $productId,
                'variant_sku' => $variantSku,
                'shade_card_id' => $request->shade_card_id,
                'box_packing_id' => $request->box_packing_id,
                'pack_litres' => $request->pack_litres,
                'variant_name' => $request->variant_name ?: "{$product->name} " . ($request->pack_litres ? "({$request->pack_litres})" : ''),
                'image' => $request->image ?: $product->image,
                'barcode' => $request->barcode,
                'status' => $request->status ?: 'Active',
            ]);

            // Save dynamic attribute values if provided
            if ($request->has('attributes') && is_array($request->attributes)) {
                foreach ($request->attributes as $attrData) {
                    ProductAttributeValue::create([
                        'product_id' => $productId,
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $attrData['attribute_id'],
                        'attribute_option_id' => $attrData['attribute_option_id'] ?? null,
                        'text_value' => is_string($attrData['value'] ?? null) ? $attrData['value'] : null,
                        'number_value' => is_numeric($attrData['value'] ?? null) ? $attrData['value'] : null,
                    ]);
                }
            }

            return $this->sendResponse(new ProductVariantResource($variant), 'Variant created successfully.', 201);
        });
    }

    /**
     * PUT /api/v2/admin/variants/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $variant = ProductVariant::find($id);
        if (!$variant) {
            return $this->sendError('Variant not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'variant_name' => 'nullable|string|max:255',
            'pack_litres' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'barcode' => 'nullable|string|max:100',
            'status' => 'nullable|in:Active,Inactive,Archived,active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $variant->update($request->only(['variant_name', 'pack_litres', 'image', 'barcode', 'status']));

        return $this->sendResponse(new ProductVariantResource($variant), 'Variant updated successfully.');
    }

    /**
     * PATCH /api/v2/admin/variants/{id}/status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $variant = ProductVariant::find($id);
        if (!$variant) {
            return $this->sendError('Variant not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Active,Inactive,Archived,active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $variant->status = ucfirst(strtolower($request->status));
        $variant->save();

        return $this->sendResponse(new ProductVariantResource($variant), "Variant status updated to {$variant->status}.");
    }
}
