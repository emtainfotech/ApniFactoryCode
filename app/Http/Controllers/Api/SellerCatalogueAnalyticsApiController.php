<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SellerCatalogueAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerCatalogueAnalyticsApiController extends Controller
{
    public function __construct(
        private SellerCatalogueAnalyticsService $analyticsService
    ) {
    }

    private function resolveAuthorizedSeller(Request $request): array
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $company = $user->resolveSellerCompany();
        if (!$company) {
            abort(403, 'No seller company account associated with authenticated user.');
        }

        if (!$user->isAdmin() && !$user->hasSellerPermission('products.view')) {
            abort(403, 'Permission denied: products.view required to view catalogue analytics.');
        }

        return [$user, $company];
    }

    /**
     * GET /api/v2/seller/catalogue/analytics/dashboard
     * Full aggregated dashboard payload.
     */
    public function dashboard(Request $request): JsonResponse
    {
        [$user, $company] = $this->resolveAuthorizedSeller($request);

        $period = $request->query('period', 'last_30_days');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $data = $this->analyticsService->getDashboardPayload($company, $period, $dateFrom, $dateTo);

        return response()->json([
            'status'  => true,
            'message' => 'Seller catalogue analytics retrieved successfully.',
            'data'    => $data,
        ], 200);
    }

    /**
     * GET /api/v2/seller/catalogue/analytics/summary
     * Catalogue overview cards summary.
     */
    public function summary(Request $request): JsonResponse
    {
        [$user, $company] = $this->resolveAuthorizedSeller($request);

        $summary = $this->analyticsService->getCatalogueSummary($company);

        return response()->json([
            'status' => true,
            'data'   => $summary,
        ], 200);
    }

    /**
     * GET /api/v2/seller/catalogue/analytics/status-distribution
     * Product status and approval distribution.
     */
    public function statusDistribution(Request $request): JsonResponse
    {
        [$user, $company] = $this->resolveAuthorizedSeller($request);

        $distribution = $this->analyticsService->getStatusDistribution($company);

        return response()->json([
            'status' => true,
            'data'   => $distribution,
        ], 200);
    }

    /**
     * GET /api/v2/seller/catalogue/analytics/health
     * Catalogue health & completeness insights.
     */
    public function health(Request $request): JsonResponse
    {
        [$user, $company] = $this->resolveAuthorizedSeller($request);

        $health = $this->analyticsService->getCatalogueHealth($company);

        return response()->json([
            'status' => true,
            'data'   => $health,
        ], 200);
    }

    /**
     * GET /api/v2/seller/catalogue/analytics/approval-performance
     * Turnaround time and approval metrics.
     */
    public function approvalPerformance(Request $request): JsonResponse
    {
        [$user, $company] = $this->resolveAuthorizedSeller($request);

        $period = $request->query('period', 'last_30_days');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $dateRange = $this->analyticsService->parseDateRange($period, $dateFrom, $dateTo);

        $approval = $this->analyticsService->getApprovalPerformance(
            $company,
            $dateRange['start_date'],
            $dateRange['end_date']
        );

        return response()->json([
            'status'     => true,
            'date_range' => [
                'period'     => $dateRange['period'],
                'label'      => $dateRange['label'],
                'start_date' => $dateRange['start_date']?->toIso8601String(),
                'end_date'   => $dateRange['end_date']?->toIso8601String(),
            ],
            'data'       => $approval,
        ], 200);
    }

    /**
     * GET /api/v2/seller/catalogue/analytics/product-performance
     * Commercial performance & tracked/untracked metrics.
     */
    public function productPerformance(Request $request): JsonResponse
    {
        [$user, $company] = $this->resolveAuthorizedSeller($request);

        $period = $request->query('period', 'last_30_days');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $dateRange = $this->analyticsService->parseDateRange($period, $dateFrom, $dateTo);

        $performance = $this->analyticsService->getProductPerformance(
            $company,
            $dateRange['start_date'],
            $dateRange['end_date']
        );

        return response()->json([
            'status'     => true,
            'date_range' => [
                'period'     => $dateRange['period'],
                'label'      => $dateRange['label'],
                'start_date' => $dateRange['start_date']?->toIso8601String(),
                'end_date'   => $dateRange['end_date']?->toIso8601String(),
            ],
            'data'       => $performance,
        ], 200);
    }

    /**
     * GET /api/v2/seller/catalogue/analytics/categories-brands
     * Category and brand product distribution.
     */
    public function categoryBrandInsights(Request $request): JsonResponse
    {
        [$user, $company] = $this->resolveAuthorizedSeller($request);

        return response()->json([
            'status' => true,
            'data'   => [
                'categories' => $this->analyticsService->getCategoryInsights($company),
                'brands'     => $this->analyticsService->getBrandInsights($company),
            ],
        ], 200);
    }
}

