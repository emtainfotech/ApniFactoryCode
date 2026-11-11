<?php

namespace App\Traits;

use App\Models\BankDetails;
use DB;
use Google\Client;
use App\Models\Customer;
trait WhatsappTraits
{
 
     public function verifyGST_trait($gst)
    {
        if (!config('marketplace_integration.crm_gst_check_enabled', true)) {
            return ['status' => false, 'message' => 'External CRM GST check is disabled by policy.'];
        }

        $url1 = config('services.crm.gst_check_url', 'https://crm.apnifactory.co.in/core/api/mobile/gst-check/');
        $body = json_encode(['gst_number' => $gst]);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        if ($token = config('services.crm.api_token')) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_URL, $url1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch1, CURLOPT_TIMEOUT, (int)config('marketplace_integration.crm_timeout_seconds', 5));
        curl_setopt($ch1, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch1, CURLOPT_POST, 1);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $body);
        $response1 = curl_exec($ch1);
        $curlError = curl_errno($ch1) ? curl_error($ch1) : null;
        $httpCode  = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
        curl_close($ch1);

        $array = is_string($response1) ? json_decode($response1, true) : null;

        try {
            $syncService = app(\App\Services\CrossPlatformSyncService::class);
            $res = $syncService->recordAndDispatchSyncEvent(
                \App\Services\CrossPlatformSyncService::CHANNEL_CRM_EXTERNAL,
                'customer',
                null,
                'GST:' . (string)$gst,
                'gst_check_log',
                ['gst_number' => (string)$gst, 'http_code' => $httpCode, 'verified' => (bool)($array['status'] ?? false), 'error' => $curlError],
                'crm_gst_' . md5((string)$gst . '_' . date('YmdHis') . '_' . uniqid()),
                null,
                \App\Models\IntegrationSyncEvent::DIRECTION_OUTBOUND,
                false
            );
            $syncService->processSyncEvent($res['event']);
        } catch (\Throwable $e) {
            // Non-blocking audit logging
        }

        return is_array($array) ? $array : ['status' => false, 'message' => $curlError ?: 'CRM GST lookup unavailable.'];
    }
    public function sendwhatsappmsg($mobile,$actionon,$extra)
    {
        $templatename = 123;
        $tempdetail = DB::table("whatsappmsgs")->where("actionon",$actionon)->first();
        if($actionon=='ordertransection_success'){
            $title = 'Transection Successfully!!!';
            $msg = 'Your Order is Placed Successfully of OrderID #'.$extra.' Please Check Your Order Details. Thank You for Shopping.';
            $this->sendwhatsappmsg_facebookapi($mobile,$templatename,$msg);
        }
        if($actionon=='ordertransection_failed'){
            $title = 'Transection Failed Successfully!!!';
            $msg = 'Your Order of OrderID #'.$extra.' is not Placed Due to Transection Status Failed !!!';
            $this->sendwhatsappmsg_facebookapi($mobile,$templatename,$msg);
        }
        if($actionon=='order_received'){
            $title = 'New Order Receieved Successfully!!!';
            $msg = 'New Order Receieved of OrderID #'.$extra.'Plz check the details in your seller panel !!!';
            $this->sendwhatsappmsg_facebookapi($mobile,$templatename,$msg);
        }
        if($actionon=='order_placed'){
            $templatename = 'order_confirm';
            $msg = [  // This should be a PHP array of parameter objects
                        ["type" => "text", "text" => $extra[0]],
                        ["type" => "text", "text" => $extra[1]]
                    ];
            $msg = json_encode($msg);
            $this->sendwhatsappmsg_facebookapi('91'.$mobile,$templatename,$msg);
        }
        if($actionon=='order_status_changed'){
            $templatename = 'order_status';
            $msg = [  // This should be a PHP array of parameter objects
                        ["type" => "text", "text" => $extra[0]],   ///orderno
                        ["type" => "text", "text" => $extra[1]],     ///orderstatus
                        ["type" => "text", "text" => $extra[2]],     ///date
                        ["type" => "text", "text" => $extra[3]]     ///note
                    ];
            $msg = json_encode($msg);
            $this->sendwhatsappmsg_facebookapi('91'.$mobile,$templatename,$extra);
         
        }
    }
    
    public function sendwhatsappmsg_facebookapi($mobile,$templatename,$extra)
    {   
        // $data = '{ "messaging_product": "whatsapp", "to": "919456638450", "type": "template", "template": { "name": '.$templatename.', "language": { "code": "en_US" } } }';
        $phone=$mobile;
$currentDate = date("M j, Y"); // Dynamically generates text like "Jun 17, 2026"
$status = "Delivered";         // Or your dynamic status variable
$note = "Thank you";
   $dataArray = [
    "messaging_product" => "whatsapp",
    "recipient_type"    => "individual",
    "to"                => $phone,
    "type"              => "template",
    "template"          => [
        "name"     => (string)$templatename,
        "language" => [
            "code" => "en"
        ],
        "components" => [
            [
                "type"       => "header",
                "parameters" => [
                    [
                        "type"           => "text",
                        "parameter_name" => "orderno", 
                        "text"           => (string)$extra[0]
                    ]
                ]
            ],
            [
                "type"       => "body",
                "parameters" => [
                    [
                        "type"           => "text",
                        "parameter_name" => "orderno", 
                        "text"           => (string)$extra[0]
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "status", 
                        "text"           => (string)$extra[1]
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "date", 
                        "text"           => (string)$extra[2]
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "note", 
                        "text"           => (string)$extra[3]
                    ],
                ]
            ]
        ]
    ]
];
        $payload = json_encode($dataArray);
        return static::dispatchWhatsappGraphRequest($payload) !== null;
    }

    protected static function dispatchWhatsappGraphRequest(string $payload): ?string
    {
        $token = config('services.whatsapp.access_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        if (empty($token) || empty($phoneNumberId)) {
            return null;
        }

        $version = config('services.whatsapp.api_version', 'v22.0');
        $url = "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            DB::table('logsofpages')->insert([
                'actionon' => 'sendwhatsappmsg_facebookapi',
                'request'  => $payload,
                'response' => curl_error($curl),
                'updateon' => now(),
            ]);
            curl_close($curl);
            return null;
        }
        curl_close($curl);
        return is_string($response) ? $response : null;
    }   
    
    public function sendnotification($token,$actionon,$screenid)
    {
        if($actionon=='order_transectionsuccess'){
            $title = 'Transection Successfully!!!';
            $screendataid = $screenid;
            $mobilescreen = 'orderdetail';
            $body = 'Your Order is Placed Successfully of OrderID #'.$screenid.' Please Check Your Order Details. Thank You for Shopping.';
            $this->sendnotificationfcm($token,$title,$body,$mobilescreen,$screendataid);
        }
        if($actionon=='order_transectionfailed'){
            $title = 'Transection Failed!!!';
            $screendataid = '';
            $mobilescreen = '';
            $body = 'Your Order of OrderID #'.$screenid.' is not Placed Due to Transection Status Failed !!!';
            $this->sendnotificationfcm($token,$title,$body,$mobilescreen,$screendataid);
        }
        
        if($actionon=='order_statuschanged'){
            $title = 'Your Order Status is Updated !!!';
            $screendataid = '';
            $mobilescreen = '';
            $body = 'Your Order of OrderID #'.$screenid.' status has Changed';
            $this->sendnotificationfcm($token,$title,$body,$mobilescreen,$screendataid);
        }
        
    }
    
     public function sendnotification_onmultipledevice($actionon,$screenid)
    {
        if($actionon=='newcoupon'){
            $title = 'New Coupon launch !!!';
            $screendataid = $screenid;
            $mobilescreen = 'Coupon';
            $body = 'New Coupon launch. Please Check the Details for Better Experience !!!';
            $this->sendnotificationfcm_multipledevices($title,$body,$mobilescreen,$screendataid);
            
        }
        if($actionon=='brandregistred'){
            $title = 'New Brand Registered!!!';
            $screendataid = $screenid;
            $mobilescreen = 'branddetail';
            $body = 'New Brand is Registered. Please Check the Details for Better Experience !!!';
            $this->sendnotificationfcm_multipledevices($title,$body,$mobilescreen,$screendataid);
        }
    }
    public function sendnotificationfcm($token,$title,$body,$mobilescreen,$screendataid) {
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
        $deviceToken = $token; // Replace with actual token

$data = [
    'message' => [
        'token' => $deviceToken,
        'notification' => [
            'title' => $title,
            'body' => $body,
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
            'screen'=>$mobilescreen,
            'screenid'=>$screendataid
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            // echo 'cURL Error: ' . curl_error($ch);
             $attibuteary = array(
                             "actionon"=> 'sendnotificationfcm', 
                             "request"=>json_encode($data), 
                             "response"=>json_encode($ch), 
                             "updateon"=>now()
                            );
            DB::table("logsofpages")->insert($attibuteary); 
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // echo 'HTTP Code: ' . $httpCode . PHP_EOL;
            // echo 'FCM Response: ' . $response;
            // echo 'cURL Error: ' . curl_error($ch);
            $custid = Customer::where("firebaseid",$deviceToken)->first();
            $savenotification = array("customer_id"=>$custid->id,"title"=>$title,"body"=>$body,"msgread"=>0);
            DB::table("notifications")->insert($savenotification); 
              if($httpCode!=200){
                $attibuteary = array(
                                 "actionon"=>'sendnotificationfcm', 
                                 "request"=>json_encode($data), 
                                 "response"=>json_encode($response), 
                                 "updateon"=>now()
                            );
            DB::table("logsofpages")->insert($attibuteary); 
            }
            return true;
            
        }
        // Close cURL
        curl_close($ch);

    } catch (Exception $e) {
        // Catch and display any errors
        echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }
}
    public function sendnotificationfcm_multipledevices($title,$body,$mobilescreen,$screendataid) {
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
        $alltoken = Customer::where("firebaseid","!=","")->select("firebaseid")->get();
        foreach($alltoken as $tok){ 
        // Device token of the Android phone
        $deviceToken = $tok->firebaseid; // Replace with actual token
        $data = [
            'message' => [
                'token' => $tok->firebaseid,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
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
                    'screen'=>(string)$mobilescreen,
                    'screenid'=>(string)$screendataid
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // Execute the request
        $response = curl_exec($ch);
        // Check for errors
        if (curl_errno($ch)) {
            // echo 'cURL Error: ' . curl_error($ch);
             $attibuteary = array(
                                 "actionon"=>'sendnotificationfcm', 
                                 "request"=>json_encode($data), 
                                 "response"=>json_encode($response), 
                                 "updateon"=>now()
                            );
            DB::table("logsofpages")->insert($attibuteary); 
        } else {
            $custid = Customer::where("firebaseid",$deviceToken)->first();
            $savenotification = array("customer_id"=>$custid->id,"title"=>$title,"body"=>$body,"msgread"=>0);
          $rt =  DB::table("notifications")->insert($savenotification); 
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // echo 'HTTP Code: ' . $httpCode . PHP_EOL;
            // echo 'FCM Response: ' . $response;
            if($httpCode!=200){
                $attibuteary = array(
                                 "actionon"=>'sendnotificationfcm', 
                                 "request"=>json_encode($data), 
                                 "response"=>json_encode($response), 
                                 "updateon"=>now()
                            );
            DB::table("logsofpages")->insert($attibuteary); 
            }
            
        }
        // Close cURL
        }
        curl_close($ch);
            return true;
    } catch (Exception $e) {
        // Catch and display any errors
        echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }
    
// dd($queries);
}
       public function sendemailfromapnifactory($email,$message)
    {
       // Use Composer's autoloader instead of manual requires if possible
    require_once public_path().'/mailer/src/Exception.php';
    require_once public_path().'/mailer/src/SMTP.php';
    require_once public_path().'/mailer/src/PHPMailer.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Optimized Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'verify@apnifactory.co.in';
        $mail->Password   = 'Verify@123#'; 
        
        // FIX: Use SMTPS for Port 465
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465; 

        // Performance: Keep connection alive if sending multiple (optional)
        $mail->SMTPKeepAlive = true; 

        // Recipients 
        $mail->setFrom('verify@apnifactory.co.in', 'Apnifactory');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $message[0];
        $mail->Body    = $message[1];

        $response = $mail->send(); // Return boolean instead of echoing
        return $res = $response;
    } catch (\Exception $e) {
        // Log the error instead of echoing to prevent breaking JSON responses
        error_log("Mailer Error: {$mail->ErrorInfo}");
       return $res = $mail->ErrorInfo;
        
    }
            }
    public function sendotponwhatsapp($mobile,$otp){
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
            $response = static::dispatchWhatsappGraphRequest(json_encode($data));
            return $response;
     }         
   
     public static function sendtobuyerthatorderstatuschangedororderplaced($phone, $status,$orderNo,$note)
        {
        $url = "https://graph.facebook.com/v22.0/960010463853608/messages";
        $currentDate = date("M j, Y"); // Dynamically generates text like "Jun 17, 2026"
        $status = !empty($status) ? $status : "Processing";
        $note = !empty($note) ? $note : "Thank you for shopping with us.";
   $dataArray = [
    "messaging_product" => "whatsapp",
    "recipient_type"    => "individual",
    "to"                => $phone,
    "type"              => "template",
    "template"          => [
        "name"     => "order_status",
        "language" => [
            "code" => "en"
        ],
        "components" => [
            [
                "type"       => "header",
                "parameters" => [
                    [
                        "type"           => "text",
                        "parameter_name" => "orderno", 
                        "text"           => (string)$orderNo
                    ]
                ]
            ],
            [
                "type"       => "body",
                "parameters" => [
                    [
                        "type"           => "text",
                        "parameter_name" => "orderno", 
                        "text"           => (string)$orderNo
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "status", 
                        "text"           => (string)$status
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "date", 
                        "text"           => (string)$currentDate
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "note", 
                        "text"           => (string)$note
                    ],
                ]
            ]
        ]
    ]
];
        $payload = json_encode($dataArray);
        static::dispatchWhatsappGraphRequest($payload);
    }
        public static function sendtosellerthatorderreceived($phone, $orderNo,$sellername)
    {
    $url = "https://graph.facebook.com/v22.0/960010463853608/messages";
    
$dataArray = [
    "messaging_product" => "whatsapp",
    "recipient_type"    => "individual",
    "to"                => $phone,
    "type"              => "template",
    "template"          => [
        "name"     => "order_received",
        "language" => [
            "code" => "en_US"
        ],
        "components" => [
            [
                "type"       => "header",
                "parameters" => [
                    [
                        "type"           => "text",
                        "parameter_name" => "orderno", 
                        "text"           => (string)$orderNo
                    ]
                ]
            ],
            [
                "type"       => "body",
                "parameters" => [
                    [
                        "type"           => "text",
                        "parameter_name" => "name", 
                        "text"           => (string)$sellername
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "orderno", 
                        "text"           => (string)$orderNo
                    ]
                ]
            ]
        ]
    ]
];
    $payload = json_encode($dataArray);
    static::dispatchWhatsappGraphRequest($payload);
}
     public static function orderinvoiceuploadmsgtoseller($phone, $sellername,$order_no,$invoice_no)
        {
        $url = "https://graph.facebook.com/v22.0/960010463853608/messages";
       
   $dataArray = [
    "messaging_product" => "whatsapp",
    "recipient_type"    => "individual",
    "to"                => $phone,
    "type"              => "template",
    "template"          => [
        "name"     => "invoice_uploaded_seller",
        "language" => [
            "code" => "en_US"
        ],
        "components" => [
            [
                "type"       => "body",
                "parameters" => [
                    [
                        "type"           => "text",
                        "parameter_name" => "sellername", 
                        "text"           => (string)$sellername
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "invoice_no", 
                        "text"           => (string)$invoice_no
                    ],
                    [
                        "type"           => "text",
                        "parameter_name" => "order_no", 
                        "text"           => (string)$order_no
                    ]
                ]
            ]
        ]
    ]
];
        $payload = json_encode($dataArray);
        static::dispatchWhatsappGraphRequest($payload);
    }
}