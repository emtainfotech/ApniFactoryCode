<?php

namespace App\Filament\Pages;

use App\Models\Product;
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
            ->count();

        return $count > 0 ? "Pending : {$count}" : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function getViewData(): array
    {
        $products = Product::where('has_gst_included', 1)
            ->whereIn('approval_status', ['pending_review', 'resubmitted'])
            ->with(['brand', 'category', 'user.company', 'product_attributes.boxPacking', 'product_attributes.shadeCard'])
            ->orderBy('id', 'desc')
            ->get();

        return [
            'products' => $products,
        ];
    }
}
