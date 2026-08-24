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

                // 3. Notify Buyer
                if ($order->customer_id) {
                    $buyer = Customer::find($order->customer_id);
                    if ($buyer) {
                        $buyerNotification = [
                            'customer_id' => $buyer->id,
                            'title'       => 'Order #' . $order->orderno . ' Cancelled (Expired)',
                            'msg'         => 'Your order #' . $order->orderno . ' was cancelled because the seller did not respond within 3 days. A full refund has been initiated.',
                            'msgread'     => 0,
                            'type'        => 'customer',
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ];
                        DB::table('notifications')->insert($buyerNotification);

                        // If WhatsApp mobile exists
                        if (!empty($buyer->mobile)) {
                            try {
                                $this->sendwhatsappmsg($buyer->mobile, 'order_cancelled_expired', $order->orderno);
                            } catch (\Throwable $e) {
                                // WhatsApp failure is non-blocking
                            }
                        }
                    }
                }

                // 4. Notify Seller of penalty / missed deadline
                if ($order->user_id) {
                    $sellerNotification = [
                        'user_id'     => $order->user_id,
                        'customer_id' => $order->user_id,
                        'title'       => 'Order #' . $order->orderno . ' Expired Due to Inactivity',
                        'msg'         => 'Order #' . $order->orderno . ' has been auto-cancelled because no response was provided within the 72-hour window. Repeated expirations affect seller rating.',
                        'msgread'     => 0,
                        'type'        => 'seller',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                    DB::table('notifications')->insert($sellerNotification);
                }

                // 5. If transaction was paid, record refund entry in wallet
                $paidTxn = DB::table('transections')
                    ->where('order_no', $order->orderno)
                    ->where('status', 'success')
                    ->first();

                if ($paidTxn) {
                    DB::table('wallet')->insert([
                        'user_id'       => $order->user_id,
                        'order_id'      => $order->id,
                        'orderno'       => $order->orderno,
                        'value'         => $order->grandtotal,
                        'commission'    => 0,
                        'refundtobuyer' => $order->grandtotal,
                        'debit'         => 0,
                        'credit'        => 0,
                        'balance'       => 0,
                        'type'          => 'refund',
                        'action'        => 'refund',
                        'addby'         => 'system',
                        'msg'           => 'Auto-refund for expired order #' . $order->orderno,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                $expiredCount++;
                $this->warn("Expired order: {$order->orderno} (Created: {$order->created_at})");
            }
        }

        $this->info("Auto-expiry run complete. Total expired orders processed: {$expiredCount}");
        return 0;
    }
}
