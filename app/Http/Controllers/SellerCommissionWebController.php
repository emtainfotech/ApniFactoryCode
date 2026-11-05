<?php

namespace App\Http\Controllers;

use App\Models\CommissionRule;
use App\Models\Company;
use App\Models\OrderCommissionSnapshot;
use App\Models\Product;
use App\Models\SellerListing;
use App\Services\CommissionCalculationService;
use App\Services\SellerPermissionService;
use Illuminate\Http\Request;

class SellerCommissionWebController extends Controller
{
    public function __construct(
        private CommissionCalculationService $commissionService,
        private SellerPermissionService $permissionService
    ) {
        $this->middleware('auth');
    }

    private function resolveCompanyOrAbort(): Company
    {
        $company = $this->permissionService->resolveCompany(auth()->user());
        if (!$company) {
            abort(403, 'No seller company account associated with authenticated user.');
        }
        return $company;
    }

    /**
     * View applicable commercial commission agreements for authenticated seller.
     */
    public function agreements(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('pricing.view')) {
                abort(403, 'Permission denied: pricing.view required.');
            }
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

        return view('seller.commissions.agreements', compact('company', 'sellerRules', 'skuRules', 'categoryRules', 'globalRule'));
    }

    /**
     * View order-level commission calculation breakdown for an order.
     */
    public function orderSnapshot(Request $request, $orderId)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        $snapshots = OrderCommissionSnapshot::with(['listing', 'product', 'variant'])
            ->where('order_id', $orderId)
            ->where('company_id', $company->id)
            ->get();

        if ($snapshots->isEmpty()) {
            abort(404, 'Order commission snapshot not found or unauthorized.');
        }

        return view('seller.commissions.order-snapshot', compact('company', 'orderId', 'snapshots'));
    }
}
