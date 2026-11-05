<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommissionRule;
use App\Models\OrderCommissionSnapshot;
use App\Models\Product;
use App\Models\SellerListing;
use App\Services\CommissionCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerCommissionApiController extends Controller
{
    protected CommissionCalculationService $commissionService;

    public function __construct(CommissionCalculationService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * GET /api/v2/seller/commissions/agreements
     * View all commercial commission agreements applicable to this seller.
     * Enforces strict multi-tenant isolation: sellers can only see their own rules.
     */
    public function agreements(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status'  => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        if (!$user->hasSellerPermission('pricing.view')) {
            return response()->json([
                'status'  => false,
                'message' => 'You do not have permission to view commission agreements.',
            ], 403);
        }

        $sellerListingIds = SellerListing::where('company_id', $company->id)->pluck('id');
        $productCategoryIds = Product::whereIn('id', SellerListing::where('company_id', $company->id)->pluck('product_id'))
            ->pluck('category_id')
            ->unique();

        // 1. Seller-specific agreements
        $sellerRules = CommissionRule::where('scope_type', 'seller')
            ->where('scope_id', $company->id)
            ->whereIn('status', ['active', 'scheduled', 'expired'])
            ->get();

        // 2. SKU-specific agreements
        $skuRules = CommissionRule::where('scope_type', 'sku')
            ->whereIn('scope_id', $sellerListingIds)
            ->whereIn('status', ['active', 'scheduled', 'expired'])
            ->get();

        // 3. Category rules applicable to products sold by this seller
        $categoryRules = CommissionRule::where('scope_type', 'category')
            ->whereIn('scope_id', $productCategoryIds)
            ->where('status', 'active')
            ->get();

        // 4. Global default rule
        $globalRule = CommissionRule::where('scope_type', 'global')
            ->where('status', 'active')
            ->first();

        return response()->json([
            'status'  => true,
            'message' => 'Applicable commission agreements retrieved successfully.',
            'data'    => [
                'seller_agreements'   => $sellerRules,
                'sku_agreements'      => $skuRules,
                'category_rates'      => $categoryRules,
                'marketplace_default' => $globalRule,
            ],
        ]);
    }

    /**
     * GET /api/v2/seller/commissions/orders/{orderId}/snapshot
     * View frozen commission calculation snapshots for an order.
     * Enforces IDOR check: sellers cannot view another seller's commission snapshots!
     */
    public function orderSnapshot(Request $request, $orderId): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status'  => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        // Multi-tenant isolation: only this seller's company snapshots
        $snapshots = OrderCommissionSnapshot::with(['listing', 'product', 'variant'])
            ->where('order_id', $orderId)
            ->where('company_id', $company->id)
            ->get();

        if ($snapshots->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Order commission snapshot not found or unauthorized.',
            ], 404);
        }

        $totalOrderBase = $snapshots->sum('base_amount');
        $totalCommission = $snapshots->sum('commission_amount');
        $totalGst = $snapshots->sum('gst_amount');
        $totalPlatformFee = $snapshots->sum('total_platform_fee');
        $totalSellerPayable = $snapshots->sum('seller_payable');

        return response()->json([
            'status'  => true,
            'message' => 'Order commission snapshot retrieved.',
            'data'    => [
                'order_id'             => (int)$orderId,
                'orderno'              => $snapshots->first()->orderno,
                'total_base_amount'    => round($totalOrderBase, 2),
                'total_commission'     => round($totalCommission, 2),
                'total_gst_on_fee'     => round($totalGst, 2),
                'total_platform_fee'   => round($totalPlatformFee, 2),
                'total_seller_payable' => round($totalSellerPayable, 2),
                'line_items'           => $snapshots,
            ],
        ]);
    }

    /**
     * GET /api/v2/seller/commissions/skus/{skuId}/applicable-rule
     * Check effective commission rule and preview breakdown for a specific SKU.
     */
    public function skuApplicableRule(Request $request, $skuId): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status'  => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        $listing = SellerListing::where('id', $skuId)
            ->where('company_id', $company->id)
            ->first();

        if (!$listing) {
            return response()->json([
                'status'  => false,
                'message' => 'Seller SKU not found.',
            ], 404);
        }

        $resolved = $this->commissionService->resolveRuleForSku($listing);
        $calc = $this->commissionService->calculateLineCommission($listing, 1, (float)$listing->seller_price);

        return response()->json([
            'status'  => true,
            'message' => 'Effective commission rule resolved.',
            'data'    => [
                'seller_listing_id' => $listing->id,
                'seller_sku'        => $listing->seller_sku,
                'seller_price'      => (float)$listing->seller_price,
                'resolved_rule'     => $resolved,
                'sample_breakdown'  => $calc,
            ],
        ]);
    }
}

