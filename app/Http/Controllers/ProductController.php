<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductReviews;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Brand;
use App\Models\Category;
use Auth;
use DB;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use App\Helper\helper;
class ProductController extends Controller
{
 //  list / store destroy
  public function store(Request $request){ 
      $previousdata = json_decode($request->previousdata);
      $name = $previousdata->name;
    //   $slug = $previousdata->slug;
      $mid = $previousdata->maincategory;
      $cid = $previousdata->category;
      $description = $previousdata->details;
      $Brand = $previousdata->brand;
      $tax = $previousdata->tax;
      $hsncode = $previousdata->hsncode;
      $delimiter = '-';
      $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $name))))), $delimiter));
    $slug = $slug.'-'.rand(1000,999999);
      if($files=$request->file('images')){
        foreach($files as $key=>$file){
            $fname=$file->getClientOriginalName();
            $file->move('storage/app/public/product',$fname);
            $images[]='product/'.$fname;
            if($key==0){$img = 'product/'.$fname;}
            }
        }
        $proid = Product::orderBy('id','desc')->first();
        if(empty($proid)){$prid=1;}else{$prid=$proid->id+1;}
      $pary = array(
                    "maincategory_id"=>$mid,
                    "category_id"=>$cid,
                    "subcategory_id"=>0,
                    "name"=>$name,
                    "slug"=>$slug,
                    "title"=>$slug,
                    "description"=>$description,
                    "status"=>'1',
                    "brand_id"=>$Brand,
                    "product_id"=>$prid,
                    "user_id"=>Auth::user()->id,
                    "multipleimages"=>json_encode($images),
                    "image"=>$img,
                    "hsncode"=>$hsncode,
                    "tax"=>$tax
                    );
        $pid = Product::insertGetId($pary);
        
        $pricingService = app(\App\Services\PaintPricingService::class);
        $commissionRate = $pricingService->getEffectiveCommissionRate(Auth::id(), $pid);

        foreach($request->boxpacking as $key=>$bxpackg){
            $enteredPrice = (float)($request->price[$key] ?? 0);
            $packLitres = $pricingService->parsePackLitres($bxpackg);
            
            // Treat entered price as Seller Factory Base Price, calculate Customer Price with commission
            $sellerPrice = $enteredPrice;
            $customerPrice = round($sellerPrice * (1 + ($commissionRate / 100)), 2);

            $patbu = array(
                            "product_id"      => $pid,
                            "color"           => $request->color[$key],
                            "quantity"        => $bxpackg,
                            "oldprice"        => $customerPrice,
                            "seller_price"    => $sellerPrice,
                            "commission_rate" => $commissionRate,
                            "pack_litres"     => $packLitres,
                            "price"           => $customerPrice
                        );
            ProductAttributes::insert($patbu);            
        }
        
        return redirect('/seller/product')->withErrors(["Successfully Added"]);
  }
  public function list(Request $request){
        $data['title'] = 'Product';
        $data['categorylist'] = Category::where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->orderby('name','asc')->get();
        $data['Brandlist'] = Brand::where('user_id',Auth::user()->id)->where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->orderby('name','asc')->get();
        $query = Product::where('user_id',Auth::user()->id);
        if ($request->filled('Brand')) {    $query = $query->where("brand_id", $request->input('Brand'));           }
        if ($request->filled('srchcategory')) {    $query = $query->where("category_id", $request->input('srchcategory'));  }
        if ($request->filled('product')) {    $query = $query->where("name", $request->input('product'));           }
             $data['list'] = $query->orderby("id","desc")->get();
             
        return view('product.list',$data);  
  }
  public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->withErrors(["Successfully Deleted"]);
    }
  public function addform(){
       $company = Company::where("user_id",Auth::user()->id)->first();
       if(empty($company)){return redirect()->back()->withErrors(["Please Add Your Company By Admin"]);}
       $Brand = Brand::where("user_id",Auth::user()->id)->first();
       if(empty($Brand)){return redirect()->back()->withErrors(["Please Add Your Brand First"]);}
        $data['title'] = 'Add Product';
        $data['step1Data'] =session()->get('product_step1', []);
        return view('product.add',$data);
  }
    public function editview($id,Request $request){
        $data['title'] = 'Edit Product';
        $pro = Product::where('id',$id)->first();
        $data['detail'] = $pro;
        $data['shadecard'] = ShadeCard::where('category_id',$pro->category_id)->get();
        $query =  ProductAttributes::where("product_id",$id);
         if ($request->filled('srchshade')) { $query = $query->where("color", $request->input('srchshade'));  }
          $data['attributelist'] = $query->get();
        return view('product.edit',$data);
  }
     public function productdetail(Request $request){ 
         $rt=array();$rmn["sellerdetail"]=array();
             $rmn = Product::where("id",$request->get('productid'))->first();
             $rmn['multipleimages'] = json_decode($rmn->multipleimages);
             $attribute = ProductAttributes::where("product_id",$request->get('productid'))->where("color",$request->get('colorid'))->get();
              foreach($attribute as $atrbt){
                   $color=ShadeCard::where("id",$atrbt->color)->first();
                   $sbm['color']=$color ? $color->name : '';
                   $sbm['hexcode']=$color ? $color->hexcode : '';
                   $quantity=BoxPacking::where("id",$atrbt->quantity)->first();
                   $sbm['boxpacking']=$quantity ? $quantity->name : '';
                   $sbm['quantity']=$quantity ? $quantity->pcs : 1;
                   $sbm['pack_litres']=$atrbt->pack_litres ?: 1.0;
                   $sbm['seller_price']=$atrbt->seller_price ?: $atrbt->price;
                   $sbm['price']=$atrbt->price;
                   $sbm['oldprice']=$atrbt->oldprice;
                   $sbm['attributeid']=$atrbt->id;
                   array_push($rt,$sbm);
              }
              $rmn['attributes'] = $rt;
              /////////////////seller detail
              $sellerdetail = Company::where("user_id",$rmn->user_id)->first();
              $srh["name"] =  $sellerdetail ? $sellerdetail->name : '';
              $srh["email"] =  $sellerdetail ? $sellerdetail->email : '';
              $srh["mobile"] =  $sellerdetail ? $sellerdetail->mobile : '';
              $srh["city"] =  $sellerdetail ? $sellerdetail->city : '';
              $srh["photo"] =  $sellerdetail ? $sellerdetail->photo : '';
              $rmn['sellerdetail'] = $srh;
              
              ////////////////product reviews
              $revary = $related = array();
              $reviewlist = ProductReviews::where("product_id",$request->get('productid'))->where("status","1")->get();
              foreach($reviewlist as $review){
                  $custmr = Customer::where("id",$review->customer_id)->first();
                  if(!empty($custmr)){
                  $rw["customername"] = $custmr->name;
                  $rw["customerimage"] = $custmr->image;
                  $rw["review"] = $review->review;
                  $rw["rating"] = $review->rating;
                  $rw["date"] = $review->created_at; 
                  array_push($revary,$rw);
                  }
              }
              $rmn["reviewlist"] = $revary;
              ///////////is customer cable to post review
               $rmn["postreview"]=true;
               $rmn["wishlist"]=false;
                $wihl = DB::table("wishlist")->where("userid",$request->get('userid'))->where("productid",$request->get('productid'))->first();
                if(!empty($wihl)){ $rmn["wishlist"]=true; }
                ///////////related product/////
               $relatedpr = Product::where("user_id",$rmn->user_id)->where("id","!=",$rmn->id)->orderby("id","desc")->limit(5)->get();
               foreach($relatedpr as $rel){
                   $rlt['id'] = $rel->id;
                   $rlt['name'] = $rel->name;
                   $rlt['image'] = $rel->image;
                   $price = ProductAttributes::where("product_id",$rel->id)->orderby('price','asc')->first();
                   $rlt['color']= $price ? Helper::getcolorsnamebyid($price->color) : '';
                   $rlt['colorid']= $price ? $price->color : 0;
                   $quantity= $price ? BoxPacking::where("id",$price->quantity)->first() : null;
                   $rlt['price'] = $price ? ('Rs. '.$price->price.($quantity ? '('.$quantity->name.')' : '')) : '';
                   array_push($related,$rlt);
               }
              $rmn["relatedproductlist"] = $related;
                if($request->get('colorid')!==''){
                    $list=ShadeCard::where("id",$request->get('colorid'))->first();
                         $rmn['colordetail']= $list;
                }
          return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
      }
     public function productattributeprice(Request $request){ 
          $attribute = ProductAttributes::where("product_id",$request->get('productid'));
          if($request->get('color')!=''){
             $attribute = $attribute->where("color",$request->get('color'));
          }
          if($request->get('quantity')!=''){
             $attribute = $attribute->where("quantity",$request->get('quantity'));
          }
          $attribute = $attribute->first();
          
          if ($attribute && empty($attribute->seller_price)) {
              $attribute->seller_price = $attribute->price;
          }
          
          return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$attribute]);
      }
     
     public function update(Request $request, $id)
{ 
    // 1. Find your product/detail record
    $detail = Product::findOrFail($id);

    // 2. Initialize array with existing images that the user DID NOT delete
    $finalImages = $request->input('current_images', []);

    // Optional: If you want to delete removed files completely from your server disk storage:
    if (!empty($detail->multipleimages)) {
        $oldImages = json_decode($detail->multipleimages, true) ?: [];
        // Find which images were deleted by comparing arrays
        $deletedImages = array_diff($oldImages, $finalImages);
        
        foreach ($deletedImages as $deletedImage) {
            // Adjust the disk and path depending on your setup
            // if (Storage::disk('public')->exists($deletedImage)) {
            //     Storage::disk('public')->delete($deletedImage);
            // }
        }
    }

    // 3. Process and upload NEW images if any were added
    if ($request->hasFile('new_images')) {
        foreach ($request->file('new_images') as $file) {
            if ($file->isValid()) {
                // Save image to your storage path
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move('storage/app/public/product/', $filename); // Change to match your env('imagepath') architecture
                
                // Add new filename to our final tracking array
                $finalImages[] = 'product/'.$filename;
                $fnlimgs = 'product/'.$filename;
            }
        }
    }else{
        $fnlimgs = $detail->image;
    }

    // 4. Save back to the database as a JSON string
    $detail->multipleimages = json_encode(array_values($finalImages));
    $detail->image = $fnlimgs;
    $detail->name = $request->name;
    $detail->brand_id = $request->brand;
    $detail->maincategory_id = $request->maincategory;
    $detail->category_id = $request->category;
    $detail->status = $request->status;
    $detail->description = $request->details;
      $detail->hsncode = $request->hsncode;
    $detail->save();

    return redirect()->back()->with('success', 'Product images updated successfully!');
}
 public function destroyFilter($id)
{
        $filter =  DB::table("product_attributes")->find($id);
        DB::table("product_attributes")->where('id', $id)->delete();
         

        // 3. Redirect back with a success message
        return redirect()->back()->with('success', 'Product variant deleted successfully.');

}
}
