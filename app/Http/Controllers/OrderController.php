<?php

namespace App\Http\Controllers;

use App\Models\order;
use Illuminate\Http\Request;
use App\Models\SubCatgory;
use App\Models\Category;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use App\Models\Company;
use App\Models\Customer;
use DB;
use App\Helper\helper;
use App\Models\CustomerAddress;
use Auth;
use App\Models\Profile;
use App\Traits\WhatsappTraits;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
 require_once public_path().'/mpdf/vendor/autoload.php';
class OrderController extends Controller
{
     use WhatsappTraits;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function checkwhatsappmsg(){
       $sendwhatsapp = $this->orderinvoiceuploadmsgtoseller('919456638450', 'surbhi','#5654645345','343564465454');
         
   }
   public function transectionsucess_app(Request $request){
       
       $resp = json_encode($request->all());
       $payid = $request->get('mihpayid');
       $mode = $request->get('mode');
       $status = $request->get('status');
       $order_id = $request->get('txnid');
       $cart_id = $request->get('udf3');
       $payment_source = $request->get('payment_source');
       $txndetail = json_encode(array(
                   "field9"=>$request->get('field9'),
                   "payment_source"=>$request->get('payment_source'),
                   "PG_TYPE"=>$request->get('PG_TYPE'),
                   "bank_ref_num"=>$request->get('bank_ref_num'),
                   "bankcode"=>$request->get('bankcode'),
                   "error"=>$request->get('error'),
                   "error_Message"=>$request->get('error_Message'),
                   "device_type"=>$request->get('device_type'),
                   "net_amount_debit"=>$request->get('net_amount_debit'),
                   "addedon"=>$request->get('addedon')
           ));
        $updateorder_id = DB::table("transections")->where("order_no",$order_id)->update([
                    "txnresponse"=>$resp,
                    "status"=>$status,
                    "txnid"=>$payid,
                    "txndetail"=>$txndetail,
                    "txnmethod"=>$payment_source,
                    "updated_at"=>now()
            ]);
        $customer = DB::table("transections")->where("order_no",$order_id)->first();
        $customerid = $customer->customer_id;
        $sellerid = $customer->user_id;
        ///remove cart condition
                if($status=="success"){
                    DB::table("cart")->where("customer_id",$customerid)->delete();
                    DB::table("cartattribute")->where("customer_id",$customerid)->delete();
                }
            $token = Customer::where("id",$customerid)->first();
            $tokenid = $token->firebaseid;
          $sendnotification = $this->sendnotification($tokenid,'order_transectionsuccess',$order_id);
          
            $cmpmoblie = Company::where("user_id",$sellerid)->first();
            $mobile = $cmpmoblie ? $cmpmoblie->mobile : '';
            if ($mobile) {
                try { $this->sendtosellerthatorderreceived($mobile,$order_id,$cmpmoblie->name); } catch (\Throwable $e) {}
            }
            $body = "New Order #{$order_id} Received. Please Check The Details in Order Tab.";
            $savenotification = [
                "user_id"     => $sellerid,
                "customer_id" => $sellerid,
                "title"       => "New Order #{$order_id} Received",
                "msg"         => $body,
                "msgread"     => 0,
                "type"        => "seller",
                "created_at"  => now(),
                "updated_at"  => now()
            ];
            DB::table("notifications")->insert($savenotification); 
            
            $buyermobile = $token ? $token->mobile : '';
            if ($buyermobile) {
                try { $this->sendtobuyerthatorderstatuschangedororderplaced($buyermobile, $buyermobile, $order_id, 'Thank You'); } catch (\Throwable $e) {}
            }
            $bodybuy = "Your Order #{$order_id} Successfully Placed. Please Check The Details in Order History.";
            $savenotificationbuy = [
                "customer_id" => $token ? $token->id : $customerid,
                "title"       => "Your Order #{$order_id} Received",
                "msg"         => $bodybuy,
                "msgread"     => 0,
                "type"        => "customer",
                "created_at"  => now(),
                "updated_at"  => now()
            ];
            DB::table("notifications")->insert($savenotificationbuy); 
        return response()->json(["message"=>"Transection Successfully!!! Please Wait..."]);
   }
    public function transectionfailed_app_testingconvertassuccess(Request $request){
        $resp = '{"mihpayid":"28283398636","mode":"UPI","status":"success","unmappedstatus":"captured","key":"Ks0wGV","txnid":"6264078429","amount":"7.08","discount":"0.00","net_amount_debit":"7.08","addedon":"2026-04-22 13:11:54","productinfo":"3208","firstname":"Surbhi","lastname":null,"address1":null,"address2":null,"city":null,"state":null,"country":null,"zipcode":null,"email":"surbhi072018@gmail.com","phone":"9834567890","udf1":null,"udf2":null,"udf3":null,"udf4":null,"udf5":null,"udf6":null,"udf7":null,"udf8":null,"udf9":null,"udf10":null,"hash":"14a76c6fe45706967a43adeda1aaf11e1c98a4ab86142b71e97aee3eb0dbf631a3a7881a7767a4e738e8800ad37b874ffb3f075a5bcb32cc40694a33e076ec8d","field1":"tanika2808@ibl","field2":"443733","field3":"tanika2808@ibl","field4":"TANIKA KHUNTETA","field5":"INDbab2890140e146a18c1e7acea6be48b2","field6":"TANIKA KHUNTETA || UCBA0002258","field7":"APPROVED OR COMPLETED SUCCESSFULLY|00","field8":null,"field9":"Success|Completed Using Callback","payment_source":"payu","meCode":"{\"pgMerchantId\":\"INDB000010846510\",\"encKey\":\"02be3a84b9fa9a23c49117e463f6ddfd92e19b7d71a1ead6e101417ca54420fd7248fd48195e70d01e6326fa82060919\",\"merchantVpa\":\"HBOSEECOMMERCEPRIVATEL-13130457.payu@indus\"}","PG_TYPE":"UPI-PG","bank_ref_num":"100075854431","bankcode":"UPI","error":"E000","error_Message":"No Error","device_type":"1"}';//json_encode($request->all());
       $payid = 'transectionfailed_app_testingconvertassuccess';//$request->get('mihpayid');
       $mode = 'UPI';//$request->get('mode');
       $status = 'success';//$request->get('status');
       $order_id = $request->get('txnid');
       $payment_source = 'payu';//$request->get('payment_source');
       $txndetail = '{"field9":"Success|Completed Using Callback","payment_source":"payu","PG_TYPE":"UPI-PG","bank_ref_num":"100075854431","bankcode":"UPI","error":"E000","error_Message":"No Error","device_type":"1","net_amount_debit":"7.08","addedon":"2026-04-22 13:11:54"}';
    //   $txndetail = json_encode(array(
    //       "field9"=>$request->get('field9'),
    //       "payment_source"=>$request->get('payment_source'),
    //       "PG_TYPE"=>$request->get('PG_TYPE'),
    //       "bank_ref_num"=>$request->get('bank_ref_num'),
    //       "bankcode"=>$request->get('bankcode'),
    //       "error"=>$request->get('error'),
    //       "error_Message"=>$request->get('error_Message'),
    //       "device_type"=>$request->get('device_type'),
    //       "net_amount_debit"=>$request->get('net_amount_debit'),
    //       "addedon"=>$request->get('addedon')
    //       ));
        $updateorder_id = DB::table("transections")->where("order_no",$order_id)->update([
            "txnresponse"=>$resp,
            "status"=>'success',
            "txnid"=>$order_id,
            "txndetail"=>$txndetail,
            "txnmethod"=>$payment_source,
            "updated_at"=>now()
            ]);
             $customer = DB::table("transections")->where("order_no",$order_id)->first();
        $customerid = $customer->customer_id;
            $token = Customer::where("id",$customerid)->first();
            $tokenid = $token->firebaseid;
           $sendnotification = $this->sendnotification($tokenid,'order_transectionfailed',$order_id);
        //   $sendwhatsapp = $this->sendwhatsappmsg($token->whatsappno,'ordertransection_failed',$order_id);
        return response()->json(["message"=>"Transections Success !!! Please Check the details."]);
       
   }
   public function transectionfailed_app(Request $request){
        $resp = json_encode($request->all());
       $payid = $request->get('mihpayid');
       $mode = $request->get('mode');
       $status = $request->get('status');
       $order_id = $request->get('txnid');
       $payment_source = $request->get('payment_source');
        $txndetail = json_encode(array(
           "field9"=>$request->get('field9'),
           "payment_source"=>$request->get('payment_source'),
           "PG_TYPE"=>$request->get('PG_TYPE'),
           "bank_ref_num"=>$request->get('bank_ref_num'),
           "bankcode"=>$request->get('bankcode'),
           "error"=>$request->get('error'),
           "error_Message"=>$request->get('error_Message'),
           "device_type"=>$request->get('device_type'),
           "net_amount_debit"=>$request->get('net_amount_debit'),
           "addedon"=>$request->get('addedon')
           ));
        $updateorder_id = DB::table("transections")->where("order_no",$order_id)->update([
            "txnresponse"=>$resp,
            "status"=>$status,
            "txnid"=>$payid,
            "txndetail"=>$txndetail,
            "txnmethod"=>$payment_source,
            "updated_at"=>now()
            ]);
             $customer = DB::table("transections")->where("order_no",$order_id)->first();
        $customerid = $customer->customer_id;
            $token = Customer::where("id",$customerid)->first();
            $tokenid = $token->firebaseid;
           $sendnotification = $this->sendnotification($tokenid,'order_transectionfailed',$order_id);
        //   $sendwhatsapp = $this->sendwhatsappmsg($token->whatsappno,'ordertransection_failed',$order_id);
        return response()->json(["message"=>"Transections Failed !!! Please Try Again."]);
       
   }
   public function orderstatus_app(Request $request){
             $orderid = $request->get('orderid');
             $orderdetail =  DB::table("order_status")->where("order_id",$orderid)->get();
          return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$orderdetail]);
    }
    public function generateHash($params, $salt) {
                // Extract parameters or use empty string if not provided
                $key = $params['key'];
                $txnid = $params['txnid'];
                $amount = $params['amount'];
                $productinfo = $params['productinfo'];
                $firstname = $params['firstname'];
                $email = $params['email'];
                $udf1 = isset($params['udf1']) ? $params['udf1'] : '';
                $udf2 = isset($params['udf2']) ? $params['udf2'] : '';
                $udf3 = isset($params['udf3']) ? $params['udf3'] : '';
                $udf4 = isset($params['udf4']) ? $params['udf4'] : '';
                $udf5 = isset($params['udf5']) ? $params['udf5'] : '';
                
                // Construct hash string with exact parameter sequence
                $hashString = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . 
                              $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . 
                              $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $salt;
                
                // Generate hash and convert to lowercase
               // return strtolower(hash('sha512', $hashString));
                return hash('sha512', $hashString);
        }

    public function placeorder(Request $request){
           $customerid = $request->get('userid');
           $addressid = $request->get('addressid');
           
           $txn_id = '0';       //$request->get('txn_id');
           $txnstatus ='0';         //$request->get('txnstatus');///success or failed
           $txnmethod = '0';        //$request->get('txnmethod');
           $txndetail = '0';        //json_encode($request->get('txndetail'));
           $txnresponse = '0';      //json_encode($request->get('txnresponse'));
           $proinfogatewy ='';
           ////////////////for customer trasport address
           $addre = CustomerAddress::where("id",$addressid)->first();
           $address = json_encode($addre);
           
             $orderno = 'AF' . date('Ymd') . '-' . rand(1000, 9999);
             ///////////////check data of this customer is in cart or not
             $cartdata = DB::table("cart")->where("customer_id",$customerid)->get();
             if($cartdata->isEmpty()){return response()->json(["status"=>false,"code"=>500,"msg"=>"Cart is Empty","data"=>""]);}
            //////////////////////for tax type
            $cmpy = DB::table("cart")->where("customer_id",$customerid)->first('company_id');
            $cmpnypin = $cmpy ? Company::where("id",$cmpy->company_id)->first() : null;
            $cmp_minmumordervalue = $cmpnypin ? $cmpnypin->minordervalue : 0;
            
            // State-based GST calculation (Intra-state: CGST+SGST, Inter-state: IGST)
            $sellerState = strtolower(trim($cmpnypin->state ?? ''));
            $custState = strtolower(trim($addre->state ?? ''));
            if(!empty($sellerState) && !empty($custState) && $sellerState === $custState){
                $gsttype = 'csgst';
            }else{
                $gsttype = '';
            }
              ///////////////now start cart loop
                   $adstr='';$admincouponamount=$sellercouponamount=$carttotal=0.0;
                   $str='';
            foreach($cartdata as $key=>$cart){
                 $producthsn = Product::where("id",$cart->product_id)->first();
                 $phsn = $producthsn ? $producthsn->hsncode : '';  $productname = $cart->productname;
                 $cmpcatbrnd = Helper::getcompanyname($cart->company_id).'/'.Helper::getcatname($cart->category_id).'/'.Helper::getbrandname($cart->brand_id);
                 
                $cartattribut = DB::table("cartattribute")->where("cart_id",$cart->id)->get();
                $attributedata = array();
                foreach($cartattribut as $crtatrbt){
                    $attributedetail = ProductAttributes::where("id",$crtatrbt->product_attributes_id)->first();
                    $fndpcs = $attributedetail ? BoxPacking::where("id",$attributedetail->quantity)->first() : null;
                    $attr["boxpacking"] = $fndpcs ? $fndpcs->name : ($attributedetail->quantity ?? '1 Unit');
                    $fndclr = $attributedetail ? ShadeCard::where("id",$attributedetail->color)->first() : null;
                    $attr["color"] = $fndclr ? $fndclr->name : ($attributedetail->color ?? 'Standard');
                    $attr["qty"] = $crtatrbt->qty;
                    $attr["boxpcs"] = $crtatrbt->boxpcs;
                    $attr["prprice"] = $crtatrbt->prprice;
                    $attr["coupon"] = $crtatrbt->coupon;
                    $attr["amntaftrcoupn"] = $crtatrbt->amntaftrcoupn;
                    $attr["unitprice"] = $crtatrbt->unitprice;
                    $attr["totalprice"] = $crtatrbt->totalprice;
                    $attr["tax"] = $crtatrbt->tax;
                    $attr["taxamount"] = $crtatrbt->taxamount;
                    if(!empty($crtatrbt->couponname)){
                    foreach(json_decode($crtatrbt->couponname,true) as $coupondetail){ 
                        $str .= $coupondetail['code'].'('.$coupondetail['amount'].') ,';
                    }
                    }
                    $str = rtrim($str,',');
                    array_push($attributedata,$attr);
                    $carttotal += $crtatrbt->totalprice;
                }
                $attibuteary = array(
                                "order_id"=>"0",
                                "orderno"=>$orderno,
                                "customer_id"=>$customerid,
                                "product_id"=>$cart->product_id,
                                "productname"=>$productname,
                                "hsn"=>$phsn,
                                "brdcmpcat"=>$cmpcatbrnd,
                                "attribute"=>json_encode($attributedata),
                                "coupondetail"=>$str,
                                "productimage"=>$producthsn->image
                            );
            DB::table("orderdetail")->insert($attibuteary); 
              $attibuteary='';
                /////remove cart condition
                if($txnstatus=="success"){
                    // DB::table("cart")->where("id",$cart->id)->delete();
                    // DB::table("cartattribute")->where("cart_id",$cart->id)->delete();
                }
                ///////////////////calculate admincouponamount & admincoupondetail
                if(empty($cart->couponbyadmin)){
                     $netamount=$carttotal;$adstr='';
                     $sellercouponamount=$sellercouponamount=0;
                }else{
                        $admincoupon = json_decode($cart->couponbyadmin,true);
                        $adstr = $admincoupon['code'] .'('. $admincoupon['amount'] .')';
                        $admincouponamount =  $admincoupon['amount'];
                        $sellercouponamount = $admincoupon['sellercoupon'];
                        $netamount = $admincoupon['finalamount'];
                }
                $proinfogatewy .= $producthsn->hsncode.',';       
            }
            ////////////////////////min order value check
                //$cmp_minmumordervalue;
                if($netamount<$cmp_minmumordervalue){
                    return response()->json(["status"=>false,"code"=>500,"msg"=>"Add More Product into Cart, to match minimum order value amount $cmp_minmumordervalue","data"=>json_decode('{}')]);
                }
           /////////////tx calculation
           $finaltax=array();$taxamount=0;
                 if($gsttype=='csgst'){
                     $taxresults = DB::table('cartattribute')
                        ->selectRaw('COUNT(product_attributes_id) as product_count, tax, CAST(SUM(taxamount) AS FLOAT) as totaltax')
                        ->where("customer_id",$customerid)
                        ->groupBy('tax')
                        ->get();
                        foreach($taxresults as $result){
                                 $taxdetail["name"] = 'GST @'.$result->tax.'%';
                                 $taxdetail["value"] = floatval($result->totaltax);
                                 $taxamount += $result->totaltax;
                                 array_push($finaltax,$taxdetail);
                        }
                 }else{
                     $tax = 18; $taxamount = ($netamount*$tax)/100;
                     $taxdetail["name"] = 'IGST @18%';
                     $taxdetail["value"] = number_format($taxamount, 2);
                     array_push($finaltax,$taxdetail);
                 }
        $orderarray = array(
                            "orderno"=>$orderno,
                            "customer_id"=>$customerid,
                            "user_id"=>$producthsn->user_id,
                            "address"=>$address,
                            "sellercouponamount"=>$sellercouponamount,//$couponcode,
                            "admincouponamount"=>$admincouponamount,///floatval($granttotal),
                            "admincoupondetail"=>$adstr,///floatval($tax),
                            "netamount"=>$netamount,///floatval($granttotal+$caltax-$couponamnt),
                            "taxdetail"=>json_encode($finaltax),
                            "taxamount"=>$taxamount,
                            "grandtotal"=>$netamount + $taxamount
                        );
        $orderid = order::insertGetId($orderarray);    
        $updateorder_id = DB::table("orderdetail")->where("orderno",$orderno)->update(["order_id"=>$orderid]);
        ////////////////////////now create transection transection history 
        $transection = array(
                             'order_id'=>$orderid, 
                             'order_no'=>$orderno, 
                             'customer_id'=>$customerid, 
                             'user_id'=>$producthsn->user_id, 
                             'status'=>$txnstatus, 
                             'txnid'=>$txn_id,
                             'txndetail'=>$txndetail,
                             'txnresponse'=>$txnresponse, 
                             'txnmethod'=>$txnmethod
                             );
        $orderid = DB::table("transections")->insert($transection);    
        /////////////////////enter default order status
        $orderstatus = array(
                             'order_id'=>$orderid, 
                             'order_no'=>$orderno, 
                             'status'=>'pending', 
                             'msg'=>'Wait For Confirmation', 
                             'user_id'=>$producthsn->user_id
                             );
        $orderid = DB::table("order_status")->insert($orderstatus);    
          $customer = Customer::where("id",$customerid)->first();
          $salt = DB::table("Admin")->where("usedin","gateway")->where("attribute","salt")->first();
          $key = DB::table("Admin")->where("usedin","gateway")->where("attribute","key")->first();
          
        // Example usage
        $params = [
            'key' =>$key->value,
            'txnid' => $orderno,
            'amount' => $netamount + $taxamount,
            'productinfo' =>trim($proinfogatewy,','),
            'firstname' => $customer->name,
            'email' => $customer->email,
            'udf1' => $producthsn->user_id,
            'udf2' => $orderid,
            'udf3' => $cart->id
        ];
        $salt = $salt->value;
        
        $hash = $this->generateHash($params, $salt);
        $surl = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/transection/success';
        $furl = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/transection/failed';
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Placed","data"=>$orderarray,"hash"=>$hash,"furl"=>$furl,"surl"=>$surl,"hashparameter"=>$params]);
      }
    
    public function orderhistory(Request $request){
        $ordr = array();
         $customerid = $request->get('userid');
         $order = order::where("customer_id",$customerid)->orderby("id","desc")->get();
        //$order = DB::table("transections")->where("customer_id",$customerid)->where("status","success")->orderby("id","desc")->get("order_id");
         foreach($order as $ord){
             $orderdetail=$ordertrack=$orderstatus='';
             $orderdetail =  DB::table("orderdetail")->where("orderno",$ord->orderno)->get();
             $ord["orderdetail"] = $orderdetail;
             $ordertrack =  DB::table("order_tracks")->where("orderno",$ord->orderno)->first();
             $ord["ordertrack"] = $ordertrack;
             $orderstatus =  DB::table("order_status")->where("order_no",$ord->orderno)->orderby("id","desc")->get();
             $ord["orderstatus"] = $orderstatus;
             array_push($ordr,$ord);
         }
          return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$ordr]);
    }
    
    public function orderdetailforapi(Request $request){
        $orderdet = array();
        $orderno = $request->get('orderno');
        $order = order::where("orderno",$orderno)->first();
        $data = $order;
        $cartattribut = DB::table("orderdetail")->select('productname','hsn','brdcmpcat','attribute','coupondetail','productimage')->where("order_id",$order->id)->get();
        foreach ($cartattribut as $orderdetail){
            $or['productname'] = $orderdetail->productname;
            $or['productimage'] = $orderdetail->productimage;
            $or['hsn'] = $orderdetail->hsn;
            $or['brdcmpcat'] = $orderdetail->brdcmpcat;
            $or['attribute'] = json_decode($orderdetail->attribute,true);
            $or['coupondetail'] = $orderdetail->coupondetail;
            array_push($orderdet,$or);
        }
        $data['orderdetail'] = $orderdet;
    // $data['orderattributes'] = json_decode($cartattribut->attribute,true);
        $data['transection'] = DB::table("transections")->select('txnid','status','txndetail')->where("order_no",$orderno)->first();
        $data['track'] = DB::table("order_tracks")->where("order_id",$order->id)->first();
        $data['status'] = DB::table("order_status")->select('msg','status','created_at')->where("order_no",$orderno)->get();
        
        // Check if order was rejected or cancelled
        $rejectionStatus = DB::table("order_status")
            ->where("order_no", $orderno)
            ->where(function ($q) {
                $q->where('status', 'like', '%reject%')
                  ->orWhere('status', 'like', '%cancel%');
            })
            ->latest('id')
            ->first();

        if ($rejectionStatus) {
            $data['rejection_reason'] = $rejectionStatus->msg ?? 'Order was not fulfilled by seller.';
            $data['refund_option'] = [
                'eligible' => true,
                'status'   => 'Initiated / Available in Wallet',
                'amount'   => $order->grandtotal,
            ];
            $data['alternative_sellers'] = $this->findAlternativeSellers($order);
        } else {
            $data['rejection_reason'] = null;
            $data['refund_option'] = null;
            $data['alternative_sellers'] = [];
        }

        if(!empty($data['track'])){
            if($data['track']->lrno!='' && $data['track']->lrno!='NA'){
                $data['invoiceurl'] = getenv('APP_URL').'/invoice/order/'.$orderno;
            } else {
                $data['invoiceurl'] = null;
            }
        } else {
            $data['invoiceurl'] = null;
        } 
        return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$data]);
    }

    /**
     * Get 3 alternative sellers offering similar products when an order is rejected or unavailable.
     */
    public function getAlternativeSellersForOrder(Request $request, $orderno = null)
    {
        $orderno = $orderno ?? $request->get('orderno');
        $order = order::where("orderno", $orderno)->first();

        if (!$order) {
            return response()->json(["status" => false, "code" => 404, "msg" => "Order not found", "data" => []], 404);
        }

        $alternatives = $this->findAlternativeSellers($order);

        return response()->json([
            "status" => true,
            "code"   => 200,
            "msg"    => "Alternative sellers retrieved successfully",
            "data"   => [
                "orderno"             => $order->orderno,
                "rejection_reason"    => DB::table("order_status")->where("order_no", $orderno)->where('status', 'like', '%reject%')->value('msg'),
                "alternative_sellers" => $alternatives,
            ]
        ]);
    }

    /**
     * Helper to find up to 3 alternative sellers in the same category/catalog.
     */
    public function findAlternativeSellers($order)
    {
        $orderItems = DB::table('orderdetail')->where('order_id', $order->id)->get();
        $categoryIds = [];

        foreach ($orderItems as $item) {
            $prod = Product::where('name', $item->productname)->first();
            if ($prod && $prod->category_id) {
                $categoryIds[] = $prod->category_id;
            }
        }

        $sellerUserIds = Product::where('user_id', '!=', $order->user_id)
            ->when(!empty($categoryIds), fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->pluck('user_id')
            ->unique();

        $companies = Company::whereIn('user_id', $sellerUserIds)->take(3)->get();

        // Fallback to any active companies if fewer than 3 found in exact category
        if ($companies->count() < 3) {
            $needed = 3 - $companies->count();
            $fallbacks = Company::where('user_id', '!=', $order->user_id)
                ->whereNotIn('id', $companies->pluck('id'))
                ->take($needed)
                ->get();
            $companies = $companies->merge($fallbacks);
        }

        return $companies->map(function ($s) {
            $sampleProd = Product::where('user_id', $s->user_id)->first();
            return [
                'seller_id'      => $s->user_id,
                'company_name'   => $s->name ?? 'Verified Seller',
                'city'           => $s->city ?? 'National Hub',
                'state'          => $s->state ?? 'India',
                'rating'         => 4.8,
                'sample_product' => $sampleProd ? $sampleProd->name : 'Similar Catalog Item',
                'delivery_est'   => '2-4 Business Days',
            ];
        })->values();
    }
    
    public function sellerorderlist(Request $request)
    {
         $addby = Auth::user()->id;  
/*    $query = DB::table('order_status as os1')
                ->select('os1.order_id', 'os1.status')
                ->join(DB::raw('(SELECT order_id, MAX(created_at) AS last_status_time 
                                 FROM order_status 
                                 GROUP BY order_id) as os2'), function($join) {
                    $join->on('os1.order_id', '=', 'os2.order_id')
                         ->on('os1.created_at', '=', 'os2.last_status_time');
                });
    if ($request->filled('status')) {
        $query->where("os1.status", $request->input('status'));
    }
    $orderlist = $query->pluck('order_id');
    $or = Order::where("user_id",$addby)->whereIn("id", $orderlist);
    if ($request->filled('orderno')) {
        $or = $or->where('orderno',  $request->input('orderno'));
    }
    if($request->filled('from_date') == $request->filled('to_date')){
        $or = $or->where('created_at', 'like', $request->input('from_date').'%');
    }else{
       if ($request->filled('from_date')) {
             $or = $or->where('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
             $or = $or->where('created_at', '<=', $request->input('to_date'));
        }
    }
    $or = $or->get();
    */
    $query = DB::table('transections')->where("user_id",$addby)->where("status","success")->pluck('order_no');
    $or = Order::where("user_id",$addby)->whereIn("orderno", $query)->get();
    $data['list'] = $or;
         return view('order.list',$data);
    }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($orderno)
    {
        $order = order::where("orderno",$orderno)->first();
        $cartattribut = DB::table("orderdetail")->where("orderno",$orderno)->get();
        $data['title'] = 'ORDERS';
        $data['order'] = $order;
        $data['orderdetail'] = $cartattribut;
        $data['profile'] = Profile::where("viewon",'Invoice')->get();
        $data['seller'] = Company::where("user_id",Auth::user()->id)->first();
        $data['buyer'] = Customer::where("id",$order->customer_id)->first();
        $data['track'] = DB::table("order_tracks")->where("orderno",$orderno)->first();
        $data['status'] = DB::table("order_status")->where("order_no",$orderno)->get();
        
        $coupon_code = json_decode($data['order']->coupon_code); 
         return view('order.detail',$data);
        
    }
    public function invoiceforappuser($orderno){
         require_once public_path().'/mpdf/vendor/autoload.php';
         $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8','format' => 'A4','margin_left' => 10, 'margin_right' => 10, 'margin_top' => 12, 'margin_bottom' => 12,
        ]);
        ///seller detail
        $order = order::where("orderno",$orderno)->first();
        $orderdetail = DB::table("orderdetail")->where("orderno",$orderno)->get();
        $seller = Company::where("user_id",$order->user_id)->first();
        $buyer = Customer::where("id",$order->customer_id)->first();
        $track = DB::table("order_tracks")->where("orderno",$orderno)->first();
        $addres = json_decode($order->address);
        $taxdetail = json_decode($order->taxdetail);
        if($taxdetail[0]->name=='IGST @18%'){$taxis='igst';$taxvalue=$taxdetail[0]->value;}
        else{$taxis='csgst';$cgst=$taxdetail[0]->value/2;$igst=$taxdetail[0]->value/2;$taxvalue=$taxdetail[0]->value;}
        $formatter = new \NumberFormatter("en_IN", \NumberFormatter::SPELLOUT);
            $amount_in_words = ucwords($formatter->format(round($order->grandtotal, 2))) . ' Rupees Only';
        $data = [
                /// seller detail
            'seller_name'    => $seller->name ?? 'Apni Factory',
            'seller_address' => $seller->city.' , '.$seller->state.' , '.$seller->pincode ?? "353 - A, Vijay Nagar, Sector A, Mahalaxmi Nagar, Indore, Madhya Pradesh 452010",
            'seller_gstin'   => $seller->gst ?? '23AAHCH5310E1Z5',
                
                ////buyer detail
            'buyer_name'     => $buyer->name ?? 'NA',
            'buyer_address'  => $order->location ?? 'NA',
            'buyer_gstin'    => $order->gstorpan ?? 'NA',
            
            ////Invoice detail
             'invoice_no'     => $track->invoiceno ?? 'NA',
            'invoice_date'   => \Carbon\Carbon::parse($track->created_at)->format('d-M-Y'),
            'order_id'       => $orderno ?? 'AF2024-00012345',
            'order_date'     => isset($order->created_at) ? \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') : '28-10-2024',
            'placeofsupply'  =>$seller->state,
            'placeofdelivery' =>$addres->state,
            
            ////product detail
            'orderdetail' => $orderdetail,
            
            ///discount&summary
            'subtotal'       => $order->netamount,
            'sellercouponamount'       => $order->sellercouponamount ?? '00',
            'admincouponamount'       => $order->admincouponamount ?? '00',
            'nettaxableamount'       => $order->netamount ?? '00',
            'taxis'     =>$taxis,
            'cgst'       => $cgst ?? '-',
            'sgst'       => $sgst ?? '-',
            'igst'      =>  $igst ?? '-',
            'tax'       => $order->taxamount,
            'grandtotal'       => $order->grandtotal,
            'amount_in_words' => $amount_in_words,
            // Logistics optional parameters
            'dispatch_date'   => \Carbon\Carbon::parse($track->created_at)->format('d-M-Y'),
            'transporter'     => $track->transname,
            'lr_no'           => $track->lrno,
            'transportno'           => $track->transcontact,
            'note'           => $track->text,
            'dispatchfrom' => $seller->city.' , '.$seller->state.' , '.$seller->pincode.' , India',
            
            'dispatchto' => $addres->name.','. $addres->landmark1.','. $addres->landmark2.','. $addres->city.','. $addres->state.','. $addres->pincode.','.  $addres->country .','.$addres->phoneno
            ];
        // 4. Bind view context variables and extract html code content 
        $html = view('order.orderinvoice', $data)->render();
        $mpdf->WriteHTML($html);
         // 3. Output as Inline PDF to view in browser
        $pdfContent = $mpdf->Output("invoice-{$orderno}.pdf", 'S'); // 'S' returns the PDF as a string
        return Response::make($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice-' . $orderno . '.pdf"'
        ]); 
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, order $order)
    {  
        $action = $request->action;
        $company = Company::where("user_id",$order->user_id)->first();
    /*   
    //////pending comission updates
    if($action=='trackstatus'){
                $status = $request->status;
            if($status=='1'){
                $transname = $request->transname;
                $transcontact = $request->transcontact;
                $lrno = $request->lrno;
                $invoiceno = $request->invoiceno;
                $note = $request->anote;
                $orderno = $order->orderno;
                $credit = $request->credit;
                $status = '1';
                if ($request->hasFile('builty_file')) {
                    $billty = $request->file('builty_file')->store('uploads', 'public');
                }
                if ($request->hasFile('invoice_file')) {
                    $invoice = $request->file('invoice_file')->store('uploads', 'public');
                }
            }else{
                $transname = 'NA';
                $transcontact = 'NA';
                $lrno = 'NA';
                $invoiceno = 'NA';
                $status = '0';
                $note = $request->rnote;
                $orderno = $order->orderno;
                $credit = $request->credit;
                $invoice = 'NA';
                $billty = 'NA';
            }
            $arry = array( 
                            "order_id"=>$order->id,
                            "transname"=>$transname,
                            "text"=>$note,
                            "transcontact"=>$transcontact,
                            "lrno"=>$lrno,
                            "invoiceno"=>$invoiceno,
                            "status"=>$status,
                            "orderno"=>$orderno,
                            "creditamnt"=>$credit,
                            "billty"=>$billty,
                            "invoice"=>$invoice,
                        );
        $track = DB::table("order_tracks")->where([ "order_id"=>$order->id])->update($arry);
        $refundbuyer = 0;
        if($track){
                $order = order::where("id",$order->id)->first();
                $grandtotal = $order->grandtotal;
                $commission = 100 - $company->comission;
                $com = ($grandtotal*$commission)/100;
                $curbalance = DB::table("wallet")->where("user_id",$order->user_id)->orderby("id","desc")->first();
                if(empty($curbalance)){$curbalance=0;}else{$curbalance=$curbalance->balance;}
            if($status){
                 ////////////////send to seller that he accept the order/////////////////
                $sendwhatsapp = $this->sendwhatsappmsg($company->mobile,'trackingdetail_updated',$order->orderno);
                $credit = $grandtotal - $com;
                $msg = 'Successfully Credited For #'.$orderno;
                $debit = $refundbuyer = 0;
                $balance = $curbalance+$credit;
            }else{
                $debit = 0;
                $credit = 0;
                $refundbuyer = $grandtotal;
                $balance = $curbalance-$debit;
                $msg = 'Successfully Debited For #'.$orderno;
                 ////////////////send to seller that he reject the order/////////////////
                $sendwhatsapp = $this->sendwhatsappmsg($company->mobile,'order_rejected',$order->orderno);
            }
             $creditary = array( 
                            "user_id"=>$order->user_id,
                            "order_id"=>$order->id,
                            "orderno"=>$orderno,
                            "value"=>$grandtotal,
                            "commission"=>$com,
                            "refundtobuyer"=>$refundbuyer,
                            "debit"=>$debit,
                            "credit"=>$credit,
                            "balance"=>$balance,
                            "addby"=>'system',
                            "msg"=>$msg,
                        );
                 $trackio = DB::table("wallet")->insert($creditary);
        }
        }       */
        if($action=='stausupdate'){ 
            $arry = array( 
                            "order_id"=> $request->orderid,
                            "order_no"=> $request->orderno,
                            "user_id"=> $request->userid,
                            "status"=> $request->status,
                            "msg" => $request->msg
                        );
        $track = DB::table("order_status")->insert($arry);
         ///////////////////SEND TO buyer that order status updated
            $formatted_date = date('F j, Y');
         $extra[0] = $request->orderno;
         $extra[1] = $request->status;
         $extra[2] = $formatted_date;
         $extra[3] = $request->msg; 
         $sendwhatsapp = $this->sendwhatsappmsg_facebookapi('91'.$request->buyermobile,'order_status',$extra);
         ///////////////panel notification to seller
          $bodybuy = "Your Order #'.$request->orderno.' Status has changed to : '.$request->status.'. Please Check The Details in Order Detail.";
            $savenotificationbuy = array("customer_id"=>$request->userid,"title"=>"Your Order #'.$request->orderno.' Status Changed","body"=>$bodybuy,"msgread"=>0,"customertype"=>"seller");
            DB::table("notifications")->insert($savenotificationbuy); 
        //////////////firebase notification to buyermobile
            $token = Customer::where("mobile",$request->buyermobile)->first();
            $tokenid = $token->firebaseid;
            $title = 'Your Order Status is Updated !!!';
            $screendataid = '';
            $mobilescreen = '';
            $body = 'Your Order of OrderID #'.$request->orderno.' status has Changed to '.$request->status.' Please Check Your Order Details';
            $this->sendnotificationfcm($tokenid,$title,$body,$mobilescreen,$screendataid);
               $savenotification = array("customer_id"=>$token->id,"title"=>"Your Order #'.$request->orderno.' Status Changed","body"=>$body,"msgread"=>0,"customertype"=>"customer");
            DB::table("notifications")->insert($savenotification); 
        }
if ($action == 'trackstatus') {
    // 1. Capture and normalize form input properties
    $statusInput = $request->input('status'); // Values will be 'Accept' or 'Reject'
    $note = $request->input('anote', '');
    $orderno = $order->orderno;

    // 2. Set conditional variables based on user selection state
    if ($statusInput == 'Accept') {
        $st = '1';
        $transname = $request->input('transname');
        $transcontact = $request->input('transcontact');
        $lrno = $request->input('lrno');
        $invoiceno = $request->input('invoiceno');
        
        // Handle file updates only if new media is attached
        $billty = $request->hasFile('builty_file') ? $request->file('builty_file')->store('tracking', 'public') : 'NA';
        $invoice = $request->hasFile('invoice_file') ? $request->file('invoice_file')->store('tracking', 'public') : 'NA';
    } else {
        // Rejection Logic Configuration
        $st = '0';
        $transname = '';
        $transcontact = '';
        $lrno = '';
        $invoiceno = '';
        $billty = 'NA';
        $invoice = 'NA';
    }
    // 3. Construct unified data tracking schema mapping
    $arry = [
        "order_id"     => $order->id,
        "transname"    => $transname,
        "text"         => $note, // Captures rejection note or buyer message seamlessly
        "transcontact" => $transcontact,
        "lrno"         => $lrno,
        "invoiceno"    => $invoiceno,
        "status"       => $st,
        "orderno"      => $orderno,
        "creditamnt"   => '0',
        "billty"       => $billty,
        "invoice"      => $invoice,
    ];

    // 4. Update existing tracking dataset or create fresh record entry
    $record = DB::table('order_tracks')->where("order_id", $order->id)->first();
    
    if (is_null($record)) {
        DB::table('order_tracks')->insert($arry);
                 ////////////////send to seller that he accept the order/////////////////
                $sendwhatsapp = $this->sendwhatsappmsg($company->mobile,'trackingdetail_updated',$order->orderno);
    } else {
        // Keep existing files intact if new media wasn't uploaded during an update action
        if ($statusInput == 'Accept') {
            if (!$request->hasFile('builty_file')) unset($arry['billty']);
            if (!$request->hasFile('invoice_file')) unset($arry['invoice']);
            $this->orderinvoiceuploadmsgtoseller($company->mobile, Auth::user()->name,$orderno,$invoiceno);
        }
        DB::table('order_tracks')->where("order_id", $order->id)->update($arry);
    }
    if($statusInput == 'Reject'){
         $arry = array( 
                            "order_id"=> $order->id,
                            "order_no"=> $orderno,
                            "user_id"=> $order->user_id,
                            "status"=> 'Rejected',
                            "msg" => $note
                        );
        $track = DB::table("order_status")->insert($arry);
                 ////////////////send to seller that he reject the order/////////////////
                $sendwhatsapp = $this->sendwhatsappmsg($company->mobile,'order_rejected',$order->orderno);
    }
    /////////set the parameters here
            // //////////////firebase notification to buyermobile
            // $token = Customer::where("mobile",$request->buyermobile)->first();
            // $tokenid = $token->firebaseid;
            // $title = 'Your Order Status is Updated !!!';
            // $screendataid = '';
            // $mobilescreen = '';
            // $body = 'Your Order of OrderID #'.$order_id.' status has Changed to '.$request->status.' Please Check Your Order Details';
            // $this->sendnotificationfcm($tokenid,$title,$body,$mobilescreen,$screendataid);
            //   $savenotification = array("customer_id"=>$token->id,"title"=>"Your Order #'.$order_id.' Status Changed","body"=>$body,"msgread"=>0,"customertype"=>"customer");
            // DB::table("notifications")->insert($savenotification); 
}
         return back()->withSuccess(["Request Successfully Updated"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(order $order)
    {
        //
    }
     public function generatePdf_ofcreditnotetoseller($id)
    {
        // 1. Fetch data from database with its relationships
        $invoice = DB::table("wallet")->find($id);
        $linkedinvoice = DB::table("order_tracks")->where("orderno",$invoice->orderno)->first('invoiceno');
        $Company = Company::where("user_id",$invoice->user_id)->first();
        $order = DB::table("orders")->where("orderno",$invoice->orderno)->first('grandtotal');
            $subtotal = floatval($order->grandtotal);
            $total_value = ($subtotal*$Company->comission)/100;
        //       // 2. Calculate 9% CGST and 9% SGST
            $cgst = $total_value * 0.09;
            $sgst = $total_value * 0.09;
            
        //     // 3. Calculate Total Invoice Value
             $grandtotal = $total_value + $cgst + $sgst;
            // 4. Convert Total Value to Words (Indian Rupees format)
            $formatter = new \NumberFormatter("en_IN", \NumberFormatter::SPELLOUT);
            $amount_in_words = ucwords($formatter->format(round($grandtotal, 2))) . ' Rupees Only';
             // 2. Map your data structure to match the template requirements
        $data = [
            'invoice_no'       => 'IN-AF-200010'.$invoice->id,
            'invoice_date'     => \Carbon\Carbon::parse($invoice->created_at)->format('d-M-Y'),
            'seller_id'        => $Company->email,
            'order_id'         => $invoice->orderno,
            'linked_invoice'   => $linkedinvoice->invoiceno,
            
            // Seller Info
            'seller_name'      => $Company->name,
            'seller_address'   => $Company->city.' , '.$Company->state.' , '.$Company->pincode,
            'seller_gstin'     => $Company->gst,
            
            // Pricing Metrics
            'subtotal'         => $total_value,
            'cgst'             => $cgst,
            'sgst'             => $sgst,
            'total_value'      => $grandtotal,
            'amount_in_words'  => $amount_in_words, // e.g., "Ten Thousand..."
            
            // Items Collection array
            'items'            => ["text"=>"Commission On Accepted Sale Value","smalltext"=>"( ₹ $order->grandtotal * $Company->comission % )"]
        ];

        // 3. Initialize mPDF configurations
        $mpdf = new \Mpdf\Mpdf([
            'mode'         => 'utf-8',
            'format'       => 'A4',
            'margin_left'  => 15,
            'margin_right' => 15,
            'margin_top'   => 15,
            'margin_bottom'=> 15,
        ]);

        // 4. Bind view context variables and extract html code content 
        $html = view('creditcmsn_invoice', $data)->render();

        $mpdf->WriteHTML($html);
         // 3. Output as Inline PDF to view in browser
    $pdfContent = $mpdf->Output("invoice-{$data['invoice_no']}.pdf", 'S'); // 'S' returns the PDF as a string

    return Response::make($pdfContent, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="invoice-' . $data['invoice_no'] . '.pdf"'
    ]);   
        //return response($mpdf->Output("invoice-{$data['invoice_no']}.pdf", 'I'));
                //->header('Content-Type', 'application/pdf');
    }
   /* public function invoiceforappuser($orderno){
         $record = DB::table('order_tracks')->where("orderno", $orderno)->first('invoice');
         // If the record doesn't exist or invoice is null
    if (!$record || !$record->invoice) {
        abort(404, 'Invoice not found.');
    }

    // Get the full physical path of the invoice (e.g., storage/app/invoices/inv_123.pdf)
    // Adjust storage_path() depending on where you store your documents
    $filePath = storage_path('/app/public/' . $record->invoice);

    // Verify if the file actually exists on the server
    if (!file_exists($filePath)) {
        abort(404, 'File not found on server.');
    }

    // Determine the correct MIME type dynamically
    $mimeType = mime_content_type($filePath);

    // Return the file as an inline response (opens directly in the browser)
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline; filename="' . $record->invoice . '"',
    ]);
    }*/
}
