<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\SubCatgory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseApiController
{
    /**
     * GET /api/v2/categories
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::with('subcategories')->withCount('categoryAttributes');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('maincategory_id')) {
            $query->where('maincategory_id', $request->maincategory_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort_by', 'sequence');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->boolean('paginate', true)) {
            $perPage = min((int) $request->get('per_page', 20), 100);
            return $this->sendPaginated($query->paginate($perPage), CategoryResource::class);
        }

        return $this->sendResponse(CategoryResource::collection($query->get()), 'Categories retrieved successfully.');
    }

    /**
     * GET /api/v2/categories/{id}
     */
    public function show($id): JsonResponse
    {
        $category = Category::with(['subcategories', 'categoryAttributes.attribute'])->find($id);
        if (!$category) {
            return $this->sendError('Category not found.', [], 404);
        }

        return $this->sendResponse(new CategoryResource($category), 'Category details retrieved successfully.');
    }

    /**
     * GET /api/v2/categories/{id}/subcategories
     */
    public function subcategories($id, Request $request): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->sendError('Category not found.', [], 404);
        }

        $query = SubCatgory::where('cid', $id);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $this->sendResponse(SubcategoryResource::collection($query->orderBy('name')->get()), 'Subcategories retrieved successfully.');
    }

    /**
     * POST /api/v2/admin/categories
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'maincategory_id' => 'required|exists:main_categories,id',
            'image' => 'nullable|string',
            'sequence' => 'nullable|integer',
            'status' => 'nullable|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $category = Category::create([
            'name' => $request->name,
            'title' => $request->title ?: $request->name,
            'maincategory_id' => $request->maincategory_id,
            'image' => $request->image,
            'sequence' => $request->sequence ?: (Category::max('sequence') + 1),
            'status' => $request->status ?: 'Active',
        ]);

        return $this->sendResponse(new CategoryResource($category), 'Category created successfully.', 201);
    }

    /**
     * PUT /api/v2/admin/categories/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->sendError('Category not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'title' => 'nullable|string|max:255',
            'maincategory_id' => 'sometimes|required|exists:main_categories,id',
            'image' => 'nullable|string',
            'sequence' => 'nullable|integer',
            'status' => 'nullable|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $category->update($request->only(['name', 'title', 'maincategory_id', 'image', 'sequence', 'status']));

        return $this->sendResponse(new CategoryResource($category), 'Category updated successfully.');
    }

    /**
     * PATCH /api/v2/admin/categories/{id}/status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->sendError('Category not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Active,Inactive,active,inactive,1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $status = in_array($request->status, ['1', 'Active', 'active']) ? 'Active' : 'Inactive';
        $category->status = $status;
        $category->save();

        return $this->sendResponse(new CategoryResource($category), "Category status updated to {$status}.");
    }
}
