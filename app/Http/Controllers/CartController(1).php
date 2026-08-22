<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubCatgory;
use App\Models\Category;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use App\Models\Company;
use DB;
use App\Helper\helper;
use App\Models\CustomerAddress;
class CartController extends Controller
{    public function updatecartaddress(Request $request){ 
         $addressid =  $request->get('addressid');
         $customerid =  $request->get('userid');
             DB::table("cart")->where("customer_id",$customerid)->update(["addressid"=>$addressid]);
             return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Updated"]);
    }
     public function checkavailability(Request $request){ 
         $addressid =  $request->get('addressid');
         $customerid =  $request->get('userid');
         $cart = DB::table("cart")->where("customer_id",$customerid)->first();
         $company_id = $cart->company_id;
         $company = Company::where("id",$company_id)->first();
         $cities = $company->restricted_city;
         if(empty($cities)){ return response()->json(["status"=>true,"code"=>100,"msg"=>"Available in your City"]);}
          $cityy = json_decode($company->restricted_city,true);
         $cusaddress = CustomerAddress::where("id",$addressid)->first();
         $city = $cusaddress->city;
         if (in_array($city, $cityy)) {
                 return response()->json(["status"=>false,"code"=>500,"msg"=>"Not Available in your City"]);
            }else{
                 return response()->json(["status"=>true,"code"=>100,"msg"=>"Available in your City"]);
            }
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","grandtotal"=>$totalprice]);
     }
        public function increasecartqty(Request $request){ 
         $rt=array();$granttotal=0;$cnt=0;
         $cartid = $request->get('cartattributeid');
         $qty = $request->get('qty');
         if($qty==0){
                $getcartid = DB::table("cartattribute")->where("id",$cartid)->first();
                $caid = $getcartid->cart_id;
                $countid = DB::table("cartattribute")->where("cart_id",$caid)->count();
                if($countid==1){
                 DB::table("cartattribute")->where("id",$cartid)->delete();
                 DB::table("cart")->where("id",$caid)->delete();
                }else{
                 DB::table("cartattribute")->where("id",$cartid)->delete();
                }
                    $totalprice = 0;
         }else{
          $attribute = DB::table("cartattribute")->where("id",$cartid)->first();
          $unitprice = $attribute->unitprice;
           $totalprice = $unitprice * $qty;
             DB::table("cartattribute")->where("id",$cartid)->update(["qty"=>$qty,"totalprice"=>$totalprice]);
         }
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","grandtotal"=>$totalprice]);
     }
     public function checkcartconditions($customerid,$bid,$pid){
         $check = DB::table("cart")->where("customer_id",$customerid)->first();////phele se konsa Producth
         if(empty($check)){return true;}
         $prod = Product::where("id",$pid)->first();///current jo add kr rha ho
         $current_cmp = $prod->user_id;
         
         $prod2 = Product::where("id",$check->product_id)->first();
         $old_cmp = $prod2->user_id;
         if($old_cmp!=$current_cmp){
             return false;
                  }
                  else{return true;}
         
     }
       public function addtocart(Request $request){ 
         $attribute=$rt=array();$granttotal=0;$cnt=0;
         $reqdata = $request->json()->all();
         $productid = $reqdata['productid'];
         $userid = $reqdata['userid'];
         $attribute = $reqdata['attributes'];
         $brandid = $reqdata['brandid'];
        $checkcondition = $this->checkcartconditions($userid,$brandid,$productid);
         if($checkcondition==false){ return response()->json(["status"=>false,"code"=>500,"msg"=>"Please Add Same Company Product","grandtotal"=>0.0,"counter"=>0,"data"=>[]]);
                  exit();}
             $prod = Product::where("id",$productid)->first();
         $check = DB::table("cart")->where("customer_id",$userid)->where("product_id",$productid)->first();
        if(empty($check)){
             ////////////////////////////////////////if attribute array empty h to entry na ho////
             if(empty($reqdata['attributes'])){
                  return response()->json(["status"=>false,"code"=>500,"msg"=>"Please Add Quantity","grandtotal"=>0.0,"counter"=>0,"data"=>[]]);
                  exit();
             }
             $company = Company::where("user_id",$prod->user_id)->first();
             $company_id = $company->id;
             $cartid = DB::table("cart")->insertGetId(
                array(
                        "product_id"=>$productid,
                        "customer_id"=>$userid,
                        "company_id"=>$company_id,
                        "brand_id"=>$prod->brand_id,
                        "category_id"=>$prod->category_id,
                        "productname"=>$prod->name
                    )
                ); 
             foreach($reqdata['attributes'] as $atrbty){
                 $attributedetail = ProductAttributes::where("id",$atrbty['id'])->first();
                 $price = $attributedetail->price;
                 $fndpcs = BoxPacking::where("id",$attributedetail->quantity)->first();
                 $pcs = $fndpcs->pcs;
                 $unitprice = $price*$pcs;
                 $totalprice = $unitprice*$atrbty['qty'];
                 $cartattribt = array(
                                        "customer_id"=>$userid,
                                        "cart_id"=>$cartid,
                                        "product_attributes_id"=>$atrbty['id'],
                                        "qty"=>$atrbty['qty'],
                                        "boxpcs"=>$pcs,
                                        "unitprice"=>$unitprice,
                                        "totalprice"=>$totalprice,
                                        "prprice"=>$price,
                                        "tax"=>$prod->tax,
                                        "taxamount"=>($totalprice*$prod->tax)/100
                                     );
                DB::table("cartattribute")->insert($cartattribt);                   
             }
             $granttotal = DB::table("cartattribute")->where("customer_id",$userid)->sum('totalprice');
             $cnt = DB::table("cart")->where("customer_id",$userid)->count('id');
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","grandtotal"=>floatval($granttotal),"counter"=>$cnt]);
         }else{
             ////product already h but attribute new aa rha h tb
             $cartid = $check->id;
              foreach($reqdata['attributes'] as $atrbty){
                  $atid = $atrbty['id'];
                  $checkattri = DB::table("cartattribute")->where("product_attributes_id",$atid)->where("customer_id",$userid)->first();
                     if(empty($checkattri)){
                          $attributedetail = ProductAttributes::where("id",$atrbty['id'])->first();
                             $price = $attributedetail->price;
                             $fndpcs = BoxPacking::where("id",$attributedetail->quantity)->first();
                             $pcs = $fndpcs->pcs;
                             $unitprice = $price*$pcs;
                             $totalprice = $unitprice*$atrbty['qty'];
                             $cartattribt = array(
                                                    "customer_id"=>$userid,
                                                    "cart_id"=>$cartid,
                                                    "product_attributes_id"=>$atrbty['id'],
                                                    "qty"=>$atrbty['qty'],
                                                    "boxpcs"=>$pcs,
                                                    "unitprice"=>$unitprice,
                                                    "totalprice"=>$totalprice,
                                                    "prprice"=>$price,
                                                    "tax"=>$prod->tax,
                                                    "taxamount"=>($totalprice*$prod->tax)/100
                                                 );
                            DB::table("cartattribute")->insert($cartattribt);   
                        $granttotal = DB::table("cartattribute")->where("customer_id",$userid)->sum('totalprice');
                        $cnt = DB::table("cart")->where("customer_id",$userid)->count('id');
                        return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","grandtotal"=>floatval($granttotal),"counter"=>$cnt]);     
                     }
                    //  else{
                    //      return response()->json(["status"=>false,"code"=>500,"msg"=>"attribute Already In Cart","grandtotal"=>0.0,"counter"=>0,"data"=>[]]);
                    //  }
              }
         return response()->json(["status"=>false,"code"=>500,"msg"=>"Already In Cart","grandtotal"=>0.0,"counter"=>0,"data"=>[]]);
         }
     }
     /*
    public function addtocart(Request $request){ 
         $attribute=$rt=array();$granttotal=0;$cnt=0;
         $reqdata = $request->json()->all();
         $productid = $reqdata['productid'];
         $userid = $reqdata['userid'];
         $attribute = $reqdata['attributes'];
         $brandid = $reqdata['brandid'];
        $checkcondition = $this->checkcartconditions($userid,$brandid,$productid);
         if($checkcondition==false){ return response()->json(["status"=>false,"code"=>500,"msg"=>"Please Add Same Company Product","grandtotal"=>0.0,"counter"=>0,"data"=>[]]);
                  exit();}
         $check = DB::table("cart")->where("customer_id",$userid)->where("product_id",$productid)->first();
        if(empty($check)){
             ////////////////////////////////////////if attribute array empty h to entry na ho////
             if(empty($reqdata['attributes'])){
                  return response()->json(["status"=>false,"code"=>500,"msg"=>"Please Add Quantity","grandtotal"=>0.0,"counter"=>0,"data"=>[]]);
                  exit();
             }
             $prod = Product::where("id",$productid)->first();
             $company = Company::where("user_id",$prod->user_id)->first();
             $company_id = $company->id;
        $cartid = DB::table("cart")->insertGetId(
            array(
                    "product_id"=>$productid,
                    "customer_id"=>$userid,
                    "company_id"=>$company_id,
                    "brand_id"=>$prod->brand_id,
                    "category_id"=>$prod->category_id,
                    "productname"=>$prod->name
                )
            ); 
             foreach($reqdata['attributes'] as $atrbty){
                 $attributedetail = ProductAttributes::where("id",$atrbty['id'])->first();
                 $price = $attributedetail->price;
                 $fndpcs = BoxPacking::where("id",$attributedetail->quantity)->first();
                 $pcs = $fndpcs->pcs;
                 $unitprice = $price*$pcs;
                 $totalprice = $unitprice*$atrbty['qty'];
                 $cartattribt = array(
                                        "customer_id"=>$userid,
                                        "cart_id"=>$cartid,
                                        "product_attributes_id"=>$atrbty['id'],
                                        "qty"=>$atrbty['qty'],
                                        "boxpcs"=>$pcs,
                                        "unitprice"=>$unitprice,
                                        "totalprice"=>$totalprice,
                                        "prprice"=>$price,
                                        "tax"=>$prod->tax,
                                        "taxamount"=>($totalprice*$prod->tax)/100
                                     );
                DB::table("cartattribute")->insert($cartattribt);                   
             }
             $granttotal = DB::table("cartattribute")->where("customer_id",$userid)->sum('totalprice');
             $cnt = DB::table("cart")->where("customer_id",$userid)->count('id');
             
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","grandtotal"=>floatval($granttotal),"counter"=>$cnt]);
         }else{
         return response()->json(["status"=>false,"code"=>500,"msg"=>"Already In Cart","grandtotal"=>0.0,"counter"=>0,"data"=>[]]);
         }
     }
     */
    public function usercart(Request $request){ DB::enableQueryLog();
        $userid = $request->get('userid'); $granttotal=0.0;$netamount=$cnt=$tax=$cpnamt=0.0;$rt=json_decode('{}');
          $couponbyadmin=0;$combinecoupon='0';$cgst=$igst=$sgst=0;  
          $cartdetail = DB::table("cart")->select("category_id","id")->where("customer_id",$userid)->groupby("category_id")->get();
          if(!empty($cartdetail[0])){
              $rt=array();
          foreach($cartdetail as $crtdt){
              $categoryname = Helper::getcatname($crtdt->category_id);
              $rt[$categoryname]=array();$proarry=array();
                $cartprod = DB::table("cart")->where("customer_id",$userid)->where("category_id",$crtdt->category_id)->get();
                foreach($cartprod as $prod){ 
                    $cnt++;
                    $catary["cartid"]=$prod->id;
                    $pnme = Product::where("id",$prod->product_id)->first();
                    $catary["productid"]=$pnme->id;
                    $catary["productname"]=$pnme->name;
                    $catary["productimage"]=$pnme->image;echo '</br>'.$crtdt->id;
                    $cartattribut = DB::table("cartattribute")->where("cart_id",$crtdt->id)->get();
                    $catary["attributedata"]=[];
                    foreach($cartattribut as $crtatrbt){
                        $t=$amountwithoutcoupn=0;$totalqty=1;$cpamount=0;
                        $totalqty =  $crtatrbt->qty * $crtatrbt->boxpcs;
                        $paid = $crtatrbt->product_attributes_id;
                        $attributedetail = ProductAttributes::where("id",$paid)->first();
                         $fndpcs = BoxPacking::where("id",$attributedetail->quantity)->first();
                         $attr["boxpacking"] = $fndpcs->name;
                         $fndclr = ShadeCard::where("id",$attributedetail->color)->first();
                         $attr["color"] = $fndclr->name;
                         $attr["totalprice"] = $crtatrbt->totalprice;
                         $attr["qty"] = $crtatrbt->qty;
                         $attr["attributeid"] = $crtatrbt->id;
                         $attr["productattributeid"] = $paid;
                         $attr["productattributeid"] = $paid;
                         $attr["couponamount"] = $crtatrbt->coupon * $totalqty;
                          $granttotal += $crtatrbt->totalprice;
                        array_push($catary["attributedata"],$attr);
                        //   print_r($attr);
                        //////////////calculate tax on ProductAttributes
                        $t = ($crtatrbt->totalprice*$pnme->tax)/100;
                        $tax += $t;
                        //////////////calculate total couponamount on ProductAttributes
                        $cpamount = $crtatrbt->coupon * $totalqty;
                        $cpnamt += $cpamount;
                     ///////////////calculate net amount(without coupon apply) to show in apply
                     $amountwithoutcoupn = $totalqty * $crtatrbt->prprice ;
                     $netamount = $netamount+$amountwithoutcoupn;
                    }
                    array_push($proarry,$catary);
                }
                array_push($rt[$categoryname],$proarry);
                 
          /////////////admin coupon ka amount grant total me se deduct hoga
           $cartforcpnck = DB::table("cart")->where("customer_id",$userid)->first();
          if(!empty($cartforcpnck->couponbyadmin)){
               $admincoupon = json_decode($cartforcpnck->couponbyadmin,true);
                $granttotal = $admincoupon['finalamount'];
                $couponbyadmin =  $admincoupon['amount'];
          }else{
                $couponbyadmin =  0;
          }
          $combinecoupon = $cpnamt.'+'.$couponbyadmin;
                        ////////agr cart me addressid h to cgst sgst igst calculate honge ni to cgst r sgst jayenge
                          
                        if($cartforcpnck->addressid != 0){
                             $addre = CustomerAddress::where("id",$cartforcpnck->addressid)->first();
                             $customerpincode = $addre->pincode;
                             $cmpnypin = Company::where("id",$cartforcpnck->company_id)->first();
                             $cmppin = $cmpnypin->pincode;
                              /////////////tx calculation
                         if($cmppin==$customerpincode){
                                        $gsttype='csgst'; 
                                        $cgst = $tax/2; $sgst = $tax-$cgst;$igst = 0;
                         }else{
                                        $tax1 = 18; $taxamount = ($granttotal*$tax1)/100;
                                        $igst = $taxamount;
                                        $cgst =  $sgst = 0;
                        }            
                        }else{
                            $cgst = $tax/2;
                            $sgst = $tax-$cgst;
                            $igst = 0;
                        }
             }
          }
        //   $tax = 5;
          if($granttotal==0.0){$tax=0.0;}
          $granttotal = $granttotal + $tax; 
         $queries = DB::getQueryLog(); dd($queries);
          
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","grandtotal"=>floatval($granttotal),"netamount"=>floatval($netamount),"counter"=>$cnt,"cgst"=>$cgst,"sgst"=>$sgst,"igst"=>$igst,"couponbyadmin"=>floatval($couponbyadmin),"combinecoupon"=>$combinecoupon,"data"=>$rt]);
     }
    public function deletecart(Request $request){ 
        $cartid = $request->get('cartid'); $granttotal=0;$cnt=0;
         DB::table("cart")->where("id",$cartid)->delete($cartid);
         DB::table("cartattribute")->where("cart_id",$cartid)->delete();
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Delete Successfully"]);
     }
    public function emptycart(Request $request){ 
        $cartid = $request->get('customer_id'); $granttotal=0;$cnt=0;
         DB::table("cart")->where("customer_id",$cartid)->delete();
         DB::table("cartattribute")->where("customer_id",$cartid)->delete();
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Delete Successfully"]);
     }
    public function deletecartattribute(Request $request){ 
        $cartid = $request->get('cartattributeid');
        $getcartid = DB::table("cartattribute")->where("id",$cartid)->first();
        $caid = $getcartid->cart_id;
        $countid = DB::table("cartattribute")->where("cart_id",$caid)->count();
        if($countid==1){
         DB::table("cartattribute")->where("id",$cartid)->delete();
         DB::table("cart")->where("id",$caid)->delete();
        }else{
         DB::table("cartattribute")->where("id",$cartid)->delete();
        }
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Delete Successfully"]);
     }
     
     
     //////////////////////////////wishlist/////////////////////
     
    public function addtowishlist(Request $request){ 
         $rt=array();$granttotal=0;$cnt=0;
         $productid = $request->get('productid');
         $userid = $request->get('userid');
         $check = DB::table("wishlist")->where("userid",$userid)->where("productid",$productid)->first();
         if(empty($check)){
             $prod = Product::where("id",$productid)->first();
        $save = DB::table("wishlist")->insert(
            array(
                    "productid"=>$productid,
                    "userid"=>$userid,
                    "productimage"=>$prod->image,
                    "productname"=>$prod->name
                )
            );
            $wishlistdetail = DB::table("wishlist")->where("userid",$userid)->get();
            $cnt = DB::table("wishlist")->where("userid",$userid)->count('id');
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","counter"=>$cnt,"data"=>$wishlistdetail]);
         }else{
         return response()->json(["status"=>true,"code"=>500,"msg"=>"Already In wishlist","grandtotal"=>0,"counter"=>0,"data"=>[]]);
         }
     }
    public function userwishlist(Request $request){ 
        $userid = $request->get('userid'); $granttotal=0;$cnt=0;$wishlist=array();
          $wishlistdetail = DB::table("wishlist")->where("userid",$userid)->get();
             foreach($wishlistdetail as $crtdt){
                 $dt["id"]=$crtdt->id;
                 $dt["name"]=$crtdt->productname;
                 $dt["image"]=$crtdt->productimage;
             $prod = Product::where("id",$crtdt->productid)->first();
                 $dt["category"]=Helper::getcatname($prod->category_id);
                 $dt["company"]=Helper::getcompanynamebybrandid($prod->brand_id);
                 $dt["brand"]= Helper::getbrandname($prod->brand_id);
             array_push($wishlist,$dt);
                 $cnt++;
             }
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","counter"=>$cnt,"data"=>$wishlist]);
     }
    public function deletewishlist(Request $request){ 
        $userid = $request->get('userid');
        $wishlistid = $request->get('wishlistid'); $granttotal=0;$cnt=0;
         DB::table("wishlist")->where("id",$wishlistid)->delete($wishlistid);
          $wishlistdetail = DB::table("wishlist")->where("userid",$userid)->get();
             foreach($wishlistdetail as $crtdt){
                 $cnt++;
             }
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Delete Successfully","counter"=>$cnt,"data"=>$wishlistdetail]);
     }
}
