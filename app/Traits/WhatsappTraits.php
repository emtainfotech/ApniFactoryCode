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
    public static function formatWhatsappPhone(?string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', (string)$mobile);
        if ($digits === '') {
            return '';
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        if (strlen($digits) === 10) {
            return '91' . $digits;
        }
        return $digits;
    }

    protected static function resolveWhatsappConfig(): array
    {
        $db = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('profiles')) {
                $db = DB::table('profiles')
                    ->whereIn('attribute', [
                        'whatsapp_phone_number_id',
                        'whatsapp_access_token',
                        'whatsapp_api_version',
                        'whatsapp_otp_template',
                        'whatsapp_otp_lang',
                        'whatsapp_otp_has_button',
                        'whatsapp_order_status_template',
                        'whatsapp_order_status_lang',
                        'whatsapp_order_received_template',
                        'whatsapp_order_received_lang',
                        'whatsapp_invoice_template',
                        'whatsapp_invoice_lang',
                        'whatsapp_welcome_template',
                        'whatsapp_welcome_lang',
                    ])
                    ->pluck('value', 'attribute')
                    ->mapWithKeys(fn ($v, $k) => [strtolower((string)$k) => $v])
                    ->toArray();
            }
        } catch (\Throwable $e) {
            $db = [];
        }

        $pick = function (string $dbKey, string $cfgKey, $default = null) use ($db) {
            $val = config("services.whatsapp.{$cfgKey}");
            if ($val !== null && $val !== '') {
                return $val;
            }
            if (isset($db[$dbKey]) && $db[$dbKey] !== '') {
                return $db[$dbKey];
            }
            return $default;
        };

        return [
            'phone_number_id'         => trim((string)$pick('whatsapp_phone_number_id', 'phone_number_id', '')),
            'access_token'            => trim((string)$pick('whatsapp_access_token', 'access_token', '')),
            'api_version'             => trim((string)$pick('whatsapp_api_version', 'api_version', 'v22.0')),
            'otp_template'            => trim((string)$pick('whatsapp_otp_template', 'otp_template', 'app_otp_verification')),
            'otp_language'            => trim((string)$pick('whatsapp_otp_lang', 'otp_language', 'en')),
            'otp_has_button'          => filter_var($pick('whatsapp_otp_has_button', 'otp_has_button', true), FILTER_VALIDATE_BOOLEAN),
            'order_status_template'   => trim((string)$pick('whatsapp_order_status_template', 'order_status_template', 'order_status')),
            'order_status_language'   => trim((string)$pick('whatsapp_order_status_lang', 'order_status_language', 'en')),
            'order_received_template' => trim((string)$pick('whatsapp_order_received_template', 'order_received_template', 'order_received')),
            'order_received_language' => trim((string)$pick('whatsapp_order_received_lang', 'order_received_language', 'en_US')),
            'invoice_template'        => trim((string)$pick('whatsapp_invoice_template', 'invoice_template', 'invoice_uploaded_seller')),
            'invoice_language'        => trim((string)$pick('whatsapp_invoice_lang', 'invoice_language', 'en_US')),
            'welcome_template'        => trim((string)$pick('whatsapp_welcome_template', 'welcome_template', '')),
            'welcome_language'        => trim((string)$pick('whatsapp_welcome_lang', 'welcome_language', 'en')),
        ];
    }

    public static function sendWhatsappTemplate(
        string $mobile,
        string $templateName,
        string $languageCode = 'en',
        array $components = [],
        string $actionTag = 'whatsapp_template'
    ): array {
        $to = static::formatWhatsappPhone($mobile);
        if ($to === '' || $templateName === '') {
            return ['sent' => false, 'http_code' => 0, 'error' => 'Missing phone number or template name', 'body' => null, 'response' => null];
        }

        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => $languageCode],
                'components' => $components,
            ],
        ]);

        return static::dispatchWhatsappGraphRequest($payload, $actionTag);
    }

    public function sendwhatsappmsg($mobile, $actionon, $extra)
    {
        $phone = static::formatWhatsappPhone($mobile);
        if ($phone === '') {
            return false;
        }

        if ($actionon === 'ordertransection_success' || $actionon === 'order_placed') {
            $orderNo = is_array($extra) ? (string)($extra[0] ?? '') : (string)$extra;
            return static::sendtobuyerthatorderstatuschangedororderplaced(
                $phone,
                'Order Placed',
                $orderNo,
                'Your order has been placed successfully. Thank you for shopping with Apni Factory.'
            );
        }

        if ($actionon === 'ordertransection_failed') {
            $orderNo = is_array($extra) ? (string)($extra[0] ?? '') : (string)$extra;
            return static::sendtobuyerthatorderstatuschangedororderplaced(
                $phone,
                'Payment Failed',
                $orderNo,
                'Your payment could not be completed. Please retry from Order History.'
            );
        }

        if ($actionon === 'order_received') {
            $orderNo = is_array($extra) ? (string)($extra[0] ?? '') : (string)$extra;
            $sellerName = is_array($extra) ? (string)($extra[1] ?? 'Seller') : 'Seller';
            return static::sendtosellerthatorderreceived($phone, $orderNo, $sellerName);
        }

        if ($actionon === 'trackingdetail_updated') {
            $orderNo = is_array($extra) ? (string)($extra[0] ?? '') : (string)$extra;
            return static::sendtobuyerthatorderstatuschangedororderplaced(
                $phone,
                'Dispatched',
                $orderNo,
                'Dispatch and transporter LR details have been updated.'
            );
        }

        if ($actionon === 'order_rejected') {
            $orderNo = is_array($extra) ? (string)($extra[0] ?? '') : (string)$extra;
            return static::sendtobuyerthatorderstatuschangedororderplaced(
                $phone,
                'Rejected',
                $orderNo,
                'Order was declined by seller. Refund / alternative options are available in app.'
            );
        }

        if ($actionon === 'order_status_changed' || $actionon === 'order_status') {
            $orderNo = is_array($extra) ? (string)($extra[0] ?? '') : (string)$extra;
            $status = is_array($extra) ? (string)($extra[1] ?? 'Updated') : 'Updated';
            $note = is_array($extra) ? (string)($extra[3] ?? 'Order status updated') : 'Order status updated';
            return static::sendtobuyerthatorderstatuschangedororderplaced($phone, $status, $orderNo, $note);
        }

        return false;
    }

    public function sendwhatsappmsg_facebookapi($mobile, $templatename, $extra)
    {
        if (is_array($extra) && count($extra) >= 2 && ($templatename === 'order_status' || $templatename === 'order_status_changed')) {
            return static::sendtobuyerthatorderstatuschangedororderplaced(
                (string)$mobile,
                (string)($extra[1] ?? 'Processing'),
                (string)($extra[0] ?? ''),
                (string)($extra[3] ?? 'Status updated')
            );
        }
        return false;
    }

    protected static function dispatchWhatsappGraphRequest(string $payload, string $actionTag = 'whatsapp_graph'): array
    {
        $cfg = static::resolveWhatsappConfig();
        $token = $cfg['access_token'];
        $phoneNumberId = $cfg['phone_number_id'];
        if ($token === '' || $phoneNumberId === '') {
            \Illuminate\Support\Facades\Log::info('WhatsApp Meta API skipped (credentials not configured)', ['tag' => $actionTag]);
            return ['sent' => false, 'http_code' => 0, 'error' => 'WhatsApp credentials not configured', 'body' => null, 'response' => null];
        }

        $version = $cfg['api_version'] ?: 'v22.0';
        $url = "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
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
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl)) {
            $err = curl_error($curl);
            \Illuminate\Support\Facades\Log::warning('WhatsApp Meta cURL error', ['tag' => $actionTag, 'error' => $err]);
            curl_close($curl);
            return ['sent' => false, 'http_code' => $httpCode, 'error' => $err, 'body' => null, 'response' => null];
        }
        curl_close($curl);

        $decoded = is_string($response) ? json_decode($response, true) : null;
        $sent = ($httpCode >= 200 && $httpCode < 300) && empty($decoded['error']);
        if (!$sent) {
            \Illuminate\Support\Facades\Log::warning('WhatsApp Meta API rejected template', [
                'tag'       => $actionTag,
                'http_code' => $httpCode,
                'response'  => $decoded ?? $response,
            ]);
        }

        return [
            'sent'      => $sent,
            'http_code' => $httpCode,
            'error'     => $sent ? null : ($decoded['error']['message'] ?? 'Meta API error'),
            'body'      => is_array($decoded) ? $decoded : null,
            'response'  => is_string($response) ? $response : null,
        ];
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
    public function sendotponwhatsapp($mobile, $otp): array
    {
        $cfg = static::resolveWhatsappConfig();
        $otpStr = (string)$otp;
        $components = [
            [
                'type'       => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $otpStr],
                ],
            ],
        ];
        if (!empty($cfg['otp_has_button'])) {
            $components[] = [
                'type'       => 'button',
                'sub_type'   => 'url',
                'index'      => '0',
                'parameters' => [
                    ['type' => 'text', 'text' => $otpStr],
                ],
            ];
        }

        return static::sendWhatsappTemplate(
            (string)$mobile,
            $cfg['otp_template'],
            $cfg['otp_language'],
            $components,
            'otp_whatsapp'
        );
    }

    public static function sendWelcomeRegistrationWhatsapp(string $mobile, string $name): bool
    {
        $cfg = static::resolveWhatsappConfig();
        if (empty($cfg['welcome_template'])) {
            return false;
        }

        $res = static::sendWhatsappTemplate(
            $mobile,
            $cfg['welcome_template'],
            $cfg['welcome_language'],
            [
                [
                    'type'       => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $name ?: 'Customer'],
                    ],
                ],
            ],
            'welcome_registration'
        );
        return (bool)$res['sent'];
    }

    public static function sendtobuyerthatorderstatuschangedororderplaced($phone, $status, $orderNo, $note): bool
    {
        $cfg = static::resolveWhatsappConfig();
        $currentDate = date('M j, Y');
        $status = !empty($status) ? $status : 'Processing';
        $note = !empty($note) ? $note : 'Thank you for shopping with us.';

        $res = static::sendWhatsappTemplate(
            (string)$phone,
            $cfg['order_status_template'],
            $cfg['order_status_language'],
            [
                [
                    'type'       => 'header',
                    'parameters' => [
                        ['type' => 'text', 'parameter_name' => 'orderno', 'text' => (string)$orderNo],
                    ],
                ],
                [
                    'type'       => 'body',
                    'parameters' => [
                        ['type' => 'text', 'parameter_name' => 'orderno', 'text' => (string)$orderNo],
                        ['type' => 'text', 'parameter_name' => 'status', 'text' => (string)$status],
                        ['type' => 'text', 'parameter_name' => 'date', 'text' => (string)$currentDate],
                        ['type' => 'text', 'parameter_name' => 'note', 'text' => (string)$note],
                    ],
                ],
            ],
            'order_status'
        );
        return (bool)$res['sent'];
    }

    public static function sendtosellerthatorderreceived($phone, $orderNo, $sellername): bool
    {
        $cfg = static::resolveWhatsappConfig();
        $res = static::sendWhatsappTemplate(
            (string)$phone,
            $cfg['order_received_template'],
            $cfg['order_received_language'],
            [
                [
                    'type'       => 'header',
                    'parameters' => [
                        ['type' => 'text', 'parameter_name' => 'orderno', 'text' => (string)$orderNo],
                    ],
                ],
                [
                    'type'       => 'body',
                    'parameters' => [
                        ['type' => 'text', 'parameter_name' => 'name', 'text' => (string)$sellername],
                        ['type' => 'text', 'parameter_name' => 'orderno', 'text' => (string)$orderNo],
                    ],
                ],
            ],
            'order_received'
        );
        return (bool)$res['sent'];
    }

    public static function orderinvoiceuploadmsgtoseller($phone, $sellername, $order_no, $invoice_no): bool
    {
        $cfg = static::resolveWhatsappConfig();
        $res = static::sendWhatsappTemplate(
            (string)$phone,
            $cfg['invoice_template'],
            $cfg['invoice_language'],
            [
                [
                    'type'       => 'body',
                    'parameters' => [
                        ['type' => 'text', 'parameter_name' => 'sellername', 'text' => (string)$sellername],
                        ['type' => 'text', 'parameter_name' => 'invoice_no', 'text' => (string)$invoice_no],
                        ['type' => 'text', 'parameter_name' => 'order_no', 'text' => (string)$order_no],
                    ],
                ],
            ],
            'invoice_uploaded_seller'
        );
        return (bool)$res['sent'];
    }
}