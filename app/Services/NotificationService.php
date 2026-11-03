<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Customer;

class NotificationService
{
    /**
     * Send Multi-Channel Notification to a Customer or Seller.
     *
     * @param string $recipientType 'customer' | 'seller' | 'admin'
     * @param int $recipientId
     * @param string $title
     * @param string $message
     * @param array $extraData
     * @return array
     */
    public function send(
        string $recipientType,
        int $recipientId,
        string $title,
        string $message,
        array $extraData = []
    ): array {
        $results = [
            'database' => false,
            'push'     => false,
            'whatsapp' => false,
            'email'    => false,
        ];

        // 1. Database Notification (In-App Notification Center)
        try {
            DB::table('notifications')->insert([
                'title'       => $title,
                'msg'         => $message,
                'type'        => $recipientType,
                'customer_id' => $recipientType === 'customer' ? $recipientId : null,
                'user_id'     => in_array($recipientType, ['seller', 'admin']) ? $recipientId : null,
                'msgread'     => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $results['database'] = true;
        } catch (\Exception $e) {
            Log::error("Failed to insert database notification: " . $e->getMessage());
        }

        // Fetch Recipient Details
        $recipientName = null;
        $recipientEmail = null;
        $recipientMobile = null;
        $fcmToken = null;

        if ($recipientType === 'customer') {
            $customer = Customer::find($recipientId);
            if ($customer) {
                $recipientName = $customer->name;
                $recipientEmail = $customer->email;
                $recipientMobile = $customer->mobile;
                $fcmToken = $customer->deviceid ?? $customer->fcm_token ?? $customer->device_token ?? null;
            }
        } elseif (in_array($recipientType, ['seller', 'admin'])) {
            $user = User::find($recipientId);
            if ($user) {
                $recipientName = $user->name;
                $recipientEmail = $user->email;
                $recipientMobile = $user->mobile;
                $fcmToken = $user->fcm_token ?? null;
            }
        }

        // 2. Firebase Cloud Messaging (FCM Mobile Push)
        if ($fcmToken || !empty($extraData['force_push'])) {
            $results['push'] = $this->sendFcmPush($fcmToken, $title, $message, $extraData);
        } else {
            Log::info("FCM Push skipped (No device token registered for {$recipientType} #{$recipientId}) - Payload: {$title}");
            $results['push'] = 'skipped_no_token';
        }

        // 3. WhatsApp Notification
        if ($recipientMobile) {
            $results['whatsapp'] = $this->sendWhatsAppMessage($recipientMobile, $recipientName, $title, $message, $extraData);
        }

        // 4. Transactional Email
        if ($recipientEmail && !empty($extraData['email_view'])) {
            $results['email'] = $this->sendEmail($recipientEmail, $recipientName, $title, $extraData['email_view'], $extraData);
        }

        return $results;
    }

    /**
     * Send FCM Push Notification via HTTP v1 or Legacy API with safe local simulation fallback.
     */
    public function sendFcmPush($token, string $title, string $message, array $data = []): bool
    {
        $serverKey = env('FCM_SERVER_KEY');
        $img = !empty($data['image']) ? $data['image'] : (!empty($data['banner']) ? $data['banner'] : null);

        if (!$serverKey) {
            // Local simulation / Mock mode
            Log::info("[MOCK FCM PUSH] Sent to token '{$token}': [{$title}] {$message}", $data);
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $message,
                    'sound' => 'default',
                    'badge' => '1',
                    'image' => $img,
                    'android_channel_id' => 'high_importance_channel',
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'high_importance_channel',
                        'image' => $img,
                        'sound' => 'default',
                        'default_vibrate_timings' => true,
                        'default_sound' => true,
                    ],
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'timestamp'    => time(),
                ]),
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type'  => 'application/json',
            ])->timeout(5)->post('https://fcm.googleapis.com/fcm/send', $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("FCM Push delivery error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Broadcast Custom Notification with Banner & Deep Link to customers from Admin Dashboard.
     */
    public function broadcastCustomPush(
        string $title,
        string $message,
        ?string $imageUrl = null,
        ?string $screen = null,
        ?string $screenId = null,
        string $targetAudience = 'all_customers',
        ?int $customerId = null
    ): array {
        $serverKey = env('FCM_SERVER_KEY');
        $fullImageUrl = null;
        if (!empty($imageUrl)) {
            $fullImageUrl = str_starts_with($imageUrl, 'http') ? $imageUrl : url('storage/' . $imageUrl);
        }

        $deepData = [
            'title'     => $title,
            'body'      => $message,
            'msg'       => $message,
            'image'     => $fullImageUrl ?? '',
            'banner'    => $fullImageUrl ?? '',
            'screen'    => $screen ?? 'none',
            'screenid'  => (string) ($screenId ?? ''),
            'deep_link' => ($screen && $screenId) ? "{$screen}/{$screenId}" : '',
        ];

        // 1. Save to database
        if ($targetAudience === 'specific_customer' && $customerId) {
            DB::table('notifications')->insert([
                'customer_id' => $customerId,
                'title'       => $title,
                'msg'         => $message,
                'type'        => 'promo',
                'image'       => $imageUrl,
                'deep_link'   => $deepData['deep_link'],
                'screen'      => $screen,
                'screenid'    => $screenId,
                'msgread'     => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $tokens = Customer::where('id', $customerId)
                ->where(function($q) {
                    $q->whereNotNull('deviceid')->orWhereNotNull('fcm_token');
                })
                ->pluck('deviceid')
                ->filter()
                ->unique()
                ->toArray();
        } else {
            // Broadcast to all customers
            DB::table('notifications')->insert([
                'customer_id' => null, // null = broadcast to all customers
                'title'       => $title,
                'msg'         => $message,
                'type'        => 'broadcast',
                'image'       => $imageUrl,
                'deep_link'   => $deepData['deep_link'],
                'screen'      => $screen,
                'screenid'    => $screenId,
                'msgread'     => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $tokens = Customer::whereNotNull('deviceid')
                ->pluck('deviceid')
                ->filter()
                ->unique()
                ->toArray();
        }

        $sentCount = 0;
        if (empty($serverKey)) {
            Log::info("[MOCK BROADCAST PUSH] To " . count($tokens) . " devices: [{$title}] {$message}", $deepData);
            return [
                'success' => true,
                'sent_count' => count($tokens),
                'mock' => true,
            ];
        }

        $chunks = array_chunk($tokens, 500);
        foreach ($chunks as $chunk) {
            if (empty($chunk)) continue;
            try {
                $payload = [
                    'registration_ids' => array_values($chunk),
                    'notification' => [
                        'title' => $title,
                        'body'  => $message,
                        'image' => $fullImageUrl,
                        'sound' => 'default',
                        'android_channel_id' => 'high_importance_channel',
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id' => 'high_importance_channel',
                            'image' => $fullImageUrl,
                            'sound' => 'default',
                        ],
                    ],
                    'data' => array_merge($deepData, [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'timestamp'    => time(),
                    ]),
                    'priority' => 'high',
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', $payload);

                if ($response->successful()) {
                    $sentCount += count($chunk);
                }
            } catch (\Exception $e) {
                Log::error("Broadcast push chunk error: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'sent_count' => $sentCount,
            'tokens_found' => count($tokens),
        ];
    }

    /**
     * Send WhatsApp Business Cloud / Wati Message with Indian Phone Normalization.
     */
    public function sendWhatsAppMessage(string $mobile, ?string $name, string $title, string $message, array $extra = []): bool
    {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($cleanMobile) === 10) {
            $cleanMobile = '91' . $cleanMobile;
        }

        $apiUrl = env('WHATSAPP_API_URL');
        $apiToken = env('WHATSAPP_API_TOKEN');

        if (!$apiUrl || !$apiToken) {
            // Simulated WhatsApp dispatch for local/dev
            Log::info("[MOCK WHATSAPP] Sent to +{$cleanMobile} ({$name}): [{$title}] {$message}");
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type'  => 'application/json',
            ])->timeout(5)->post($apiUrl, [
                'phone'   => $cleanMobile,
                'message' => "*{$title}*\n\nHi {$name},\n{$message}\n\n_Team ApniFactory_",
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("WhatsApp delivery error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Transactional Email using Blade template.
     */
    public function sendEmail(string $email, ?string $name, string $subject, string $bladeView, array $data = []): bool
    {
        try {
            Mail::send($bladeView, array_merge($data, ['name' => $name, 'subject' => $subject]), function ($message) use ($email, $name, $subject) {
                $message->to($email, $name ?: 'ApniFactory User')
                        ->subject($subject);
            });
            return true;
        } catch (\Exception $e) {
            Log::error("Transactional Email delivery error to {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update device FCM token for Customer or Seller.
     */
    public function updateDeviceToken(string $type, int $id, string $token): bool
    {
        if ($type === 'customer') {
            return (bool) Customer::where('id', $id)->update(['fcm_token' => $token]);
        } elseif ($type === 'seller' || $type === 'user') {
            return (bool) User::where('id', $id)->update(['fcm_token' => $token]);
        }
        return false;
    }
}
