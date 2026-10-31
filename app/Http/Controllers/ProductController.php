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
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $fname = time() . '_' . uniqid() . '.' . $ext;
            $file->move('storage/app/public/product', $fname);
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
        if ($request->filled('product')) {    $query = $query->where("name", 'like', '%' . $request->input('product') . '%');           }
        $data['list'] = $query->orderby("id","desc")->paginate(20)->withQueryString();
             
        return view('product.list',$data);  
  }

  public function bulkUploadView()
  {
      $company = Company::where("user_id", Auth::user()->id)->first();
      if (empty($company)) {
          return redirect('/seller/product')->withErrors(["Please Add Your Company By Admin First"]);
      }
      $data['title'] = 'Bulk Upload Products';
      $data['categorylist'] = Category::where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->orderby('name','asc')->get();
      $data['Brandlist'] = Brand::where('user_id', Auth::user()->id)->where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->orderby('name','asc')->get();
      return view('product.bulk-upload', $data);
  }

  public function downloadBulkSample()
  {
      $headers = [
          'Content-Type'        => 'text/csv; charset=UTF-8',
          'Content-Disposition' => 'attachment; filename="apnifactory_products_bulk_sample.csv"',
      ];

      $callback = function () {
          $handle = fopen('php://output', 'w');
          fputcsv($handle, [
              'category_name',
              'brand_name',
              'product_name',
              'title',
              'description',
              'hsn_code',
              'tax_percent',
              'box_packing',
              'shade_or_color',
              'seller_price',
              'customer_mrp'
          ]);

          fputcsv($handle, [
              'Enamel Paints',
              'Asian Paints Pro',
              'Apcolite Premium Gloss White',
              'Apcolite Premium Gloss Enamel Paint',
              'High gloss solvent based exterior and interior enamel',
              '3208',
              '18',
              '1 Litre',
              'Super White',
              '400.00',
              '500.00'
          ]);

          fputcsv($handle, [
              'Enamel Paints',
              'Asian Paints Pro',
              'Apcolite Premium Gloss White',
              'Apcolite Premium Gloss Enamel Paint',
              'High gloss solvent based exterior and interior enamel',
              '3208',
              '18',
              '4 Litres',
              'Super White',
              '1550.00',
              '1950.00'
          ]);

          fputcsv($handle, [
              'Cotton Fabrics',
              'Vardhman Textiles',
              'Premium Cambric Cotton 60s',
              'Fine Cambric Cotton Fabric Roll',
              '100% fine combed cotton weave for luxury apparel',
              '5208',
              '5',
              '25 Meters',
              'White',
              '2400.00',
              '3000.00'
          ]);

          fputcsv($handle, [
              'Men Wear',
              'Raymond Apparel',
              'Slim-Fit Cotton Oxford Shirt Navy',
              'Slim-Fit Oxford Shirt',
              'Premium formal button-down shirt',
              '6205',
              '12',
              'Size L',
              'Navy Blue',
              '650.00',
              '899.00'
          ]);

          fclose($handle);
      };

      return response()->stream($callback, 200, $headers);
  }

  public function processBulkUpload(Request $request)
  {
      $request->validate([
          'csv_file' => 'required|file|max:10240',
      ]);

      $file = $request->file('csv_file');
      $filePath = $file->getRealPath();

      $company = Company::where("user_id", Auth::user()->id)->first();
      if (empty($company)) {
          return redirect()->back()->withErrors(["Please Add Your Company By Admin First"]);
      }

      $handle = fopen($filePath, 'r');
      if (!$handle) {
          return redirect()->back()->withErrors(["Unable to open uploaded CSV file."]);
      }

      $header = fgetcsv($handle);
      if (!$header) {
          fclose($handle);
          return redirect()->back()->withErrors(["The uploaded CSV file is empty."]);
      }

      // Clean headers (trim, lowercase, remove BOM)
      $cleanHeaders = array_map(function($h) {
          return strtolower(trim(str_replace(["\xEF\xBB\xBF", ' '], ['', '_'], $h)));
      }, $header);

      $pricingService = app(\App\Services\PaintPricingService::class);
      $importedProducts = 0;
      $importedVariants = 0;
      $rowNumber = 1;
      $errors = [];

      DB::beginTransaction();
      try {
          while (($row = fgetcsv($handle)) !== false) {
              $rowNumber++;
              if (count(array_filter($row)) === 0) {
                  continue;
              }

              $rowData = [];
              foreach ($cleanHeaders as $index => $key) {
                  $rowData[$key] = isset($row[$index]) ? trim($row[$index]) : '';
              }

              $categoryName = $rowData['category_name'] ?? '';
              $brandName    = $rowData['brand_name'] ?? '';
              $productName  = $rowData['product_name'] ?? '';
              $boxPacking   = !empty($rowData['box_packing']) ? $rowData['box_packing'] : 'Standard';
              $shadeColor   = !empty($rowData['shade_or_color']) ? $rowData['shade_or_color'] : 'Standard';
              $sellerPrice  = (float) ($rowData['seller_price'] ?? 0);
              $customerMrp  = (float) ($rowData['customer_mrp'] ?? 0);
              $tax          = (int) ($rowData['tax_percent'] ?? 18);
              $hsnCode      = !empty($rowData['hsn_code']) ? $rowData['hsn_code'] : '0000';
              $title        = !empty($rowData['title']) ? $rowData['title'] : $productName;
              $description  = !empty($rowData['description']) ? $rowData['description'] : $title;

              if (empty($productName)) {
                  $errors[] = "Row #{$rowNumber}: Product name is required.";
                  continue;
              }

              // Resolve Category
              $category = Category::where('name', 'like', $categoryName)->first();
              if (!$category) {
                  $category = Category::where('status', 'Active')->first() ?? Category::first();
              }
              $categoryId = $category ? $category->id : 1;
              $mainCategoryId = $category ? ($category->maincategory_id ?? $category->mid ?? 1) : 1;

              // Resolve Brand
              $brand = Brand::where('name', 'like', $brandName)->where('user_id', Auth::user()->id)->first();
              if (!$brand) {
                  $brand = Brand::where('name', 'like', $brandName)->first();
              }
              if (!$brand) {
                  $brand = Brand::create([
                      'name'          => !empty($brandName) ? $brandName : 'Seller Brand',
                      'user_id'       => Auth::user()->id,
                      'company_id'    => $company->id,
                      'category_id'   => $categoryId,
                      'mid'           => $mainCategoryId,
                      'status'        => 'Active',
                      'adminresponse' => 'Approved',
                  ]);
              }
              $brandId = $brand->id;

              // Find or create parent Product
              $product = Product::where('user_id', Auth::user()->id)
                  ->where('name', $productName)
                  ->first();

              if (!$product) {
                  $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $productName))) . '-' . rand(1000, 99999);
                  $product = Product::create([
                      'user_id'         => Auth::user()->id,
                      'brand_id'        => $brandId,
                      'category_id'     => $categoryId,
                      'maincategory_id' => $mainCategoryId,
                      'subcategory_id'  => 0,
                      'name'            => $productName,
                      'slug'            => $slug,
                      'title'           => $title,
                      'description'     => $description,
                      'image'           => 'default.png',
                      'multipleimages'  => json_encode(['default.png']),
                      'hsncode'         => $hsnCode,
                      'tax'             => $tax,
                      'status'          => '1',
                      'product_id'      => rand(100000, 999999),
                  ]);
                  $importedProducts++;
              }

              // Compute pricing with commission
              $commissionRate = $pricingService->getEffectiveCommissionRate(Auth::id(), $product->id);
              $packLitres     = $pricingService->parsePackLitres($boxPacking);
              if ($customerMrp <= 0) {
                  $customerMrp = round($sellerPrice * (1 + ($commissionRate / 100)), 2);
              }

              // Insert or update variant in product_attributes
              ProductAttributes::updateOrCreate(
                  [
                      'product_id' => $product->id,
                      'color'      => $shadeColor,
                      'quantity'   => $boxPacking,
                  ],
                  [
                      'oldprice'        => $customerMrp,
                      'seller_price'    => $sellerPrice,
                      'commission_rate' => $commissionRate,
                      'pack_litres'     => $packLitres,
                      'price'           => $customerMrp,
                      'status'          => 'Active',
                  ]
              );
              $importedVariants++;
          }

          DB::commit();
          fclose($handle);

          $msg = "Bulk Upload Successful! {$importedProducts} new products created, and {$importedVariants} product variants updated.";
          return redirect('/seller/product')->with('success', $msg);

      } catch (\Exception $e) {
          DB::rollBack();
          fclose($handle);
          return redirect()->back()->withErrors(["Bulk import failed: " . $e->getMessage()]);
      }
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
              if ($attribute->isEmpty()) {
                  $firstColor = ProductAttributes::where("product_id", $request->get('productid'))->value('color');
                  if ($firstColor) {
                      $attribute = ProductAttributes::where("product_id", $request->get('productid'))->where("color", $firstColor)->get();
                  } else {
                      $attribute = ProductAttributes::where("product_id", $request->get('productid'))->get();
                  }
              }
              foreach($attribute as $atrbt){
                   $color=ShadeCard::where("id",$atrbt->color)->first();
                   $sbm['color']=$color ? $color->name : '';
                   $sbm['hexcode']=$color ? $color->hexcode : '';
                   $quantity=BoxPacking::where("id",$atrbt->quantity)->first();
                   $sbm['boxpacking']=$quantity ? $quantity->name : '';
                   $sbm['quantity']=$quantity ? $quantity->pcs : 1;
                   $sbm['pack_litres']=$atrbt->pack_litres ?: 1.0;
                   $sbm['seller_price']=$atrbt->seller_price ?: $atrbt->price;
                   $sbm['price']=(float)$atrbt->price;
                   $sbm['oldprice']=(float)$atrbt->oldprice;
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
              $reviewlist = ProductReviews::where("product_id",$request->get('productid'))->whereIn("status", ["Active", "active", "1"])->get();
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
