<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\SellerCatalogueAnalyticsService;
use App\Services\SellerPermissionService;
use Illuminate\Http\Request;

class SellerCatalogueAnalyticsWebController extends Controller
{
    public function __construct(
        private SellerCatalogueAnalyticsService $analyticsService,
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
     * Display the seller catalogue dashboard & performance analytics.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('products.view')) {
                abort(403, 'Permission denied: products.view required to view catalogue analytics.');
            }
        }

        $period = $request->input('period', 'last_30_days');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $analytics = $this->analyticsService->getDashboardPayload($company, $period, $dateFrom, $dateTo);

        return view('seller.catalogue.analytics', [
            'company'    => $company,
            'analytics'  => $analytics,
            'period'     => $period,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
        ]);
    }
}
