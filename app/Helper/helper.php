<?php

namespace App\Helper;

use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\MainCategory;
use App\Models\Brand;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use App\Models\Company;
use App\Models\Product;
use App\Models\Customer;
use Auth;
use Illuminate\Http\Request;

class Helper {
     private static $catCache = [];
     private static $mainCatCache = [];
     private static $shadeCardCache = [];
     private static $boxPackingCache = [];
     private static $brandCache = [];
     private static $companyCache = [];
     private static $productCache = [];
     private static $customerCache = [];
     private static $orderStatusCache = [];

     public static function getmidcidbybid(){
          $bid = $_REQUEST['bid'];
     $list=Brand::where("id",$bid)->first();
     $data["mid"]=$list->mid;
     $data["cid"]=$list->category_id;
     $data["listdata"] = Helper::getshadecardlistbycatforajax($list->category_id);
     if(empty($list)){return '';}else{return $data;}
 }
 public static function getmaincat(){
     $list=MainCategory::whereIn("status",["1", "Active", 1])->get();
     return $list;
 }
 public static function getsubcatfordropdown(){
     $mid = $_POST['mid'];
     $list=Category::where("maincategory_id",$mid)->whereIn("status",["1", "Active", 1])->get();
     $listop = '<option value="">Select Category</option>';;
     foreach($list as $lt){ $listop .= '<option value="'.$lt->id.'">'.$lt->name.'</option>';}
     return json_encode($listop);
 }
 public static function getmaincatname($mid){
     if(empty($mid)) return '';
     if (!array_key_exists($mid, self::$mainCatCache)) {
         $list = MainCategory::where("id", $mid)->first();
         self::$mainCatCache[$mid] = $list ? $list->name : '';
     }
     return self::$mainCatCache[$mid];
 }
 public static function getcatname($cid){
     if(empty($cid)) return '';
     if (!array_key_exists($cid, self::$catCache)) {
         $list = Category::where("id", $cid)->first();
         self::$catCache[$cid] = $list ? $list->name : '';
     }
     return self::$catCache[$cid];
 }
  public static function getbrand(){
     $list=Brand::where("user_id",Auth::user()->id)->whereIn("status",["1", "Active", 1])->get();
     return $list;
 } 
 public static function getshadecardlistbycatforajax($catid){ 
    // $catid = $_REQUEST['catid'];
     $midd = Category::select("maincategory_id")->where("id",$catid)->first();
     $mid = $midd ? $midd->maincategory_id : 0;
     $ids = [$mid, 0];
     $list=ShadeCard::whereIn("status",["1", "Active", 1])->where("category_id",$catid)->get();
      $listop = '';
       $listop .= '<table id="" class="table table-striped table-bordered" style="width:100%">
                                        <tbody>
                                        <tr  class="table-sticky">
                                            <th></th>';
                                            foreach(BoxPacking::whereIn("status",["1", "Active", 1])->whereIN("maincategory_id",$ids)->orderby("maincategory_id", "desc")->get() as $bxp){
                                            $listop .= '<th> <input class="form-check-input checkAll'.$bxp->id.'" onClick="checkallitsrow('.$bxp->id.')" type="checkbox" id="checkAll" name="attribu">  '.$bxp->name.' </th>';
                                            }
                                $listop .= '</tr>';
      foreach($list as $gcr){
                              $listop .= '<tr  class="table-col-sticky">
                                            <th><span class="dot" style="background-color:'.$gcr->hexcode.'"></span>'.$gcr->name.'</th>';
                                            foreach(BoxPacking::whereIn("status",["1", "Active", 1])->whereIN("maincategory_id",$ids)->orderby("maincategory_id", "desc")->get() as $bxpg)
                                            {
                             $listop .= '<td>
                                                <div class="form-check">
                                                    <input class="form-check-input checkofrow'.$bxpg->id.'" type="checkbox" value="'.$bxpg->id.'-&-'.$gcr->id.'" id="flexCheckDefault'.$bxpg->id.'" name="attributes[]">
                                                    <label class="form-check-label" for="flexCheckDefault"></label>
                                                </div>
                                            </td>';
                                             }
                            $listop .= '</tr>';
                            }
                            $listop .= '</tbody>
                                        </table>';
     return $listop;
 }
 public static function getcolors(){
     $list = ShadeCard::whereIn("status",["1", "Active", 1])->get();
     return $list;
 }
 public static function getboxpacking(){
     $list=BoxPacking::whereIn("status",["1", "Active", 1])->get();
     return $list;
 }
 public static function getcolorsnamebyid($id){
     if(empty($id)) return '';
     if (!array_key_exists($id, self::$shadeCardCache)) {
         self::$shadeCardCache[$id] = ShadeCard::where("id", $id)->first();
     }
     return self::$shadeCardCache[$id] ? self::$shadeCardCache[$id]->name : '';
 }
 public static function getcolorshexcodebyid($id){
     if(empty($id)) return '';
     if (!array_key_exists($id, self::$shadeCardCache)) {
         self::$shadeCardCache[$id] = ShadeCard::where("id", $id)->first();
     }
     return self::$shadeCardCache[$id] ? self::$shadeCardCache[$id]->hexcode : '';
 }
 public static function getboxpackingnamebyid($id){
     if(empty($id)) return '';
     if (!array_key_exists($id, self::$boxPackingCache)) {
         $list = BoxPacking::where("id", $id)->first();
         self::$boxPackingCache[$id] = $list ? $list->name : '';
     }
     return self::$boxPackingCache[$id];
 }
 public static function getbrandname($bid){
     if(empty($bid)) return '';
     if (!array_key_exists($bid, self::$brandCache)) {
         $list = Brand::where("id", $bid)->first();
         self::$brandCache[$bid] = $list ? $list->name : '';
     }
     return self::$brandCache[$bid];
 }
 public static function getbrandforajax(){
     $list=Brand::where("user_id",Auth::user()->id)->whereIn("status",["1", "Active", 1])->get();
     $listop = '';
     foreach($list as $lt){ 
         $catname = Helper::getcatname($lt->category_id);
         $listop .= '<option value="'.$lt->id.'">'.$lt->name.'('.$catname.')</option>';}
     return json_encode($listop);
 }
 public static function getcompanyname($bid){
     if(empty($bid)) return '';
     if (!array_key_exists($bid, self::$companyCache)) {
         $list = Company::where("id", $bid)->first();
         self::$companyCache[$bid] = $list ? $list->name : '';
     }
     return self::$companyCache[$bid];
 }
 public static function getproductlistforajax(){
     $list=Product::where("user_id",Auth::user()->id)->whereIn("status",["1", "Active", 1])->get();
     $listop = '';
     foreach($list as $lt){ 
         $catname = Helper::getcatname($lt->category_id);
         $listop .= '<option value="'.$lt->id.'">'.$lt->name.'('.$catname.')</option>';}
     return json_encode($listop);
 }
 public static function getproductname($pid){
     if(empty($pid)) return '';
     if (!array_key_exists($pid, self::$productCache)) {
         $list = Product::where("id", $pid)->first();
         self::$productCache[$pid] = $list ? $list->name : '';
     }
     return self::$productCache[$pid];
 }
 public static function getcompanynamebybrandid($bid){
     if(empty($bid)) return '';
     $b = Brand::where("id", $bid)->first();
     if(empty($b)) return '';
     return Helper::getcompanyname($b->company_id);
 }
 public static function getcustomerdetail($id){
     if(empty($id)) return null;
     if (!array_key_exists($id, self::$customerCache)) {
         self::$customerCache[$id] = Customer::where("id", $id)->first();
     }
     return self::$customerCache[$id];
 }
 
 public static function getmaincatandcatbybrand(Request $request){ 
     $cid=$request->get('cid');$mid=$request->get('mid');
    //  $list=Brand::where("id",$bid)->first();
     //$mid = $list->mid;
     //$cid = $list->category_id;
     $mname = Helper::getmaincatname($mid);
     $cname = Helper::getcatname($cid);
     $md = '<option value="'.$mid.'">'.$mname.'</option>';
     $cd = '<option value="'.$cid.'">'.$cname.'</option>';
      $list=ShadeCard::where("status","1")->where("category_id",$cid)->get();
      $listop = '';
       $listop .= '<table id="" class="table table-striped table-bordered" style="width:100%">
                                        <tbody>
                                        <tr>
                                            <th></th>';
                                            foreach(BoxPacking::where("status","1")->whereIN("maincategory_id","($mid,0)")->orderby("maincategory_id")->get() as $bxp){
                                            $listop .= '<th> <input class="form-check-input checkAll'.$bxp->id.'" onClick="checkallitsrow('.$bxp->id.')" type="checkbox" id="checkAll" name="attribu">  '.$bxp->name.' </th>';
                                            }
                                $listop .= '</tr>';
      foreach($list as $gcr){
                              $listop .= '<tr>
                                            <th><span class="dot" style="background-color:'.$gcr->hexcode.'"></span>'.$gcr->name.'</th>';
                                            foreach(BoxPacking::where("status","1")->get() as $bxpg)
                                            {
                             $listop .= '<td>
                                                <div class="form-check">
                                                    <input class="form-check-input checkofrow'.$bxpg->id.'" type="checkbox" value="'.$bxpg->id.'-&-'.$gcr->id.'" id="flexCheckDefault'.$bxpg->id.'" name="attributes[]">
                                                    <label class="form-check-label" for="flexCheckDefault"></label>
                                                </div>
                                            </td>';
                                             }
                            $listop .= '</tr>';
                            }
                            $listop .= '</tbody>
                                        </table>';
     $listopp = json_encode($listop);
     $listoper = array("mid"=>$md,"cid"=>$cd,"shadecard"=>$listop);
     return json_encode($listoper);
 }
   public static function getstatusoforder($id){
     if(empty($id)) return false;
     if (!array_key_exists($id, self::$orderStatusCache)) {
         $list = DB::table("order_status")->where("order_no", $id)->orderby("id", "desc")->first('status');
         self::$orderStatusCache[$id] = $list ? $list->status : false;
     }
     return self::$orderStatusCache[$id];
 }
 
 public static function addnotificationindb($userid,$usertype,$title,$msg){
     $arymsg = array(
         "customer_id"=>$userid,
         "title"=>$title,
         "body"=>$msg,
         "customertype"=>$usertype );
         $rt=DB::table("notifications")->insert($arymsg);
         if($rt){ return true;}
 }
}
