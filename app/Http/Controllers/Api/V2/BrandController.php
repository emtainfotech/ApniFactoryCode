<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends BaseApiController
{
    /**
     * GET /api/v2/brands
     */
    public function index(Request $request): JsonResponse
    {
        $query = Brand::with('company');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->boolean('paginate', true)) {
            $perPage = min((int) $request->get('per_page', 20), 100);
            return $this->sendPaginated($query->paginate($perPage), BrandResource::class);
        }

        return $this->sendResponse(BrandResource::collection($query->get()), 'Brands retrieved successfully.');
    }

    /**
     * GET /api/v2/brands/{id}
     */
    public function show($id): JsonResponse
    {
        $brand = Brand::with(['company', 'category'])->find($id);
        if (!$brand) {
            return $this->sendError('Brand not found.', [], 404);
        }

        return $this->sendResponse(new BrandResource($brand), 'Brand details retrieved successfully.');
    }

    /**
     * GET /api/v2/brands/by-category/{categoryId}
     */
    public function byCategory($categoryId): JsonResponse
    {
        $brands = Brand::with('company')
            ->where('category_id', $categoryId)
            ->whereIn('status', ['Active', 'active', '1'])
            ->orderBy('name')
            ->get();

        return $this->sendResponse(BrandResource::collection($brands), 'Brands for category retrieved successfully.');
    }

    /**
     * POST /api/v2/admin/brands
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|string',
            'status' => 'nullable|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        // Prevent duplicate brand name for the same category or company
        $existing = Brand::where('name', $request->name)
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->first();

        if ($existing) {
            return $this->sendError('A brand with this name already exists in this category.', [], 409);
        }

        $brand = Brand::create([
            'name' => $request->name,
            'company_id' => $request->company_id,
            'category_id' => $request->category_id,
            'image' => $request->image,
            'status' => $request->status ?: 'Active',
            'addby' => auth()->id() ?: 1,
        ]);

        return $this->sendResponse(new BrandResource($brand), 'Brand created successfully.', 201);
    }

    /**
     * PUT /api/v2/admin/brands/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return $this->sendError('Brand not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|string',
            'status' => 'nullable|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        // Prevent duplicate rename
        if ($request->filled('name') && $request->name !== $brand->name) {
            $catId = $request->category_id ?: $brand->category_id;
            $duplicate = Brand::where('name', $request->name)
                ->where('category_id', $catId)
                ->where('id', '!=', $brand->id)
                ->exists();

            if ($duplicate) {
                return $this->sendError('Another brand with this name already exists in this category.', [], 409);
            }
        }

        $brand->update($request->only(['name', 'company_id', 'category_id', 'image', 'status']));

        return $this->sendResponse(new BrandResource($brand), 'Brand updated successfully.');
    }

    /**
     * PATCH /api/v2/admin/brands/{id}/status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return $this->sendError('Brand not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $status = in_array($request->status, ['1', 'Active', 'active']) ? 'Active' : 'Inactive';
        $brand->status = $status;
        $brand->save();

        return $this->sendResponse(new BrandResource($brand), "Brand status updated to {$status}.");
    }
}

