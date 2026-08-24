<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class PaymentRefundService
{
    /**
     * Process automated refund for a cancelled/rejected order.
     *
     * @param int|Order $order
     * @param string $reason
     * @param float|null $amount
     * @return array
     */
    public function processRefund($order, string $reason = 'Order Cancelled', ?float $amount = null): array
    {
        if (is_numeric($order)) {
            $order = Order::find($order);
        }

        if (!$order) {
            return [
                'success' => false,
                'message' => 'Order not found.',
            ];
        }

        $refundAmount = $amount !== null ? $amount : (float)$order->grandtotal;

        return DB::transaction(function () use ($order, $reason, $refundAmount) {
            $txn = DB::table('transections')->where('order_no', $order->orderno)->first();
            $gatewayTxnId = $txn ? $txn->txnid : null;
            $refundTxnId = 'REF_' . strtoupper(uniqid()) . '_' . $order->id;

            $gatewayRefundSuccess = false;
            $gatewayResponse = null;

            // 1. Attempt Payment Gateway API Refund if production keys exist
            $razorpayKey = env('RAZORPAY_KEY');
            $razorpaySecret = env('RAZORPAY_SECRET');

            if ($razorpayKey && $razorpaySecret && $gatewayTxnId && !str_contains($gatewayTxnId, 'TEST')) {
                try {
                    $response = Http::withBasicAuth($razorpayKey, $razorpaySecret)
                        ->post("https://api.razorpay.com/v1/payments/{$gatewayTxnId}/refund", [
                            'amount' => (int)($refundAmount * 100), // In paise
                            'notes'  => [
                                'order_id' => $order->id,
                                'reason'   => $reason,
                            ],
                        ]);

                    if ($response->successful()) {
                        $gatewayRefundSuccess = true;
                        $gatewayResponse = $response->json();
                        $refundTxnId = $gatewayResponse['id'] ?? $refundTxnId;
                    }
                } catch (\Exception $e) {
                    Log::error("Gateway refund API failed: " . $e->getMessage());
                }
            }

            // 2. Record Transaction in Database
            DB::table('transections')->insert([
                'order_id'    => $order->id,
                'order_no'    => $order->orderno,
                'customer_id' => $order->customer_id,
                'user_id'     => $order->user_id,
                'status'      => 'Refunded',
                'txnid'       => $refundTxnId,
                'txnmethod'   => 'Refund',
                'txndetail'   => json_encode([
                    'type'             => 'refund',
                    'amount'           => $refundAmount,
                    'reason'           => $reason,
                    'gateway_success'  => $gatewayRefundSuccess,
                    'gateway_response' => $gatewayResponse,
                    'timestamp'        => now()->toDateTimeString(),
                ]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // 3. Update Order Status in order_status & order_tracks
            DB::table('order_status')->insert([
                'order_id'   => $order->id,
                'order_no'   => $order->orderno,
                'user_id'    => $order->user_id,
                'status'     => 'Cancelled',
                'msg'        => "Order cancelled & refunded: {$reason}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('order_tracks')->updateOrInsert(
                ['order_id' => $order->id],
                [
                    'orderno'    => $order->orderno,
                    'status'     => '0',
                    'text'       => "Order cancelled & refunded: {$reason}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 4. Also Credit In-App Wallet for instant buying power
            if (Schema::hasTable('wallet')) {
                DB::table('wallet')->insert([
                    'user_id'       => $order->user_id,
                    'order_id'      => $order->id,
                    'orderno'       => $order->orderno,
                    'value'         => $refundAmount,
                    'commission'    => 0,
                    'refundtobuyer' => $refundAmount,
                    'debit'         => 0,
                    'credit'        => $refundAmount,
                    'balance'       => $refundAmount,
                    'type'          => 'refund',
                    'action'        => 'refund',
                    'addby'         => 'system',
                    'msg'           => "Refund for Order #{$order->orderno} - {$reason}",
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 5. Notify Buyer of Successful Refund
            $notificationService = app()->make(NotificationService::class);
            $notificationService->send(
                'customer',
                (int)$order->customer_id,
                "💰 Refund Processed for Order #{$order->orderno}",
                "Your refund of ₹" . number_format($refundAmount, 2) . " has been successfully credited to your account/wallet. Reason: {$reason}",
                [
                    'order_id'   => $order->id,
                    'refund_id'  => $refundTxnId,
                    'email_view' => 'emails.order_cancelled_buyer',
                ]
            );

            return [
                'success'         => true,
                'message'         => 'Refund of ₹' . number_format($refundAmount, 2) . ' processed successfully.',
                'refund_id'       => $refundTxnId,
                'amount'          => $refundAmount,
                'gateway_success' => $gatewayRefundSuccess,
            ];
        });
    }

    /**
     * Handle incoming payment gateway webhooks (Razorpay / Cashfree / Stripe).
     */
    public function handleWebhook(array $payload, ?string $signature = null): array
    {
        Log::info("Payment Webhook received:", $payload);

        $event = $payload['event'] ?? 'unknown';

        if ($event === 'payment.captured') {
            $payment = $payload['payload']['payment']['entity'] ?? [];
            $orderNo = $payment['notes']['order_no'] ?? null;
            if ($orderNo) {
                Order::where('orderno', $orderNo)->update([
                    'payment_status' => 'Paid',
                    'status'         => 'Pending', // Pending seller acceptance
                ]);
            }
        } elseif ($event === 'refund.processed') {
            $refund = $payload['payload']['refund']['entity'] ?? [];
            $paymentId = $refund['payment_id'] ?? null;
            if ($paymentId) {
                DB::table('transections')->where('txnid', $paymentId)->update(['status' => 'Refunded']);
            }
        }

        return ['status' => true, 'event' => $event];
    }
}
