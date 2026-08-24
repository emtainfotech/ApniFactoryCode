<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Company;
use App\Traits\WhatsappTraits;

class AutoExpirePendingOrders extends Command
{
    use WhatsappTraits;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-expire-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expires and cancels orders where seller has not responded within 3 days (72 hours).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for pending orders exceeding the 72-hour seller response deadline...');

        $cutoffTime = now()->subHours(72);

        // Find orders created more than 72 hours ago
        $orders = Order::where('created_at', '<=', $cutoffTime)->get();

        $expiredCount = 0;

        foreach ($orders as $order) {
            // Get latest status for this order
            $latestStatus = DB::table('order_status')
                ->where('order_no', $order->orderno)
                ->orderBy('id', 'desc')
                ->first();

            $statusName = $latestStatus ? strtolower($latestStatus->status) : 'pending';

            // Only cancel if still pending
            if ($statusName === 'pending' || $statusName === 'wait for confirmation' || $statusName === 'order received') {
                $expiryMessage = 'Order automatically cancelled by system: Seller response deadline (72 hours) exceeded.';

                // 1. Insert Cancelled status in order_status
                DB::table('order_status')->insert([
                    'order_id'   => $order->id,
                    'order_no'   => $order->orderno,
                    'user_id'    => $order->user_id,
                    'status'     => 'Cancelled',
                    'msg'        => $expiryMessage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Insert/Update order_tracks record
                DB::table('order_tracks')->updateOrInsert(
                    ['order_id' => $order->id],
                    [
                        'orderno'    => $order->orderno,
                        'status'     => '0',
                        'text'       => $expiryMessage,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // 3. Process Automated Refund via PaymentRefundService
                $refundService = app()->make(\App\Services\PaymentRefundService::class);
                $refundService->processRefund($order, 'Seller response deadline (72 hours) exceeded');

                // 4. Find Top 3 Alternative Sellers
                $altService = app()->make(\App\Services\AlternativeSellerService::class);
                $alternatives = $altService->getAlternativeSellers($order, 3);

                // 5. Notify Buyer with Alternatives and Refund info
                if ($order->customer_id) {
                    $buyer = Customer::find($order->customer_id);
                    if ($buyer) {
                        $notificationService = app()->make(\App\Services\NotificationService::class);
                        $notificationService->send(
                            'customer',
                            $buyer->id,
                            'Order #' . $order->orderno . ' Auto-Cancelled (Refunded)',
                            'Your order was cancelled as the seller did not respond within 3 days. A 100% refund has been credited. We found ' . count($alternatives) . ' alternative verified sellers for you.',
                            [
                                'order_id'     => $order->id,
                                'email_view'   => 'emails.order_cancelled_buyer',
                                'alternatives' => $alternatives,
                            ]
                        );
                    }
                }

                // 6. Notify Seller of SLA violation
                if ($order->user_id) {
                    $notificationService = app()->make(\App\Services\NotificationService::class);
                    $notificationService->send(
                        'seller',
                        (int)$order->user_id,
                        'Order #' . $order->orderno . ' Expired Due to Inactivity',
                        'Order #' . $order->orderno . ' has been auto-cancelled because no response was provided within the 72-hour window. Repeated expirations affect your fulfillment rating.'
                    );

                    // Update Company Rejection Rate Metric
                    $company = Company::where('user_id', $order->user_id)->first();
                    if ($company) {
                        $totalRec = (int)($company->total_orders_received ?? 0) + 1;
                        $totalRej = (int)($company->total_orders_rejected ?? 0) + 1;
                        $rejRate = ($totalRej / max(1, $totalRec)) * 100;
                        $company->update([
                            'total_orders_received' => $totalRec,
                            'total_orders_rejected' => $totalRej,
                            'rejection_rate'        => round($rejRate, 2),
                        ]);
                    }
                }

                $expiredCount++;
                $this->warn("Expired order: {$order->orderno} (Created: {$order->created_at})");
            }
        }

        $this->info("Auto-expiry run complete. Total expired orders processed: {$expiredCount}");
        return 0;
    }
}
