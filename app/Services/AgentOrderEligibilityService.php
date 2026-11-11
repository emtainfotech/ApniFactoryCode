<?php

namespace App\Services;

use App\Jobs\SendAgentIncentiveNotificationJob;
use App\Models\AgentEligibleOrder;
use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveProcessingLog;
use App\Models\BuyerAgentAttribution;
use App\Models\Customer;
use App\Models\FulfilmentClaim;
use App\Models\Order;
use App\Models\SellerFulfilment;
use App\Models\Transection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AgentOrderEligibilityService
{
    public function __construct(
        protected AgentIncentiveCalculationService $calculationService
    ) {
    }

    /**
     * Evaluate or update eligible-order slot state for any order lifecycle event
     * (verified payment webhook, seller fulfilment acceptance/rejection/dispatch,
     * buyer acknowledgement, dispute/claim, cancellation, or refund).
     *
     * Idempotent, concurrency-safe (DB row lock + unique constraints),
     * and treats the parent checkout order as one order unit across all sellers.
     */
    public function handleOrderLifecycleEvent(
        Order $order,
        string $eventType,
        ?string $eventRef = null,
        array $meta = []
    ): ?AgentEligibleOrder {
        $parentOrder = $order->parent_order_id
            ? Order::find($order->parent_order_id) ?? $order
            : $order;

        $eventKey = $eventRef
            ? "evt_{$parentOrder->id}_{$eventType}_{$eventRef}"
            : null;

        // 1. Replay Deduplication Guard (idempotent return on webhook/job replay)
        if ($eventKey) {
            $existingEvent = AgentIncentiveProcessingLog::where('event_key', $eventKey)->first();
            if ($existingEvent && $existingEvent->status === AgentIncentiveProcessingLog::STATUS_PROCESSED) {
                return AgentEligibleOrder::where('parent_order_id', $parentOrder->id)->first();
            }
        }

        $procLog = AgentIncentiveProcessingLog::updateOrCreate(
            ['event_key' => $eventKey ?: "evt_{$parentOrder->id}_{$eventType}_" . Str::uuid()],
            [
                'event_type'        => $eventType,
                'parent_order_id'   => $parentOrder->id,
                'order_id'          => $order->id,
                'payload'           => $meta,
                'status'            => AgentIncentiveProcessingLog::STATUS_PENDING,
                'last_attempted_at' => now(),
            ]
        );

        try {
            // 2. Resolve buyer's APPROVED active primary field agent attribution
            $attribution = BuyerAgentAttribution::where('customer_id', $parentOrder->customer_id)
                ->where('status', BuyerAgentAttribution::STATUS_ACTIVE)
                ->where('approval_status', BuyerAgentAttribution::APPROVAL_APPROVED)
                ->with('agent')
                ->first();

            // Do not create tracking records for buyers without an approved attribution
            if (!$attribution) {
                $existingTracked = AgentEligibleOrder::where('parent_order_id', $parentOrder->id)->first();
                if (!$existingTracked) {
                    $procLog->update([
                        'status'       => AgentIncentiveProcessingLog::STATUS_SKIPPED,
                        'processed_at' => now(),
                    ]);
                    return null;
                }
            }

            if ($attribution && config('marketplace_agents.require_attribution_prior_to_order', false)) {
                $attributedAt = $attribution->approved_at ?? $attribution->attributed_at;
                if ($attributedAt && $parentOrder->created_at && $parentOrder->created_at->lt($attributedAt)) {
                    $procLog->update([
                        'status'       => AgentIncentiveProcessingLog::STATUS_SKIPPED,
                        'processed_at' => now(),
                        'error_message'=> 'Order predates buyer attribution timestamp (per prior-attribution policy).',
                    ]);
                    return null;
                }
            }

            // 3. Evaluate holistic parent-order eligibility state across ALL sellers/transactions/claims
            $evaluation = $this->inspectParentOrderEligibilityState($parentOrder);
            $schemeLimit = (int)config('marketplace_agents.eligible_orders_limit', 3);

            $tracked = DB::transaction(function () use (
                $parentOrder,
                $attribution,
                $evaluation,
                $eventType,
                $eventRef,
                $eventKey,
                $schemeLimit
            ) {
                // Pessimistic buyer lock serializes concurrent orders so sequence 1..3 is atomic!
                Customer::where('id', $parentOrder->customer_id)->lockForUpdate()->first();

                $record = AgentEligibleOrder::where('parent_order_id', $parentOrder->id)
                    ->lockForUpdate()
                    ->first();

                $resolvedAgentId = $record ? $record->field_agent_id : $attribution->field_agent_id;
                $resolvedAttrId = $record ? $record->buyer_agent_attribution_id : $attribution->id;

                if (!$record) {
                    $record = AgentEligibleOrder::create([
                        'eligibility_ref'            => 'ELIG-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                        'customer_id'                => (int)$parentOrder->customer_id,
                        'field_agent_id'             => (int)$resolvedAgentId,
                        'buyer_agent_attribution_id' => (int)$resolvedAttrId,
                        'parent_order_id'            => (int)$parentOrder->id,
                        'orderno'                    => $parentOrder->orderno,
                        'seller_fulfilment_refs'     => $evaluation['seller_fulfilment_refs'],
                        'seller_count'               => $evaluation['seller_count'],
                        'eligibility_status'         => AgentEligibleOrder::STATUS_PENDING,
                        'source_event_type'          => $eventType,
                        'source_event_ref'           => $eventRef,
                        'idempotency_key'            => $eventKey,
                        'payment_verified_at'        => $evaluation['payment_verified_at'],
                        'fulfilled_at'               => $evaluation['fulfilled_at'],
                        'cooling_window_ends_at'     => $evaluation['cooling_window_ends_at'],
                        'incentive_generation_status'=> AgentEligibleOrder::INCENTIVE_PENDING_ELIGIBILITY,
                    ]);
                }

                $prevStatus = $record->eligibility_status;
                $targetStatus = $evaluation['status'];

                // Case A: Previously Eligible order is now Cancelled or Fully Refunded -> release slot & flag reversal
                if ($prevStatus === AgentEligibleOrder::STATUS_ELIGIBLE &&
                    in_array($targetStatus, [
                        AgentEligibleOrder::STATUS_INELIGIBLE_REFUNDED,
                        AgentEligibleOrder::STATUS_INELIGIBLE_CANCELLED,
                    ], true)
                ) {
                    $reversedStatus = $targetStatus === AgentEligibleOrder::STATUS_INELIGIBLE_REFUNDED
                        ? AgentEligibleOrder::STATUS_REVERSED_REFUNDED
                        : AgentEligibleOrder::STATUS_REVERSED_CANCELLED;

                    $record->update([
                        'eligibility_status'          => $reversedStatus,
                        'active_slot_number'          => null, // Free buyer slot without erasing sequence_number audit
                        'seller_fulfilment_refs'      => $evaluation['seller_fulfilment_refs'],
                        'seller_count'                => $evaluation['seller_count'],
                        'source_event_type'           => $eventType,
                        'source_event_ref'            => $eventRef,
                        'incentive_generation_status' => AgentEligibleOrder::INCENTIVE_NOT_APPLICABLE,
                        'evaluation_notes'            => $evaluation['reason'],
                    ]);

                    AgentIncentiveAuditLog::logAction(
                        'eligible_order',
                        $record->id,
                        'eligible_order_reversed_' . $targetStatus,
                        null,
                        $prevStatus,
                        $reversedStatus,
                        $evaluation['reason'],
                        ['sequence_number' => $record->sequence_number]
                    );

                    $this->calculationService->handleEligibilityReversal($record, $evaluation['reason']);

                    return $record->fresh();
                }

                // Case B: Previously Eligible order later encounters dispute or partial refund -> hold pending_review
                if ($prevStatus === AgentEligibleOrder::STATUS_ELIGIBLE &&
                    $targetStatus === AgentEligibleOrder::STATUS_PENDING_REVIEW
                ) {
                    $record->update([
                        'eligibility_status'          => AgentEligibleOrder::STATUS_PENDING_REVIEW,
                        'seller_fulfilment_refs'      => $evaluation['seller_fulfilment_refs'],
                        'seller_count'                => $evaluation['seller_count'],
                        'source_event_type'           => $eventType,
                        'source_event_ref'            => $eventRef,
                        'incentive_generation_status' => AgentEligibleOrder::INCENTIVE_PENDING_REVIEW,
                        'evaluation_notes'            => $evaluation['reason'],
                    ]);

                    $this->calculationService->flagLedgerPendingReview($record, $evaluation['reason']);

                    AgentIncentiveAuditLog::logAction(
                        'eligible_order',
                        $record->id,
                        'eligible_order_held_pending_review',
                        null,
                        $prevStatus,
                        AgentEligibleOrder::STATUS_PENDING_REVIEW,
                        $evaluation['reason']
                    );

                    return $record->fresh();
                }

                // Case C: Already Eligible & still Eligible -> idempotent update (never allocate a second slot!)
                if ($prevStatus === AgentEligibleOrder::STATUS_ELIGIBLE &&
                    $targetStatus === AgentEligibleOrder::STATUS_ELIGIBLE
                ) {
                    $record->update([
                        'seller_fulfilment_refs' => $evaluation['seller_fulfilment_refs'],
                        'seller_count'           => $evaluation['seller_count'],
                        'source_event_type'      => $eventType,
                        'source_event_ref'       => $eventRef,
                    ]);

                    // Ensure incentive attempt is reconciled if not yet generated (e.g. policy just approved)
                    if (!$record->earnedLedgerEntry()->exists()) {
                        $this->calculationService->processIncentiveForEligibleOrder($record, $eventRef);
                    }

                    return $record->fresh();
                }

                // Case D: Transitioning INTO Eligible -> allocate sequence 1, 2, or 3 (max 3 per buyer!)
                if ($targetStatus === AgentEligibleOrder::STATUS_ELIGIBLE) {
                    $occupiedSlots = AgentEligibleOrder::where('customer_id', $parentOrder->customer_id)
                        ->whereNotNull('active_slot_number')
                        ->lockForUpdate()
                        ->pluck('active_slot_number')
                        ->all();

                    $nextSlot = null;
                    for ($slot = 1; $slot <= $schemeLimit; $slot++) {
                        if (!in_array($slot, $occupiedSlots, true)) {
                            $nextSlot = $slot;
                            break;
                        }
                    }

                    if ($nextSlot === null) {
                        // Buyer already completed all 3 eligible orders across platform sellers!
                        $record->update([
                            'eligibility_status'          => AgentEligibleOrder::STATUS_INELIGIBLE_LIMIT_EXCEEDED,
                            'sequence_number'             => null,
                            'active_slot_number'          => null,
                            'seller_fulfilment_refs'      => $evaluation['seller_fulfilment_refs'],
                            'seller_count'                => $evaluation['seller_count'],
                            'payment_verified_at'         => $evaluation['payment_verified_at'],
                            'fulfilled_at'                => $evaluation['fulfilled_at'],
                            'cooling_window_ends_at'      => $evaluation['cooling_window_ends_at'],
                            'source_event_type'           => $eventType,
                            'source_event_ref'            => $eventRef,
                            'incentive_generation_status' => AgentEligibleOrder::INCENTIVE_NOT_APPLICABLE,
                            'incentive_block_reason'      => "Scheme cap ({$schemeLimit}/{$schemeLimit}) already reached for buyer account.",
                            'evaluation_notes'            => "Order qualified operationally, but buyer already consumed max {$schemeLimit} eligible slots.",
                        ]);

                        AgentIncentiveAuditLog::logAction(
                            'eligible_order',
                            $record->id,
                            'eligible_order_limit_exceeded',
                            null,
                            $prevStatus,
                            AgentEligibleOrder::STATUS_INELIGIBLE_LIMIT_EXCEEDED,
                            "Maximum {$schemeLimit} eligible orders reached for customer #{$parentOrder->customer_id}."
                        );

                        return $record->fresh();
                    }

                    $record->update([
                        'eligibility_status'     => AgentEligibleOrder::STATUS_ELIGIBLE,
                        'sequence_number'        => $nextSlot,
                        'active_slot_number'     => $nextSlot,
                        'seller_fulfilment_refs' => $evaluation['seller_fulfilment_refs'],
                        'seller_count'           => $evaluation['seller_count'],
                        'payment_verified_at'    => $evaluation['payment_verified_at'],
                        'fulfilled_at'           => $evaluation['fulfilled_at'],
                        'cooling_window_ends_at' => $evaluation['cooling_window_ends_at'],
                        'eligible_at'            => $record->eligible_at ?: now(),
                        'source_event_type'      => $eventType,
                        'source_event_ref'       => $eventRef,
                        'evaluation_notes'       => $evaluation['reason'],
                    ]);

                    AgentIncentiveAuditLog::logAction(
                        'eligible_order',
                        $record->id,
                        'eligible_order_slot_assigned',
                        null,
                        $prevStatus,
                        AgentEligibleOrder::STATUS_ELIGIBLE,
                        "Assigned buyer eligible-order sequence {$nextSlot}/{$schemeLimit} on event '{$eventType}'.",
                        null,
                        [
                            'sequence_number' => $nextSlot,
                            'parent_order_id' => $parentOrder->id,
                            'seller_count'    => $evaluation['seller_count'],
                        ]
                    );

                    $this->calculationService->processIncentiveForEligibleOrder($record, $eventRef);

                    return $record->fresh();
                }

                // Case E: Non-eligible / Pending states (NEVER consume a slot & NEVER create incentive!)
                $incentiveStatus = match ($targetStatus) {
                    AgentEligibleOrder::STATUS_PENDING        => AgentEligibleOrder::INCENTIVE_PENDING_ELIGIBILITY,
                    AgentEligibleOrder::STATUS_PENDING_REVIEW => AgentEligibleOrder::INCENTIVE_PENDING_REVIEW,
                    default                                   => AgentEligibleOrder::INCENTIVE_NOT_APPLICABLE,
                };

                $record->update([
                    'eligibility_status'          => $targetStatus,
                    'sequence_number'             => $record->sequence_number,
                    'active_slot_number'          => null,
                    'seller_fulfilment_refs'      => $evaluation['seller_fulfilment_refs'],
                    'seller_count'                => $evaluation['seller_count'],
                    'payment_verified_at'         => $evaluation['payment_verified_at'],
                    'fulfilled_at'                => $evaluation['fulfilled_at'],
                    'cooling_window_ends_at'      => $evaluation['cooling_window_ends_at'],
                    'source_event_type'           => $eventType,
                    'source_event_ref'            => $eventRef,
                    'incentive_generation_status' => $incentiveStatus,
                    'evaluation_notes'            => $evaluation['reason'],
                ]);

                return $record->fresh();
            });

            $procLog->update([
                'status'       => AgentIncentiveProcessingLog::STATUS_PROCESSED,
                'processed_at' => now(),
                'error_message'=> null,
            ]);

            return $tracked;
        } catch (QueryException $e) {
            $procLog->increment('attempts');
            $procLog->update([
                'status'        => AgentIncentiveProcessingLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
            $this->notifyAdminsOnProcessingFailure($parentOrder, $eventType, $e->getMessage(), $procLog->id);
            Log::error("[AgentOrderEligibility] Query error processing order #{$parentOrder->id}: " . $e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            $procLog->increment('attempts');
            $procLog->update([
                'status'        => AgentIncentiveProcessingLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
            $this->notifyAdminsOnProcessingFailure($parentOrder, $eventType, $e->getMessage(), $procLog->id);
            Log::error("[AgentOrderEligibility] Error processing order #{$parentOrder->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Evaluate holistic eligibility state of a parent order across child seller orders,
     * verified payment transactions, order_status entries, and dispute claims.
     */
    public function inspectParentOrderEligibilityState(Order $parentOrder): array
    {
        $childOrders = $parentOrder->childOrders()->get();
        $childIds = $childOrders->pluck('id')->all();
        $allOrderIds = array_values(array_unique(array_merge([$parentOrder->id], $childIds)));

        $fulfilments = SellerFulfilment::whereIn('order_id', $allOrderIds)
            ->orWhere('parent_order_id', $parentOrder->id)
            ->with(['company:id,name', 'order:id,orderno,user_id,grandtotal,netamount'])
            ->get()
            ->unique('id')
            ->values();

        $sellerRefs = $fulfilments->map(function (SellerFulfilment $f) {
            return [
                'fulfilment_id'     => $f->id,
                'fulfilment_ref'    => $f->fulfilment_ref,
                'order_id'          => $f->order_id,
                'order_no'          => $f->order?->orderno,
                'seller_user_id'    => $f->seller_user_id,
                'company_name'      => $f->company?->name,
                'fulfilment_status' => $f->fulfilment_status,
                'dispute_status'    => $f->dispute_status,
            ];
        })->values()->all();

        $sellerCount = max(1, count($childIds) > 0 ? count($childIds) : max(1, $fulfilments->count()));

        $transactions = Transection::whereIn('order_id', $allOrderIds)
            ->orWhere('parent_order_id', $parentOrder->id)
            ->orWhere('order_no', $parentOrder->orderno)
            ->get();

        $latestOrderStatuses = DB::table('order_status')
            ->whereIn('order_id', $allOrderIds)
            ->orWhere('order_no', $parentOrder->orderno)
            ->orderByDesc('id')
            ->get();

        $openClaimsCount = FulfilmentClaim::where(function ($q) use ($parentOrder, $allOrderIds) {
            $q->where('parent_order_id', $parentOrder->id)
              ->orWhereIn('order_id', $allOrderIds);
        })->whereIn('claim_status', [
            FulfilmentClaim::STATUS_SUBMITTED,
            FulfilmentClaim::STATUS_SELLER_RESPONDED,
            FulfilmentClaim::STATUS_UNDER_REVIEW,
        ])->count();

        // Check Refunded / Fraudulent / Cancelled / Rejected FIRST
        $hasTamperedOrFraud = $transactions->contains(function ($t) {
            return in_array(strtolower((string)$t->verification_status), ['tampered', 'failed'], true)
                || str_contains(strtolower((string)$t->failure_code), 'fraud');
        }) && !$transactions->contains(fn($t) => strtolower((string)$t->status) === 'successful' || strtolower((string)$t->status) === 'success');

        if ($hasTamperedOrFraud) {
            return $this->evalResult(
                AgentEligibleOrder::STATUS_INELIGIBLE_FRAUDULENT,
                'Order payment failed cryptographic verification or triggered fraud hold.',
                $sellerRefs,
                $sellerCount
            );
        }

        $refundTxns = $transactions->filter(fn($t) => strtolower((string)$t->status) === 'refunded');
        if (strtolower((string)$parentOrder->payment_status) === 'refunded' || $refundTxns->isNotEmpty()) {
            $refundSum = 0.0;
            foreach ($refundTxns as $rt) {
                $detail = json_decode((string)$rt->txndetail, true);
                if (is_array($detail) && isset($detail['amount'])) {
                    $refundSum += (float)$detail['amount'];
                } else {
                    $refundSum = (float)$parentOrder->grandtotal; // Full refund indicated when detail omits partial amount
                }
            }

            if (strtolower((string)$parentOrder->payment_status) === 'refunded' ||
                $refundSum <= 0 ||
                $refundSum + 0.01 >= (float)$parentOrder->grandtotal
            ) {
                return $this->evalResult(
                    AgentEligibleOrder::STATUS_INELIGIBLE_REFUNDED,
                    'Full order refund recorded; order excluded from consuming an eligible slot.',
                    $sellerRefs,
                    $sellerCount
                );
            }

            return $this->evalResult(
                AgentEligibleOrder::STATUS_PENDING_REVIEW,
                'Partial refund detected; held pending_review until business approves partial refund policy.',
                $sellerRefs,
                $sellerCount
            );
        }

        // Check cancellation & rejection in order_status + fulfilment_status
        $latestStatusString = strtolower((string)($latestOrderStatuses->first()->status ?? ''));
        $cancelledFulfilments = $fulfilments->where('fulfilment_status', SellerFulfilment::STATUS_CANCELLED)->count();
        $rejectedFulfilments = $fulfilments->where('fulfilment_status', SellerFulfilment::STATUS_REJECTED)->count();

        if ($latestStatusString === 'cancelled' || ($fulfilments->isNotEmpty() && $cancelledFulfilments === $fulfilments->count())) {
            return $this->evalResult(
                AgentEligibleOrder::STATUS_INELIGIBLE_CANCELLED,
                'Order cancelled across sellers; does not consume an eligible slot.',
                $sellerRefs,
                $sellerCount
            );
        }

        if ($latestStatusString === 'rejected' || ($fulfilments->isNotEmpty() && $rejectedFulfilments === $fulfilments->count())) {
            return $this->evalResult(
                AgentEligibleOrder::STATUS_INELIGIBLE_REJECTED,
                'Order rejected by seller(s); does not consume an eligible slot.',
                $sellerRefs,
                $sellerCount
            );
        }

        // Check Verified Payment prerequisite (unpaid never consumes slot)
        $hasUnverifiedOnly = $transactions->isNotEmpty() &&
            $transactions->every(fn($t) => strtolower((string)$t->verification_status) === 'unverified' && !in_array(strtolower((string)$t->status), ['success', 'successful'], true));

        if (!$parentOrder->isPaid() || $hasUnverifiedOnly) {
            return $this->evalResult(
                AgentEligibleOrder::STATUS_INELIGIBLE_UNPAID,
                'Order is unpaid or payment verification is incomplete.',
                $sellerRefs,
                $sellerCount
            );
        }

        $paymentVerifiedAt = $parentOrder->paid_at
            ?? $transactions->first(fn($t) => in_array(strtolower((string)$t->status), ['success', 'successful'], true))?->verified_at
            ?? now();

        // Check Partial Cancellation / Rejection or Open Disputes -> hold pending_review!
        if ($cancelledFulfilments > 0 || $rejectedFulfilments > 0) {
            return $this->evalResult(
                AgentEligibleOrder::STATUS_PENDING_REVIEW,
                'Partial cancellation/rejection detected across multi-seller fulfilment; awaiting policy review.',
                $sellerRefs,
                $sellerCount,
                $paymentVerifiedAt
            );
        }

        $disputedFulfilment = $fulfilments->first(fn(SellerFulfilment $f) => $f->dispute_status !== SellerFulfilment::DISPUTE_NONE);
        if ($openClaimsCount > 0 || $disputedFulfilment) {
            return $this->evalResult(
                AgentEligibleOrder::STATUS_PENDING_REVIEW,
                'Open shortage/damage dispute detected; held pending_review per dispute policy.',
                $sellerRefs,
                $sellerCount,
                $paymentVerifiedAt
            );
        }

        // Verify ALL seller sub-orders have completed qualifying fulfilment milestone
        $expectedSubOrdersCount = count($childIds) > 0 ? count($childIds) : 1;
        $qualifyingEvent = config('marketplace_agents.eligibility_qualifying_event', 'buyer_acknowledged_or_delivered');

        $permittedStatuses = match ($qualifyingEvent) {
            'dispatched' => [
                SellerFulfilment::STATUS_DISPATCHED,
                SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED,
                SellerFulfilment::STATUS_DELIVERED,
            ],
            default => [
                SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED,
                SellerFulfilment::STATUS_DELIVERED,
            ],
        };

        $qualifiedFulfilments = $fulfilments->filter(fn(SellerFulfilment $f) => in_array($f->fulfilment_status, $permittedStatuses, true));

        if ($fulfilments->isEmpty() || $qualifiedFulfilments->count() < $expectedSubOrdersCount) {
            return $this->evalResult(
                AgentEligibleOrder::STATUS_PENDING,
                "Paid order is awaiting final qualifying fulfilment/receipt across all {$expectedSubOrdersCount} seller(s) ({$qualifiedFulfilments->count()}/{$expectedSubOrdersCount} completed).",
                $sellerRefs,
                $sellerCount,
                $paymentVerifiedAt
            );
        }

        // Compute latest fulfilment/acknowledgement timestamp across all sellers
        $fulfilledAt = $qualifiedFulfilments->max(function (SellerFulfilment $f) {
            return $f->buyer_acknowledged_at ?? $f->updated_at ?? now();
        });
        $fulfilledCarbon = $fulfilledAt ? Carbon::parse($fulfilledAt) : now();

        // Check configurable cancellation/refund cooling window if approved/configured
        $windowDays = config('marketplace_agents.eligibility_cooling_window_days');
        $windowEndsAt = ($windowDays !== null && (int)$windowDays > 0)
            ? $fulfilledCarbon->copy()->addDays((int)$windowDays)
            : null;

        if ($windowEndsAt && now()->lt($windowEndsAt)) {
            return $this->evalResult(
                AgentEligibleOrder::STATUS_PENDING,
                "All sellers fulfilled, but order remains inside {$windowDays}-day cancellation/refund window (ends {$windowEndsAt->toDateTimeString()}).",
                $sellerRefs,
                $sellerCount,
                $paymentVerifiedAt,
                $fulfilledCarbon,
                $windowEndsAt
            );
        }

        return $this->evalResult(
            AgentEligibleOrder::STATUS_ELIGIBLE,
            "Parent order satisfied verified payment and full multi-seller fulfilment ({$qualifiedFulfilments->count()}/{$expectedSubOrdersCount}).",
            $sellerRefs,
            $sellerCount,
            $paymentVerifiedAt,
            $fulfilledCarbon,
            $windowEndsAt
        );
    }

    /**
     * Progress summary (0/3..3/3) for buyer, agent, and admin portals.
     */
    public function getBuyerEligibilityProgress(int $customerId): array
    {
        return $this->getBatchEligibilityProgress([$customerId])[$customerId];
    }

    /**
     * Batch-load buyer progress across multiple customers in a single query (eliminates N+1 in dashboards).
     */
    public function getBatchEligibilityProgress(array $customerIds): array
    {
        $uniqueIds = array_values(array_unique(array_map('intval', $customerIds)));
        $trackingEnabled = (bool)config('marketplace_agents.eligible_order_tracking_implemented', true);
        $limit = (int)config('marketplace_agents.eligible_orders_limit', 3);

        if (empty($uniqueIds)) {
            return [];
        }

        if (!$trackingEnabled) {
            $out = [];
            foreach ($uniqueIds as $cid) {
                $out[$cid] = [
                    'available'          => false,
                    'eligible_count'     => 0,
                    'pending_count'      => 0,
                    'scheme_order_limit' => $limit,
                    'display'            => 'Unavailable (Pending Phase 6.2 Eligible-Order Rules)',
                    'orders'             => [],
                ];
            }
            return $out;
        }

        $grouped = AgentEligibleOrder::whereIn('customer_id', $uniqueIds)
            ->orderByRaw('CASE WHEN active_slot_number IS NULL THEN 99 ELSE active_slot_number END ASC')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('customer_id');

        $results = [];
        foreach ($uniqueIds as $cid) {
            $trackedOrders = $grouped->get($cid, collect());
            $eligibleSlots = $trackedOrders->where('eligibility_status', AgentEligibleOrder::STATUS_ELIGIBLE)
                ->whereNotNull('active_slot_number');
            $eligibleCount = min($limit, $eligibleSlots->count());
            $pendingCount = $trackedOrders->whereIn('eligibility_status', [
                AgentEligibleOrder::STATUS_PENDING,
                AgentEligibleOrder::STATUS_PENDING_REVIEW,
            ])->count();

            $results[$cid] = [
                'available'          => true,
                'eligible_count'     => $eligibleCount,
                'pending_count'      => $pendingCount,
                'remaining_slots'    => max(0, $limit - $eligibleCount),
                'scheme_order_limit' => $limit,
                'display'            => "{$eligibleCount}/{$limit}",
                'orders'             => $trackedOrders->map(fn(AgentEligibleOrder $o) => $o->toAgentSafeArray())->values()->all(),
            ];
        }

        return $results;
    }

    /**
     * Paginated list of eligible/tracked orders for Admin portal & API.
     */
    public function listEligibleOrders(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = AgentEligibleOrder::with([
            'customer:id,name,mobile,location',
            'agent:id,agent_code,name,territory',
            'parentOrder:id,orderno,grandtotal,netamount,payment_status,created_at',
            'earnedLedgerEntry',
        ])->orderByDesc('id');

        if (!empty($filters['field_agent_id'])) {
            $query->where('field_agent_id', (int)$filters['field_agent_id']);
        }
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', (int)$filters['customer_id']);
        }
        if (!empty($filters['eligibility_status'])) {
            $query->where('eligibility_status', $filters['eligibility_status']);
        }
        if (!empty($filters['territory'])) {
            $territory = trim((string)$filters['territory']);
            $query->whereHas('agent', fn($a) => $a->where('territory', 'like', "%{$territory}%"));
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $term = trim((string)$filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('eligibility_ref', 'like', "%{$term}%")
                  ->orWhere('orderno', 'like', "%{$term}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('agent', fn($a) => $a->where('agent_code', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%"));
            });
        }

        return $query->paginate(min(max(1, $perPage), 100));
    }

    /**
     * Safe retry mechanism for previously failed lifecycle events (Stage 5).
     */
    public function retryFailedEvent(int $processingLogId, User $admin): ?AgentEligibleOrder
    {
        if (!$admin->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Administrator access required to retry failed events.'],
            ]);
        }

        $log = AgentIncentiveProcessingLog::findOrFail($processingLogId);
        $order = Order::findOrFail($log->order_id ?: $log->parent_order_id);

        $log->increment('attempts');
        $log->update([
            'status'            => AgentIncentiveProcessingLog::STATUS_RETRIED,
            'last_attempted_at' => now(),
        ]);

        $result = $this->handleOrderLifecycleEvent($order, $log->event_type, $log->event_key . '_retry_' . $log->attempts);

        $log->update([
            'status'        => AgentIncentiveProcessingLog::STATUS_PROCESSED,
            'processed_at'  => now(),
            'error_message' => null,
        ]);

        return $result;
    }

    protected function evalResult(
        string $status,
        string $reason,
        array $sellerRefs,
        int $sellerCount,
        $paymentVerifiedAt = null,
        $fulfilledAt = null,
        $coolingEndsAt = null
    ): array {
        return [
            'status'                 => $status,
            'reason'                 => $reason,
            'seller_fulfilment_refs' => $sellerRefs,
            'seller_count'           => $sellerCount,
            'payment_verified_at'    => $paymentVerifiedAt,
            'fulfilled_at'           => $fulfilledAt,
            'cooling_window_ends_at' => $coolingEndsAt,
        ];
    }

    protected function notifyAdminsOnProcessingFailure(Order $parentOrder, string $eventType, string $error, int $logId): void
    {
        try {
            $admins = User::where(function ($q) {
                $q->where('id', 1)->orWhere('email', 'like', '%admin%');
            })->limit(5)->get()->filter(fn(User $u) => $u->isAdmin())->take(3);

            foreach ($admins as $admin) {
                SendAgentIncentiveNotificationJob::dispatch(
                    (int)$admin->id,
                    'admin',
                    "Agent Incentive Event Failure: Order #{$parentOrder->orderno}",
                    "Processing failed for event '{$eventType}' on Order #{$parentOrder->orderno}: {$error}",
                    [
                        'Order'     => $parentOrder->orderno,
                        'Event'     => $eventType,
                        'Log ID'    => (string)$logId,
                    ],
                    route('admin.incentives.eligible-orders'),
                    "inc_fail_{$logId}_{$admin->id}"
                );
            }
        } catch (\Throwable $e) {
            // Do not mask the primary failure exception
        }
    }
}

