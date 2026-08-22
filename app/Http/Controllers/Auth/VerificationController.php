<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
    
public function verifyGST(Request $request)
{
    $gst = $request->gst;
    // GST API
        $array = $this->verifyGST($gst);
        if($array["status"]==true){
            dd($array["data"]);
                return response()->json([
                    'status' => true,
                    'data' => [
                        'company_name' => 'ABC Pvt Ltd',
                        'owner_name' => 'Rahul Sharma',
                        'city' => 'Delhi',
                    ]
                ]);
                                    }else{
                                        return json_encode(["status"=>false,"code"=>500,"msg"=>"Not Verified","data"=>$array]); 
                                    }
}
public function sendEmailOtp(Request $request)
{
    $otp = rand(111111,999999);

    session(['email_otp' => $otp]);

    Mail::raw("Your OTP is ".$otp, function($message) use ($request){
        $message->to($request->email)
                ->subject('Email Verification OTP');
    });

    return response()->json([
        'status' => true
    ]);
}
public function sendMobileOtp(Request $request)
{
    $otp = rand(111111,999999);

    session(['mobile_otp' => $otp]);

    // SMS API HERE

    return response()->json([
        'status' => true
    ]);
}

    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
