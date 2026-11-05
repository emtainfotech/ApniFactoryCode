<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceRevision;
use App\Models\PriceRevisionItem;
use App\Models\Product;
use App\Models\SellerListing;
use App\Services\DynamicPriceRevisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PriceRevisionApiController extends Controller
{
    protected DynamicPriceRevisionService $pricingService;

    public function __construct(DynamicPriceRevisionService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * GET /api/v2/seller/pricing/revisions/filter-options
     * Returns available filter options (categories, brands, products, units) for the authenticated seller.
     */
    public function filterOptions(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        if (!$user->hasSellerPermission('pricing.view')) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to view pricing.',
            ], 403);
        }

        $listings = SellerListing::where('company_id', $company->id)->where('status', '!=', 'Archived');
        $productIds = (clone $listings)->pluck('product_id')->unique();

        $categories = Category::whereIn('id', Product::whereIn('id', $productIds)->pluck('category_id')->unique())
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $brands = Brand::whereIn('id', Product::whereIn('id', $productIds)->pluck('brand_id')->unique())
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $units = (clone $listings)
            ->pluck('unit_of_measurement')
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Filter options retrieved successfully.',
            'data' => [
                'categories' => $categories,
                'brands' => $brands,
                'units' => $units,
                'rounding_rules' => [
                    ['id' => 'round_half', 'name' => 'Round to nearest 0.50 (e.g. ₹104.50)'],
                    ['id' => 'round_integer', 'name' => 'Round to whole Rupee (e.g. ₹105.00)'],
                    ['id' => 'round_to_99', 'name' => 'Psychological pricing (e.g. ₹104.99)'],
                    ['id' => 'none', 'name' => 'Standard 2 decimals (no rounding)'],
                ],
                'revision_methods' => [
                    ['id' => 'percentage', 'name' => 'Percentage Revision (+/- %)'],
                    ['id' => 'flat_per_unit', 'name' => 'Flat per Unit (+/- ₹ per L/Kg)'],
                    ['id' => 'explicit_list', 'name' => 'Explicit Pack Price List'],
                ],
            ],
        ]);
    }

    /**
     * POST /api/v2/seller/pricing/revisions/preview
     * Generate non-mutating preview of dynamic price revision.
     */
    public function preview(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        if (!$user->hasSellerPermission('pricing.view') && !$user->hasSellerPermission('pricing.update')) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to preview price revisions.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'revision_method' => 'required|string|in:percentage,flat_per_unit,explicit_list',
            'revision_direction' => 'nullable|string|in:increase,decrease,custom',
            'revision_value' => 'nullable|numeric',
            'price_basis_unit' => 'nullable|string|max:50',
            'rounding_rule' => 'nullable|string|in:none,round_integer,round_half,round_to_99',
            'filters' => 'nullable|array',
            'filters.category_id' => 'nullable|integer',
            'filters.brand_id' => 'nullable|integer',
            'filters.product_id' => 'nullable|integer',
            'filters.seller_listing_ids' => 'nullable|array',
            'filters.seller_listing_ids.*' => 'integer',
            'explicit_prices' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $preview = $this->pricingService->previewRevision($company, $request->all());

        return response()->json([
            'status' => true,
            'message' => 'Price revision preview generated successfully.',
            'data' => $preview,
        ]);
    }

    /**
     * POST /api/v2/seller/pricing/revisions
     * Create / schedule a dynamic price revision.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        if (!$user->hasSellerPermission('pricing.update')) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to create or schedule price revisions.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'preview_token' => 'nullable|string',
            'revision_method' => 'required_without:preview_token|string|in:percentage,flat_per_unit,explicit_list',
            'revision_direction' => 'nullable|string|in:increase,decrease,custom',
            'revision_value' => 'nullable|numeric',
            'price_basis_unit' => 'nullable|string|max:50',
            'rounding_rule' => 'nullable|string|in:none,round_integer,round_half,round_to_99',
            'effective_at' => 'required',
            'reason' => 'nullable|string|max:500',
            'approval_required' => 'nullable|boolean',
            'idempotency_key' => 'nullable|string|max:100',
            'filters' => 'nullable|array',
            'explicit_prices' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check idempotency if key provided
        if ($request->filled('idempotency_key')) {
            $existing = PriceRevision::where('idempotency_key', $request->idempotency_key)
                ->where('company_id', $company->id)
                ->first();
            if ($existing) {
                return response()->json([
                    'status' => true,
                    'message' => 'Price revision already created (idempotent response).',
                    'data' => $existing->load('items'),
                ], 200);
            }
        }

        try {
            $revision = $this->pricingService->createRevision($company, $user, $request->all());

            return response()->json([
                'status' => true,
                'message' => $revision->status === 'active'
                    ? 'Price revision applied immediately.'
                    : 'Price revision scheduled successfully.',
                'data' => $revision->load('items'),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to schedule price revision: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v2/seller/pricing/revisions
     * List price revisions for the authenticated seller.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        if (!$user->hasSellerPermission('pricing.view') && !$user->hasSellerPermission('pricing.view_history')) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to view price revision history.',
            ], 403);
        }

        $query = PriceRevision::forCompany($company->id)
            ->with(['creator:id,name,email', 'approver:id,name,email'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('revision_method')) {
            $query->where('revision_method', $request->revision_method);
        }

        $perPage = min((int)$request->get('per_page', 15), 50);
        $revisions = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Price revisions retrieved successfully.',
            'data' => $revisions,
        ]);
    }

    /**
     * GET /api/v2/seller/pricing/revisions/{id}
     * Get details and SKU items of a specific price revision.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        // Multi-tenant isolation: IDOR returns 404
        $revision = PriceRevision::forCompany($company->id)
            ->with(['creator:id,name,email', 'approver:id,name,email', 'items.product', 'items.variant'])
            ->where('id', $id)
            ->first();

        if (!$revision) {
            return response()->json([
                'status' => false,
                'message' => 'Price revision not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Price revision retrieved successfully.',
            'data' => $revision,
        ]);
    }

    /**
     * POST /api/v2/seller/pricing/revisions/{id}/cancel
     * Cancel a pending or scheduled price revision.
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        if (!$user->hasSellerPermission('pricing.update')) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to cancel price revisions.',
            ], 403);
        }

        $revision = PriceRevision::forCompany($company->id)->where('id', $id)->first();

        if (!$revision) {
            return response()->json([
                'status' => false,
                'message' => 'Price revision not found.',
            ], 404);
        }

        try {
            $cancelled = $this->pricingService->cancelRevision($revision, $user);
            return response()->json([
                'status' => true,
                'message' => "Price revision {$revision->revision_code} cancelled successfully.",
                'data' => $cancelled,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v2/seller/pricing/skus/{skuId}/history
     * Get price revision history for a specific seller listing.
     */
    public function skuPriceHistory(Request $request, $skuId): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        $listing = SellerListing::where('id', $skuId)->where('company_id', $company->id)->first();
        if (!$listing) {
            return response()->json([
                'status' => false,
                'message' => 'Seller listing SKU not found.',
            ], 404);
        }

        $history = PriceRevisionItem::where('seller_listing_id', $listing->id)
            ->with(['revision:id,revision_code,revision_method,revision_direction,revision_value,status,effective_at,activated_at'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'SKU price revision history retrieved.',
            'data' => [
                'seller_listing_id' => $listing->id,
                'seller_sku' => $listing->seller_sku,
                'current_seller_price' => (float)$listing->seller_price,
                'current_selling_price' => (float)$listing->selling_price,
                'mrp' => (float)$listing->mrp,
                'unit_of_measurement' => $listing->unit_of_measurement,
                'history' => $history,
            ],
        ]);
    }
}
