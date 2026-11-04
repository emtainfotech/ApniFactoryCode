<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\SubCatgory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class CatalogueOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        $totalProducts = Product::count();
        $activeProducts = Product::whereIn('status', ['Active', 'active', '1'])->count();
        $inactiveProducts = $totalProducts - $activeProducts;

        $totalVariants = ProductVariant::count();
        $totalSkus = SellerListing::count();
        $activeSkus = SellerListing::where('status', 'Active')->count();
        $inStockSkus = SellerListing::where('status', 'Active')->where('stock_quantity', '>', 0)->count();
        $outOfStockSkus = SellerListing::where('status', 'Active')->where('stock_quantity', '<=', 0)->count();

        return [
            Card::make('Total Categories', Category::count())
                ->description(SubCatgory::count() . ' Subcategories')
                ->descriptionIcon('heroicon-s-folder')
                ->color('primary'),

            Card::make('Total Brands', Brand::count())
                ->description('Verified Brands across Categories')
                ->descriptionIcon('heroicon-s-badge-check')
                ->color('success'),

            Card::make('Total Products', $totalProducts)
                ->description("{$activeProducts} Active | {$inactiveProducts} Inactive")
                ->descriptionIcon('heroicon-s-cube')
                ->color($inactiveProducts > 0 ? 'warning' : 'success'),

            Card::make('Product Variants', $totalVariants)
                ->description('Configured Attribute Combinations')
                ->descriptionIcon('heroicon-s-color-swatch')
                ->color('primary'),

            Card::make('Seller SKU Listings', $totalSkus)
                ->description("{$activeSkus} Active Offers")
                ->descriptionIcon('heroicon-s-tag')
                ->color('primary'),

            Card::make('Inventory Status', "{$inStockSkus} In-Stock")
                ->description("{$outOfStockSkus} Out of Stock")
                ->descriptionIcon('heroicon-s-shopping-bag')
                ->color($outOfStockSkus > 0 ? 'danger' : 'success'),
        ];
    }
}
