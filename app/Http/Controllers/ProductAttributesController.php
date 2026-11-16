<?php

namespace App\Http\Controllers;

use App\Models\ProductAttributes;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use DB;
class ProductAttributesController extends Controller
{
    
 public function updateattributeprice(Request $request){
     $id = $request->aid;
     $attr = ProductAttributes::find($id);
     if (!$attr) {
         return response()->json(['status' => false, 'message' => 'Variant not found'], 404);
     }

     $product = Product::find($attr->product_id);
     $pricingService = app(\App\Services\PaintPricingService::class);
     $commissionRate = $pricingService->getEffectiveCommissionRate(
         $product?->user_id ?? auth()->id(),
         null,
         $product?->category_id,
         null,
         $product?->maincategory_id
     );

     $rawSellerPrice = $request->input('seller_price');
     $rawDisplayPrice = $request->input('price');

     // If seller price wasn't passed directly, try price
     if (($rawSellerPrice === null || $rawSellerPrice === '') && ($rawDisplayPrice !== null && $rawDisplayPrice !== '')) {
         $rawSellerPrice = $rawDisplayPrice;
     }

     // Parse seller price: fallback to current variant price if input was empty
     $sellerPrice = ($rawSellerPrice !== null && $rawSellerPrice !== '') 
         ? (float)$rawSellerPrice 
         : (float)($attr->seller_price ?: $attr->price);

     // Calculate display price according to commission structure
     if ($rawDisplayPrice !== null && $rawDisplayPrice !== '' && (float)$rawDisplayPrice > 0) {
         $displayPrice = round((float)$rawDisplayPrice, 2);
     } else {
         $displayPrice = round($sellerPrice * (1 + ($commissionRate / 100)), 2);
     }

     $isGstIncluded = $request->boolean('is_gst_included') || $request->input('is_gst_included') == '1' || $request->input('is_gst_included') === 1 ? 1 : 0;
     $gstRate = $request->input('gst_rate') !== null ? (float)$request->input('gst_rate') : ($product->tax ?? 18.00);
     $mrp = $request->input('mrp', null);
     if ($mrp !== null && $mrp !== '') {
         $mrp = (float)$mrp;
     } else {
         $mrp = null;
     }

     $updateData = ['price' => $displayPrice];
     if (Schema::hasColumn('product_attributes', 'seller_price')) {
         $updateData['seller_price'] = $sellerPrice;
     }
     if (Schema::hasColumn('product_attributes', 'is_gst_included')) {
         $updateData['is_gst_included'] = $isGstIncluded;
     }
     if ($gstRate !== null && Schema::hasColumn('product_attributes', 'gst_rate')) {
         $updateData['gst_rate'] = $gstRate;
     }
     if (Schema::hasColumn('product_attributes', 'mrp')) {
         $updateData['mrp'] = $mrp;
     }
     if (!empty($mrp) && Schema::hasColumn('product_attributes', 'oldprice')) {
         $updateData['oldprice'] = $mrp;
     } elseif (Schema::hasColumn('product_attributes', 'oldprice')) {
         $updateData['oldprice'] = $displayPrice;
     }

     $attr->update($updateData);

     // Log to ProductReviewHistory
     if ($product) {
         \App\Models\ProductReviewHistory::create([
             'product_id'      => $product->id,
             'company_id'      => $product->user?->company?->id ?? 0,
             'seller_user_id'  => auth()->id(),
             'reviewer_id'     => auth()->user() && auth()->user()->isAdmin() ? auth()->id() : null,
             'action'          => 'variant_price_updated',
             'previous_status' => $product->approval_status,
             'new_status'      => $isGstIncluded ? 'pending_review' : $product->approval_status,
             'notes'           => "Updated variant SKU #{$id} (Pack: {$attr->quantity}, Shade: {$attr->color}). Factory: ₹{$sellerPrice}, Display: ₹{$displayPrice}" . ($isGstIncluded ? " [GST Incl. {$gstRate}%]" : ''),
             'metadata'        => $updateData,
         ]);
     }

     // If GST is included, route product to admin review bucket
     if ($isGstIncluded) {
         if ($product) {
             $pUpdate = [];
             if (Schema::hasColumn('products', 'has_gst_included')) {
                 $pUpdate['has_gst_included'] = 1;
             }
             if (Schema::hasColumn('products', 'approval_status')) {
                 $pUpdate['approval_status'] = 'pending_review';
             }
             if (Schema::hasColumn('products', 'status')) {
                 $pUpdate['status'] = 'Deactive';
             }
             if (Schema::hasColumn('products', 'submitted_at')) {
                 $pUpdate['submitted_at'] = now();
             }
             if (!empty($pUpdate)) {
                 $product->update($pUpdate);
             }
         }
     }

     return response()->json([
         'status' => true,
         'message' => 'Successfully Updated',
         'is_gst_included' => $isGstIncluded,
         'seller_price' => $sellerPrice,
         'price' => $displayPrice,
         'mrp' => $mrp
     ]);
 }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $color = $request->colorid;
        $packing = $request->packingid;
        $sellerPrice = $request->input('seller_price', $request->input('price'));
        $displayPrice = $request->input('price');
        $mrp = $request->input('mrp', $displayPrice);
        $proid = $request->productid;
        $isGstIncluded = $request->boolean('is_gst_included') || $request->input('is_gst_included') == '1' || $request->input('is_gst_included') === 1 ? 1 : 0;

        $pray = array(
            "product_id" => $proid,
            "color" => $color,
            "quantity" => $packing,
            "oldprice" => $mrp ?: $displayPrice,
            "price" => $displayPrice,
        );

        if (Schema::hasColumn('product_attributes', 'seller_price')) {
            $pray['seller_price'] = $sellerPrice;
        }
        if (Schema::hasColumn('product_attributes', 'is_gst_included')) {
            $pray['is_gst_included'] = $isGstIncluded;
        }
        if (Schema::hasColumn('product_attributes', 'mrp')) {
            $pray['mrp'] = $mrp;
        }

        ProductAttributes::insert($pray);

        // If GST is included, route product to admin review bucket
        if ($isGstIncluded) {
            $product = Product::find($proid);
            if ($product) {
                $pUpdate = [];
                if (Schema::hasColumn('products', 'has_gst_included')) {
                    $pUpdate['has_gst_included'] = 1;
                }
                if (Schema::hasColumn('products', 'approval_status')) {
                    $pUpdate['approval_status'] = 'pending_review';
                }
                if (Schema::hasColumn('products', 'status')) {
                    $pUpdate['status'] = 'Deactive';
                }
                if (Schema::hasColumn('products', 'submitted_at')) {
                    $pUpdate['submitted_at'] = now();
                }
                if (!empty($pUpdate)) {
                    $product->update($pUpdate);
                }
            }
        }

        return back()->with('success', 'Product attribute successfully added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductAttributes  $productAttributes
     * @return \Illuminate\Http\Response
     */
    public function show(ProductAttributes $productAttributes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductAttributes  $productAttributes
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductAttributes $productAttributes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductAttributes  $productAttributes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductAttributes $productAttributes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductAttributes  $productAttributes
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductAttributes $productAttributes)
    {
        //
    }
}
