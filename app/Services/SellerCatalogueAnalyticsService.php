<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReviewHistory;
use App\Models\ProductReviews;
use App\Models\ProductValidationResult;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SellerCatalogueAnalyticsService
{
    public function __construct(
        private SellerPermissionService $permissionService,
        private ProductCatalogueApprovalService $approvalService
    ) {
    }

    /**
     * Resolve date boundaries based on selected reporting period.
     */
    public function parseDateRange(string $period = 'last_30_days', ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                'period'     => 'today',
                'label'      => 'Today',
                'start_date' => $now->copy()->startOfDay(),
                'end_date'   => $now->copy()->endOfDay(),
            ],
            'last_7_days' => [
                'period'     => 'last_7_days',
                'label'      => 'Last 7 Days',
                'start_date' => $now->copy()->subDays(6)->startOfDay(),
                'end_date'   => $now->copy()->endOfDay(),
            ],
            'last_90_days' => [
                'period'     => 'last_90_days',
                'label'      => 'Last 90 Days',
                'start_date' => $now->copy()->subDays(89)->startOfDay(),
                'end_date'   => $now->copy()->endOfDay(),
            ],
            'custom' => [
                'period'     => 'custom',
                'label'      => ($dateFrom && $dateTo) ? "Custom ({$dateFrom} to {$dateTo})" : 'Custom Range',
                'start_date' => $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->subDays(29)->startOfDay(),
                'end_date'   => $dateTo ? Carbon::parse($dateTo)->endOfDay() : $now->copy()->endOfDay(),
            ],
            'all_time' => [
                'period'     => 'all_time',
                'label'      => 'All Time',
                'start_date' => null,
                'end_date'   => null,
            ],
            default => [ // last_30_days
                'period'     => 'last_30_days',
                'label'      => 'Last 30 Days',
                'start_date' => $now->copy()->subDays(29)->startOfDay(),
                'end_date'   => $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * Complete dashboard aggregated payload with date filtering.
     */
    public function getDashboardPayload(
        Company $company,
        string $period = 'last_30_days',
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $dateRange = $this->parseDateRange($period, $dateFrom, $dateTo);

        return [
            'company'              => [
                'id'       => $company->id,
                'name'     => $company->name,
                'user_id'  => $company->user_id,
            ],
            'date_range'           => [
                'period'     => $dateRange['period'],
                'label'      => $dateRange['label'],
                'start_date' => $dateRange['start_date']?->toIso8601String(),
                'end_date'   => $dateRange['end_date']?->toIso8601String(),
            ],
            'summary'              => $this->getCatalogueSummary($company),
            'status_distribution' => $this->getStatusDistribution($company),
            'catalogue_health'     => $this->getCatalogueHealth($company),
            'approval_performance' => $this->getApprovalPerformance($company, $dateRange['start_date'], $dateRange['end_date']),
            'product_performance'  => $this->getProductPerformance($company, $dateRange['start_date'], $dateRange['end_date']),
            'category_insights'    => $this->getCategoryInsights($company),
            'brand_insights'       => $this->getBrandInsights($company),
            'generated_at'         => now()->toIso8601String(),
        ];
    }

    /**
     * 1. Catalogue Overview Cards (Current State)
     * Defines clear, accurate counting rules without double-counting variants.
     */
    public function getCatalogueSummary(Company $company): array
    {
        $cacheKey = "seller_catalogue_summary_{$company->id}";

        return Cache::remember($cacheKey, 60, function () use ($company) {
            $productQuery = Product::where('user_id', $company->user_id);

            // Avoid double counting variants as parent products
            $totalProducts = (clone $productQuery)->count();
            $activeProducts = (clone $productQuery)->whereIn('status', ['Active', '1', 1])->count();
            $inactiveProducts = (clone $productQuery)->whereIn('status', ['Deactive', '0', 0])->count();

            // Approval status breakdown
            $draftProducts = (clone $productQuery)->where('approval_status', 'draft')->count();
            $pendingApprovalProducts = (clone $productQuery)->whereIn('approval_status', ['pending_review', 'resubmitted'])->count();
            $approvedProducts = (clone $productQuery)->where('approval_status', 'approved')->count();
            $rejectedProducts = (clone $productQuery)->where('approval_status', 'rejected')->count();
            $correctionsRequiredProducts = (clone $productQuery)->where('approval_status', 'corrections_requested')->count();

            // Live on marketplace requires BOTH status = Active AND approval_status = approved
            $liveMarketplaceProducts = (clone $productQuery)
                ->where('approval_status', 'approved')
                ->whereIn('status', ['Active', '1', 1])
                ->count();

            // Variants & Listings
            $totalVariants = ProductVariant::whereHas('product', fn($q) => $q->where('user_id', $company->user_id))->count();
            $totalSellerSkus = SellerListing::where('company_id', $company->id)->count();
            $activeSellerSkus = SellerListing::where('company_id', $company->id)->where('status', 'Active')->count();
            $inactiveSellerSkus = SellerListing::where('company_id', $company->id)->whereIn('status', ['Deactive', 'Inactive'])->count();
            $archivedSellerSkus = SellerListing::where('company_id', $company->id)->where('status', 'Archived')->count();

            return [
                'total_products'                 => $totalProducts,
                'active_products'                => $activeProducts,
                'inactive_products'              => $inactiveProducts,
                'draft_products'                 => $draftProducts,
                'pending_approval_products'      => $pendingApprovalProducts,
                'approved_products'              => $approvedProducts,
                'rejected_products'              => $rejectedProducts,
                'corrections_required_products'  => $correctionsRequiredProducts,
                'live_marketplace_products'      => $liveMarketplaceProducts,
                'total_variants'                 => $totalVariants,
                'total_seller_skus'              => $totalSellerSkus,
                'active_seller_skus'             => $activeSellerSkus,
                'inactive_seller_skus'           => $inactiveSellerSkus,
                'archived_seller_skus'           => $archivedSellerSkus,
            ];
        });
    }

    /**
     * 2. Catalogue Status Analytics (Reconciles with summary cards)
     */
    public function getStatusDistribution(Company $company): array
    {
        $summary = $this->getCatalogueSummary($company);
        $total = max(1, $summary['total_products']);

        return [
            'approval_distribution' => [
                'draft'                 => $summary['draft_products'],
                'pending_review'        => $summary['pending_approval_products'],
                'approved'              => $summary['approved_products'],
                'rejected'              => $summary['rejected_products'],
                'corrections_requested' => $summary['corrections_required_products'],
            ],
            'approval_percentages'  => [
                'draft'                 => round(($summary['draft_products'] / $total) * 100, 1),
                'pending_review'        => round(($summary['pending_approval_products'] / $total) * 100, 1),
                'approved'              => round(($summary['approved_products'] / $total) * 100, 1),
                'rejected'              => round(($summary['rejected_products'] / $total) * 100, 1),
                'corrections_requested' => round(($summary['corrections_required_products'] / $total) * 100, 1),
            ],
            'active_vs_inactive'    => [
                'active'   => $summary['active_products'],
                'inactive' => $summary['inactive_products'],
            ],
            'reconciled_total'      => $summary['total_products'],
        ];
    }

    /**
     * 3. Catalogue Health & Completeness Insights
     * Reuses Phase 2.5 validation logic and rules.
     */
    public function getCatalogueHealth(Company $company): array
    {
        $products = Product::where('user_id', $company->user_id)
            ->with(['attributeValues', 'latestValidationResult', 'variants', 'listings'])
            ->get();

        $totalAssessed = $products->count();

        if ($totalAssessed === 0) {
            return [
                'total_assessed'                   => 0,
                'health_score'                     => 100,
                'missing_images_count'             => 0,
                'missing_descriptions_count'       => 0,
                'missing_required_attributes_count'=> 0,
                'complete_mandatory_attributes'    => 0,
                'products_with_validation_errors'  => 0,
                'products_with_warnings'           => 0,
                'products_awaiting_corrections'    => 0,
                'products_not_eligible_submission' => 0,
                'healthy_products_count'           => 0,
                'needs_attention_count'            => 0,
            ];
        }

        $missingImagesCount             = 0;
        $missingDescriptionsCount       = 0;
        $missingRequiredAttributesCount = 0;
        $completeMandatoryAttributes    = 0;
        $productsWithErrors             = 0;
        $productsWithWarnings           = 0;
        $notEligibleForSubmission       = 0;

        // Preload required category attribute mappings
        $requiredCategoryAttrs = CategoryAttribute::where('is_required', true)
            ->get()
            ->groupBy('category_id');

        foreach ($products as $p) {
            // Check media
            $hasImage = !empty($p->image) || !empty($p->multipleimages);
            if (!$hasImage) {
                $missingImagesCount++;
            }

            // Check description
            $hasDesc = !empty(trim($p->description ?? '')) || !empty(trim($p->title ?? ''));
            if (!$hasDesc) {
                $missingDescriptionsCount++;
            }

            // Check dynamic category attributes
            $reqMappings = $requiredCategoryAttrs->get($p->category_id, collect());
            if ($p->subcategory_id) {
                $reqMappings = $reqMappings->merge($requiredCategoryAttrs->get($p->subcategory_id, collect()));
            }

            $attrMap = $p->attributeValues->keyBy('attribute_id');
            $hasMissingAttr = false;

            foreach ($reqMappings as $mapping) {
                $valRecord = $attrMap->get($mapping->attribute_id);
                $hasVal = $valRecord && (
                    !is_null($valRecord->attribute_option_id) ||
                    !empty($valRecord->text_value) ||
                    !is_null($valRecord->number_value) ||
                    !is_null($valRecord->boolean_value)
                );
                if (!$hasVal) {
                    $hasMissingAttr = true;
                    break;
                }
            }

            if ($hasMissingAttr) {
                $missingRequiredAttributesCount++;
            } else {
                $completeMandatoryAttributes++;
            }

            // Validation results evaluation
            $latestVal = $p->latestValidationResult;
            if ($latestVal) {
                if ($latestVal->error_count > 0 || $latestVal->status === 'failed') {
                    $productsWithErrors++;
                }
                if ($latestVal->warning_count > 0) {
                    $productsWithWarnings++;
                }
            }

            // Submission eligibility
            if (!$p->canBeSubmitted() || ($latestVal && $latestVal->hasBlockingErrors())) {
                $notEligibleForSubmission++;
            }
        }

        $awaitingCorrections = $products->where('approval_status', 'corrections_requested')->count();
        $healthyProductsCount = $products->where('approval_status', 'approved')->whereIn('status', ['Active', '1', 1])->count();
        $needsAttentionCount = $missingImagesCount + $missingRequiredAttributesCount + $productsWithErrors + $awaitingCorrections;

        // Health Score calculation (0 to 100%)
        $positivePoints = ($completeMandatoryAttributes * 30)
            + (($totalAssessed - $missingImagesCount) * 30)
            + (($totalAssessed - $missingDescriptionsCount) * 20)
            + (($totalAssessed - $productsWithErrors) * 20);
        $maxPoints = $totalAssessed * 100;
        $healthScore = round(($positivePoints / max(1, $maxPoints)) * 100, 1);

        return [
            'total_assessed'                   => $totalAssessed,
            'health_score'                     => min(100, max(0, $healthScore)),
            'missing_images_count'             => $missingImagesCount,
            'missing_descriptions_count'       => $missingDescriptionsCount,
            'missing_required_attributes_count'=> $missingRequiredAttributesCount,
            'complete_mandatory_attributes'    => $completeMandatoryAttributes,
            'products_with_validation_errors'  => $productsWithErrors,
            'products_with_warnings'           => $productsWithWarnings,
            'products_awaiting_corrections'    => $awaitingCorrections,
            'products_not_eligible_submission' => $notEligibleForSubmission,
            'healthy_products_count'           => $healthyProductsCount,
            'needs_attention_count'            => $needsAttentionCount,
        ];
    }

    /**
     * 4. Approval Performance Analytics (Turnaround, attempts, history trends)
     */
    public function getApprovalPerformance(Company $company, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $historyQuery = ProductReviewHistory::where('company_id', $company->id);

        if ($startDate && $endDate) {
            $historyQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $events = (clone $historyQuery)->get();

        $submittedCount          = $events->where('action', 'submitted')->count();
        $resubmittedCount        = $events->where('action', 'resubmitted')->count();
        $approvedCount           = $events->where('action', 'approved')->count();
        $rejectedCount           = $events->where('action', 'rejected')->count();
        $correctionsReqCount     = $events->where('action', 'corrections_requested')->count();

        // Currently in queue awaiting admin review
        $awaitingReviewCount = Product::where('user_id', $company->user_id)
            ->whereIn('approval_status', ['pending_review', 'resubmitted'])
            ->count();

        // Turnaround Time Calculation (hours between submitted_at and reviewed_at for reviewed products)
        $reviewedProductsQuery = Product::where('user_id', $company->user_id)
            ->whereNotNull('submitted_at')
            ->whereNotNull('reviewed_at');

        if ($startDate && $endDate) {
            $reviewedProductsQuery->whereBetween('reviewed_at', [$startDate, $endDate]);
        }

        $reviewedProducts = $reviewedProductsQuery->get();
        $totalHours = 0;
        $validTurnaroundCount = 0;

        foreach ($reviewedProducts as $rp) {
            if ($rp->reviewed_at >= $rp->submitted_at) {
                $hours = $rp->submitted_at->diffInHours($rp->reviewed_at, true);
                $totalHours += $hours;
                $validTurnaroundCount++;
            }
        }

        $avgTurnaroundHours = $validTurnaroundCount > 0 ? round($totalHours / $validTurnaroundCount, 1) : null;
        $avgTurnaroundFormatted = $avgTurnaroundHours !== null
            ? ($avgTurnaroundHours >= 24 ? round($avgTurnaroundHours / 24, 1) . ' days' : "{$avgTurnaroundHours} hrs")
            : 'No data';

        // Trend over time (submissions vs approvals grouped by date)
        $trends = [];
        if ($startDate && $endDate && $startDate->diffInDays($endDate) <= 90) {
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dayStr = $current->format('Y-m-d');
                $trends[$dayStr] = [
                    'date'        => $dayStr,
                    'day_label'   => $current->format('d M'),
                    'submissions' => 0,
                    'approvals'   => 0,
                    'rejections'  => 0,
                ];
                $current->addDay();
            }

            foreach ($events as $ev) {
                $d = $ev->created_at?->format('Y-m-d');
                if ($d && isset($trends[$d])) {
                    if (in_array($ev->action, ['submitted', 'resubmitted'])) {
                        $trends[$d]['submissions']++;
                    } elseif ($ev->action === 'approved') {
                        $trends[$d]['approvals']++;
                    } elseif ($ev->action === 'rejected') {
                        $trends[$d]['rejections']++;
                    }
                }
            }
        }

        return [
            'products_submitted'             => $submittedCount + $resubmittedCount,
            'initial_submissions'            => $submittedCount,
            'resubmissions'                  => $resubmittedCount,
            'products_approved'              => $approvedCount,
            'products_rejected'              => $rejectedCount,
            'products_returned_corrections'  => $correctionsReqCount,
            'products_awaiting_review'       => $awaitingReviewCount,
            'average_turnaround_hours'       => $avgTurnaroundHours,
            'average_turnaround_formatted'   => $avgTurnaroundFormatted,
            'has_turnaround_data'            => $validTurnaroundCount > 0,
            'review_trends'                  => array_values($trends),
        ];
    }

    /**
     * 5. Product Commercial & Transactional Performance Analytics
     * Uses actual recorded orders, sales, units sold, and carts.
     * Documents unavailable metrics explicitly (does not fabricate or show false zeros).
     */
    public function getProductPerformance(Company $company, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $ordersQuery = Order::where('user_id', $company->user_id);

        if ($startDate && $endDate) {
            $ordersQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = (clone $ordersQuery)->get();
        $totalOrders = $orders->count();
        $totalRevenue = (float)$orders->sum('grandtotal') ?: (float)$orders->sum('netamount');

        // Total units sold from orderdetail table linked to seller orders
        $orderNos = $orders->pluck('orderno')->filter()->all();
        $unitsSold = 0;

        if (!empty($orderNos)) {
            $orderDetails = DB::table('orderdetail')
                ->whereIn('orderno', $orderNos)
                ->get();

            foreach ($orderDetails as $item) {
                if (!empty($item->attribute)) {
                    $attrData = is_string($item->attribute) ? json_decode($item->attribute, true) : $item->attribute;
                    $qty = (int)($attrData['qty'] ?? $attrData['quantity'] ?? 1);
                    $unitsSold += max(1, $qty);
                } else {
                    $unitsSold += 1;
                }
            }
        }

        // Active cart items (customer interest)
        $activeCartItems = DB::table('cart')
            ->where('company_id', $company->id)
            ->count();

        // Product reviews & average rating
        $reviewsQuery = ProductReviews::whereHas('product', fn($q) => $q->where('user_id', $company->user_id));
        $totalReviews = $reviewsQuery->count();
        $avgRating = $totalReviews > 0 ? round((float)$reviewsQuery->avg('rating'), 1) : null;

        return [
            // Tracked transactional metrics
            'total_orders'       => $totalOrders,
            'units_sold'         => $unitsSold,
            'sales_value'        => round($totalRevenue, 2),
            'currency'           => 'INR',
            'currency_symbol'    => '₹',
            'active_cart_count'  => $activeCartItems,
            'product_reviews'    => [
                'total_reviews'  => $totalReviews,
                'average_rating' => $avgRating,
            ],

            // Untracked metrics (explicitly documented as unavailable per enterprise requirements)
            'product_impressions' => [
                'value'        => null,
                'is_available' => false,
                'status'       => 'unavailable',
                'description'  => 'Impression logging is not enabled in the current marketplace version.',
            ],
            'product_views' => [
                'value'        => null,
                'is_available' => false,
                'status'       => 'unavailable',
                'description'  => 'Product detail click/view tracking is not enabled in the current marketplace version.',
            ],
            'product_enquiries' => [
                'value'        => null,
                'is_available' => false,
                'status'       => 'unavailable',
                'description'  => 'Direct buyer catalogue enquiries are not tracked in the current marketplace version.',
            ],
        ];
    }

    /**
     * 6. Category Insights Breakdown
     */
    public function getCategoryInsights(Company $company): array
    {
        $products = Product::where('user_id', $company->user_id)
            ->with('category:id,name')
            ->get();

        $grouped = $products->groupBy('category_id');
        $insights = [];

        foreach ($grouped as $catId => $catProducts) {
            $catName = $catProducts->first()?->category?->name ?? 'Uncategorized';
            $total = $catProducts->count();
            $approved = $catProducts->where('approval_status', 'approved')->count();
            $pending = $catProducts->whereIn('approval_status', ['pending_review', 'resubmitted'])->count();
            $corrections = $catProducts->where('approval_status', 'corrections_requested')->count();
            $rejected = $catProducts->where('approval_status', 'rejected')->count();

            $insights[] = [
                'category_id'           => $catId ?: 0,
                'category_name'         => $catName,
                'total_products'        => $total,
                'approved_products'     => $approved,
                'pending_products'      => $pending,
                'corrections_requested' => $corrections,
                'rejected_products'     => $rejected,
                'filter_url_params'     => [
                    'category_id' => $catId,
                ],
            ];
        }

        // Sort descending by total products
        usort($insights, fn($a, $b) => $b['total_products'] <=> $a['total_products']);

        return $insights;
    }

    /**
     * 7. Brand Insights Breakdown
     */
    public function getBrandInsights(Company $company): array
    {
        $products = Product::where('user_id', $company->user_id)
            ->with('brand:id,name')
            ->get();

        $grouped = $products->groupBy('brand_id');
        $insights = [];

        foreach ($grouped as $brandId => $brandProducts) {
            $brandName = $brandProducts->first()?->brand?->name ?? 'No Brand';
            $total = $brandProducts->count();
            $approved = $brandProducts->where('approval_status', 'approved')->count();
            $pending = $brandProducts->whereIn('approval_status', ['pending_review', 'resubmitted'])->count();
            $corrections = $brandProducts->where('approval_status', 'corrections_requested')->count();
            $rejected = $brandProducts->where('approval_status', 'rejected')->count();

            $insights[] = [
                'brand_id'              => $brandId ?: 0,
                'brand_name'            => $brandName,
                'total_products'        => $total,
                'approved_products'     => $approved,
                'pending_products'      => $pending,
                'corrections_requested' => $corrections,
                'rejected_products'     => $rejected,
                'filter_url_params'     => [
                    'brand_id' => $brandId,
                ],
            ];
        }

        usort($insights, fn($a, $b) => $b['total_products'] <=> $a['total_products']);

        return $insights;
    }

    /**
     * Invalidate seller catalogue cache upon catalogue changes.
     */
    public function invalidateSellerCache(int $companyId): void
    {
        Cache::forget("seller_catalogue_summary_{$companyId}");
    }
}

