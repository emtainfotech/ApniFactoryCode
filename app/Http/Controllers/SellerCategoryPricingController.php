<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyAuditLog;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Services\PaintPricingService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerCategoryPricingController extends Controller
{
    public function __construct(private PaintPricingService $pricingService)
    {
        $this->middleware('auth');
    }

    private function getSellerId(): int
    {
        return Auth::id() ?? 1;
    }

    /**
     * Category Product Pricing & Offer Manager for Seller Web Portal.
     * Strictly scopes all products and pricing to the logged-in seller company.
     */
    public function index(Request $request)
    {
        $sellerId = $this->getSellerId();
        $company = Company::where('user_id', $sellerId)->first();

        // 1. Fetch only categories where this seller has existing products
        $sellerCategoryIds = Product::where('user_id', $sellerId)
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id');

        $categories = Category::whereIn('id', $sellerCategoryIds)
            ->orderBy('name', 'asc')
            ->get();

        $selectedCategoryId = $request->query('category_id');
        if (!$selectedCategoryId && $categories->isNotEmpty()) {
            $selectedCategoryId = $categories->first()->id;
        }

        $selectedCategory = $selectedCategoryId ? Category::find($selectedCategoryId) : null;
        $products = collect();
        $effectiveCommissionRate = 25.00;

        if ($selectedCategoryId) {
            $effectiveCommissionRate = $this->pricingService->getEffectiveCommissionRate(
                $sellerId,
                null,
                $selectedCategoryId,
                null,
                $selectedCategory?->maincategory_id
            );

            // Strictly fetch only this seller's products in this category
            $products = Product::where('user_id', $sellerId)
                ->where('category_id', $selectedCategoryId)
                ->with([
                    'brand',
                    'product_attributes' => function ($q) {
                        $q->with(['boxPacking', 'shadeCard'])->orderBy('quantity', 'asc');
                    }
                ])
                ->orderBy('name', 'asc')
                ->get();
        }

        $title = 'Category Pricing & Offers';

        return view('product.category_pricing', compact(
            'title',
            'company',
            'categories',
            'selectedCategoryId',
            'selectedCategory',
            'products',
            'effectiveCommissionRate'
        ));
    }

    /**
     * Update individual SKU Factory Price.
     * Automatically recalculates Display Price based on category commission rate.
     * Guaranteed to only affect this seller's own product SKU.
     */
    public function updateSku(Request $request)
    {
        $request->validate([
            'sku_id'       => 'required|integer',
            'seller_price' => 'required|numeric|min:0',
        ]);

        $sellerId = $this->getSellerId();
        $skuId = (int)$request->input('sku_id');
        $newFactoryPrice = round((float)$request->input('seller_price'), 2);

        // Security check: ensure SKU belongs to a product owned by THIS seller
        $sku = ProductAttributes::whereHas('product', function ($q) use ($sellerId) {
            $q->where('user_id', $sellerId);
        })->find($skuId);

        if (!$sku) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized or SKU not found in your inventory.',
            ], 403);
        }

        $product = Product::find($sku->product_id);
        $commissionRate = $this->pricingService->getEffectiveCommissionRate(
            $sellerId,
            $product->id,
            $product->category_id
        );

        // Calculate new display price
        $newDisplayPrice = round($newFactoryPrice * (1 + ($commissionRate / 100)), 2);
        $oldDisplayPrice = $sku->price;

        $sku->update([
            'oldprice'        => $oldDisplayPrice,
            'seller_price'    => $newFactoryPrice,
            'price'           => $newDisplayPrice,
            'commission_rate' => $commissionRate,
        ]);

        return response()->json([
            'status'            => true,
            'message'           => 'Factory price updated and display price recalculated successfully.',
            'sku_id'            => $sku->id,
            'seller_price'      => $newFactoryPrice,
            'display_price'     => $newDisplayPrice,
            'commission_rate'   => $commissionRate,
        ]);
    }

    /**
     * Apply bulk offer / price adjustment to Factory Price across a Category for THIS seller.
     * Recalculates all Display Prices automatically.
     */
    public function bulkApply(Request $request)
    {
        $request->validate([
            'category_id'      => 'required|integer',
            'adjustment_type'  => 'required|in:percentage,fixed_per_unit',
            'adjustment_value' => 'required|numeric',
        ]);

        $sellerId = $this->getSellerId();
        $categoryId = (int)$request->input('category_id');
        $adjType = $request->input('adjustment_type');
        $adjValue = (float)$request->input('adjustment_value');

        // Verify seller has products in this category
        $products = Product::where('user_id', $sellerId)
            ->where('category_id', $categoryId)
            ->with('product_attributes')
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No products found in this category for your seller account.',
            ], 404);
        }

        $effectiveCommissionRate = $this->pricingService->getEffectiveCommissionRate($sellerId, null, $categoryId);
        $updatedSkusCount = 0;

        DB::beginTransaction();
        try {
            foreach ($products as $prod) {
                foreach ($prod->product_attributes as $sku) {
                    $currentFactory = (float)($sku->seller_price ?: ($sku->price ? round($sku->price / (1 + ($effectiveCommissionRate / 100)), 2) : 0));
                    
                    if ($adjType === 'percentage') {
                        $newFactory = max(0, round($currentFactory * (1 + ($adjValue / 100)), 2));
                    } else {
                        // fixed per unit
                        $newFactory = max(0, round($currentFactory + $adjValue, 2));
                    }

                    $newDisplay = round($newFactory * (1 + ($effectiveCommissionRate / 100)), 2);

                    $sku->update([
                        'oldprice'        => $sku->price,
                        'seller_price'    => $newFactory,
                        'price'           => $newDisplay,
                        'commission_rate' => $effectiveCommissionRate,
                    ]);

                    $updatedSkusCount++;
                }
            }

            // Company audit log
            $company = Company::where('user_id', $sellerId)->first();
            if ($company) {
                CompanyAuditLog::logChange(
                    $company->id,
                    'category_offer_pricing',
                    "Category Pricing Offer Applied: Category #{$categoryId}",
                    "Applied {$adjType} offer ({$adjValue}) across {$updatedSkusCount} SKUs.",
                    [],
                    ['category_id' => $categoryId, 'updated_skus' => $updatedSkusCount],
                    $sellerId
                );
            }

            DB::commit();

            return response()->json([
                'status'         => true,
                'message'        => "Successfully updated factory prices and recalculated display prices for {$updatedSkusCount} SKUs in this category.",
                'updated_count'  => $updatedSkusCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Error applying category pricing: ' . $e->getMessage(),
            ], 500);
        }
    }
}

