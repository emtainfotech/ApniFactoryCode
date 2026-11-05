<?php

namespace App\Services;

use App\Models\FinancialLedger;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\SellerSettlement;
use App\Models\Transection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FinancialLedgerService
{
    /**
     * Record double-entry financial ledger entries and settlement records upon verified order payment.
     */
    public function recordOrderPayment(Order $order, Transection $txn): array
    {
        return DB::transaction(function () use ($order, $txn) {
            // Determine seller child orders if parent order, or treat order as its own seller fulfilment order
            $sellerOrders = $order->childOrders()->count() > 0 ? $order->childOrders()->get() : collect([$order]);
            $settlements = [];

            foreach ($sellerOrders as $sellerOrder) {
                // Idempotency: skip if settlement already created for this seller order
                $existing = SellerSettlement::where('order_id', $sellerOrder->id)->first();
                if ($existing) {
                    $settlements[] = $existing;
                    continue;
                }

                $sellerUserId = (int) $sellerOrder->user_id;
                $grossOrderAmount = (float) $sellerOrder->grandtotal;

                // 1. Resolve commission snapshot or calculate from Phase 3.2 commission engine
                $commissionSnapshot = OrderCommissionSnapshot::where('order_id', $sellerOrder->id)->first();

                if ($commissionSnapshot) {
                    $commissionAmount = (float) $commissionSnapshot->commission_amount;
                    $commissionGstAmount = (float) $commissionSnapshot->gst_amount;
                } else {
                    // Fallback to active commission rate or company setting
                    $company = DB::table('companies')->where('user_id', $sellerUserId)->first();
                    $commissionPct = $company ? (float)($company->comission ?? 10.00) : 10.00;
                    $commissionAmount = round(($grossOrderAmount * $commissionPct) / 100, 2);
                    $commissionGstAmount = round(($commissionAmount * 0.18), 2); // Standard 18% GST on platform services
                }

                $totalPlatformDeduction = round($commissionAmount + $commissionGstAmount, 2);
                $netSellerPayable = round($grossOrderAmount - $totalPlatformDeduction, 2);

                $companyId = DB::table('companies')->where('user_id', $sellerUserId)->value('id');
                $settlementRef = 'SETTLE-' . date('Y') . '-' . strtoupper(Str::random(8));

                // 2. Create Seller Settlement Record
                $settlement = SellerSettlement::create([
                    'settlement_ref'           => $settlementRef,
                    'parent_order_id'          => $order->parent_order_id ?: $order->id,
                    'order_id'                 => $sellerOrder->id,
                    'seller_user_id'           => $sellerUserId,
                    'company_id'               => $companyId,
                    'gross_order_amount'       => $grossOrderAmount,
                    'commission_amount'        => $commissionAmount,
                    'commission_gst_amount'    => $commissionGstAmount,
                    'total_platform_deduction' => $totalPlatformDeduction,
                    'net_seller_payable'       => $netSellerPayable,
                    'settlement_status'        => SellerSettlement::STATUS_ON_HOLD,
                    'hold_until'               => now()->addDays(7), // Payout hold period (configurable)
                    'notes'                    => "Settlement generated on payment {$txn->payment_ref} for order #{$sellerOrder->orderno}.",
                ]);

                // 3. Post Double-Entry Financial Ledger Records
                $lastBalance = (float) FinancialLedger::where('seller_user_id', $sellerUserId)
                    ->orderBy('id', 'desc')
                    ->value('running_balance') ?? 0.00;

                // Entry A: Gross Seller Payable (Credit to Seller Balance)
                $runningBalance = round($lastBalance + $grossOrderAmount, 2);
                FinancialLedger::create([
                    'entry_ref'          => 'LEDGER-' . strtoupper(Str::random(10)),
                    'seller_user_id'     => $sellerUserId,
                    'company_id'         => $companyId,
                    'parent_order_id'    => $order->parent_order_id ?: $order->id,
                    'order_id'           => $sellerOrder->id,
                    'transection_id'     => $txn->id,
                    'settlement_id'      => $settlement->id,
                    'entry_type'         => FinancialLedger::TYPE_SELLER_PAYABLE,
                    'direction'          => FinancialLedger::DIRECTION_CREDIT,
                    'amount'             => $grossOrderAmount,
                    'currency'           => 'INR',
                    'running_balance'    => $runningBalance,
                    'source_process'     => 'order_payment_settlement',
                    'notes'              => "Gross payable credited for order #{$sellerOrder->orderno}",
                ]);

                // Entry B: Platform Commission Fee (Debit from Seller Balance)
                $runningBalance = round($runningBalance - $commissionAmount, 2);
                FinancialLedger::create([
                    'entry_ref'          => 'LEDGER-' . strtoupper(Str::random(10)),
                    'seller_user_id'     => $sellerUserId,
                    'company_id'         => $companyId,
                    'parent_order_id'    => $order->parent_order_id ?: $order->id,
                    'order_id'           => $sellerOrder->id,
                    'transection_id'     => $txn->id,
                    'settlement_id'      => $settlement->id,
                    'entry_type'         => FinancialLedger::TYPE_PLATFORM_COMMISSION,
                    'direction'          => FinancialLedger::DIRECTION_DEBIT,
                    'amount'             => $commissionAmount,
                    'currency'           => 'INR',
                    'running_balance'    => $runningBalance,
                    'source_process'     => 'order_payment_settlement',
                    'notes'              => "Marketplace commission fee deducted for order #{$sellerOrder->orderno}",
                ]);

                // Entry C: GST on Platform Service (Debit from Seller Balance)
                if ($commissionGstAmount > 0) {
                    $runningBalance = round($runningBalance - $commissionGstAmount, 2);
                    FinancialLedger::create([
                        'entry_ref'          => 'LEDGER-' . strtoupper(Str::random(10)),
                        'seller_user_id'     => $sellerUserId,
                        'company_id'         => $companyId,
                        'parent_order_id'    => $order->parent_order_id ?: $order->id,
                        'order_id'           => $sellerOrder->id,
                        'transection_id'     => $txn->id,
                        'settlement_id'      => $settlement->id,
                        'entry_type'         => FinancialLedger::TYPE_GST_ON_COMMISSION,
                        'direction'          => FinancialLedger::DIRECTION_DEBIT,
                        'amount'             => $commissionGstAmount,
                        'currency'           => 'INR',
                        'running_balance'    => $runningBalance,
                        'source_process'     => 'order_payment_settlement',
                        'notes'              => "GST (18%) on marketplace commission for order #{$sellerOrder->orderno}",
                    ]);
                }

                $settlements[] = $settlement;
            }

            return $settlements;
        });
    }

    /**
     * Get seller-isolated financial ledger entries.
     */
    public function getSellerLedgerQuery(int $sellerUserId, array $filters = []): Builder
    {
        $query = FinancialLedger::where('seller_user_id', $sellerUserId)->orderBy('id', 'desc');

        if (!empty($filters['entry_type'])) {
            $query->where('entry_type', $filters['entry_type']);
        }
        if (!empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Get settlements query for finance administration.
     */
    public function getSettlementsQuery(array $filters = []): Builder
    {
        $query = SellerSettlement::with(['order', 'seller', 'company'])->orderBy('id', 'desc');

        if (!empty($filters['seller_user_id'])) {
            $query->where('seller_user_id', $filters['seller_user_id']);
        }
        if (!empty($filters['settlement_status'])) {
            $query->where('settlement_status', $filters['settlement_status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('settlement_ref', 'LIKE', "%{$search}%")
                  ->orWhere('payout_reference', 'LIKE', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Update settlement status with idempotency and payout protection.
     */
    public function updateSettlementStatus(
        SellerSettlement $settlement,
        string $newStatus,
        ?string $payoutRef = null,
        ?int $adminId = null,
        ?string $notes = null
    ): SellerSettlement {
        $allowedStatuses = [
            SellerSettlement::STATUS_PENDING,
            SellerSettlement::STATUS_ON_HOLD,
            SellerSettlement::STATUS_ELIGIBLE,
            SellerSettlement::STATUS_PROCESSING,
            SellerSettlement::STATUS_COMPLETED,
            SellerSettlement::STATUS_CANCELLED,
        ];

        if (!in_array($newStatus, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'status' => ["Invalid settlement status '{$newStatus}'."],
            ]);
        }

        // Idempotency check: Cannot complete an already completed settlement
        if ($settlement->isCompleted() && $newStatus === SellerSettlement::STATUS_COMPLETED) {
            return $settlement;
        }

        return DB::transaction(function () use ($settlement, $newStatus, $payoutRef, $adminId, $notes) {
            $settlement->update([
                'settlement_status'    => $newStatus,
                'payout_reference'     => $payoutRef ?? $settlement->payout_reference,
                'processed_at'         => ($newStatus === SellerSettlement::STATUS_COMPLETED) ? now() : $settlement->processed_at,
                'processed_by_user_id' => $adminId ?? $settlement->processed_by_user_id,
                'notes'                => $notes ? ($settlement->notes . "\n" . $notes) : $settlement->notes,
            ]);

            // When settlement is completed (disbursed), post the payout debit entry to the ledger
            if ($newStatus === SellerSettlement::STATUS_COMPLETED) {
                $lastBalance = (float) FinancialLedger::where('seller_user_id', $settlement->seller_user_id)
                    ->orderBy('id', 'desc')
                    ->value('running_balance') ?? 0.00;

                $newBalance = round($lastBalance - (float)$settlement->net_seller_payable, 2);

                FinancialLedger::create([
                    'entry_ref'          => 'LEDGER-PAYOUT-' . strtoupper(Str::random(8)),
                    'seller_user_id'     => $settlement->seller_user_id,
                    'company_id'         => $settlement->company_id,
                    'parent_order_id'    => $settlement->parent_order_id,
                    'order_id'           => $settlement->order_id,
                    'settlement_id'      => $settlement->id,
                    'entry_type'         => FinancialLedger::TYPE_PAYOUT_RELEASED,
                    'direction'          => FinancialLedger::DIRECTION_DEBIT,
                    'amount'             => (float)$settlement->net_seller_payable,
                    'currency'           => 'INR',
                    'running_balance'    => $newBalance,
                    'source_process'     => 'payout_disbursement',
                    'notes'              => "Payout disbursed to seller via reference '{$payoutRef}'.",
                    'created_by_user_id' => $adminId,
                ]);
            }

            return $settlement->fresh();
        });
    }
}

