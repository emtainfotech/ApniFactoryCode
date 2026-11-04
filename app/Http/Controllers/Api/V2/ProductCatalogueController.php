<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductCatalogueController extends BaseApiController
{
    /**
     * GET /api/v2/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['brand', 'category', 'subcategory', 'listings' => function($q) {
            $q->where('status', 'Active')->orderBy('selling_price', 'asc');
        }])->withCount('variants');

        // Apply Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['Active', 'active', '1'])
              ->where(function ($q) {
                  $q->whereNull('approval_status')->orWhere('approval_status', 'approved');
              });
        }

        // Marketplace publication rule: Only approved products are publicly visible
        $query->where(function ($q) {
            $q->whereNull('approval_status')->orWhere('approval_status', 'approved');
        });

        // Hierarchy Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'name') {
            $query->orderBy('name', $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        return $this->sendPaginated($query->paginate($perPage), ProductResource::class);
    }

    /**
     * GET /api/v2/products/{id}
     */
    public function show($id): JsonResponse
    {
        $product = Product::with([
            'brand',
            'category',
            'subcategory',
            'variants.primaryListing.seller',
            'variants.attributeValues.attribute',
            'variants.attributeValues.option',
            'attributeValues.attribute',
            'attributeValues.option',
            'listings' => function($q) {
                $q->where('status', 'Active')->orderBy('selling_price', 'asc');
            }
        ])->find($id);

        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        // Visibility guard: unapproved products can only be viewed by owner or admin
        $user = auth('sanctum')->user();
        if ($product->approval_status && $product->approval_status !== 'approved') {
            if (!$user || (!$user->isAdmin() && $user->id !== $product->user_id)) {
                return $this->sendError('Product not found or pending catalogue approval.', [], 404);
            }
        }

        return $this->sendResponse(new ProductDetailResource($product), 'Product details retrieved successfully.');
    }

    /**
     * GET /api/v2/products/by-category/{categoryId}
     */
    public function byCategory($categoryId, Request $request): JsonResponse
    {
        $request->merge(['category_id' => $categoryId]);
        return $this->index($request);
    }

    /**
     * GET /api/v2/products/by-subcategory/{subcategoryId}
     */
    public function bySubcategory($subcategoryId, Request $request): JsonResponse
    {
        $request->merge(['subcategory_id' => $subcategoryId]);
        return $this->index($request);
    }

    /**
     * GET /api/v2/products/by-brand/{brandId}
     */
    public function byBrand($brandId, Request $request): JsonResponse
    {
        $request->merge(['brand_id' => $brandId]);
        return $this->index($request);
    }

    /**
     * GET /api/v2/catalogue/search
     * Unified catalogue search with dynamic attributes and availability filters.
     */
    public function search(Request $request): JsonResponse
    {
        $query = Product::with(['brand', 'category', 'subcategory', 'listings' => function($q) {
            $q->where('status', 'Active')->orderBy('selling_price', 'asc');
        }])->withCount('variants');

        $query->whereIn('status', ['Active', 'active', '1'])
              ->where(function ($q) {
                  $q->whereNull('approval_status')->orWhere('approval_status', 'approved');
              });

        // Keyword Search across Product name, brand name, variant SKU
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($queryGroup) use ($q) {
                $queryGroup->where('name', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('brand', function($b) use ($q) {
                        $b->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('variants', function($v) use ($q) {
                        $v->where('variant_sku', 'like', "%{$q}%")
                          ->orWhere('variant_name', 'like', "%{$q}%");
                    });
            });
        }

        // Category & Subcategory
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // In Stock Filter
        if ($request->boolean('in_stock', false)) {
            $query->whereHas('listings', function($l) {
                $l->where('status', 'Active')
                  ->where('stock_quantity', '>', 0);
            });
        }

        // Price Filter
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $min = (float) $request->get('min_price', 0);
            $max = (float) $request->get('max_price', 9999999);
            $query->whereHas('listings', function($l) use ($min, $max) {
                $l->where('status', 'Active')
                  ->whereBetween('selling_price', [$min, $max]);
            });
        }

        // Dynamic Attribute Filters: attributes[code]=value
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attrCode => $attrValue) {
                if (empty($attrValue)) continue;
                $query->whereHas('attributeValues', function($av) use ($attrCode, $attrValue) {
                    $av->whereHas('attribute', function($a) use ($attrCode) {
                        $a->where('code', $attrCode);
                    })->where(function($valQ) use ($attrValue) {
                        $valQ->where('text_value', $attrValue)
                             ->orWhere('number_value', is_numeric($attrValue) ? $attrValue : -99999)
                             ->orWhereHas('option', function($opt) use ($attrValue) {
                                 $opt->where('value', $attrValue)->orWhere('label', $attrValue);
                             });
                    });
                });
            }
        }

        // Sorting
        $sort = $request->get('sort', 'default');
        if ($sort === 'price_asc') {
            $query->leftJoin('seller_listings', function($join) {
                $join->on('seller_listings.product_id', '=', 'products.id')
                     ->where('seller_listings.status', '=', 'Active');
            })->select('products.*', \Illuminate\Support\Facades\DB::raw('MIN(seller_listings.selling_price) as min_price'))
              ->groupBy('products.id')
              ->orderBy('min_price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->leftJoin('seller_listings', function($join) {
                $join->on('seller_listings.product_id', '=', 'products.id')
                     ->where('seller_listings.status', '=', 'Active');
            })->select('products.*', \Illuminate\Support\Facades\DB::raw('MIN(seller_listings.selling_price) as min_price'))
              ->groupBy('products.id')
              ->orderBy('min_price', 'desc');
        } else {
            $query->orderBy('products.id', 'desc');
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        return $this->sendPaginated($query->paginate($perPage), ProductResource::class, 'Search results retrieved.');
    }

    /**
     * POST /api/v2/admin/products
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:sub_categories,id',
            'maincategory_id' => 'nullable|exists:main_categories,id',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',
            'hsncode' => 'nullable|string|max:50',
            'status' => 'nullable|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $slug = Str::slug($request->name);
        $count = Product::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $product = Product::create([
            'name' => $request->name,
            'title' => $request->title ?: $request->name,
            'slug' => $slug,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'maincategory_id' => $request->maincategory_id ?: ($request->category_id ? \App\Models\Category::find($request->category_id)->maincategory_id : null),
            'image' => $request->image,
            'description' => $request->description,
            'tax' => $request->tax ?: 18.00,
            'hsncode' => $request->hsncode,
            'status' => $request->status ?: 'Active',
            'user_id' => auth()->id() ?: 1,
        ]);

        return $this->sendResponse(new ProductResource($product), 'Product created successfully.', 201);
    }

    /**
     * PUT /api/v2/admin/products/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        // Visibility guard: unapproved products can only be viewed by owner or admin
        $user = auth('sanctum')->user();
        if ($product->approval_status && $product->approval_status !== 'approved') {
            if (!$user || (!$user->isAdmin() && $user->id !== $product->user_id)) {
                return $this->sendError('Product not found or pending catalogue approval.', [], 404);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'title' => 'nullable|string|max:255',
            'brand_id' => 'sometimes|required|exists:brands,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:sub_categories,id',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',
            'hsncode' => 'nullable|string|max:50',
            'status' => 'nullable|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $product->update($request->only([
            'name', 'title', 'brand_id', 'category_id', 'subcategory_id',
            'image', 'description', 'tax', 'hsncode', 'status'
        ]));

        return $this->sendResponse(new ProductResource($product), 'Product updated successfully.');
    }

    /**
     * PATCH /api/v2/admin/products/{id}/status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        // Visibility guard: unapproved products can only be viewed by owner or admin
        $user = auth('sanctum')->user();
        if ($product->approval_status && $product->approval_status !== 'approved') {
            if (!$user || (!$user->isAdmin() && $user->id !== $product->user_id)) {
                return $this->sendError('Product not found or pending catalogue approval.', [], 404);
            }
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $status = in_array($request->status, ['1', 'Active', 'active']) ? 'Active' : 'Inactive';
        $product->status = $status;
        $product->save();

        return $this->sendResponse(new ProductResource($product), "Product status updated to {$status}.");
    }
}

