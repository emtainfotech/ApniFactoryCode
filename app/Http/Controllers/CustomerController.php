<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use DB;
use App\Models\CustomerAddress;
use Google\Client;
use App\Traits\WhatsappTraits;
class CustomerController extends Controller
{
    use WhatsappTraits;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function verifyGST(Request $request)
    { 
        $crtnewob = new Jwt();
        $randno = rand('100','9999999'); 
        $gst = $_POST['gst'];  //request->get("gst")
        $array = $this->verifyGST_trait($gst);
        if($array["status"]==true){
                                        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Verified","data"=>$array]); 
                                    }else{
                                        return json_encode(["status"=>false,"code"=>500,"msg"=>"Not Verified","data"=>$array]); 
                                    }
                                    /*{
    "status": true,
    "code": 100,
    "msg": "Successfully Verified",
    "data": {
        "status": true,
        "message": "GST verified successfully",
        "data": {
            "gstin": "08AAACA3622K1ZV",
            "legal_name": "ASIAN PAINTS LIMITED",
            "business_name": "ASIAN PAINTS LIMITED",
            "status": "Active",
            "taxpayer_type": "Regular",
            "registration_date": "01/07/2017",
            "address": {
                "full": "F-601-648, 6",
                "city": "VK I Area",
                "state": "Rajasthan",
                "pincode": "302013"
            }
        }
    }*/
    }
    
public function sendnotification(Request $request) {
    // Path to your downloaded JSON service account file
    $serviceAccountPath = storage_path('app/keysfirebase.json');  // Adjust path if needed
    // Your Firebase Project ID (from Firebase Console)
    $projectId = 'apni-factory';  // e.g., 'my-app-12345'
    try {
        // Initialize Google Client with the JSON credentials
        $client = new Client();
        $client->setAuthConfig($serviceAccountPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        // Fetch access token
        $client->fetchAccessTokenWithAssertion();
        $accessToken = $client->getAccessToken()['access_token'];
        // Device token of the Android phone
        $customer = Customer::where("id","10")->first();
        $deviceToken = $customer->firebaseid;  // Replace with actual token
echo $deviceToken.'</br>';
    /*    // Notification payload for FCM v1 API (fixed structure)
        $data = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => 'Test Notification',
                    'body' => 'This is a test message from PHP!',
                ],
                'android' => [
                    'notification' => [
                        'icon' => 'ic_launcher',  // Android-specific icon
                        'sound' => 'default'      // Android-specific sound
                    ]
                ],
                // Optional: Add custom data for background handling
                // 'data' => ['key1' => 'value1']
            ]
        ];*/
$data = [
    'message' => [
        'token' => $deviceToken,
        'notification' => [
            'title' => 'Test Notification fouth time',
            'body' => 'This is a test message from PHP! fouth time',
        ],
        'android' => [
            'priority' => 'high', // Set priority to high
            'notification' => [
                'channel_id' => 'high_importance_channel',
                'icon' => 'ic_launcher',
                'sound' => 'default',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Required for some background clicks
            ]
        ],
        // Adding data block helps onMessage identify the payload easily
        'data' => [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'status' => 'done',
        ],
    ]
];
        // FCM v1 API URL
        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // Execute the request
        $response = curl_exec($ch);
        // Check for errors
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo 'HTTP Code: ' . $httpCode . PHP_EOL;
            echo 'FCM Response: ' . $response;
        }
       // Close cURL
        curl_close($ch);

    } catch (Exception $e) {
        // Catch and display any errors
        echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }
}
     public function sendotp(Request $request){ 
        $memberid =  $request['emailmobile']; //'919456638450';//'saxena.surbhiverma@gmail.com';//
        $sendon =  $request['sendon'];//'email';///
        $rmn = array();
            $otp = rand(1000,9999);
        $tblotp = DB::table('tbl_otp')->where('otpon',$memberid)->first();
        if(empty($tblotp)){
            $intary = array("otpon"=>$memberid,"otp"=>$otp);
            DB::table('tbl_otp')->insert($intary);
        }else{
            DB::table('tbl_otp')->where("otpon",$memberid)->update(["otp"=>$otp]);
        }
                if($sendon=='email'){
                    ///////////write code to sendotp on its email
                    $message[0]='Verification Code From Apnifactory';
                    $message[1]='<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Account</title>
</head>
<body style="margin: 0; padding: 0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; margin-top: 50px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <tr>
            <td align="center" style="padding: 40px 0 20px 0;">
                <h1 style="color: #333; margin: 0; font-size: 24px;">Confirm Your Email</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 40px; color: #555; line-height: 1.6; font-size: 16px;">
                <p>Hello,</p>
                <p>Thank you for signing up! Please use this '.$otp.' verification code below to complete your account registration. This code is valid for 10 minutes.</p>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding: 20px 0;">
                <div style="background-color: #f8f9fa; border: 1px dashed #007bff; display: inline-block; padding: 15px 30px; border-radius: 4px;">
                    <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #007bff;">'.$otp.'</span>
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 40px; color: #777; font-size: 14px; text-align: center;">
                <p>If you didnt request this, you can safely ignore this email.</p>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                <p style="margin: 0;">&copy; 2024 Your Company. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>';
                    $sendemail = $this->sendemailfromapnifactory($memberid,$message);
                    $res = $sendemail;
                }
                if($sendon=='mobile'){
                    ///////////write code to sendotp on its mobile
                    $sendotp = $this->sendotponwhatsapp($memberid,$otp);
                    $res = $sendotp;
                }
                $rmn['otp']=$otp;
                return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Send","data"=>$rmn,"originalresponse"=>$res]);
     }

    /* public function sendotponwhatsapp($mobile,$otp){
          $mobile='919456638450';
         $data = [
    "messaging_product" => "whatsapp",
    "to" => $mobile,
    "type" => "template",
    "template" => [
        "name" => "app_otp_verification", // Must match your approved template name
        "language" => [
            "code" => "en"
        ],
        "components" => [
            [
                "type" => "body",
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $otp
                    ]
                ]
            ],
            [
                "type" => "button",
                "sub_type" => "url",
                "index" => "0", 
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $otp
                    ]
                ]
            ]
        ]
    ]
];
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://graph.facebook.com/v22.0/960010463853608/messages',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>json_encode($data),
              CURLOPT_HTTPHEADER => array(
                  'Authorization: Bearer EAATYG5IA1aIBQ7q2gCJt4zgjtcsqR60EMqmycjl1Yc18eUGT3JoQbZBZAkXQlXLQzihHB5S0tKGYVtstZAaKPkq0bh8CEcVkDWYf20nokR6M2lAH2THPMHAv8cVAd7da7nMrjK7LZCTY8yNfhLRlTRqqPIdXyukhivyPZCT3ouOdKant5IHBzQipuWvrDFAZDZD',
                'Content-Type: application/json'
              ),
            ));
            $response = curl_exec($curl);
             if (curl_errno($curl)) {
                echo 'cURL Error: ' . curl_error($curl);
                 $attibuteary = array(
                                 "actionon"=> 'sendwhatsappmsg_facebookapi', 
                                 "request"=>json_encode($data), 
                                 "response"=>json_encode($curl), 
                                 "updateon"=>now()
                                );
                DB::table("logsofpages")->insert($attibuteary); 
        } else {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            echo 'HTTP Code: ' . $httpCode . PHP_EOL;
            echo 'FCM Response: ' . json_encode($response);
            return true;
        }
            curl_close($curl);
} */
     public function verifyotp(Request $request){ 
        $memberid =  $request['emailmobile'];
        $otp =  $request['otp'];
        $rmn = array();
         $tblotp = DB::table('tbl_otp')->where('otpon',$memberid)->first();
         if($tblotp->otp==$otp){  return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Match","data"=>$tblotp]);}
         else{ return json_encode(["status"=>false,"code"=>500,"msg"=>"otp Not Match","data"=>$tblotp]);}
        // $rmn = Customer::where('email',$memberid)->orWhere('mobile',$memberid)->first();
        // if(empty($rmn)){
        //      return response()->json(["status"=>false,"code"=>500,"msg"=>"User Not Exist","data"=>json_decode('{}')]);
        // }else{
        //     if($otp==$rmn->otp){
        //         Customer::where('id',$rmn->id)->update(["lastlogin"=>date('Y-m-d H:i:s')]);
        //         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Login","data"=>$rmn]);
        //     }else{
        //      return json_encode(["status"=>false,"code"=>500,"msg"=>"otp Not Match","data"=>$rmn]);
        //     }
        // }
       
     }  
     public function resetpassword(Request $request){ 
        $memberid =  $request['emailmobile'];
        $password =  $request['password'];
        $rmn = array();
        $rmn = Customer::where('email',$memberid)->orWhere('mobile',$memberid)->first();
        if(empty($rmn)){
             return response()->json(["status"=>false,"code"=>500,"msg"=>"User Not Exist","data"=>json_decode('{}')]);
        }else{
                Customer::where('id',$rmn->id)->update(["password"=>$password,"lastlogin"=>date('Y-m-d H:i:s')]);
                 $rmn = Customer::where('id',$memberid)->first();
                return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Updated","data"=>$rmn]);
        }
     }
     public function login(Request $request){ 
        $memberid =  $request['email'];
        $password =  $request['password'];
        $deviceid =  $request['deviceid'];
        $rmn = array();
        $rmn = Customer::where('email',$memberid)->orwhere('mobile',$memberid)->first();
        if(empty($rmn)){
             return response()->json(["status"=>false,"code"=>500,"msg"=>"User Not Exist","data"=>json_decode('{}')]);
        }else{
            if($rmn->regby=='app'){
            if($password==$rmn->password){
                Customer::where('email',$memberid)->orwhere('mobile',$memberid)->update(["lastlogin"=>date('Y-m-d H:i:s'),"firebaseid"=>$deviceid]);
                  $add = CustomerAddress::where('customer_id',$rmn->id)->first();
                  if(!empty($add)){ $rmn['address'] = $add; }else{ $rmn['address'] = array();}
                return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Login","data"=>$rmn]);
            }else{
             return response()->json(["status"=>false,"code"=>500,"msg"=>"Password Not Match","data"=>json_decode('{}')]);
            }
            }else{ return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Login","data"=>$rmn]);}
       
        }
     }
     public function register(Request $request){ 
        $name =  $request['name'];
        $mobile =  $request['mobile'];
        $regby =  $request['regby'];
        $email =  $request['email'];
        $password =  $request['password'];
        $whatsappno =  '0';///$request['whatsappno'];
        $gstorpan =  '';//$request['gstorpan'];
        $rmn = Customer::where('email',$email)->orWhere("mobile",$mobile)->first();
        if(empty($rmn)){
            $data = array(
                        "email"=>$email,
                        "mobile"=>$mobile,
                        "regby"=>$regby,
                        "name"=>$name,
                        "password"=>$password,
                        "whatsappno"=>$whatsappno,
                        "gstorpan"=>$gstorpan
                    );
                Customer::insert($data);
                $rmn = Customer::where('email',$email)->Where("mobile",$mobile)->first();
               return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Register","data"=>$rmn]);
            
        }else{
             return response()->json(["status"=>false,"code"=>500,"msg"=>"User Already Exist","data"=>$rmn]);
        }
       
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
         $rmn = Customer::where('id',$id)->first();
               return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
          $name =  $request['name'];
            $mobile =  $request['mobile'];
            $email =  $request['email'];
            // $password =  $request['password'];
            $location =  $request['location'];
            $whatsappno =  $request['whatsappno'];
            $gstorpan =  $request['gstorpan'];
            $id =  $id;
            $file = $request->file('image');
            $rmn = Customer::where('id',$id)->first();
            if(!empty($file)){
            $imageName = $id.time().'.'.$file->extension();
            $imagePath = public_path(). '/user';
           $file->move($imagePath, $imageName);
           $imageName ='user/'.$imageName;
            }else{
               $imageName = $rmn->image;
           }
        if(!empty($rmn)){
            $data = array(
                        "name"=>$name,
                        // "password"=>$password,
                        "location"=>$location,
                        "whatsappno"=>$whatsappno,
                        "gstorpan"=>$gstorpan,
                        "image"=>$imageName
                    );
            if($mobile!=$rmn->mobile){
           $checkmobile =  Customer::where('mobile',$mobile)->first();
               if(!empty($checkmobile)){
                   return response()->json(["status"=>false,"code"=>500,"msg"=>"Mobile Already Exist","data"=>json_decode('{}')]);
               }else{$data['mobile']=$mobile;}
            }
            if($email!=$rmn->email){
           $checkemail =  Customer::where('email',$email)->first();
               if(!empty($checkemail)){
                   return response()->json(["status"=>false,"code"=>500,"msg"=>"Email Already Exist","data"=>json_decode('{}')]);
               }else{$data['email']=$email;}
            }
                 Customer::where('id',$id)->update($data);
                $rmn = Customer::where('id',$id)->first();
               return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Updated","data"=>$rmn]);
        }else{
             return response()->json(["status"=>false,"code"=>500,"msg"=>"User Not Exist","data"=>json_decode('{}')]);
        }
    }

    public function changepassword(Request $request, $id)
    {
            $oldpassword =  $request['currentpassword'];
            $newpassword =  $request['newpassword'];
            $id =  $id;
            $rmn = Customer::where('id',$id)->first();
        if(!empty($rmn)){
            if($rmn->password==$oldpassword){
            $data = array(
                        "password"=>$newpassword
                    );
                 Customer::where('id',$id)->update($data);
                $rmn = Customer::where('id',$id)->first();
               return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Updated","data"=>$rmn]);
        }else{ return response()->json(["status"=>false,"code"=>500,"msg"=>"Current Password Not Match","data"=>json_decode('{}')]);}
        }else{
             return response()->json(["status"=>false,"code"=>500,"msg"=>"User Not Exist","data"=>json_decode('{}')]);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    ////used in registration
public function verifyGST_registration(Request $request)
{
    $gst = $request->gst; 
    // GST API
        $array = $this->verifyGST_trait($gst);
        if($array["status"]==true){
                return response()->json([
                    'status' => true,
                    'message' => $array["message"],
                    'data' => [
                        'company_name' => $array['data']["business_name"],
                        'legal_name' => $array['data']["legal_name"],
                        'city' => $array['data']["address"]["city"]
                    ]
                ]);
           }else{
                return response()->json([
                    'status' => false,
                    'message' => $array["message"]
                ]); 
                                    }
}
    

}
