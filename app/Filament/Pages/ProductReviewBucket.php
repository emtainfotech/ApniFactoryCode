<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\ProductReviewHistory;
use Filament\Pages\Page;

class ProductReviewBucket extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-check';

    protected static string $view = 'filament.pages.product-review-bucket';

    protected static ?string $navigationGroup = 'Catalogue Management';

    protected static ?string $navigationLabel = 'Review Bucket (GST)';

    protected static ?string $title = 'Display Price & GST Review Bucket';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = Product::where('has_gst_included', 1)
            ->whereIn('approval_status', ['pending_review', 'resubmitted'])
            ->whereHas('product_attributes', function ($q) {
                $q->where('is_gst_included', 1)->orWhere('is_gst_included', '1');
            })
            ->count();

        return $count > 0 ? "Pending : {$count}" : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function getViewData(): array
    {
        // 1. Pending Products with ONLY GST-included SKUs
        $pendingProducts = Product::where('has_gst_included', 1)
            ->whereIn('approval_status', ['pending_review', 'resubmitted'])
            ->whereHas('product_attributes', function ($q) {
                $q->where('is_gst_included', 1)->orWhere('is_gst_included', '1');
            })
            ->with([
                'brand',
                'category',
                'user.company',
                'product_attributes' => function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('is_gst_included', 1)->orWhere('is_gst_included', '1');
                    })->with(['boxPacking', 'shadeCard']);
                }
            ])
            ->orderBy('id', 'desc')
            ->get();

        // 2. Review History & Audit Logs
        $historyLogs = ProductReviewHistory::with(['product.brand', 'product.category', 'company', 'reviewer', 'seller'])
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        // 3. Approved / Reviewed Products that had GST review
        $approvedProducts = Product::where('approval_status', 'approved')
            ->where(function ($q) {
                $q->where('has_gst_included', 1)
                  ->orWhereNotNull('reviewed_at');
            })
            ->whereHas('product_attributes', function ($q) {
                $q->where('is_gst_included', 1)->orWhere('is_gst_included', '1');
            })
            ->with([
                'brand',
                'category',
                'user.company',
                'product_attributes' => function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('is_gst_included', 1)->orWhere('is_gst_included', '1');
                    })->with(['boxPacking', 'shadeCard']);
                }
            ])
            ->orderBy('reviewed_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get();

        return [
            'products'         => $pendingProducts,
            'pendingCount'     => $pendingProducts->count(),
            'historyLogs'      => $historyLogs,
            'approvedProducts' => $approvedProducts,
            'historyCount'     => $historyLogs->count(),
        ];
    }
}

