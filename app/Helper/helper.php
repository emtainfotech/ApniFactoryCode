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
     public static function getmidcidbybid(){
          $bid = $_REQUEST['bid'];
     $list=Brand::where("id",$bid)->first();
     $data["mid"]=$list->mid;
     $data["cid"]=$list->category_id;
     $data["listdata"] = Helper::getshadecardlistbycatforajax($list->category_id);
     if(empty($list)){return '';}else{return $data;}
 }
 public static function getmaincat(){
     $list=MainCategory::where("status","1")->get();
     return $list;
 }
 public static function getsubcatfordropdown(){
     $mid = $_POST['mid'];
     $list=Category::where("maincategory_id",$mid)->where("status","1")->get();
     $listop = '<option value="">Select Category</option>';;
     foreach($list as $lt){ $listop .= '<option value="'.$lt->id.'">'.$lt->name.'</option>';}
     return json_encode($listop);
 }
 public static function getmaincatname($mid){
     $list=MainCategory::where("id",$mid)->where("status","1")->first();
     if(empty($list)){return '';}else{return $list->name;}
     
 }
 public static function getcatname($cid){
     $list=Category::where("id",$cid)->first();
     if(empty($list)){return '';}else{return $list->name;}
 }
  public static function getbrand(){
     $list=Brand::where("user_id",Auth::user()->id)->where("status","1")->get();
     return $list;
 } 
 public static function getshadecardlistbycatforajax($catid){ 
    // $catid = $_REQUEST['catid'];
     $midd = Category::select("maincategory_id")->where("id",$catid)->first();
     $mid = $midd->maincategory_id;$ids = [$mid, 0];
     $list=ShadeCard::where("status","1")->where("category_id",$catid)->get();
      $listop = '';
       $listop .= '<table id="" class="table table-striped table-bordered" style="width:100%">
                                        <tbody>
                                        <tr  class="table-sticky">
                                            <th></th>';
                                            foreach(BoxPacking::where("status","1")->whereIN("maincategory_id",$ids)->orderby("maincategory_id", "desc")->get() as $bxp){
                                            $listop .= '<th> <input class="form-check-input checkAll'.$bxp->id.'" onClick="checkallitsrow('.$bxp->id.')" type="checkbox" id="checkAll" name="attribu">  '.$bxp->name.' </th>';
                                            }
                                $listop .= '</tr>';
      foreach($list as $gcr){
                              $listop .= '<tr  class="table-col-sticky">
                                            <th><span class="dot" style="background-color:'.$gcr->hexcode.'"></span>'.$gcr->name.'</th>';
                                            foreach(BoxPacking::where("status","1")->whereIN("maincategory_id",$ids)->orderby("maincategory_id", "desc")->get() as $bxpg)
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
     $list = ShadeCard::where("status","1")->get();
     return $list;
 }
 public static function getboxpacking(){
     $list=BoxPacking::where("status","1")->get();
     return $list;
 }
 public static function getcolorsnamebyid($id){
     $list=ShadeCard::where("id",$id)->first();
     if(empty($list)){return '';}else{return $list->name;}
 }
 public static function getcolorshexcodebyid($id){
     $list=ShadeCard::where("id",$id)->first();
     if(empty($list)){return '';}else{return $list->hexcode;}
 }
 public static function getboxpackingnamebyid($id){
     $list=BoxPacking::where("id",$id)->first();
     if(empty($list)){return '';}else{return $list->name;}
 }
 public static function getbrandname($bid){
     $list=Brand::where("id",$bid)->first();
     if(empty($list)){return '';}else{return $list->name;}
 }
 public static function getbrandforajax(){
     $list=Brand::where("user_id",Auth::user()->id)->where("status","1")->get();
     $listop = '';
     foreach($list as $lt){ 
         $catname = Helper::getcatname($lt->category_id);
         $listop .= '<option value="'.$lt->id.'">'.$lt->name.'('.$catname.')</option>';}
     return json_encode($listop);
 }
 public static function getcompanyname($bid){
     $list=Company::where("id",$bid)->first();
     if(empty($list)){return '';}else{return $list->name;}
 }
 public static function getproductlistforajax(){
     $list=Product::where("user_id",Auth::user()->id)->where("status","1")->get();
     $listop = '';
     foreach($list as $lt){ 
         $catname = Helper::getcatname($lt->category_id);
         $listop .= '<option value="'.$lt->id.'">'.$lt->name.'('.$catname.')</option>';}
     return json_encode($listop);
 }
 public static function getproductname($pid){
     $list=Product::where("id",$pid)->first();
     if(empty($list)){return '';}else{return $list->name;}
 }
 public static function getcompanynamebybrandid($bid){
     $b=Brand::where("id",$bid)->first();
     $list=Company::where("id",$b->company_id)->first();
     if(empty($list)){return '';}else{return $list->name;}
 }
  public static function getcustomerdetail($id){
     $list=Customer::where("id",$id)->first();
     return $list;
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
     $list= DB::table("order_status")->where("order_no",$id)->orderby("id","desc")->first('status');
     if(!empty($list)){ return $list->status;}else{return false;}
    
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
?>



























