<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessSellerCreditNote extends Command
{
    // The name and signature used to call the command
    protected $signature = 'credit:process-notes';

    // Description
    protected $description = 'Process seller credit commissions 21 days after successful delivery';

    public function handle()
    {   echo 'shi h 2';
        // For direct execution/testing, you can change subDays(21) to subDays(0) or match your test data rows
        // $targetDate = Carbon::now()->subDays(21)->toDateString();
        $targetDate = Carbon::now()->subDays(0)->toDateString();

            // Configure your static commission percentage deduction (e.g., 12%)
        $commissionPercentage = 12; 

        // 2. Query matching orders by mapping records directly on the unique `order_no` / `orderno` strings
        $eligibleOrders = DB::table('order_status as os')
            ->join('transections as t', 'os.order_no', '=', 't.order_no')
            ->join('order_tracks as ot', 'os.order_no', '=', 'ot.orderno') // Note: maps to 'orderno' in your tracking schema
            ->join('orders as o', 'os.order_no', '=', 'o.orderno')       // Note: maps to 'orderno' in your main orders schema
            ->select(
                'os.order_id',
                'os.order_no',
                'os.user_id as seller_id', 
                'o.grandtotal'
            )
            // Condition 1: Confirm delivered status occurred exactly 21 days ago
            ///->whereDate('os.created_at', $targetDate)
            ->where('os.status', 'delivered')
            
            // Condition 2: Double table business criteria cross-check
            ->where('t.status', 'success')
            ->where('ot.status', '1') // Adjust string flag to align with your platform settings
            
            // Condition 3: Ensure this specific order string does not already exist in the credits table
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('wallet')
                      ->whereColumn('wallet.orderno', 'os.order_no');
            })
            ->get();

        if ($eligibleOrders->isEmpty()) {
            logger('Credit Processor: No matching orders found for order number execution date: ' . $targetDate);
            return 0;
        }
        foreach ($eligibleOrders as $order) {
            
            $comision = DB::table("companies")->where('user_id', $order->seller_id)->first('comission');
            $commissionPercentage = $comision->comission;
            // Condition 4: Calculate custom percentage rates against total sales value
            $grandTotal = (float) $order->grandtotal;
            $calculatedCommission = ($grandTotal * $commissionPercentage) / 100;
            // Process within isolated database transactions to safeguard ledger balances against race conditions
            DB::transaction(function () use ($order, $calculatedCommission, $grandTotal) {
                
                // Fetch the seller's absolute latest ledger balance
                $lastCreditRecord = DB::table('wallet')
                    ->where('user_id', $order->seller_id)
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();
                $updatecomisamount = $grandTotal - $calculatedCommission;
                $previousBalance = $lastCreditRecord ? (float) $lastCreditRecord->balance : 0.00;
                $newBalance = $previousBalance + $updatecomisamount;
                
                    $insertcredit=[
                        'user_id'        => $order->seller_id,
                        'order_id'       => $order->order_id,
                        'orderno'        => $order->order_no,
                        'value'          => $grandTotal,
                        'commission'     => $calculatedCommission,
                        'refundtobuyer'  => 0.00,
                        'debit'          => 0.00,
                        'credit'         => $updatecomisamount,
                        'balance'        => $newBalance,
                        'addby'          => 'system',
                        'msg'            => "Commission Earned For Order #{$order->order_no}",
                        'created_at'     => Carbon::now(),
                        'updated_at'     => Carbon::now()
                    ];
                // Push new credit row entry mapped back to your credits table schema
                DB::table('wallet')->insert($insertcredit);
            });
        }

        logger('Credit Processor: All matching order numbers evaluated and balances credited.');
        return 0;
    }
}