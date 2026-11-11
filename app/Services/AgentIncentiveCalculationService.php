<?php

namespace App\Services;

use App\Jobs\SendAgentIncentiveNotificationJob;
use App\Models\AgentEligibleOrder;
use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentIncentiveRule;
use App\Models\AgentPayout;
use App\Models\AgentPayoutItem;
use App\Models\AgentPayoutReconciliationException;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AgentIncentiveCalculationService
{
    public function __construct(
        protected AgentIncentiveRuleService $ruleService
    ) {
    }

    /**
     * Check whether financial liability generation is permitted by written policy approval.
     */
    public function canGeneratePayableIncentive(): bool
    {
        return (bool)config('marketplace_agents.funding_policy_approved', false)
            && (bool)config('marketplace_agents.incentive_calculation_enabled', false)
            && config('marketplace_agents.incentive_funding_model') === AgentIncentiveRule::FUNDING_APNI_FACTORY
            && (bool)config('marketplace_agents.seller_deductions_prohibited', true);
    }

    /**
     * Compute and post ONE immutable 'earned' ledger row for an eligible order, ONLY when
     * the funding policy + calculation flag + an approved effective rule exist.
     * NEVER modifies SellerSettlement or FinancialLedger.
     */
    public function processIncentiveForEligibleOrder(
        AgentEligibleOrder $eligibleOrder,
        ?string $eventRef = null
    ): ?AgentIncentiveLedger {
        if ($eligibleOrder->eligibility_status !== AgentEligibleOrder::STATUS_ELIGIBLE ||
            !$eligibleOrder->sequence_number
        ) {
            return null;
        }

        // Critical Business-Policy Guard: never generate payable liabilities until BRD funding conflict is resolved
        if (!$this->canGeneratePayableIncentive()) {
            $reason = 'Payable incentive calculation blocked: Written business & finance approval of the Apni Factory-funded model is required before generating financial liabilities.';

            $eligibleOrder->update([
                'incentive_generation_status' => AgentEligibleOrder::INCENTIVE_BLOCKED_FUNDING,
                'incentive_block_reason'      => $reason,
            ]);

            AgentIncentiveAuditLog::logAction(
                'eligible_order',
                $eligibleOrder->id,
                'incentive_blocked_unapproved_funding',
                null,
                null,
                AgentEligibleOrder::INCENTIVE_BLOCKED_FUNDING,
                $reason
            );

            return null;
        }

        // Resolve approved rule effective at the order's eligibility timestamp
        $effectiveRule = $this->ruleService->resolveEffectiveRule($eligibleOrder->eligible_at ?? now());
        if (!$effectiveRule) {
            $eligibleOrder->update([
                'incentive_generation_status' => AgentEligibleOrder::INCENTIVE_BLOCKED_NO_RULE,
                'incentive_block_reason'      => 'No active approved incentive rule effective for order eligibility timestamp.',
            ]);
            return null;
        }

        try {
            $ledgerEntry = DB::transaction(function () use ($eligibleOrder, $effectiveRule, $eventRef) {
                $existing = AgentIncentiveLedger::where('earned_parent_order_lock', $eligibleOrder->parent_order_id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing;
                }

                $parentOrder = $eligibleOrder->parentOrder()->firstOrFail();
                $calc = $this->ruleService->calculateAmountForOrder($parentOrder, $effectiveRule);

                $entry = AgentIncentiveLedger::create([
                    'ledger_ref'                      => 'AIL-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                    'entry_type'                      => AgentIncentiveLedger::TYPE_EARNED,
                    'earned_parent_order_lock'        => $eligibleOrder->parent_order_id,
                    'customer_id'                     => $eligibleOrder->customer_id,
                    'field_agent_id'                  => $eligibleOrder->field_agent_id,
                    'buyer_agent_attribution_id'      => $eligibleOrder->buyer_agent_attribution_id,
                    'agent_eligible_order_id'         => $eligibleOrder->id,
                    'parent_order_id'                 => $eligibleOrder->parent_order_id,
                    'seller_fulfilment_refs'          => $eligibleOrder->seller_fulfilment_refs,
                    'eligible_order_sequence'         => $eligibleOrder->sequence_number,
                    'eligibility_event_ref'           => $eventRef ?? $eligibleOrder->eligibility_ref,
                    'eligibility_event_at'            => $eligibleOrder->eligible_at ?? now(),
                    'agent_incentive_rule_id'         => $calc['rule_id'],
                    'rule_code'                       => $calc['rule_code'],
                    'rule_version'                    => $calc['rule_version'],
                    'calculation_method'              => $calc['calculation_method'],
                    'calculation_base'                => $calc['calculation_base'],
                    'calculation_base_amount'         => $calc['calculation_base_amount'],
                    'applied_rate'                    => $calc['applied_rate'],
                    'applied_fixed_amount'            => $calc['applied_fixed_amount'],
                    'max_cap_amount'                  => $calc['max_cap_amount'],
                    'cap_applied'                     => $calc['cap_applied'],
                    'rounding_rule'                   => $calc['rounding_rule'],
                    'tax_treatment_snapshot'          => $calc['tax_treatment_snapshot'],
                    'gross_incentive_amount'          => $calc['gross_incentive_amount'],
                    'tax_deduction_amount'            => $calc['tax_deduction_amount'],
                    'calculated_incentive_amount'     => $calc['calculated_incentive_amount'],
                    'funding_expense_account'         => AgentIncentiveLedger::EXPENSE_APNI_FACTORY_PLATFORM,
                    'funding_policy_approved_snapshot'=> true,
                    'status'                          => AgentIncentiveLedger::STATUS_CALCULATED_PENDING_APPROVAL,
                    'idempotency_key'                 => "earned_order_{$eligibleOrder->parent_order_id}",
                    'reason'                          => "Calculated using {$calc['rule_code']} v{$calc['rule_version']} ({$calc['calculation_method']}). Funded strictly as Apni Factory expense.",
                ]);

                $eligibleOrder->update([
                    'incentive_generation_status' => AgentEligibleOrder::INCENTIVE_GENERATED,
                    'incentive_block_reason'      => null,
                ]);

                AgentIncentiveAuditLog::logAction(
                    'ledger',
                    $entry->id,
                    'ledger_earned_created',
                    null,
                    null,
                    $entry->status,
                    $entry->reason,
                    null,
                    $entry->toArray(),
                    'system'
                );

                return $entry;
            });

            $this->dispatchLedgerStatusNotification($ledgerEntry, 'calculated_pending_approval');

            return $ledgerEntry;
        } catch (QueryException $e) {
            if (str_contains(strtolower($e->getMessage()), 'unique') || str_contains(strtolower($e->getMessage()), 'duplicate')) {
                return AgentIncentiveLedger::where('earned_parent_order_lock', $eligibleOrder->parent_order_id)->first();
            }
            throw $e;
        }
    }

    /**
     * Authorize an Admin/Finance approval transition for a calculated ledger entry.
     */
    public function approveLedgerEntry(AgentIncentiveLedger $entry, User $admin, ?string $notes = null): AgentIncentiveLedger
    {
        if (!$admin->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Only authorized finance/platform administrators may approve incentive entries.'],
            ]);
        }

        if (!config('marketplace_agents.funding_policy_approved', false)) {
            throw ValidationException::withMessages([
                'funding_policy' => ['Ledger approval blocked: Funding policy must be approved before authorizing payable incentives.'],
            ]);
        }

        // Idempotent duplicate approval protection (Stage 2)
        if ($entry->status === AgentIncentiveLedger::STATUS_APPROVED) {
            return $entry;
        }

        if ($entry->entry_type !== AgentIncentiveLedger::TYPE_EARNED) {
            throw ValidationException::withMessages([
                'entry_type' => ['Only primary earned incentive entries can go through incentive approval.'],
            ]);
        }

        if (!in_array($entry->status, [
            AgentIncentiveLedger::STATUS_CALCULATED_PENDING_APPROVAL,
            AgentIncentiveLedger::STATUS_PENDING_REVIEW,
            AgentIncentiveLedger::STATUS_ON_HOLD,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => ["Ledger entry #{$entry->ledger_ref} cannot be approved from '{$entry->status}'."],
            ]);
        }

        // Prevent approval for unverified, unpaid, cancelled, refunded, or ineligible orders (Stage 2 & Test #2)
        $eligibleOrder = $entry->eligibleOrder()->with('parentOrder')->first();
        if (!$eligibleOrder || $eligibleOrder->eligibility_status !== AgentEligibleOrder::STATUS_ELIGIBLE || !$eligibleOrder->parentOrder?->isPaid()) {
            throw ValidationException::withMessages([
                'eligibility_status' => ["Cannot approve incentive #{$entry->ledger_ref}: underlying order is not verified eligible (status: " . ($eligibleOrder->eligibility_status ?? 'missing') . ').'],
            ]);
        }

        // Prevent unapproved tax/withholding configuration (Test #11)
        $taxCode = $entry->tax_treatment_snapshot['tax_treatment_code'] ?? AgentIncentiveRule::TAX_PENDING_DECISION;
        if ($taxCode === AgentIncentiveRule::TAX_PENDING_DECISION ||
            ((float)$entry->tax_deduction_amount > 0 && !config('marketplace_agents.payout_tax_withholding_approved', false))
        ) {
            throw ValidationException::withMessages([
                'tax_treatment' => ['Cannot approve incentive: applicable tax/withholding configuration has not been approved.'],
            ]);
        }

        $updated = DB::transaction(function () use ($entry, $admin, $notes) {
            $locked = AgentIncentiveLedger::where('id', $entry->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === AgentIncentiveLedger::STATUS_APPROVED) {
                return $locked;
            }

            $prevStatus = $locked->status;
            $locked->update([
                'status'              => AgentIncentiveLedger::STATUS_APPROVED,
                'approved_by_user_id' => $admin->id,
                'approved_at'         => now(),
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
                'hold_reason'         => null,
                'admin_notes'         => $notes !== null ? trim(($locked->admin_notes ? $locked->admin_notes . "\n" : '') . $notes) : $locked->admin_notes,
            ]);

            AgentIncentiveAuditLog::logAction(
                'ledger',
                $locked->id,
                'ledger_approved',
                $admin,
                $prevStatus,
                AgentIncentiveLedger::STATUS_APPROVED,
                $notes ?? 'Approved by finance/operations administrator.'
            );

            return $locked->fresh();
        });

        $this->dispatchLedgerStatusNotification($updated, 'approved');

        return $updated;
    }

    /**
     * Place an incentive ledger entry on hold with documented reason (Stage 2).
     */
    public function holdLedgerEntry(AgentIncentiveLedger $entry, User $admin, string $reason, ?string $notes = null): AgentIncentiveLedger
    {
        if (!$admin->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Only authorized finance/platform administrators may place incentives on hold.'],
            ]);
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages(['reason' => ['A documented hold reason is required.']]);
        }

        if (in_array($entry->status, [AgentIncentiveLedger::STATUS_PAID, AgentIncentiveLedger::STATUS_REVERSED, AgentIncentiveLedger::STATUS_REJECTED, AgentIncentiveLedger::STATUS_CANCELLED], true)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot place entry #{$entry->ledger_ref} on hold from terminal status '{$entry->status}'."],
            ]);
        }

        if (AgentPayoutItem::where('agent_incentive_ledger_id', $entry->id)->whereNotNull('active_ledger_lock')->exists()) {
            throw ValidationException::withMessages([
                'payout' => ["Ledger entry #{$entry->ledger_ref} is locked inside an active payout batch. Cancel or fail the payout batch first."],
            ]);
        }

        $updated = DB::transaction(function () use ($entry, $admin, $reason, $notes) {
            $prevStatus = $entry->status;
            $entry->update([
                'status'              => AgentIncentiveLedger::STATUS_ON_HOLD,
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
                'hold_reason'         => $reason,
                'admin_notes'         => $notes !== null ? trim(($entry->admin_notes ? $entry->admin_notes . "\n" : '') . $notes) : $entry->admin_notes,
            ]);

            AgentIncentiveAuditLog::logAction(
                'ledger',
                $entry->id,
                'ledger_held',
                $admin,
                $prevStatus,
                AgentIncentiveLedger::STATUS_ON_HOLD,
                $reason
            );

            return $entry->fresh();
        });

        $this->dispatchLedgerStatusNotification($updated, 'on_hold');

        return $updated;
    }

    /**
     * Reject an incentive ledger entry without mutating its immutable calculation snapshot.
     */
    public function rejectLedgerEntry(AgentIncentiveLedger $entry, User $admin, string $reason, ?string $notes = null): AgentIncentiveLedger
    {
        if (!$admin->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Only authorized finance/platform administrators may reject incentive entries.'],
            ]);
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages(['reason' => ['A documented rejection reason is required.']]);
        }

        if (in_array($entry->status, [AgentIncentiveLedger::STATUS_PAID, AgentIncentiveLedger::STATUS_REVERSED], true)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot reject entry #{$entry->ledger_ref} from status '{$entry->status}'."],
            ]);
        }

        if (AgentPayoutItem::where('agent_incentive_ledger_id', $entry->id)->whereNotNull('active_ledger_lock')->exists()) {
            throw ValidationException::withMessages([
                'payout' => ["Ledger entry #{$entry->ledger_ref} is locked inside an active payout batch."],
            ]);
        }

        $updated = DB::transaction(function () use ($entry, $admin, $reason, $notes) {
            $prevStatus = $entry->status;
            $entry->update([
                'status'              => AgentIncentiveLedger::STATUS_REJECTED,
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
                'rejected_by_user_id' => $admin->id,
                'rejected_at'         => now(),
                'rejection_reason'    => $reason,
                'admin_notes'         => $notes !== null ? trim(($entry->admin_notes ? $entry->admin_notes . "\n" : '') . $notes) : $entry->admin_notes,
            ]);

            AgentIncentiveAuditLog::logAction(
                'ledger',
                $entry->id,
                'ledger_rejected',
                $admin,
                $prevStatus,
                AgentIncentiveLedger::STATUS_REJECTED,
                $reason
            );

            return $entry->fresh();
        });

        $this->dispatchLedgerStatusNotification($updated, 'rejected');

        return $updated;
    }

    /**
     * Reverse an earned ledger entry without altering the original entry's amount/snapshot.
     * Creates an offsetting negative immutable 'reversal' entry referencing the original.
     */
    public function reverseLedgerEntry(
        AgentIncentiveLedger $original,
        ?User $actor,
        string $reason,
        ?string $idempotencyKey = null,
        ?string $notes = null
    ): AgentIncentiveLedger {
        if ($actor && !$actor->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Administrator authorization required to reverse incentive ledger entries.'],
            ]);
        }

        if ($idempotencyKey) {
            $existingIdem = AgentIncentiveLedger::where('idempotency_key', $idempotencyKey)->first();
            if ($existingIdem) {
                return $existingIdem;
            }
        }

        if ($original->entry_type !== AgentIncentiveLedger::TYPE_EARNED) {
            throw ValidationException::withMessages([
                'entry_type' => ['Only primary earned ledger entries can be reversed directly.'],
            ]);
        }

        if ($original->status === AgentIncentiveLedger::STATUS_PAID) {
            throw ValidationException::withMessages([
                'status' => ['Already-paid incentive entries require an approved recovery/clawback policy before reversal (deferred per BRD).'],
            ]);
        }

        return DB::transaction(function () use ($original, $actor, $reason, $idempotencyKey, $notes) {
            $lockedOriginal = AgentIncentiveLedger::where('id', $original->id)->lockForUpdate()->firstOrFail();

            if ($lockedOriginal->status === AgentIncentiveLedger::STATUS_REVERSED) {
                $existingRev = AgentIncentiveLedger::where('reference_ledger_id', $lockedOriginal->id)
                    ->where('entry_type', AgentIncentiveLedger::TYPE_REVERSAL)
                    ->first();
                if ($existingRev) {
                    return $existingRev;
                }
            }

            // Sum remaining net balance across original + any adjustments
            $netBalance = round((float)$lockedOriginal->calculated_incentive_amount + (float)AgentIncentiveLedger::where('reference_ledger_id', $lockedOriginal->id)->sum('calculated_incentive_amount'), 2);
            if ($netBalance <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ["Ledger entry #{$lockedOriginal->ledger_ref} already has zero remaining balance."],
                ]);
            }

            $reversal = AgentIncentiveLedger::create([
                'ledger_ref'                      => 'AIL-REV-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'entry_type'                      => AgentIncentiveLedger::TYPE_REVERSAL,
                'earned_parent_order_lock'        => null,
                'customer_id'                     => $lockedOriginal->customer_id,
                'field_agent_id'                  => $lockedOriginal->field_agent_id,
                'buyer_agent_attribution_id'      => $lockedOriginal->buyer_agent_attribution_id,
                'agent_eligible_order_id'         => $lockedOriginal->agent_eligible_order_id,
                'parent_order_id'                 => $lockedOriginal->parent_order_id,
                'seller_fulfilment_refs'          => $lockedOriginal->seller_fulfilment_refs,
                'eligible_order_sequence'         => $lockedOriginal->eligible_order_sequence,
                'eligibility_event_ref'           => $lockedOriginal->eligibility_event_ref,
                'eligibility_event_at'            => now(),
                'agent_incentive_rule_id'         => $lockedOriginal->agent_incentive_rule_id,
                'rule_code'                       => $lockedOriginal->rule_code,
                'rule_version'                    => $lockedOriginal->rule_version,
                'calculation_method'              => $lockedOriginal->calculation_method,
                'calculation_base'                => $lockedOriginal->calculation_base,
                'calculation_base_amount'         => $lockedOriginal->calculation_base_amount,
                'applied_rate'                    => $lockedOriginal->applied_rate,
                'applied_fixed_amount'            => $lockedOriginal->applied_fixed_amount,
                'max_cap_amount'                  => $lockedOriginal->max_cap_amount,
                'cap_applied'                     => $lockedOriginal->cap_applied,
                'rounding_rule'                   => $lockedOriginal->rounding_rule,
                'tax_treatment_snapshot'          => $lockedOriginal->tax_treatment_snapshot,
                'gross_incentive_amount'          => -1 * abs($netBalance),
                'tax_deduction_amount'            => 0.00,
                'calculated_incentive_amount'     => -1 * abs($netBalance),
                'funding_expense_account'         => AgentIncentiveLedger::EXPENSE_APNI_FACTORY_PLATFORM,
                'funding_policy_approved_snapshot'=> $lockedOriginal->funding_policy_approved_snapshot,
                'status'                          => AgentIncentiveLedger::STATUS_APPROVED,
                'reference_ledger_id'             => $lockedOriginal->id,
                'reversal_or_adjustment_ref'      => 'REV-' . $lockedOriginal->ledger_ref . '-' . strtoupper(Str::random(4)),
                'idempotency_key'                 => $idempotencyKey,
                'reason'                          => $reason,
                'admin_notes'                     => $notes,
                'created_by_user_id'              => $actor?->id,
                'approved_by_user_id'             => $actor?->id,
                'approved_at'                     => now(),
            ]);

            $prevOrigStatus = $lockedOriginal->status;
            $lockedOriginal->update([
                'status' => AgentIncentiveLedger::STATUS_REVERSED,
            ]);

            // Stage 7: If included in an unreleased payout batch, flag reconciliation exception & release lock
            $activeItems = AgentPayoutItem::where('agent_incentive_ledger_id', $lockedOriginal->id)
                ->whereNotNull('active_ledger_lock')
                ->with('payout')
                ->get();

            foreach ($activeItems as $pItem) {
                $payout = $pItem->payout;
                if ($payout && $payout->status !== AgentPayout::STATUS_PAID_CONFIRMED) {
                    $pItem->update(['active_ledger_lock' => null]);
                    $payout->update([
                        'status'                => AgentPayout::STATUS_RECONCILIATION_EXCEPTION,
                        'reconciliation_status' => AgentPayout::RECON_MISMATCH,
                        'reconciliation_notes'  => "Included incentive #{$lockedOriginal->ledger_ref} was reversed: {$reason}",
                    ]);
                    AgentPayoutReconciliationException::create([
                        'exception_ref'             => 'EX-PREREV-' . strtoupper(Str::random(6)),
                        'agent_payout_id'           => $payout->id,
                        'agent_incentive_ledger_id' => $lockedOriginal->id,
                        'field_agent_id'            => $lockedOriginal->field_agent_id,
                        'exception_type'            => AgentPayoutReconciliationException::TYPE_PRE_PAYOUT_REVERSAL,
                        'expected_amount'           => $lockedOriginal->calculated_incentive_amount,
                        'reported_amount'           => 0.00,
                        'status'                    => AgentPayoutReconciliationException::STATUS_OPEN,
                        'description'               => "Incentive #{$lockedOriginal->ledger_ref} reversed before payout disbursement ({$reason}). Review batch #{$payout->payout_ref}.",
                        'created_by_user_id'        => $actor?->id,
                    ]);
                }
            }

            AgentIncentiveAuditLog::logAction(
                'ledger',
                $reversal->id,
                'ledger_reversed',
                $actor,
                $prevOrigStatus,
                AgentIncentiveLedger::STATUS_REVERSED,
                $reason,
                ['original_ledger_id' => $lockedOriginal->id, 'net_reversed' => $netBalance],
                $reversal->toArray(),
                $actor ? 'admin' : 'system'
            );

            return $reversal->fresh();
        });
    }

    /**
     * Create an auditable manual adjustment entry referencing an existing earned ledger entry.
     * Prevents creating a negative net ledger balance without an approved recovery policy.
     */
    public function adjustLedgerEntry(
        AgentIncentiveLedger $original,
        float $adjustmentAmount,
        User $admin,
        string $reason,
        ?string $idempotencyKey = null,
        ?string $notes = null
    ): AgentIncentiveLedger {
        if (!$admin->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Only authorized administrators can post incentive adjustments.'],
            ]);
        }

        if ($idempotencyKey) {
            $existingIdem = AgentIncentiveLedger::where('idempotency_key', $idempotencyKey)->first();
            if ($existingIdem) {
                return $existingIdem;
            }
        }

        $roundedAdj = round($adjustmentAmount, 2);
        if (abs($roundedAdj) < 0.01) {
            throw ValidationException::withMessages([
                'adjustment_amount' => ['Adjustment amount must be non-zero.'],
            ]);
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => ['A documented reason is required for manual ledger adjustments.'],
            ]);
        }

        return DB::transaction(function () use ($original, $roundedAdj, $admin, $reason, $idempotencyKey, $notes) {
            $lockedOriginal = AgentIncentiveLedger::where('id', $original->id)->lockForUpdate()->firstOrFail();

            $currentNet = round((float)$lockedOriginal->calculated_incentive_amount + (float)AgentIncentiveLedger::where('reference_ledger_id', $lockedOriginal->id)->sum('calculated_incentive_amount'), 2);
            if (($currentNet + $roundedAdj) < -0.009) {
                throw ValidationException::withMessages([
                    'adjustment_amount' => ["Adjustment rejected: Reducing entry #{$lockedOriginal->ledger_ref} (remaining ₹{$currentNet}) below zero requires an approved negative-balance recovery policy."],
                ]);
            }

            $adj = AgentIncentiveLedger::create([
                'ledger_ref'                      => 'AIL-ADJ-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'entry_type'                      => AgentIncentiveLedger::TYPE_ADJUSTMENT,
                'earned_parent_order_lock'        => null,
                'customer_id'                     => $lockedOriginal->customer_id,
                'field_agent_id'                  => $lockedOriginal->field_agent_id,
                'buyer_agent_attribution_id'      => $lockedOriginal->buyer_agent_attribution_id,
                'agent_eligible_order_id'         => $lockedOriginal->agent_eligible_order_id,
                'parent_order_id'                 => $lockedOriginal->parent_order_id,
                'seller_fulfilment_refs'          => $lockedOriginal->seller_fulfilment_refs,
                'eligible_order_sequence'         => $lockedOriginal->eligible_order_sequence,
                'eligibility_event_ref'           => $lockedOriginal->eligibility_event_ref,
                'eligibility_event_at'            => now(),
                'agent_incentive_rule_id'         => $lockedOriginal->agent_incentive_rule_id,
                'rule_code'                       => $lockedOriginal->rule_code,
                'rule_version'                    => $lockedOriginal->rule_version,
                'calculation_method'              => $lockedOriginal->calculation_method,
                'calculation_base'                => $lockedOriginal->calculation_base,
                'calculation_base_amount'         => $lockedOriginal->calculation_base_amount,
                'applied_rate'                    => $lockedOriginal->applied_rate,
                'applied_fixed_amount'            => $lockedOriginal->applied_fixed_amount,
                'max_cap_amount'                  => $lockedOriginal->max_cap_amount,
                'cap_applied'                     => $lockedOriginal->cap_applied,
                'rounding_rule'                   => $lockedOriginal->rounding_rule,
                'tax_treatment_snapshot'          => $lockedOriginal->tax_treatment_snapshot,
                'gross_incentive_amount'          => $roundedAdj,
                'tax_deduction_amount'            => 0.00,
                'calculated_incentive_amount'     => $roundedAdj,
                'funding_expense_account'         => AgentIncentiveLedger::EXPENSE_APNI_FACTORY_PLATFORM,
                'funding_policy_approved_snapshot'=> $lockedOriginal->funding_policy_approved_snapshot,
                'status'                          => AgentIncentiveLedger::STATUS_APPROVED,
                'reference_ledger_id'             => $lockedOriginal->id,
                'reversal_or_adjustment_ref'      => 'ADJ-' . $lockedOriginal->ledger_ref . '-' . strtoupper(Str::random(4)),
                'idempotency_key'                 => $idempotencyKey,
                'reason'                          => $reason,
                'admin_notes'                     => $notes,
                'created_by_user_id'              => $admin->id,
                'approved_by_user_id'             => $admin->id,
                'approved_at'                     => now(),
            ]);

            if ($roundedAdj < 0 && ($currentNet + $roundedAdj) == 0.0) {
                $lockedOriginal->update(['status' => AgentIncentiveLedger::STATUS_REVERSED]);
            } elseif ($roundedAdj < 0) {
                $lockedOriginal->update(['status' => AgentIncentiveLedger::STATUS_PARTIALLY_REVERSED]);
            }

            AgentIncentiveAuditLog::logAction(
                'ledger',
                $adj->id,
                'ledger_adjusted',
                $admin,
                $lockedOriginal->status,
                $adj->status,
                $reason,
                ['original_ledger_id' => $lockedOriginal->id, 'previous_net' => $currentNet],
                $adj->toArray()
            );

            return $adj->fresh();
        });
    }

    /**
     * Automatically reverse or cancel open earned entry when a qualifying order is cancelled/refunded.
     */
    public function handleEligibilityReversal(AgentEligibleOrder $eligibleOrder, string $reason): void
    {
        $earned = AgentIncentiveLedger::where('agent_eligible_order_id', $eligibleOrder->id)
            ->where('entry_type', AgentIncentiveLedger::TYPE_EARNED)
            ->first();

        if (!$earned || in_array($earned->status, [AgentIncentiveLedger::STATUS_REVERSED, AgentIncentiveLedger::STATUS_CANCELLED, AgentIncentiveLedger::STATUS_REJECTED], true)) {
            return;
        }

        // Stage 7: Post-payout refund/cancellation -> do NOT invent clawback/offset rules; open finance review exception!
        if ($earned->status === AgentIncentiveLedger::STATUS_PAID) {
            $alreadyFlagged = AgentPayoutReconciliationException::where('agent_incentive_ledger_id', $earned->id)
                ->where('exception_type', AgentPayoutReconciliationException::TYPE_POST_PAYOUT_REVERSAL)
                ->whereIn('status', [AgentPayoutReconciliationException::STATUS_OPEN, AgentPayoutReconciliationException::STATUS_UNDER_REVIEW])
                ->exists();

            if (!$alreadyFlagged) {
                AgentPayoutReconciliationException::create([
                    'exception_ref'             => 'EX-POSTREV-' . strtoupper(Str::random(6)),
                    'agent_payout_id'           => $earned->agent_payout_id,
                    'agent_incentive_ledger_id' => $earned->id,
                    'field_agent_id'            => $earned->field_agent_id,
                    'exception_type'            => AgentPayoutReconciliationException::TYPE_POST_PAYOUT_REVERSAL,
                    'expected_amount'           => $earned->calculated_incentive_amount,
                    'reported_amount'           => 0.00,
                    'status'                    => AgentPayoutReconciliationException::STATUS_OPEN,
                    'description'               => "Post-payment reversal/refund event received for paid ledger #{$earned->ledger_ref} ({$reason}). Held for manual finance policy decision; no automatic recovery performed.",
                ]);
            }
            return;
        }

        $this->reverseLedgerEntry(
            $earned,
            null,
            "Automated event reversal: {$reason}",
            "auto_rev_{$earned->id}"
        );
    }

    /**
     * Hold open earned entry in pending_review when partial refund or dispute arises after eligibility.
     */
    public function flagLedgerPendingReview(AgentEligibleOrder $eligibleOrder, string $reason): void
    {
        $earned = AgentIncentiveLedger::where('agent_eligible_order_id', $eligibleOrder->id)
            ->where('entry_type', AgentIncentiveLedger::TYPE_EARNED)
            ->where('status', AgentIncentiveLedger::STATUS_CALCULATED_PENDING_APPROVAL)
            ->first();

        if ($earned) {
            $earned->update([
                'status' => AgentIncentiveLedger::STATUS_PENDING_REVIEW,
                'reason' => trim(($earned->reason ? $earned->reason . ' | ' : '') . $reason),
            ]);
        }
    }

    /**
     * Paginated admin/finance ledger listing with filtering.
     */
    public function listLedgerEntries(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->applyLedgerFilters(
            AgentIncentiveLedger::with([
                'customer:id,name,mobile,location',
                'agent:id,agent_code,name,territory',
                'parentOrder:id,orderno,grandtotal,netamount',
                'rule:id,rule_code,version,name',
                'referenceLedger:id,ledger_ref',
            ]),
            $filters
        )->orderByDesc('id');

        return $query->paginate(min(max(1, $perPage), 100));
    }

    /**
     * Reports strictly reconciled from immutable agent_incentive_ledgers entries ONLY (Stage 8).
     */
    public function getLedgerReconciledReport(array $filters = []): array
    {
        $entries = $this->applyLedgerFilters(
            AgentIncentiveLedger::with([
                'agent:id,agent_code,name,territory',
                'customer:id,name,location',
            ]),
            $filters
        )->get();

        $earnedEntries = $entries->where('entry_type', AgentIncentiveLedger::TYPE_EARNED);
        $reversalEntries = $entries->where('entry_type', AgentIncentiveLedger::TYPE_REVERSAL);
        $adjustmentEntries = $entries->where('entry_type', AgentIncentiveLedger::TYPE_ADJUSTMENT);

        $earnedTotal = round((float)$earnedEntries->sum('calculated_incentive_amount'), 2);
        $pendingApprovalTotal = round((float)$earnedEntries->whereIn('status', [
            AgentIncentiveLedger::STATUS_CALCULATED_PENDING_APPROVAL,
            AgentIncentiveLedger::STATUS_PENDING_REVIEW,
            AgentIncentiveLedger::STATUS_ON_HOLD,
        ])->sum('calculated_incentive_amount'), 2);
        $approvedGross = round((float)$earnedEntries->where('status', AgentIncentiveLedger::STATUS_APPROVED)->sum('calculated_incentive_amount'), 2);
        $paidTotal = round((float)$earnedEntries->where('status', AgentIncentiveLedger::STATUS_PAID)->sum('calculated_incentive_amount'), 2);
        $reversalsTotal = round((float)$reversalEntries->sum('calculated_incentive_amount'), 2); // negative
        $adjustmentsTotal = round((float)$adjustmentEntries->sum('calculated_incentive_amount'), 2);
        $netLedgerTotal = round((float)$entries->sum('calculated_incentive_amount'), 2);

        $groupBy = $filters['group_by'] ?? 'agent';
        $grouped = [];

        foreach ($entries as $entry) {
            [$groupKey, $groupLabel] = match ($groupBy) {
                'buyer' => [
                    'buyer_' . $entry->customer_id,
                    ($entry->customer?->name ?? "Buyer #{$entry->customer_id}"),
                ],
                'territory' => [
                    'terr_' . ($entry->agent?->territory ?? 'Unassigned'),
                    ($entry->agent?->territory ?? 'Unassigned Territory'),
                ],
                'sequence' => [
                    'seq_' . $entry->eligible_order_sequence,
                    'Eligible Order #' . $entry->eligible_order_sequence . '/3',
                ],
                'period' => [
                    $entry->created_at?->format('Y-m') ?? 'unknown',
                    $entry->created_at?->format('M Y') ?? 'Unknown Period',
                ],
                default => [
                    'agent_' . $entry->field_agent_id,
                    ($entry->agent ? "{$entry->agent->agent_code} — {$entry->agent->name}" : "Agent #{$entry->field_agent_id}"),
                ],
            };

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'group_key'          => $groupKey,
                    'group_label'        => $groupLabel,
                    'eligible_orders_count' => 0,
                    'earned_amount'      => 0.00,
                    'pending_amount'     => 0.00,
                    'approved_amount'    => 0.00,
                    'paid_amount'        => 0.00,
                    'reversals_amount'   => 0.00,
                    'adjustments_amount' => 0.00,
                    'net_ledger_amount'  => 0.00,
                ];
            }

            if ($entry->entry_type === AgentIncentiveLedger::TYPE_EARNED) {
                $grouped[$groupKey]['eligible_orders_count']++;
                $grouped[$groupKey]['earned_amount'] = round($grouped[$groupKey]['earned_amount'] + (float)$entry->calculated_incentive_amount, 2);

                if (in_array($entry->status, [AgentIncentiveLedger::STATUS_CALCULATED_PENDING_APPROVAL, AgentIncentiveLedger::STATUS_PENDING_REVIEW, AgentIncentiveLedger::STATUS_ON_HOLD], true)) {
                    $grouped[$groupKey]['pending_amount'] = round($grouped[$groupKey]['pending_amount'] + (float)$entry->calculated_incentive_amount, 2);
                } elseif ($entry->status === AgentIncentiveLedger::STATUS_APPROVED) {
                    $grouped[$groupKey]['approved_amount'] = round($grouped[$groupKey]['approved_amount'] + (float)$entry->calculated_incentive_amount, 2);
                } elseif ($entry->status === AgentIncentiveLedger::STATUS_PAID) {
                    $grouped[$groupKey]['paid_amount'] = round($grouped[$groupKey]['paid_amount'] + (float)$entry->calculated_incentive_amount, 2);
                }
            } elseif ($entry->entry_type === AgentIncentiveLedger::TYPE_REVERSAL) {
                $grouped[$groupKey]['reversals_amount'] = round($grouped[$groupKey]['reversals_amount'] + (float)$entry->calculated_incentive_amount, 2);
            } elseif ($entry->entry_type === AgentIncentiveLedger::TYPE_ADJUSTMENT) {
                $grouped[$groupKey]['adjustments_amount'] = round($grouped[$groupKey]['adjustments_amount'] + (float)$entry->calculated_incentive_amount, 2);
            }

            $grouped[$groupKey]['net_ledger_amount'] = round($grouped[$groupKey]['net_ledger_amount'] + (float)$entry->calculated_incentive_amount, 2);
        }

        // Eligible orders summary metrics across same filters (non-financial counts)
        $eligQuery = AgentEligibleOrder::query();
        if (!empty($filters['field_agent_id'])) {
            $eligQuery->where('field_agent_id', (int)$filters['field_agent_id']);
        }
        if (!empty($filters['customer_id'])) {
            $eligQuery->where('customer_id', (int)$filters['customer_id']);
        }
        if (!empty($filters['territory'])) {
            $terr = trim((string)$filters['territory']);
            $eligQuery->whereHas('agent', fn($a) => $a->where('territory', 'like', "%{$terr}%"));
        }
        if (!empty($filters['date_from'])) {
            $eligQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $eligQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        return [
            'summary' => [
                'funding_policy_approved' => (bool)config('marketplace_agents.funding_policy_approved', false),
                'funding_expense_account' => AgentIncentiveLedger::EXPENSE_APNI_FACTORY_PLATFORM,
                'payout_execution_enabled'=> (bool)config('marketplace_agents.payout_execution_enabled', false),
                'total_eligible_orders'   => (clone $eligQuery)->where('eligibility_status', AgentEligibleOrder::STATUS_ELIGIBLE)->count(),
                'total_pending_orders'    => (clone $eligQuery)->whereIn('eligibility_status', [AgentEligibleOrder::STATUS_PENDING, AgentEligibleOrder::STATUS_PENDING_REVIEW])->count(),
                'ledger_entries_count'    => $entries->count(),
                'earned_total'            => $earnedTotal,
                'pending_approval_total'  => $pendingApprovalTotal,
                'approved_total'          => $approvedGross,
                'paid_total'              => $paidTotal,
                'reversals_total'         => $reversalsTotal,
                'adjustments_total'       => $adjustmentsTotal,
                'net_incentive_liability' => $netLedgerTotal,
                'reconciled_checksum_diff'=> round($netLedgerTotal - ($earnedTotal + $reversalsTotal + $adjustmentsTotal), 2),
            ],
            'group_by' => $groupBy,
            'rows'     => array_values($grouped),
        ];
    }

    /**
     * Scoped agent summary respecting Stage 7 approval visibility policy.
     */
    public function getAgentIncentiveSummary(int $fieldAgentId): array
    {
        $fundingApproved = (bool)config('marketplace_agents.funding_policy_approved', false);
        $exposeAmounts = (bool)config('marketplace_agents.expose_agent_incentive_amounts', false);

        $eligibleCount = AgentEligibleOrder::where('field_agent_id', $fieldAgentId)
            ->where('eligibility_status', AgentEligibleOrder::STATUS_ELIGIBLE)
            ->count();
        $pendingCount = AgentEligibleOrder::where('field_agent_id', $fieldAgentId)
            ->whereIn('eligibility_status', [AgentEligibleOrder::STATUS_PENDING, AgentEligibleOrder::STATUS_PENDING_REVIEW])
            ->count();

        if (!$fundingApproved || !$exposeAmounts) {
            return [
                'financials_disclosed'    => false,
                'disclosure_notice'       => 'Financial incentive amounts remain withheld pending written commercial & funding policy approval.',
                'eligible_orders_count'   => $eligibleCount,
                'pending_orders_count'    => $pendingCount,
                'earned_total'            => null,
                'pending_approval_total'  => null,
                'approved_total'          => null,
                'paid_total'              => null,
            ];
        }

        $entries = AgentIncentiveLedger::where('field_agent_id', $fieldAgentId)->get();
        $earned = $entries->where('entry_type', AgentIncentiveLedger::TYPE_EARNED);

        return [
            'financials_disclosed'   => true,
            'disclosure_notice'      => 'Apni Factory-funded scheme ledger summary (no seller deductions).',
            'eligible_orders_count'  => $eligibleCount,
            'pending_orders_count'   => $pendingCount,
            'earned_total'           => round((float)$earned->sum('calculated_incentive_amount'), 2),
            'pending_approval_total' => round((float)$earned->whereIn('status', [AgentIncentiveLedger::STATUS_CALCULATED_PENDING_APPROVAL, AgentIncentiveLedger::STATUS_PENDING_REVIEW, AgentIncentiveLedger::STATUS_ON_HOLD])->sum('calculated_incentive_amount'), 2),
            'approved_total'         => round((float)$earned->where('status', AgentIncentiveLedger::STATUS_APPROVED)->sum('calculated_incentive_amount'), 2),
            'paid_total'             => round((float)$earned->where('status', AgentIncentiveLedger::STATUS_PAID)->sum('calculated_incentive_amount'), 2),
            'reversals_adjustments'  => round((float)$entries->whereIn('entry_type', [AgentIncentiveLedger::TYPE_REVERSAL, AgentIncentiveLedger::TYPE_ADJUSTMENT])->sum('calculated_incentive_amount'), 2),
        ];
    }

    protected function applyLedgerFilters($query, array $filters)
    {
        if (!empty($filters['field_agent_id'])) {
            $query->where('field_agent_id', (int)$filters['field_agent_id']);
        }
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', (int)$filters['customer_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['entry_type'])) {
            $query->where('entry_type', $filters['entry_type']);
        }
        if (!empty($filters['sequence'])) {
            $query->where('eligible_order_sequence', (int)$filters['sequence']);
        }
        if (!empty($filters['territory'])) {
            $terr = trim((string)$filters['territory']);
            $query->whereHas('agent', fn($a) => $a->where('territory', 'like', "%{$terr}%"));
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
                $q->where('ledger_ref', 'like', "%{$term}%")
                  ->orWhere('rule_code', 'like', "%{$term}%")
                  ->orWhereHas('parentOrder', fn($o) => $o->where('orderno', 'like', "%{$term}%"));
            });
        }

        return $query;
    }

    protected function dispatchLedgerStatusNotification(AgentIncentiveLedger $entry, string $event): void
    {
        try {
            if (!config('marketplace_agents.funding_policy_approved', false)) {
                return;
            }

            $entry->loadMissing(['agent', 'customer', 'parentOrder']);
            $agent = $entry->agent;
            if (!$agent || !$agent->user_id) {
                return;
            }

            // Do not send unapproved financial payout promises (Stage 11)
            if ($event === 'approved') {
                SendAgentIncentiveNotificationJob::dispatch(
                    (int)$agent->user_id,
                    'agent',
                    "Incentive Approved: #{$entry->ledger_ref}",
                    "Incentive calculation #{$entry->ledger_ref} for eligible order #{$entry->eligible_order_sequence}/3 ({$entry->parentOrder?->orderno}) has been approved under rule {$entry->rule_code} v{$entry->rule_version}. Disbursement remains subject to payout batch readiness and finance release.",
                    [
                        'Ledger Ref' => $entry->ledger_ref,
                        'Order'      => $entry->parentOrder?->orderno ?? (string)$entry->parent_order_id,
                        'Slot'       => "{$entry->eligible_order_sequence}/3",
                        'Rule'       => "{$entry->rule_code} v{$entry->rule_version}",
                    ],
                    route('agent.incentives.index'),
                    "ledger_approved_{$entry->id}"
                );
            } elseif (in_array($event, ['on_hold', 'rejected'], true)) {
                $label = $event === 'on_hold' ? 'Placed on Hold' : 'Declined / Rejected';
                $reasonText = $event === 'on_hold' ? ($entry->hold_reason ?? 'Under finance review') : ($entry->rejection_reason ?? 'Rejected per policy');
                SendAgentIncentiveNotificationJob::dispatch(
                    (int)$agent->user_id,
                    'agent',
                    "Incentive {$label}: #{$entry->ledger_ref}",
                    "Incentive entry #{$entry->ledger_ref} for order #{$entry->parentOrder?->orderno} has been {$label}. Reason: {$reasonText}",
                    [
                        'Ledger Ref' => $entry->ledger_ref,
                        'Status'     => strtoupper($event),
                        'Reason'     => $reasonText,
                    ],
                    route('agent.incentives.index'),
                    "ledger_{$event}_{$entry->id}"
                );
            }
        } catch (\Throwable $e) {
            // Notification failures must never roll back ledger mutations
        }
    }
}

