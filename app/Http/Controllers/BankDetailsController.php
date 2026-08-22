<?php

namespace App\Http\Controllers;
use App\Traits\WhatsappTraits;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\BankDetails;
use Auth;
use DB;
use App\Helper\Helper;
class BankDetailsController extends Controller
{   use WhatsappTraits;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banklist = BankDetails::where('user_id',Auth::user()->id)->get();
        if(!empty($banklist[0])){$data["first"]=$banklist[0];}else{ $data["first"]='';}
        // if(!empty($banklist[1])){$data["second"]=$banklist[1];}else{ $data["second"]='';}
         $data['title'] = 'Bank Details';
         $data['otpsend']=0;
         /////otp send to registed mobile
        $cmpid = Company::where("user_id",Auth::user()->id)->first();
        if(empty($cmpid)){
          
             return back()->withErrors(["Please Registered Your Company And its Mobile Number"]);
        }
         $mobile = $cmpid->mobile;
         if(!empty($_GET['otpsend']) && $_GET['otpsend']==3){
             $otp = rand(1000,999999);
             $tbl = DB::table("tbl_otp")->updateOrInsert(["otpon"=>$mobile], ["otpon"=>$mobile,"otp"=>$otp]);
             $sendotp = $this->sendotponwhatsapp($mobile,$otp);
             if($sendotp==true){
                    $data['otpsend']=2;
             }else{
                 
                   return back()->withErrors(["Whatsapp Api Error"]);
             }
         }
         if(!empty($_GET['otpsend']) && $_GET['otpsend']==2){
             $getotp=$_GET['otp'];
             $tbl = DB::table("tbl_otp")->where("otpon",$mobile)->where("otp",$getotp)->orderby("id","desc")->first();
             if(!empty($tbl)){$data['otpsend']=1;}else{ return back()->withErrors(["OTP Not Match "]);}
         }
        return view('seller.bank-detail',$data);
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
        $cmpid = Company::where("user_id",Auth::user()->id)->first();
                if(!empty($request->accountno1)){
                if($request->isprimary==1){$pr='Y';}else{$pr='N';}
                $array = array(
                                "accountholder"=>$request->accountholder1,
                                "accountno"=>$request->accountno1,
                                "bankname"=>$request->bankname1,
                                "branch"=>$request->branch1,
                                "ifsc"=>$request->ifsc1,
                                "isprimary"=>$pr,
                                "user_id"=>Auth::user()->id,
                                "status"=>'1',
                                "company_id"=>$cmpid->id
                                );
                if($request->actionof1=='insert'){  BankDetails::insert($array);  }else{BankDetails::where("id",$request->actionof1)->update($array);}
                }
                if(!empty($request->accountno2)){
                if($request->isprimary==2){$pr='Y';}else{$pr='N';}
                $array2 = array(
                                "accountholder"=>$request->accountholder2,
                                "accountno"=>$request->accountno2,
                                "bankname"=>$request->bankname2,
                                "branch"=>$request->branch2,
                                "ifsc"=>$request->ifsc2,
                                "isprimary"=>$pr,
                                "user_id"=>Auth::user()->id,
                                "status"=>'1',
                                "company_id"=>$cmpid->id
                                );
                if($request->actionof2=='insert'){  BankDetails::insert($array2);  }else{BankDetails::where("id",$request->actionof2)->update($array2);}
                }
                
            Helper::addnotificationindb(Auth::user()->id,'seller','Bank Detail Updated','Your Bank Details Are Successfully Updated Your Related Bank '.$request->bankname1);        
         return back()->withErrors(["Successfully Updated"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BankDetails  $bankDetails
     * @return \Illuminate\Http\Response
     */
    public function show(BankDetails $bankDetails)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BankDetails  $bankDetails
     * @return \Illuminate\Http\Response
     */
    public function edit(BankDetails $bankDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankDetails  $bankDetails
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankDetails $bankDetails)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankDetails  $bankDetails
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankDetails $bankDetails)
    {
        //
    }
}
