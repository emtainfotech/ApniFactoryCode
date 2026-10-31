<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubCatgory;
use App\Models\Category;
use App\Models\MainCategory;
use App\Models\Slider;
use App\Models\Product;
use App\Models\ShadeCard;
use App\Models\ProductAttributes;
use App\Models\Brand;
use App\Models\Company;
use DB;
use App\Models\Advertisement;
use App\Helper\Helper;
use DateTime;
class AppController extends Controller
{
      public function notificationreadbyuser(Request $request){
            $brrmn = DB::table("notifications")->where("id",$request->notificationid)->update(["msgread"=>1]);
            return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Updated"]);
    }
    public function notificationlist(Request $request){
            $brrmn = DB::table("notifications")->where("customer_id",$request->userid)->get();
            return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$brrmn]);
    }
    public function branddetail(Request $request){
            $brrmn = Brand::where("id",$request->brandid)->first();
            $cmp = Company::where("id",$brrmn->company_id)->first();
            $brrmn["Company"] = $cmp;
            return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$brrmn]);
    }
     public function search(Request $request){ 
         $search = $request->get('word');
         $category  = Category::select(['id', 'name', 'image','maincategory_id as relatedid'])->whereIn("status", ["Active", "active", "1"])->where("title","like","%{$search}%")->get()->map(function ($category) {
                         $category->{'screen'} = 'category';
                         return $category;
                        });
         $company  = Company::select(['id', 'name', 'photo as image', 'user_id'])->whereIn("status", ["Active", "active", "1"])->where("name","like","%{$search}%")->get()->map(function ($company) {
                         $company->{'screen'} = 'company';
                         $categid = Product::select(['category_id as cat'])->where("user_id",$company->user_id)->first();
                         if(!empty($categid)){
                              $company->{'relatedid'} = $categid->cat;
                         }else{
                             $company->{'relatedid'} = 0;
                         }
                         unset($company->user_id);
                         return $company;
                        });
         $product  = Product::select(['id', 'name', 'image'])->whereIn("status", ["Active", "active", "1"])->where(function($q) use ($search) { $q->where("name","like","%{$search}%")->orWhere("description","like","%{$search}%"); })->get();
         $prodIds = $product->pluck('id')->toArray();
         $firstAttrs = ProductAttributes::whereIn('product_id', $prodIds)->groupBy('product_id')->selectRaw('product_id, MIN(id) as attr_id')->pluck('attr_id', 'product_id');
         $product = $product->map(function ($p) use ($firstAttrs) {
                         $p->{'screen'} = 'product';
                         $p->{'relatedid'} = $firstAttrs->get($p->id, 0);
                         return $p;
                        });
         $shadecard = ShadeCard::where("name","like","%{$search}%")->pluck('id');
         $productattribute = ProductAttributes::select('product_id')->whereIn("color",$shadecard)->groupby('product_id')->pluck('product_id');
         $productbyshade  = Product::select(['id', 'name', 'image'])->whereIn("status", ["Active", "active", "1"])->whereIn("id",$productattribute)->get();
         $shadeProdIds = $productbyshade->pluck('id')->toArray();
         $firstShadeAttrs = ProductAttributes::whereIn('product_id', $shadeProdIds)->groupBy('product_id')->selectRaw('product_id, MIN(id) as attr_id')->pluck('attr_id', 'product_id');
         $productbyshade = $productbyshade->map(function ($p) use ($firstShadeAttrs) {
                          $p->{'screen'} = 'product';
                          $p->{'relatedid'} = $firstShadeAttrs->get($p->id, 0);
                          return $p;
                         });
         $mergedCollection = collect();
         // Merge the results, some of which might be empty
         $mergedCollection = $mergedCollection
             ->merge($category)->merge($company)->merge($product)->merge($productbyshade);
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$mergedCollection]);
     }
     public function homescreen(Request $request){ 
         $rmn['company'] = array();
         $maincategory = MainCategory::whereIn("status", ["Active", "active", "1"])->orderby("sequence","asc")->get();
         $slider1 = Slider::whereIn("status", ["Active", "active", "1"])
             ->where(function($q) { $q->whereNull("enddate")->orWhere("enddate", ">=", date("Y-m-d")); })
             ->where("screen","homescreen")->orderby("id","desc")->pluck('image');
         $cntnotification = DB::table("notifications")->where("customer_id",$request->userid)->count('id');
         
         if ($slider1->count() >= 2) {
             $twoArrays = $slider1->split(2)->values();
             $slider = array_values($twoArrays->get(0) ? $twoArrays->get(0)->values()->toArray() : []); 
             $advertisement = array_values($twoArrays->get(1) ? $twoArrays->get(1)->values()->toArray() : []); 
         } elseif ($slider1->count() == 1) {
             $slider = array_values($slider1->toArray());
             $advertisement = [];
         } else {
             $slider = [];
             $advertisement = [];
         }
                 // 1. Fetch Random Products
        $randomProductsRaw = Product::whereIn("status", ["Active", "active", "1"])
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $prodIds = $randomProductsRaw->pluck('id')->toArray();
        $minPrices = DB::table('product_attributes')
            ->select('product_id', DB::raw('MIN(price) as min_price'), DB::raw('MIN(color) as first_color'))
            ->whereIn('product_id', $prodIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $randomProducts = [];
        foreach ($randomProductsRaw as $p) {
            $attr = $minPrices->get($p->id);
            $price = $attr ? (float)$attr->min_price : 0.0;
            if ($price <= 0 && $p->price) {
                $price = (float)$p->price;
            }
            $colorId = $attr && $attr->first_color ? (string)$attr->first_color : '0';

            $randomProducts[] = [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->image ?: 'default.png',
                'category_id' => $p->category_id ?: 0,
                'category_name' => Helper::getcatname($p->category_id),
                'brand_id' => $p->brand_id ?: 0,
                'brand_name' => Helper::getbrandname($p->brand_id),
                'price' => $price,
                'oldprice' => $price > 0 ? round($price * 1.2, 2) : 0.0,
                'color_id' => $colorId,
            ];
        }

        // 2. Fetch Popular Brands
        $brandsRaw = Brand::whereIn("status", ["Active", "active", "1"])
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->inRandomOrder()
            ->get(['id', 'name', 'image', 'company_id', 'category_id'])
            ->unique('name')
            ->values()
            ->take(10);

        $brands = [];
        foreach ($brandsRaw as $b) {
            $brands[] = [
                'id' => $b->id,
                'name' => $b->name,
                'image' => $b->image ?: 'default.png',
                'company_id' => $b->company_id ?: 0,
                'category_id' => $b->category_id ?: 0,
            ];
        }

        // 3. Fetch More Products (Second random batch)
        $moreProductsRaw = Product::whereIn("status", ["Active", "active", "1"])
            ->whereNotIn('id', $prodIds)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $moreProdIds = $moreProductsRaw->pluck('id')->toArray();
        $moreMinPrices = DB::table('product_attributes')
            ->select('product_id', DB::raw('MIN(price) as min_price'), DB::raw('MIN(color) as first_color'))
            ->whereIn('product_id', $moreProdIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $moreProducts = [];
        foreach ($moreProductsRaw as $p) {
            $attr = $moreMinPrices->get($p->id);
            $price = $attr ? (float)$attr->min_price : 0.0;
            if ($price <= 0 && $p->price) {
                $price = (float)$p->price;
            }
            $colorId = $attr && $attr->first_color ? (string)$attr->first_color : '0';

            $moreProducts[] = [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->image ?: 'default.png',
                'category_id' => $p->category_id ?: 0,
                'category_name' => Helper::getcatname($p->category_id),
                'brand_id' => $p->brand_id ?: 0,
                'brand_name' => Helper::getbrandname($p->brand_id),
                'price' => $price,
                'oldprice' => $price > 0 ? round($price * 1.2, 2) : 0.0,
                'color_id' => $colorId,
            ];
        }

        $rmn = [
            "slider" => $slider,
            "maincategory" => $maincategory,
            "advertisement" => $advertisement,
            "notification" => $cntnotification,
            "random_products" => $randomProducts,
            "brands" => $brands,
            "more_products" => $moreProducts,
        ];
        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }

     public function randomproducts(Request $request){
        $limit = (int)($request->get('limit') ?: 10);
        $exclude = $request->get('exclude');
        $excludeIds = is_array($exclude) ? $exclude : (is_string($exclude) ? explode(',', $exclude) : []);

        $query = Product::whereIn("status", ["Active", "active", "1"]);
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        $productsRaw = $query->inRandomOrder()->limit($limit)->get();
        $prodIds = $productsRaw->pluck('id')->toArray();

        $minPrices = DB::table('product_attributes')
            ->select('product_id', DB::raw('MIN(price) as min_price'), DB::raw('MIN(color) as first_color'))
            ->whereIn('product_id', $prodIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $products = [];
        foreach ($productsRaw as $p) {
            $attr = $minPrices->get($p->id);
            $price = $attr ? (float)$attr->min_price : 0.0;
            if ($price <= 0 && $p->price) {
                $price = (float)$p->price;
            }
            $colorId = $attr && $attr->first_color ? (string)$attr->first_color : '0';

            $products[] = [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->image ?: 'default.png',
                'category_id' => $p->category_id ?: 0,
                'category_name' => Helper::getcatname($p->category_id),
                'brand_id' => $p->brand_id ?: 0,
                'brand_name' => Helper::getbrandname($p->brand_id),
                'price' => $price,
                'oldprice' => $price > 0 ? round($price * 1.2, 2) : 0.0,
                'color_id' => $colorId,
            ];
        }

        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$products]);
     }
     public function secondscreen_category_company(Request $request){
         $rmn['category']=$rmn['company']=$rmn['advertisement']=array();
         $category  = Category::whereIn("status", ["Active", "active", "1"])->where("maincategory_id",$request->get('mid'))->orderby("sequence","asc")->get();
          foreach($category as $cat){
              $ct['name']=$cat->name;
              $ct['image']=$cat->image;
              $ct['cid']=$cat->id;
              array_push($rmn['category'],$ct);
          }
          $company = Company::whereIn("status", ["Active", "active", "1"])->where("maincategory_id",$request->get('mid'))->orderby("id","desc")->get();
          foreach($company as $cmp){
              $cm['name']=$cmp->name;
              $cm['image']=$cmp->photo;
              $cm['cmpid']=$cmp->id;  
              $userid = Company::where("id",$cmp->id)->first();
              $count = $userid ? Product::where('user_id', $userid->user_id)->distinct()->count('category_id') : 0;
              $cm['count']=$count;
              $cm['city']=$cmp->city;
              array_push($rmn['company'],$cm);
          }
           $sliderPluck = Advertisement::whereIn("status", ["Active", "active", "1"])->where("screen","CategoryScreen")->where(function($q){$q->whereNull("enddate")->orWhere("enddate",">=",date("Y-m-d"));})->orderby("sequence","asc")->pluck('file');
           $rmn['slider'] = array_values($sliderPluck->toArray());
           $adPluck = Slider::whereIn("status", ["Active", "active", "1"])->where(function($q){$q->whereNull("enddate")->orWhere("enddate",">=",date("Y-m-d"));})->where("screen","CategoryScreen")->orderby("id","desc")->pluck('image');
           $rmn['advertisement'] = array_values($adPluck->toArray());
           return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     public function brandlist_bycompanyid(Request $request){
        if($request->get('cid')=='0'){
            $rmnBrand  = Brand::whereIn("status", ["Active", "active", "1"])->where("company_id",$request->get('cmpid'))->get();
          $data = collect($rmnBrand)
        ->unique('name')
        ->values()
        ->toArray();
         $rmn['Brand']  = $data;
          $comp = Company::where("id",$request->get('cmpid'))->first();
           if(empty($comp)){$rmn['bannerimage']='';}else{$rmn['bannerimage']=$comp->photo;}
          $rmn['companyname']= Helper::getcompanyname($request->get('cmpid'));
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
        }else{
         $rmnBrand  = Brand::whereIn("status", ["Active", "active", "1"])->where("company_id",$request->get('cmpid'))->where("category_id",$request->get('cid'))->get();
          $data = collect($rmnBrand)
        ->unique('name')
        ->values()
        ->toArray();
         $rmn['Brand']  = $data;
          $comp = Company::where("id",$request->get('cmpid'))->first();
           if(empty($comp)){$rmn['bannerimage']='';}else{$rmn['bannerimage']=$comp->photo;}
          $rmn['companyname']= Helper::getcompanyname($request->get('cmpid'));
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
        }
     }
     public function companylist_bycategoryid(Request $request){
         $sellerid  = Product::select('user_id')->whereIn("status", ["Active", "active", "1"])->where("category_id",$request->get('cid'))->groupby('user_id')->get('user_id');
          $prid=array();foreach($sellerid as $seller){array_push($prid,$seller->user_id);} 
           $rmn['company'] = Company::whereIn("status", ["Active", "active", "1"])->whereIN("user_id",$prid)->orderby("id","desc")->get();
           $sliderPluck = Advertisement::whereIn("status", ["Active", "active", "1"])->where("screen","CompanyScreen")->where(function($q){$q->whereNull("enddate")->orWhere("enddate",">=",date("Y-m-d"));})->orderby("sequence","asc")->pluck('file');
           $rmn['slider'] = array_values($sliderPluck->toArray());
           $adPluck = Slider::whereIn("status", ["Active", "active", "1"])->where(function($q){$q->whereNull("enddate")->orWhere("enddate",">=",date("Y-m-d"));})->where("screen","CompanyScreen")->orderby("id","desc")->pluck('image');
           $rmn['advertisement'] = array_values($adPluck->toArray());
          $rmn['categoryname']= Helper::getcatname($request->get('cid'));
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     public function productcategorylist_bybrandid(Request $request){
          $bid = $request->get('bid');
          $cid = $request->get('cid');
          $productlist  = Product::whereIn("status", ["Active", "active", "1"])->where("brand_id",$bid)->where("category_id",$cid)->get();
          $rmn['product'] = [];
          $sbct = [];
          $userid = '';

          if($productlist->isNotEmpty()){
              $pids = $productlist->pluck('id')->toArray();
              $attributes = DB::table('product_attributes')
                  ->select('product_id', 'color', DB::raw('MIN(price) as min_price'))
                  ->whereIn('product_id', $pids)
                  ->groupBy('product_id', 'color')
                  ->get();

              $colorIds = $attributes->pluck('color')->unique()->filter()->values()->toArray();
              $shadeCards = ShadeCard::whereIn('id', $colorIds)->get()->keyBy('id');
              $attrsByProduct = $attributes->groupBy('product_id');
              $catName = Helper::getcatname($cid);

              foreach($productlist as $prod){
                  $prodAttrs = $attrsByProduct->get($prod->id, collect());
                  foreach($prodAttrs as $pat){
                      $sc = $shadeCards->get($pat->color);
                      $colorName = $sc ? $sc->name : '';
                      $hexcode = $sc ? $sc->hexcode : '';
                      $cm = [
                          'name' => $prod->name . '(' . $colorName . ')',
                          'image' => $prod->image,
                          'price' => (float)$pat->min_price,
                          'cid' => $prod->category_id,
                          'categoryname' => $catName,
                          'pid' => $prod->id,
                          'color' => $colorName,
                          'hexcode' => $hexcode,
                          'hexcode_image' => ($hexcode == '' && $sc) ? ($sc->image ?? '') : '',
                          'colorid' => $pat->color,
                      ];
                      $rmn['product'][] = $cm;
                  }
                  $userid = $prod->user_id;
              }
          }

          if(!empty($userid)){
              $b = Helper::getbrandname($bid);
              $brandModels = Brand::where("user_id", $userid)->where("name", "like", '%' . $b . '%')->get(['id', 'category_id']);
              $brandIds = $brandModels->pluck('id')->toArray();
              $brandByCat = $brandModels->keyBy('category_id');

              $catIds = Product::whereIn("status", ["Active", "active", "1"])
                  ->where("user_id", $userid)
                  ->whereIn("brand_id", $brandIds)
                  ->distinct()
                  ->pluck('category_id')
                  ->toArray();

              $categories = Category::whereIn("status", ["Active", "active", "1"])
                  ->whereIn("id", $catIds)
                  ->get();

              $cidnm = Helper::getcatname($cid);
              foreach($categories as $category){
                  $getbrand1 = $brandByCat->get($category->id);
                  $sbct[] = [
                      'highlight' => ($category->name == $cidnm),
                      'name' => $category->name,
                      'image' => $category->image,
                      'cid' => $category->id,
                      'bid' => $getbrand1 ? $getbrand1->id : 0,
                  ];
              }
          }
          $rmn['subcategory'] = $sbct;
          $slider = Slider::where("screen","CategoryScreen")->orderby("id","desc")->first();
          $rmn['bannerimage'] = $slider ? $slider->image : '';
          $rmn['brandname'] = Helper::getbrandname($bid);
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     
     public function productlistby_bybrandidandsid(Request $request){
          $bid = $request->get('bid');
          $sid = $request->get('sid');
          $productlist  = Product::whereIn("status", ["Active", "active", "1"])->where("category_id",$sid)->where("brand_id",$bid)->get();
          $rmn['product'] = [];
          $sbct = [];
          $userid = '';

          if($productlist->isNotEmpty()){
              $pids = $productlist->pluck('id')->toArray();
              $attributes = DB::table('product_attributes')
                  ->select('product_id', 'color', DB::raw('MIN(price) as min_price'))
                  ->whereIn('product_id', $pids)
                  ->groupBy('product_id', 'color')
                  ->get();

              $colorIds = $attributes->pluck('color')->unique()->filter()->values()->toArray();
              $shadeCards = ShadeCard::whereIn('id', $colorIds)->get()->keyBy('id');
              $attrsByProduct = $attributes->groupBy('product_id');
              $catName = Helper::getcatname($sid);

              foreach($productlist as $prod){
                  $prodAttrs = $attrsByProduct->get($prod->id, collect());
                  foreach($prodAttrs as $pat){
                      $sc = $shadeCards->get($pat->color);
                      $colorName = $sc ? $sc->name : '';
                      $hexcode = $sc ? $sc->hexcode : '';
                      $cm = [
                          'name' => $prod->name . '(' . $colorName . ')',
                          'image' => $prod->image,
                          'price' => (float)$pat->min_price,
                          'cid' => $prod->category_id,
                          'categoryname' => $catName,
                          'pid' => $prod->id,
                          'color' => $colorName,
                          'hexcode' => $hexcode,
                          'hexcode_image' => ($hexcode == '' && $sc) ? ($sc->image ?? '') : '',
                          'colorid' => $pat->color,
                      ];
                      $rmn['product'][] = $cm;
                  }
                  $userid = $prod->user_id;
              }
          }

          $catname = Helper::getcatname($sid);
          $getuser = Product::whereIn("status", ["Active", "active", "1"])->where("brand_id",$bid)->first();
          $brdnm = Helper::getbrandname($bid);
          if(!empty($getuser)){
              $userid = $getuser->user_id;
              $brandModels = Brand::where("user_id", $userid)->where("name", "like", '%' . $brdnm . '%')->get(['id', 'category_id']);
              $brandIds = $brandModels->pluck('id')->toArray();
              $brandByCat = $brandModels->keyBy('category_id');

              $catIds = Product::whereIn("status", ["Active", "active", "1"])
                  ->where("user_id", $userid)
                  ->whereIn("brand_id", $brandIds)
                  ->distinct()
                  ->pluck('category_id')
                  ->toArray();

              $categories = Category::whereIn("status", ["Active", "active", "1"])
                  ->whereIn("id", $catIds)
                  ->get();

              foreach($categories as $category){
                  $getbrand1 = $brandByCat->get($category->id);
                  $sbct[] = [
                      'highlight' => ($category->name == $catname),
                      'name' => $category->name,
                      'image' => $category->image,
                      'cid' => $category->id,
                      'bid' => $getbrand1 ? $getbrand1->id : 0,
                  ];
              }
          }
          $rmn['subcategory'] = $sbct;
          $rmn['bannerimage'] = 'storage/app/public/slider/banner.jpg';
          $rmn['brandname'] = Helper::getbrandname($bid);
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }

     public function statecitypincode(Request $request){ 
          $get = $request->get("listof");
          $id = $request->get("name");
          $cacheKey = "pincode_{$get}_" . md5($id ?? 'all');
          $list = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function() use ($get, $id) {
              if($get=='state'){
                  return DB::table("india_pincode")->groupBy("state")->pluck('state');
              }
              if($get=='city'){
                  return DB::table("india_pincode")->where("state",$id)->groupBy("city")->pluck('city');
              }
              if($get=='pincode'){
                  return DB::table("india_pincode")->where("city",$id)->groupBy("pincode")->pluck('pincode');
              }
              return [];
          });
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$list]);
     }
     ////////////////////////////////////////////////////////old functions
     public function category(Request $request){ 
         $rmn = MainCategory::where("status","active")->get();
         if(!empty($request->get('mid'))){
             $rmn = Category::where("status","active")->where("mid",$request->get('mid'))->get();
         }
         if(!empty($request->get('cid'))){
             $rmn = SubCatgory::where("status","active")->where("cid",$request->get('cid'))->get();
         }
         if(!empty($request->get('sid'))){
             $rmn = Product::where("status","active")->where("sid",$request->get('sid'))->get();
         }
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     public function colourdata(Request $request){ 
         $rt=array();
             $rmn = Product::where("status","active")->where("mid",$request->get('id'))->get();
             foreach($rmn as $row){
                 if($row->colorcode==null){
                     $dt["mid"] = $row->mid;
                     $dt["cid"] = $row->cid;
                     $dt["sid"] = $row->sid;
                     $dt["color"] = $row->colorimage;
                     $dt["type"] = "colorimage";
                 }else{
                     $dt["mid"] = $row->mid;
                     $dt["cid"] = $row->cid;
                     $dt["sid"] = $row->sid;
                     $dt["color"] = $row->colorcode;
                     $dt["type"] = "colorcode";
                 }
                 array_push($rt,$dt);
             }
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rt]);
     }
       public function shardecard(Request $request){ 
         $rmn = ShadeCard::where("status","active");
         if(!empty($request->get('cid'))){
             $rmn = $rmn->where("subcategoryid",$request->get('cid'));
         }
             $rmn = $rmn->orderby('id','desc')->get();
        
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
        public function brandaccordingsharcardid(Request $request){ 
         $attrib = ProductAttributes::select('product_id')->where("color",$request->get('shadecardid'))->get('product_id');
         $prid=array();foreach($attrib as $att){array_push($prid,$att->product_id);}
         $rmn = Product::select('brand')->whereIN("product_id",$prid)->groupby('brand')->get();
          $brid=array();foreach($rmn as $rmnb){
              $brrmn = Product::where("brand",$rmnb->brand)->first();
              $bridw['shadeid']=$brrmn->id;
              $bridw['image']=config('app.url').'/'.$brrmn->image;
              $Brand = Brand::where("id",$rmnb->brand)->first();
              $bridw['name']=$Brand->name;
              $bridw['brandid']=$rmnb->brand;
              array_push($brid,$bridw);
          }
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$brid]);
     } 
     public function productlistaccordingbrandandshade(Request $request){ 
            $brrmn = Product::where("brand",$request->brandid)->get();
              $brid=array();foreach($brrmn as $rmnb){
              $brrmn1 = ProductAttributes::where("color",$request->shadeid)->where("product_id",$rmnb->product_id)->first();
              if(!empty($brrmn1)){
                   array_push($brid,$brrmn);
              }else{}
               return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$brid]);
              }
              
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
}
