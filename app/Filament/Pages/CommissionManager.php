<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\MainCategory;
use App\Models\SubCatgory;
use Filament\Pages\Page;

class CommissionManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static string $view = 'filament.pages.commission-manager';

    protected static ?string $navigationGroup = 'Catalogue Management';

    protected static ?string $navigationLabel = 'Commission Manager';

    protected static ?string $title = 'Category Commission Hierarchy & Tree Manager';

    protected static ?int $navigationSort = 1;

    public function getViewData(): array
    {
        $mainCategories = MainCategory::orderBy('sequence', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $allCategories = Category::orderBy('sequence', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $allSubcategories = SubCatgory::orderBy('name', 'asc')->get();

        $subcategoriesByCat = $allSubcategories->groupBy(function ($item) {
            return $item->category_id ?: $item->cid;
        });

        $categoriesByMain = $allCategories->groupBy(function ($item) {
            return $item->maincategory_id ?: $item->mid;
        });

        foreach ($mainCategories as $mainCat) {
            $cats = $categoriesByMain->get($mainCat->id, collect());
            foreach ($cats as $cat) {
                $cat->nested_subcategories = $subcategoriesByCat->get($cat->id, collect());
            }
            $mainCat->child_categories = $cats;
        }

        $totalSubcategories = $allSubcategories->count();
        $totalCategories = $allCategories->count();
        $configuredOverrides = $allCategories->whereNotNull('commission_rate')->count() 
            + $allSubcategories->whereNotNull('commission_rate')->count();

        return [
            'mainCategories'      => $mainCategories,
            'totalCategories'     => $totalCategories,
            'totalSubcategories'  => $totalSubcategories,
            'configuredOverrides' => $configuredOverrides,
        ];
    }
}
