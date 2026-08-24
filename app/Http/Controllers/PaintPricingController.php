<?php

namespace App\Http\Controllers;

use App\Models\BoxPacking;
use App\Models\Brand;
use App\Models\Company;
use App\Models\PaintPriceAdjustment;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ShadeCard;
use App\Services\PaintPricingService;
use Auth;
use Illuminate\Http\Request;

class PaintPricingController extends Controller
{
    protected PaintPricingService $pricingService;

    public function __construct(PaintPricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Display Paint Family Pricing Dashboard (Seller Web Portal).
     */
    public function index(Request $request)
    {
        // Load categories
        $data['categories'] = \App\Models\Category::where(function($q) {
            $q->where('status', '1')
              ->orWhere('status', 'Active')
              ->orWhere('status', 'active');
        })->orderBy('name', 'asc')->get();

        // Load seller's products (supporting both '1' and 'Active' status values)
        $data['products'] = Product::where('user_id', $userId)
            ->where(function($q) {
                $q->where('status', '1')
                  ->orWhere('status', 'Active')
                  ->orWhere('status', 'active');
            })
            ->orderBy('name', 'asc')
            ->get();

        $data['company'] = Company::where('user_id', $userId)->first();
        $data['selectedProductId'] = $request->query('product_id', $data['products']->first()->id ?? null);
        $data['selectedCategoryId'] = $request->query('category_id', null);

        if ($data['selectedProductId']) {
            $data['selectedProduct'] = Product::find($data['selectedProductId']);
            if ($data['selectedProduct']) {
                $data['shades'] = ShadeCard::where('category_id', $data['selectedProduct']->category_id)->get();
                $data['packings'] = BoxPacking::where(function($q) {
                    $q->where('status', '1')
                      ->orWhere('status', 'Active')
                      ->orWhere('status', 'active');
                })->get();
                $data['skus'] = ProductAttributes::where('product_id', $data['selectedProductId'])->get();
                $data['adjustments'] = PaintPriceAdjustment::where('product_id', $data['selectedProductId'])
                    ->orderBy('id', 'desc')
                    ->limit(10)
                    ->get();
            }
        }

        return view('product.paint_pricing', $data);
    }

    /**
     * Get Paint Family Pricing Data for API or AJAX.
     */
    public function getFamilyPricingData($id)
    {
        $product = Product::findOrFail($id);
        $commissionRate = $this->pricingService->getEffectiveCommissionRate($product->user_id, $id);

        $skus = ProductAttributes::where('product_id', $id)->get();
        $shades = ShadeCard::where('category_id', $product->category_id)->get();
        $packings = BoxPacking::where(function($q) {
            $q->where('status', '1')
              ->orWhere('status', 'Active')
              ->orWhere('status', 'active');
        })->get();

        $skuMatrix = [];
        foreach ($skus as $sku) {
            $shade = $shades->firstWhere('id', $sku->color);
            $packing = $packings->firstWhere('id', $sku->quantity);
            $packLitres = $sku->pack_litres && (float)$sku->pack_litres > 0
                ? (float)$sku->pack_litres
                : $this->pricingService->parsePackLitres($sku->quantity);

            $sellerPrice = $sku->seller_price !== null && (float)$sku->seller_price > 0
                ? (float)$sku->seller_price
                : ((float)$sku->price > 0 ? round((float)$sku->price / (1 + ($commissionRate / 100)), 2) : 0);

            $customerPrice = (float)$sku->price > 0
                ? (float)$sku->price
                : round($sellerPrice * (1 + ($commissionRate / 100)), 2);

            $skuMatrix[] = [
                'id'                 => $sku->id,
                'shade_id'           => $sku->color,
                'shade_name'         => $shade ? $shade->name : ('Shade #' . $sku->color),
                'hexcode'            => $shade ? $shade->hexcode : '#CCCCCC',
                'packing_id'         => $sku->quantity,
                'packing_name'       => $packing ? $packing->name : ('Pack #' . $sku->quantity),
                'pack_litres'        => $packLitres,
                'seller_price'       => $sellerPrice,
                'customer_price'     => $customerPrice,
                'oldprice'           => (float)$sku->oldprice,
                'commission_rate'    => $commissionRate,
            ];
        }

        return response()->json([
            'status'          => true,
            'code'            => 200,
            'product'         => [
                'id'          => $product->id,
                'name'        => $product->name,
                'brand_id'    => $product->brand_id,
                'category_id' => $product->category_id,
            ],
            'commission_rate' => $commissionRate,
            'shades'          => $shades,
            'packings'        => $packings,
            'skus'            => $skuMatrix,
        ]);
    }

    /**
     * Preview proposed price adjustments without saving.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'adjustment_type'  => 'required|in:per_litre,per_liter,percentage,fixed',
            'adjustment_value' => 'required|numeric',
            'scope_type'       => 'nullable|in:family,shades,packings,skus',
            'shades'           => 'nullable|array',
            'packings'         => 'nullable|array',
            'skus'             => 'nullable|array',
        ]);

        $scope = [
            'type'     => $request->input('scope_type', 'family'),
            'shades'   => $request->input('shades', []),
            'packings' => $request->input('packings', []),
            'skus'     => $request->input('skus', []),
        ];

        $preview = $this->pricingService->calculatePreview(
            (int)$request->product_id,
            (string)$request->adjustment_type,
            (float)$request->adjustment_value,
            $scope
        );

        return response()->json([
            'status' => true,
            'code'   => 200,
            'data'   => $preview,
        ]);
    }

    /**
     * Confirm and apply bulk price adjustment atomically.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'adjustment_type'  => 'required|in:per_litre,per_liter,percentage,fixed',
            'adjustment_value' => 'required|numeric',
            'scope_type'       => 'nullable|in:family,shades,packings,skus',
            'shades'           => 'nullable|array',
            'packings'         => 'nullable|array',
            'skus'             => 'nullable|array',
        ]);

        $userId = Auth::id() ?? 1;

        $scope = [
            'type'     => $request->input('scope_type', 'family'),
            'shades'   => $request->input('shades', []),
            'packings' => $request->input('packings', []),
            'skus'     => $request->input('skus', []),
        ];

        $result = $this->pricingService->applyAdjustment(
            (int)$request->product_id,
            (string)$request->adjustment_type,
            (float)$request->adjustment_value,
            $scope,
            $userId
        );

        if ($request->ajax() || $request->wantsJson() || $request->isJson() || $request->expectsJson() || $request->is('api/*') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'status'  => $result['success'],
                'code'    => $result['success'] ? 200 : 400,
                'message' => $result['message'],
                'data'    => $result,
            ]);
        }

        return redirect()
            ->route('seller.paint-pricing.index', ['product_id' => $request->product_id])
            ->with('success', $result['message']);
    }

    /**
     * Individual SKU manual price override.
     */
    public function updateSingleSku(Request $request)
    {
        $request->validate([
            'sku_id'       => 'required|exists:product_attributes,id',
            'seller_price' => 'nullable|numeric|min:0',
            'price'        => 'nullable|numeric|min:0',
        ]);

        $sku = ProductAttributes::findOrFail($request->sku_id);
        $product = Product::findOrFail($sku->product_id);
        $commissionRate = $this->pricingService->getEffectiveCommissionRate($product->user_id, $product->id);

        if ($request->filled('seller_price')) {
            $sellerPrice = (float)$request->seller_price;
            $customerPrice = round($sellerPrice * (1 + ($commissionRate / 100)), 2);
        } elseif ($request->filled('price')) {
            $customerPrice = (float)$request->price;
            $sellerPrice = round($customerPrice / (1 + ($commissionRate / 100)), 2);
        } else {
            return response()->json(['status' => false, 'message' => 'Please provide a price.'], 422);
        }

        $oldCustomerPrice = $sku->price;

        $sku->update([
            'oldprice'        => $oldCustomerPrice,
            'seller_price'    => $sellerPrice,
            'price'           => $customerPrice,
            'commission_rate' => $commissionRate,
        ]);

        return response()->json([
            'status'         => true,
            'code'           => 200,
            'message'        => 'SKU price updated successfully.',
            'sku_id'         => $sku->id,
            'seller_price'   => $sellerPrice,
            'customer_price' => $customerPrice,
        ]);
    }

    /**
     * Audit log history for a product family.
     */
    public function auditHistory($productId)
    {
        $adjustments = PaintPriceAdjustment::where('product_id', $productId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'code'   => 200,
            'data'   => $adjustments,
        ]);
    }

    /**
     * Preview proposed category-wise price adjustments across all products.
     */
    public function categoryPreview(Request $request)
    {
        $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'adjustment_type'  => 'required|in:per_litre,per_liter,percentage,fixed',
            'adjustment_value' => 'required|numeric',
        ]);

        $userId = Auth::id() ?? 1;
        $preview = $this->pricingService->calculateCategoryPreview(
            (int)$request->category_id,
            (string)$request->adjustment_type,
            (float)$request->adjustment_value,
            $userId
        );

        return response()->json([
            'status' => true,
            'code'   => 200,
            'data'   => $preview,
        ]);
    }

    /**
     * Confirm and apply category-wise price adjustment atomically across all products.
     */
    public function categoryApply(Request $request)
    {
        $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'adjustment_type'  => 'required|in:per_litre,per_liter,percentage,fixed',
            'adjustment_value' => 'required|numeric',
        ]);

        $userId = Auth::id() ?? 1;
        $result = $this->pricingService->applyCategoryAdjustment(
            (int)$request->category_id,
            (string)$request->adjustment_type,
            (float)$request->adjustment_value,
            $userId
        );

        return response()->json([
            'status'  => $result['success'],
            'code'    => $result['success'] ? 200 : 400,
            'message' => $result['message'],
            'data'    => $result,
        ]);
    }
}
