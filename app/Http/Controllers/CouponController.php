<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Company;
use Illuminate\Http\Request;
use Auth;
use DB;
class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['list'] = Coupon::where('user_id',Auth::user()->id)->get();
        $data['title'] = 'Coupon';
        return view('seller.coupon',$data);
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
        $couponon = $request->couponon;
        if($couponon==1){$couponon='Brand';}else{$couponon='Product';}
        $couponof = $request->couponof;
        $title = $request->title;
        $code = $request->code;
        $type = $request->type;
        $amount = $request->amount;
        $enddate = $request->enddate;
        $startdate = $request->startdate;
        $file = $request->file('image');
        $addby = Auth::user()->id;
        $status = '0';
        if(!empty($request->file('image'))){
            $fname=$file->getClientOriginalName();
            $file->move('storage/app/public/coupon',$fname); $images=$fname;
        }else{$images='';}
           
            $check = Coupon::where("user_id",$addby)->where("code",$code)->orWhere("name",$title)->first();
            if(empty($check)){
            $array = array("code"=>$code,
                            "type"=>$type,
                            "name"=>$title,
                            "price"=>$amount,
                            "status"=>$status,
                            "description"=>$title,
                            "expiry"=>$enddate,
                            "name"=>$title,
                            "title"=>$title,
                            "image"=>'coupon/'.$images,
                            "couponon"=>$couponon,
                            "couponapplyon"=>json_encode($couponof),
                            "user_id"=>$addby,
                            "startdate"=>$startdate);
            Coupon::insert($array);        
           
        }
        return back()->withErrors(["Successfully Added"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $id)
    {
        ///////////////used in App
         $userid = $request->userid;
         $couponid = $request->couponid;
         $coupn = Coupon::where("id",$couponid)->first();
          //////////////agr product ya brand pr h ya seller ka h to perticuler hr product pr apply hoga 
          /////////admin pr h to grand total pr lagega
         
       return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coupon $coupon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->back()->withErrors(["Successfully Deleted"]);
    }
    
    ///////////////////////////////////////////userd in app api
    public function listforapp(Request $request)
    {
        if(empty($request->userid)){ return response()->json(["status"=>false,"code"=>500,"msg"=>"Please add Product into cart"]);}
        $userid = $request->userid;
         $cmpid = DB::table("cart")->where("customer_id",$userid)->first();
        if(empty($cmpid)){ return response()->json(["status"=>false,"code"=>500,"msg"=>"Please add Product into cart"]);}
         $sellerid = Company::where("id",$cmpid->company_id)->first();
         $data= Coupon::where("status","1")->where('startdate','<=',date('Y-m-d'))->where('expiry','>=',date('Y-m-d'));
         $couponof = $request->couponof;
         if($couponof=='seller'){ $data=$data->where('user_id',$sellerid->user_id); }
        if($couponof=='admin'){$data=$data->where('user_id',"0");}
        $data=$data->get();
        return response()->json(["status"=>true,"code"=>200,"msg"=>"Successfully Show","data"=>$data]);
    }
    
        public function applycoupon(Request $request)
    {
        if(empty($request->userid)){ return response()->json(["status"=>false,"code"=>500,"msg"=>"Please add Product into cart"]);}
        $userid = $request->userid;
        $cmpid = DB::table("cart")->where("customer_id",$userid)->first();
        if(empty($cmpid)){ return response()->json(["status"=>false,"code"=>500,"msg"=>"Please add Product into cart"]);}
        $couponid = $request->couponid;
        $coupn = Coupon::where("id",$couponid)->first();
        $cpntpe = $coupn->type;
        if($coupn->user_id==0){
            //////////////admin coupon h total pr lagega
            $attr_tlt = DB::table("cartattribute")->where("customer_id",$userid)->sum('totalprice');
            $couponprice = DB::table("cartattribute")->where("customer_id",$userid)->sum('coupon');
            $price =  $attr_tlt ;
            if($cpntpe=='percentage'){
                            $offerprice = $coupn->price;
                            $offeramnt = ($price*$offerprice)/100;
                        }else{
                            $offeramnt =  $coupn->price;
                        }
                        $coupname = $coupn->couponname;
                        $c2 = array("sellercoupon"=>$couponprice,"carttotal"=>$attr_tlt,"code"=>$coupn->code,"amount"=>$offeramnt,"finalamount"=>$price-$offeramnt);
                        $cpn = json_encode($c2);
                         DB::table("cart")->where("customer_id",$userid)->update(["couponbyadmin"=>$cpn]);
        }else{
            $couponon = $coupn->couponon;
            $coupapplyon = json_decode($coupn->couponapplyon);
            $cpncode = $coupn->code;
            if($couponon=='Brand'){
                $cartdata = DB::table("cart")->where("customer_id",$userid)->whereIn('brand_id', $coupapplyon)->get();
                foreach($cartdata as $cart){
                    $crtattribute = DB::table("cartattribute")->where("cart_id",$cart->id)->get();
                    foreach($crtattribute as $crtatr){
                        $prototal = $crtatr->prprice;
                        if($cpntpe=='percentage'){
                            $offerprice = $coupn->price;
                            $offeramnt = ($prototal*$offerprice)/100;
                        }else{
                            $offeramnt =  $coupn->price;
                        }
                        $coupname = $crtatr->couponname;
                        $cnpnme = $c2 =array();
                        if($coupname!=''){$cnpnme = json_decode($crtatr->couponname,true);}
                        $c2 = array("id"=>$couponid,"name"=>$coupn->name,"code"=>$cpncode,"amount"=>$offeramnt);
                        array_push($cnpnme,$c2);
                        
                        $new_prprice = $crtatr->prprice - $offeramnt;
                        $new_unitprice = $new_prprice * $crtatr->boxpcs;
                        $new_totalprice = $new_unitprice *  $crtatr->qty;
                        $new_taxamount = ($new_totalprice * $crtatr->tax)/100;
                        
                         DB::table("cartattribute")->where("id",$crtatr->id)
                         ->update(["coupon"=>$crtatr->coupon+$offeramnt,"couponname"=>$cnpnme,
                                    "amntaftrcoupn"=>$new_prprice,"unitprice"=>$new_unitprice,"totalprice"=>$new_totalprice,"taxamount"=>$new_taxamount
                         ]);
                         
                    }
                }
            }else{
                /////product pr hoga
                $cartdata = DB::table("cart")->where("customer_id",$userid)->whereIn('product_id', $coupapplyon)->get();
                foreach($cartdata as $cart){
                    $crtattribute = DB::table("cartattribute")->where("cart_id",$cart->id)->get();
                    foreach($crtattribute as $crtatr){
                        $prototal = $crtatr->prprice;
                        if($cpntpe=='percentage'){
                            $offerprice = $coupn->price;
                            $offeramnt = ($prototal*$offerprice)/100;
                        }else{
                            $offeramnt =  $coupn->price;
                        }
                        $coupname = $crtatr->couponname;
                        $cnpnme = $c2 =array();
                        if($coupname!=''){$cnpnme = json_decode($crtatr->couponname,true);}
                        $c2 = array("id"=>$couponid,"name"=>$coupn->name,"code"=>$cpncode,"amount"=>$offeramnt);
                        array_push($cnpnme,$c2);
                        
                        $new_prprice = $crtatr->prprice - $offeramnt;
                        $new_unitprice = $new_prprice * $crtatr->boxpcs;
                        $new_totalprice = $new_unitprice *  $crtatr->qty;
                        $new_taxamount = ($new_totalprice * $crtatr->tax)/100;
                        
                         DB::table("cartattribute")->where("id",$crtatr->id)
                         ->update(["coupon"=>$crtatr->coupon+$offeramnt,"couponname"=>$cnpnme,
                                    "amntaftrcoupn"=>$new_prprice,"unitprice"=>$new_unitprice,"totalprice"=>$new_totalprice,"taxamount"=>$new_taxamount
                         ]);
                    }
                }
                
            }
        }
       return response()->json(["status"=>true,"code"=>200,"msg"=>"Successfully Show"]);
    }
}
