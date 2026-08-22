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
         $category  = Category::select(['id', 'name', 'image','maincategory_id as relatedid'])->where("status","1")->where("title","like","%{$search}%")->get()->map(function ($category) {
                         $category->{'screen'} = 'category';
                         return $category;
                        });
        /* $brand  = Brand::select(['id', 'name', 'image'])->where("status","1")->where("name","like","%{$search}%")->get()->map(function ($brand) {
                         $brand->{'screen'} = 'brand';
                         return $brand;
                        });*/
         $company  = Company::select(['id', 'name', 'photo as image'])->where("status","1")->where("name","like","%{$search}%")->get()->map(function ($company) {
                         $company->{'screen'} = 'company';
                         $getuserid = Company::select(['user_id'])->where("id",$company->id)->first();
                         $categid = Product::select(['category_id as cat'])->where("user_id",$getuserid->user_id)->first();
                        //  echo 'pp'.$categid->cat;
                        if(!empty($categid)){
                             $company->{'relatedid'} = $categid->cat;
                             return $company;
                        }else{
                            $company->{'relatedid'} = 0;
                             return $company;
                        }
                        });
         $product  = Product::select(['id', 'name', 'image'])->where("status","1")->where("name","like","%{$search}%")->orWhere("description","like","%{$search}%")->get()->map(function ($product) {
                         $product->{'screen'} = 'product';
        $productattribute = ProductAttributes::select('id')->where("product_id",$product->id)->first('id');
                           $product->{'relatedid'} = $productattribute->id;
                         return $product;
                        });
        $shadecard = ShadeCard::where("name","like","%{$search}%")->pluck('id');
        $productattribute = ProductAttributes::select('product_id')->whereIn("color",$shadecard)->groupby('product_id')->pluck('product_id');
        $productbyshade  = Product::select(['id', 'name', 'image'])->where("status","1")->whereIn("id",$productattribute)->get()->map(function ($productbyshade) {
                         $productbyshade->{'screen'} = 'product';
                          $prodattrite = ProductAttributes::select('id')->where("product_id",$productbyshade->id)->first('id');
                         $productbyshade->{'relatedid'} = $prodattrite->id;
                         return $productbyshade;
                        });
        $mergedCollection = collect();
            // Merge the results, some of which might be empty
            $mergedCollection = $mergedCollection
                ->merge($category)->merge($company)->merge($product)->merge($productbyshade);
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$mergedCollection]);
     }
     public function homescreen(Request $request){ 
         $rmn['company'] = array();
         $maincategory = MainCategory::where("status","1")->orderby("sequence","asc")->get();
         $slider1 = Slider::where("status","1")->where("enddate",">=",date("Y-m-d"))->where("screen","homescreen")->orderby("id","desc")->pluck('image');
        //  $advertisement = Advertisement::where("status","1")->where("screen","homescreen")->where("enddate",">=",date("Y-m-d"))->orderby("sequence","asc")->pluck('file');
         $cntnotification = DB::table("notifications")->where("customer_id",$request->userid)->count('id');
         $twoArrays = $slider1->split(2)->values()->toArray();
         $slider =$twoArrays[0]; 
         $advertisement = $twoArrays[1]; 
         $rmn = array("slider"=>$slider,"maincategory"=>$maincategory,"advertisement"=>$advertisement,"notification"=>$cntnotification);
        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     public function secondscreen_category_company(Request $request){
         $rmn['category']=$rmn['company']=$rmn['advertisement']=array();
         $category  = Category::where("status","1")->where("maincategory_id",$request->get('mid'))->orderby("sequence","asc")->get();
          foreach($category as $cat){
              $ct['name']=$cat->name;
              $ct['image']=$cat->image;
              $ct['cid']=$cat->id;
              array_push($rmn['category'],$ct);
          }
          $company = Company::where("status","1")->where("maincategory_id",$request->get('mid'))->orderby("id","desc")->get();
          foreach($company as $cmp){
              $cm['name']=$cmp->name;
              $cm['image']=$cmp->photo;
              $cm['cmpid']=$cmp->id;  
              $userid = Company::where("id",$cmp->id)->first();
              $count = Product::where('user_id', $userid->user_id)->distinct()->count('category_id');
              $cm['count']=$count;
              $cm['city']=$cmp->city;
              array_push($rmn['company'],$cm);
          }
           $rmn['slider'] = Advertisement::where("status","1")->where("screen","CategoryScreen")->where("enddate",">=",date("Y-m-d"))->orderby("sequence","asc")->pluck('file');
           $rmn['advertisement'] = Slider::where("status","1")->where("enddate",">=",date("Y-m-d"))->where("screen","CategoryScreen")->orderby("id","desc")->pluck('image');
        //   $rmn['advertisement'] = Advertisement::where("status","1")->where("screen","CategoryScreen")->where("enddate",">=",date("Y-m-d"))->orderby("id","desc")->get();
        //   foreach($advertisement as $ad){
        //       $adm['name']=$ad->name;
        //       $adm['image']=$ad->file;
        //       $adm['aid']=$cmp->id;
        //       array_push($rmn['advertisement'],$adm);
        //   }
        //   $rmn['bannerimage']='storage/app/public/slider/banner.jpg';
           return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     public function brandlist_bycompanyid(Request $request){
        //   $rmnBrand  = Brand::where("status","1")->where("company_id",$request->get('cmpid'))->get();
        if($request->get('cid')=='0'){
            $rmnBrand  = Brand::where("status","1")->where("company_id",$request->get('cmpid'))->get();
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
         $rmnBrand  = Brand::where("status","1")->where("company_id",$request->get('cmpid'))->where("category_id",$request->get('cid'))->get();
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
         $sellerid  = Product::select('user_id')->where("status","1")->where("category_id",$request->get('cid'))->groupby('user_id')->get('user_id');
          $prid=array();foreach($sellerid as $seller){array_push($prid,$seller->user_id);} 
           $rmn['company'] = Company::where("status","1")->whereIN("user_id",$prid)->orderby("id","desc")->get();
           $rmn['slider'] = Advertisement::where("status","1")->where("screen","CompanyScreen")->where("enddate",">=",date("Y-m-d"))->orderby("sequence","asc")->pluck('file');
           $rmn['advertisement'] = Slider::where("status","1")->where("enddate",">=",date("Y-m-d"))->where("screen","CompanyScreen")->orderby("id","desc")->pluck('image');
          $rmn['categoryname']= Helper::getcatname($request->get('cid'));
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     public function productcategorylist_bybrandid(Request $request){
          $productlist  = Product::where("status","1")->where("brand_id",$request->get('bid'))->where("category_id",$request->get('cid'))->get();
          $rmn['product']=$sbct=$finalary=$frst=$sbct1=array();$userid='';
          if(!empty($productlist)){
          foreach($productlist as $prod){
            //   $price = ProductAttributes::where("product_id",$prod->id)->min('price');
              $productattribute  = ProductAttributes::where("product_id",$prod->id)->select('color')->groupBy('color')->get();
              foreach($productattribute as $pat){
              $price = ProductAttributes::where("product_id",$prod->id)->where("color",$pat->color)->min('price');
                  $cm['name']=$prod->name.'('.Helper::getcolorsnamebyid($pat->color).')';
                  $cm['image']=$prod->image;
                  $cm['price']=$price;
                  $cm['cid']=$prod->category_id;
                  $cm['categoryname']=Helper::getcatname($prod->category_id);
                  $cm['pid']=$prod->id;
                  $cm['color']= Helper::getcolorsnamebyid($pat->color);
                  $cm['hexcode']= Helper::getcolorshexcodebyid($pat->color);
                  if($cm['hexcode']==''){
                        $list=ShadeCard::where("id",$pat->color)->first();
                        $cm['hexcode_image']= $list->image;
                  }
              $cm['colorid']= $pat->color;
               $catname = $cm['categoryname'];
                array_push($rmn['product'],$cm);
              }
                $userid = $prod->user_id;
          }
          }
          if(!empty($userid)){
              ////////////////get brand ides
                $b = Helper::getbrandname($request->get('bid'));
               $getbrand  = Brand::where("user_id",$userid)->where("name","like",'%'.$b.'%')->pluck('id');
              $sb1  = Product::where("status","1")->where("user_id",$userid)->whereIN("brand_id",$getbrand)->select('category_id')->groupBy('category_id')->get();
          foreach($sb1 as $psb1){
              $category  = Category::where("status","1")->where("id",$psb1->category_id)->first();
              if(!empty($category)){
                $cidnm = Helper::getcatname($request->get('cid'));
                if($category->name==$cidnm){$sbm['highlight']=true;}else{$sbm['highlight']=false;}
                      $sbm['name']=$category->name;
                      $sbm['image']=$category->image;
                      $sbm['cid']=$category->id;
                      $getbrand1  = Brand::where("user_id",$userid)->where("category_id",$category->id)->where("name","like",'%'.$b.'%')->first('id');
                      $sbm['bid']=$getbrand1->id;
                      array_push($sbct,$sbm);
              $finalary = array_merge($sbct1,$sbct);
              }
          }
          }
          $rmn['subcategory']=$finalary;
           $slider = Slider::where("screen","CategoryScreen")->orderby("id","desc")->first();
           if(empty($slider)){$rmn['bannerimage']='';}else{$rmn['bannerimage']=$slider->image;}
        //   $rmn['bannerimage']='storage/app/public/slider/banner.jpg';
          $rmn['brandname']= Helper::getbrandname($request->get('bid'));
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     
     public function productlistby_bybrandidandsid(Request $request){
          $productlist  = Product::where("status","1")->where("category_id",$request->get('sid'))->where("brand_id",$request->get('bid'))->get();
          $rmn['product']=$sbct=$finalary=$frst=$sbct1=array();$userid='';
          if(!empty($productlist)){
          foreach($productlist as $prod){
              $productattribute  = ProductAttributes::where("product_id",$prod->id)->select('color')->groupBy('color')->get();
              foreach($productattribute as $pat){
              $price = ProductAttributes::where("product_id",$prod->id)->where("color",$pat->color)->min('price');
              $cm['name']=$prod->name.'('.Helper::getcolorsnamebyid($pat->color).')';
              $cm['image']=$prod->image;
              $cm['price']=$price;
              $cm['cid']=$prod->category_id;
              $cm['categoryname']=Helper::getcatname($prod->category_id);
              $cm['pid']=$prod->id;
              $cm['color']= Helper::getcolorsnamebyid($pat->color);
              $cm['hexcode']= Helper::getcolorshexcodebyid($pat->color);
                  if($cm['hexcode']==''){
                        $list=ShadeCard::where("id",$pat->color)->first();
                        $cm['hexcode_image']= $list->image;
                  }
              $cm['colorid']= $pat->color;
              array_push($rmn['product'],$cm);
              $catname = $cm['categoryname'];
              }
                $userid = $prod->user_id;
          }
          }
          ///logic goes with brand id get category because product blank pr bhi to category aani chahiye////
              $catname=Helper::getcatname($request->get('sid'));
          $getuser  = Product::where("status","1")->where("brand_id",$request->get('bid'))->first();
          $brdnm = Helper::getbrandname($request->get('bid'));
          if(!empty($getuser)){
          $userid=$getuser->user_id;  
               $getbrand  = Brand::where("user_id",$userid)->where("name","like",'%'.$brdnm.'%')->pluck('id');
              $sb1  = Product::where("status","1")->where("user_id",$userid)->whereIN("brand_id",$getbrand)->select('category_id')->groupBy('category_id')->get();
          foreach($sb1 as $psb1){
              $category  = Category::where("status","1")->where("id",$psb1->category_id)->first();
              if(!empty($category)){
                if($category->name==$catname){$sbm['highlight']=true;}else{$sbm['highlight']=false;}
                      $sbm['name']=$category->name;
                      $sbm['image']=$category->image;
                      $sbm['cid']=$category->id;
                    //  $b = Helper::getbrandname($request->get('bid'));->where("name","like",'%'.$b.'%')
                      $getbrand1  = Brand::where("user_id",$userid)->where("category_id",$category->id)->where("name","like",'%'.$brdnm.'%')->first('id');
                      $sbm['bid']=$getbrand1->id;
                      array_push($sbct,$sbm);
              $finalary = array_merge($sbct1,$sbct);
              }
          }
          }
          $rmn['subcategory']=$finalary;
          $rmn['bannerimage']='storage/app/public/slider/banner.jpg';
          $rmn['brandname']= Helper::getbrandname($request->get('bid'));
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
      public function statecitypincode(Request $request){ 
          $get = $request->get("listof");
          $id = $request->get("name");
          if($get=='state'){
               $list = DB::table("india_pincode")->groupBy("state")->pluck('state');
          }
           if($get=='city'){
               $list = DB::table("india_pincode")->where("state",$id)->groupBy("city")->pluck('city');
           }
           if($get=='pincode'){
               $list = DB::table("india_pincode")->where("city",$id)->groupBy("pincode")->pluck('pincode');
           }
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
