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
use App\Models\FieldAgent;
use App\Models\FulfilmentClaim;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgentPayoutService
{
    public const VERIFIED_CONFIRMATION_SOURCES = [
        'verified_bank_statement',
        'approved_provider_callback',
        'verified_utr_reconciliation',
    ];

    protected function ensureAdmin(User $actor): void
    {
        if (!$actor->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Only authorized finance/platform administrators may perform agent payout actions.'],
            ]);
        }
    }

    /**
     * Evaluate comprehensive Stage 3 payout readiness checks for a single incentive ledger entry.
     * Never invents KYC thresholds, hold periods, or tax withholding rules.
     */
    public function checkLedgerEntryReadiness(AgentIncentiveLedger $entry, ?float $requestedWithholdingRate = null): array
    {
        $entry->loadMissing(['agent', 'attribution', 'eligibleOrder.parentOrder']);

        $reasons = [];
        $checks = [];

        // 1. Commercial Funding Policy Approved (BRD Section 12/21 vs Seller-Funded contradiction guard)
        $fundingApproved = (bool)config('marketplace_agents.funding_policy_approved', false)
            && config('marketplace_agents.incentive_funding_model') === AgentIncentiveRule::FUNDING_APNI_FACTORY;
        $checks['funding_policy_approved'] = $fundingApproved;
        if (!$fundingApproved) {
            $reasons[] = 'Commercial funding policy approval is pending written executive confirmation.';
        }

        // 2. Entry Type & Approved Status
        $isEarned = $entry->entry_type === AgentIncentiveLedger::TYPE_EARNED;
        $isApprovedStatus = $entry->status === AgentIncentiveLedger::STATUS_APPROVED;
        $checks['incentive_approved'] = $isEarned && $isApprovedStatus;
        if (!$isEarned) {
            $reasons[] = 'Only primary earned incentive entries are eligible for payout batching.';
        } elseif (!$isApprovedStatus) {
            $reasons[] = "Incentive status must be 'approved' (currently '{$entry->status}').";
        }

        // 3. Agent Exists & Active
        $agent = $entry->agent;
        $agentActive = $agent && $agent->isActive();
        $checks['agent_eligible'] = $agentActive;
        if (!$agentActive) {
            $reasons[] = 'Field sales agent account does not exist or is not active.';
        }

        // 4. Buyer Attribution Valid
        $attr = $entry->attribution;
        $attrValid = $attr && $attr->status === 'active' && $attr->approval_status === 'approved';
        $checks['attribution_valid'] = $attrValid;
        if (!$attrValid) {
            $reasons[] = 'Buyer-to-agent attribution record is not active and approved.';
        }

        // 5. Underlying Eligible Order Verified & Not Refunded/Cancelled
        $eligOrder = $entry->eligibleOrder;
        $orderValid = $eligOrder
            && $eligOrder->eligibility_status === AgentEligibleOrder::STATUS_ELIGIBLE
            && $eligOrder->parentOrder?->isPaid();
        $checks['order_eligible'] = $orderValid;
        if (!$orderValid) {
            $reasons[] = 'Underlying parent order is not in verified eligible state.';
        }

        // 6. Configurable KYC & Contract Requirements (only when explicitly mandated by company policy)
        $requireKyc = (bool)config('marketplace_agents.payout_require_kyc_verified', false);
        $kycOk = !$requireKyc || ($agent && $agent->kyc_status === FieldAgent::KYC_VERIFIED);
        $checks['kyc_check'] = $kycOk;
        if (!$kycOk) {
            $reasons[] = "Agent KYC status must be 'verified' per company policy (current: " . ($agent->kyc_status ?? 'none') . ').';
        }

        $requireContract = (bool)config('marketplace_agents.payout_require_active_contract', false);
        $contractOk = !$requireContract || ($agent && $agent->contract_status === 'active');
        $checks['contract_check'] = $contractOk;
        if (!$contractOk) {
            $reasons[] = "Agent contract status must be 'active' per policy (current: " . ($agent->contract_status ?? 'none') . ').';
        }

        // 7. Approved Payout Details Configured on Agent Profile
        $payoutDetailsOk = $agent && ($agent->maskedPayoutSummary()['configured'] ?? false) === true;
        $checks['payout_details_configured'] = $payoutDetailsOk;
        if (!$payoutDetailsOk) {
            $reasons[] = 'Verified bank/UPI payout details are missing from agent profile.';
        }

        // 8. Applicable Configurable Hold Period (if approved/configured)
        $holdDays = config('marketplace_agents.payout_hold_days');
        $holdSatisfied = true;
        if ($holdDays !== null && (int)$holdDays > 0) {
            $anchor = $entry->approved_at ?? $entry->created_at;
            if ($anchor && now()->lt($anchor->copy()->addDays((int)$holdDays))) {
                $holdSatisfied = false;
                $reasons[] = "Configured {$holdDays}-day payout hold period has not elapsed yet.";
            }
        }
        $checks['hold_period_satisfied'] = $holdSatisfied;

        // 9. Not Already Paid or Locked Inside Another Active Batch
        $alreadyLocked = AgentPayoutItem::where('agent_incentive_ledger_id', $entry->id)
            ->whereNotNull('active_ledger_lock')
            ->exists();
        $notDoubled = empty($entry->payout_reference) && $entry->status !== AgentIncentiveLedger::STATUS_PAID && !$alreadyLocked;
        $checks['not_already_paid_or_batched'] = $notDoubled;
        if (!$notDoubled) {
            $reasons[] = 'Incentive entry is already paid or locked inside an existing active payout batch.';
        }

        // 10. Tax / Withholding Configuration Approved
        $taxCode = $entry->tax_treatment_snapshot['tax_treatment_code'] ?? AgentIncentiveRule::TAX_PENDING_DECISION;
        $withholdingApproved = (bool)config('marketplace_agents.payout_tax_withholding_approved', false);
        $appliesWithholding = ((float)$entry->tax_deduction_amount > 0)
            || ($requestedWithholdingRate !== null && $requestedWithholdingRate > 0);

        $taxReady = ($taxCode !== AgentIncentiveRule::TAX_PENDING_DECISION) && (!$appliesWithholding || $withholdingApproved);
        $checks['tax_withholding_approved'] = $taxReady;
        if (!$taxReady) {
            $reasons[] = $taxCode === AgentIncentiveRule::TAX_PENDING_DECISION
                ? 'Applicable tax/withholding configuration is pending finance approval.'
                : 'Unapproved withholding/TDS tax rate cannot be applied.';
        }

        // 11. No Unresolved Claims or Reconciliation Holds
        $openClaims = $eligOrder ? FulfilmentClaim::where('parent_order_id', $eligOrder->parent_order_id)
            ->whereIn('claim_status', [FulfilmentClaim::STATUS_SUBMITTED, FulfilmentClaim::STATUS_SELLER_RESPONDED, FulfilmentClaim::STATUS_UNDER_REVIEW])
            ->exists() : false;
        $openExceptions = AgentPayoutReconciliationException::where('agent_incentive_ledger_id', $entry->id)
            ->whereIn('status', [AgentPayoutReconciliationException::STATUS_OPEN, AgentPayoutReconciliationException::STATUS_UNDER_REVIEW])
            ->exists();
        $noHolds = !$openClaims && !$openExceptions;
        $checks['no_unresolved_holds'] = $noHolds;
        if (!$noHolds) {
            $reasons[] = 'Order has an open dispute claim or unresolved reconciliation exception.';
        }

        // Compute net amount factoring approved adjustments
        $adjustmentsSum = round((float)AgentIncentiveLedger::where('reference_ledger_id', $entry->id)
            ->where('entry_type', AgentIncentiveLedger::TYPE_ADJUSTMENT)
            ->where('status', AgentIncentiveLedger::STATUS_APPROVED)
            ->sum('calculated_incentive_amount'), 2);
        $grossAmount = round((float)$entry->calculated_incentive_amount, 2);
        $netAmount = round($grossAmount + $adjustmentsSum, 2);

        if ($netAmount <= 0) {
            $reasons[] = "Net payable amount for entry #{$entry->ledger_ref} is zero or negative (₹{$netAmount}).";
        }

        return [
            'ready'             => empty($reasons),
            'reasons'           => $reasons,
            'checks'            => $checks,
            'ledger_ref'        => $entry->ledger_ref,
            'gross_amount'      => $grossAmount,
            'adjustment_amount' => $adjustmentsSum,
            'net_payable'       => $netAmount,
        ];
    }

    /**
     * Create an auditable Payout Batch / Record from one or more approved, payout-ready ledger entries.
     * Prevents double-payment via pessimistic locking and DB unique `active_ledger_lock`.
     */
    public function createPayoutBatch(int $fieldAgentId, array $ledgerIds, User $admin, array $data = []): AgentPayout
    {
        $this->ensureAdmin($admin);

        // 1. API Idempotency Protection
        $idempotencyKey = !empty($data['idempotency_key']) ? trim((string)$data['idempotency_key']) : null;
        if ($idempotencyKey) {
            $existing = AgentPayout::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing->load(['items.ledgerEntry', 'agent']);
            }
        }

        $uniqueIds = array_values(array_unique(array_map('intval', $ledgerIds)));
        if (empty($uniqueIds)) {
            throw ValidationException::withMessages(['ledger_ids' => ['Select at least one approved incentive ledger entry.']]);
        }

        $agent = FieldAgent::findOrFail($fieldAgentId);

        // Enforce tax/withholding policy BEFORE opening batch transaction (Test #11)
        $withholdingRate = isset($data['withholding_tax_rate']) && $data['withholding_tax_rate'] !== ''
            ? round((float)$data['withholding_tax_rate'], 4)
            : null;
        if ($withholdingRate !== null && $withholdingRate < 0) {
            throw ValidationException::withMessages(['withholding_tax_rate' => ['Withholding tax rate cannot be negative.']]);
        }
        if ($withholdingRate !== null && $withholdingRate > 0 && !config('marketplace_agents.payout_tax_withholding_approved', false)) {
            throw ValidationException::withMessages([
                'withholding_tax_rate' => ['Unapproved tax/withholding rule: payout_tax_withholding_approved must be ratified in writing before deducting TDS/withholding.'],
            ]);
        }

        try {
            return DB::transaction(function () use ($agent, $uniqueIds, $admin, $data, $idempotencyKey, $withholdingRate) {
                $ledgers = AgentIncentiveLedger::whereIn('id', $uniqueIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($ledgers->count() !== count($uniqueIds)) {
                    throw ValidationException::withMessages(['ledger_ids' => ['One or more specified incentive ledger entries do not exist.']]);
                }

                $grossSum = 0.00;
                $adjSum = 0.00;
                $itemPayloads = [];

                foreach ($ledgers as $ledger) {
                    if ((int)$ledger->field_agent_id !== (int)$agent->id) {
                        throw ValidationException::withMessages([
                            'ledger_ids' => ["Ledger entry #{$ledger->ledger_ref} belongs to a different field agent."],
                        ]);
                    }

                    $readiness = $this->checkLedgerEntryReadiness($ledger, $withholdingRate);
                    if (!$readiness['ready']) {
                        throw ValidationException::withMessages([
                            'readiness' => ["Ledger #{$ledger->ledger_ref} is not payout-ready: " . implode(' ', $readiness['reasons'])],
                        ]);
                    }

                    $grossSum = round($grossSum + $readiness['gross_amount'], 2);
                    $adjSum = round($adjSum + $readiness['adjustment_amount'], 2);

                    $itemPayloads[] = [
                        'ledger'                  => $ledger,
                        'ledger_gross_amount'     => $readiness['gross_amount'],
                        'adjustment_amount'       => $readiness['adjustment_amount'],
                        'item_net_payable_amount' => $readiness['net_payable'],
                    ];
                }

                $preTaxNet = round($grossSum + $adjSum, 2);
                $withholdingAmount = ($withholdingRate !== null && $withholdingRate > 0)
                    ? round($preTaxNet * ($withholdingRate / 100.0), 2)
                    : 0.00;
                $netPayable = round(max(0.00, $preTaxNet - $withholdingAmount), 2);

                // Optional minimum threshold check ONLY when configured by approved policy
                $minThreshold = config('marketplace_agents.payout_minimum_threshold');
                if ($minThreshold !== null && $netPayable < (float)$minThreshold) {
                    throw ValidationException::withMessages([
                        'minimum_threshold' => ["Net payout ₹{$netPayable} is below approved minimum threshold ₹{$minThreshold}."],
                    ]);
                }

                // Stage 5: Safe non-executable state when execution/provider is not approved
                $executionEnabled = (bool)config('marketplace_agents.payout_execution_enabled', false);
                $executionMode = (string)config('marketplace_agents.payout_execution_mode', 'awaiting_policy');

                $initialStatus = ($executionEnabled && $executionMode !== 'awaiting_policy')
                    ? AgentPayout::STATUS_PENDING_AUTHORIZATION
                    : AgentPayout::STATUS_DRAFT_AWAITING_POLICY;

                $requestedMethod = $data['payment_method'] ?? null;
                $paymentMethod = ($executionEnabled && $executionMode !== 'awaiting_policy' && in_array($requestedMethod, [AgentPayout::METHOD_MANUAL_BANK, AgentPayout::METHOD_MANUAL_UPI, AgentPayout::METHOD_APPROVED_BANK_API], true))
                    ? $requestedMethod
                    : AgentPayout::METHOD_AWAITING_POLICY;

                $payout = AgentPayout::create([
                    'payout_ref'                  => 'PAYOUT-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                    'field_agent_id'              => $agent->id,
                    'currency'                    => 'INR',
                    'gross_incentive_amount'      => $grossSum,
                    'approved_adjustments_amount' => $adjSum,
                    'withholding_tax_rate'        => $withholdingRate,
                    'withholding_tax_amount'      => $withholdingAmount,
                    'net_payable_amount'          => $netPayable,
                    'funding_expense_account'     => AgentIncentiveLedger::EXPENSE_APNI_FACTORY_PLATFORM,
                    'payment_method'              => $paymentMethod,
                    'status'                      => $initialStatus,
                    'payout_destination_snapshot' => array_merge($agent->maskedPayoutSummary(), [
                        'snapshot_at' => now()->toIso8601String(),
                    ]),
                    'reconciliation_status'       => AgentPayout::RECON_UNRECONCILED,
                    'idempotency_key'             => $idempotencyKey,
                    'initiated_by_user_id'        => $admin->id,
                    'initiated_at'                => now(),
                    'admin_notes'                 => $data['admin_notes'] ?? null,
                ]);

                foreach ($itemPayloads as $row) {
                    /** @var AgentIncentiveLedger $l */
                    $l = $row['ledger'];
                    AgentPayoutItem::create([
                        'agent_payout_id'           => $payout->id,
                        'agent_incentive_ledger_id' => $l->id,
                        'active_ledger_lock'        => $l->id, // DB unique lock prevents double inclusion!
                        'parent_order_id'           => $l->parent_order_id,
                        'eligible_order_sequence'   => $l->eligible_order_sequence,
                        'ledger_gross_amount'       => $row['ledger_gross_amount'],
                        'adjustment_amount'         => $row['adjustment_amount'],
                        'item_net_payable_amount'   => $row['item_net_payable_amount'],
                    ]);

                    $l->update([
                        'agent_payout_id'  => $payout->id,
                        'payout_reference' => $payout->payout_ref,
                    ]);
                }

                // Reconcile line item sum to payout header before commit
                $itemsSum = round((float)AgentPayoutItem::where('agent_payout_id', $payout->id)->sum('item_net_payable_amount'), 2);
                if (abs($itemsSum - $preTaxNet) > 0.01) {
                    throw new \RuntimeException("Payout header-to-item reconciliation mismatch (items: {$itemsSum} vs header: {$preTaxNet}).");
                }

                AgentIncentiveAuditLog::logAction(
                    'payout',
                    $payout->id,
                    'payout_batch_created',
                    $admin,
                    null,
                    $payout->status,
                    "Created payout batch {$payout->payout_ref} for {$agent->agent_code} covering " . count($itemPayloads) . " ledger entry(ies).",
                    null,
                    ['net_payable_amount' => $netPayable, 'ledger_ids' => $uniqueIds]
                );

                return $payout->fresh(['items.ledgerEntry', 'agent']);
            });
        } catch (QueryException $e) {
            if (str_contains(strtolower($e->getMessage()), 'active_ledger_lock') || str_contains(strtolower($e->getMessage()), 'unique')) {
                throw ValidationException::withMessages([
                    'ledger_ids' => ['Concurrency / Duplicate Inclusion Blocked: One or more incentive ledger entries are already assigned to an active payout batch.'],
                ]);
            }
            throw $e;
        }
    }

    /**
     * Step 2: Authorize an eligible payout batch (blocked while execution disabled or awaiting_policy).
     */
    public function authorizePayout(AgentPayout $payout, User $admin, ?string $notes = null): AgentPayout
    {
        $this->ensureAdmin($admin);
        $this->ensureExecutionPermitted('authorize payout');

        if ($payout->status === AgentPayout::STATUS_AUTHORIZED) {
            return $payout;
        }

        if (!in_array($payout->status, [AgentPayout::STATUS_DRAFT_AWAITING_POLICY, AgentPayout::STATUS_PENDING_AUTHORIZATION], true)) {
            throw ValidationException::withMessages([
                'status' => ["Payout #{$payout->payout_ref} cannot be authorized from status '{$payout->status}'."],
            ]);
        }

        // Enforce optional maker-checker ONLY when configured as true (never invented!)
        if (config('marketplace_agents.payout_dual_approval_required') === true && (int)$payout->initiated_by_user_id === (int)$admin->id) {
            throw ValidationException::withMessages([
                'authorization' => ['Dual-control policy requires a different authorizer from the batch initiator.'],
            ]);
        }

        return DB::transaction(function () use ($payout, $admin, $notes) {
            $prev = $payout->status;
            $payout->update([
                'status'                => AgentPayout::STATUS_AUTHORIZED,
                'authorized_by_user_id' => $admin->id,
                'authorized_at'         => now(),
                'payment_method'        => $payout->payment_method === AgentPayout::METHOD_AWAITING_POLICY
                    ? AgentPayout::METHOD_MANUAL_BANK
                    : $payout->payment_method,
                'admin_notes'           => $notes ? trim(($payout->admin_notes ? $payout->admin_notes . "\n" : '') . $notes) : $payout->admin_notes,
            ]);

            AgentIncentiveAuditLog::logAction(
                'payout',
                $payout->id,
                'payout_authorized',
                $admin,
                $prev,
                AgentPayout::STATUS_AUTHORIZED,
                $notes ?? 'Payout batch authorized for processing.'
            );

            return $payout->fresh(['items', 'agent']);
        });
    }

    /**
     * Step 3: Record controlled payment initiation (still NOT marked paid until authoritative confirmation!).
     */
    public function recordPaymentInitiation(AgentPayout $payout, User $admin, array $data = []): AgentPayout
    {
        $this->ensureAdmin($admin);
        $this->ensureExecutionPermitted('initiate payment');

        if ($payout->status === AgentPayout::STATUS_PROCESSING_INITIATED) {
            return $payout;
        }

        if ($payout->status !== AgentPayout::STATUS_AUTHORIZED) {
            throw ValidationException::withMessages([
                'status' => ["Payout #{$payout->payout_ref} must be 'authorized' before initiating payment (current: '{$payout->status}')."],
            ]);
        }

        $method = $data['payment_method'] ?? $payout->payment_method;
        if (!in_array($method, [AgentPayout::METHOD_MANUAL_BANK, AgentPayout::METHOD_MANUAL_UPI, AgentPayout::METHOD_APPROVED_BANK_API], true)) {
            throw ValidationException::withMessages([
                'payment_method' => ['An approved execution payment method must be selected to initiate payout.'],
            ]);
        }

        $updated = DB::transaction(function () use ($payout, $admin, $method, $data) {
            $prev = $payout->status;
            $payout->update([
                'status'                 => AgentPayout::STATUS_PROCESSING_INITIATED,
                'payment_method'         => $method,
                'external_provider_name' => $data['external_provider_name'] ?? null,
                'admin_notes'            => !empty($data['notes']) ? trim(($payout->admin_notes ? $payout->admin_notes . "\n" : '') . $data['notes']) : $payout->admin_notes,
            ]);

            AgentIncentiveAuditLog::logAction(
                'payout',
                $payout->id,
                'payout_processing_initiated',
                $admin,
                $prev,
                AgentPayout::STATUS_PROCESSING_INITIATED,
                $data['notes'] ?? "Payment initiation recorded via {$method} (awaiting authoritative confirmation)."
            );

            return $payout->fresh(['items', 'agent']);
        });

        $this->dispatchPayoutNotification($updated, 'processing_initiated');

        return $updated;
    }

    /**
     * Step 4: Authoritative confirmation & reconciliation of payment (bank UTR / verified provider callback).
     * Rejects unverified statuses, client-reported claims, duplicate UTRs, and mismatched amounts.
     */
    public function confirmPayoutPayment(AgentPayout $payout, array $data, ?User $actor = null): AgentPayout
    {
        if ($actor) {
            $this->ensureAdmin($actor);
        }
        $this->ensureExecutionPermitted('confirm payment');

        // Callback / Event Idempotency check (Test #16)
        $callbackEventId = !empty($data['callback_event_id']) ? trim((string)$data['callback_event_id']) : null;
        if ($callbackEventId && $payout->callback_event_id === $callbackEventId && $payout->status === AgentPayout::STATUS_PAID_CONFIRMED) {
            return $payout->load(['items.ledgerEntry', 'agent']);
        }

        if ($payout->status === AgentPayout::STATUS_PAID_CONFIRMED && empty($callbackEventId)) {
            return $payout->load(['items.ledgerEntry', 'agent']);
        }

        // Strict Verification Check (Stage 5, 12 & Test #8): Do NOT trust unverified or client-side claims!
        $source = (string)($data['confirmation_source'] ?? '');
        $verificationStatus = strtolower((string)($data['verification_status'] ?? ''));

        if (!in_array($source, self::VERIFIED_CONFIRMATION_SOURCES, true) || $verificationStatus !== 'verified') {
            throw ValidationException::withMessages([
                'confirmation_source' => [
                    'Payment confirmation rejected: Unverified status or client-side confirmation cannot mark an agent payout paid. Authoritative bank/provider verification required.',
                ],
            ]);
        }

        if (!in_array($payout->status, [AgentPayout::STATUS_AUTHORIZED, AgentPayout::STATUS_PROCESSING_INITIATED], true)) {
            throw ValidationException::withMessages([
                'status' => ["Payout #{$payout->payout_ref} cannot be marked paid from status '{$payout->status}'."],
            ]);
        }

        $externalRef = trim((string)($data['external_transaction_ref'] ?? ''));
        if ($externalRef === '') {
            throw ValidationException::withMessages([
                'external_transaction_ref' => ['Authoritative external bank/UTR/provider reference is required.'],
            ]);
        }

        // Duplicate External Reference Detection (Stage 7)
        $duplicatePayout = AgentPayout::where('external_transaction_ref', $externalRef)
            ->where('id', '!=', $payout->id)
            ->first();
        if ($duplicatePayout) {
            AgentPayoutReconciliationException::create([
                'exception_ref'      => 'EX-DUP-' . strtoupper(Str::random(6)),
                'agent_payout_id'    => $payout->id,
                'field_agent_id'     => $payout->field_agent_id,
                'exception_type'     => AgentPayoutReconciliationException::TYPE_DUPLICATE_PAYMENT,
                'external_reference' => $externalRef,
                'expected_amount'    => $payout->net_payable_amount,
                'reported_amount'    => $data['confirmed_amount'] ?? $payout->net_payable_amount,
                'status'             => AgentPayoutReconciliationException::STATUS_OPEN,
                'description'        => "Duplicate external reference '{$externalRef}' already attached to payout #{$duplicatePayout->payout_ref}.",
                'created_by_user_id' => $actor?->id,
            ]);

            throw ValidationException::withMessages([
                'external_transaction_ref' => ["Duplicate payment detected: external reference '{$externalRef}' already belongs to payout #{$duplicatePayout->payout_ref}."],
            ]);
        }

        // Reconciled Amount Check
        $confirmedAmount = isset($data['confirmed_amount']) ? round((float)$data['confirmed_amount'], 2) : $payout->net_payable_amount;
        if (abs($confirmedAmount - (float)$payout->net_payable_amount) >= 0.01) {
            $payout->update([
                'status'                => AgentPayout::STATUS_RECONCILIATION_EXCEPTION,
                'reconciliation_status' => AgentPayout::RECON_MISMATCH,
                'reconciliation_notes'  => "Amount mismatch: expected ₹{$payout->net_payable_amount}, confirmed ₹{$confirmedAmount}.",
            ]);

            AgentPayoutReconciliationException::create([
                'exception_ref'      => 'EX-AMT-' . strtoupper(Str::random(6)),
                'agent_payout_id'    => $payout->id,
                'field_agent_id'     => $payout->field_agent_id,
                'exception_type'     => AgentPayoutReconciliationException::TYPE_AMOUNT_MISMATCH,
                'external_reference' => $externalRef,
                'expected_amount'    => $payout->net_payable_amount,
                'reported_amount'    => $confirmedAmount,
                'status'             => AgentPayoutReconciliationException::STATUS_OPEN,
                'description'        => "Bank confirmed ₹{$confirmedAmount} but payout #{$payout->payout_ref} expects ₹{$payout->net_payable_amount}.",
                'created_by_user_id' => $actor?->id,
            ]);

            throw ValidationException::withMessages([
                'confirmed_amount' => ["Reconciliation mismatch: expected ₹{$payout->net_payable_amount}, received ₹{$confirmedAmount}."],
            ]);
        }

        $confirmed = DB::transaction(function () use ($payout, $externalRef, $source, $callbackEventId, $data, $actor) {
            $locked = AgentPayout::where('id', $payout->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === AgentPayout::STATUS_PAID_CONFIRMED) {
                return $locked;
            }

            $prev = $locked->status;
            $paidAt = !empty($data['paid_at']) ? \Carbon\Carbon::parse($data['paid_at']) : now();

            $locked->update([
                'status'                      => AgentPayout::STATUS_PAID_CONFIRMED,
                'external_transaction_ref'    => $externalRef,
                'external_provider_name'      => $data['external_provider_name'] ?? $locked->external_provider_name,
                'payment_confirmation_source' => $source,
                'reconciliation_status'       => AgentPayout::RECON_MATCHED,
                'reconciled_at'               => now(),
                'reconciled_by_user_id'       => $actor?->id,
                'reconciliation_notes'        => $data['reconciliation_notes'] ?? "Verified payment confirmed ({$source} / {$externalRef}).",
                'callback_event_id'           => $callbackEventId ?? $locked->callback_event_id,
                'paid_at'                     => $paidAt,
                'payment_date'                => $paidAt->toDateString(),
            ]);

            $ledgerIds = AgentPayoutItem::where('agent_payout_id', $locked->id)->pluck('agent_incentive_ledger_id');
            AgentIncentiveLedger::whereIn('id', $ledgerIds)->update([
                'status'           => AgentIncentiveLedger::STATUS_PAID,
                'payout_reference' => $locked->payout_ref,
                'agent_payout_id'  => $locked->id,
            ]);

            AgentIncentiveAuditLog::logAction(
                'payout',
                $locked->id,
                'payout_payment_confirmed',
                $actor,
                $prev,
                AgentPayout::STATUS_PAID_CONFIRMED,
                "Confirmed via {$source} ref {$externalRef}.",
                null,
                ['external_transaction_ref' => $externalRef, 'net_payable_amount' => $locked->net_payable_amount]
            );

            return $locked->fresh(['items.ledgerEntry', 'agent']);
        });

        $this->dispatchPayoutNotification($confirmed, 'paid_confirmed');

        return $confirmed;
    }

    /**
     * Record a failed / rejected payout while keeping permanent batch visibility
     * for reconciliation and unlocking eligible ledger items cleanly if unpaid.
     */
    public function recordPayoutFailure(AgentPayout $payout, User $admin, string $failureReason, ?string $externalRef = null): AgentPayout
    {
        $this->ensureAdmin($admin);

        if ($payout->status === AgentPayout::STATUS_PAID_CONFIRMED) {
            throw ValidationException::withMessages([
                'status' => ['Cannot mark an already confirmed paid payout as failed without reversal workflow.'],
            ]);
        }

        if (trim($failureReason) === '') {
            throw ValidationException::withMessages(['failure_reason' => ['A documented failure reason is required.']]);
        }

        $failed = DB::transaction(function () use ($payout, $admin, $failureReason, $externalRef) {
            $locked = AgentPayout::where('id', $payout->id)->lockForUpdate()->firstOrFail();
            $prev = $locked->status;

            $locked->update([
                'status'                => AgentPayout::STATUS_FAILED,
                'reconciliation_status' => AgentPayout::RECON_UNRECONCILED,
                'failure_reason'        => $failureReason,
                'reconciliation_notes'  => "Payout failed: {$failureReason}",
            ]);

            // Release active_ledger_lock on failed batch items while preserving batch + item rows cleanly (Test #9)
            $items = AgentPayoutItem::where('agent_payout_id', $locked->id)->get();
            foreach ($items as $item) {
                $item->update(['active_ledger_lock' => null]);
                $ledger = AgentIncentiveLedger::find($item->agent_incentive_ledger_id);
                if ($ledger && $ledger->status !== AgentIncentiveLedger::STATUS_REVERSED && $ledger->status !== AgentIncentiveLedger::STATUS_PAID) {
                    $ledger->update([
                        'agent_payout_id'  => null,
                        'payout_reference' => null,
                    ]);
                }
            }

            AgentPayoutReconciliationException::create([
                'exception_ref'      => 'EX-FAIL-' . strtoupper(Str::random(6)),
                'agent_payout_id'    => $locked->id,
                'field_agent_id'     => $locked->field_agent_id,
                'exception_type'     => AgentPayoutReconciliationException::TYPE_FAILED_PAYOUT,
                'external_reference' => $externalRef,
                'expected_amount'    => $locked->net_payable_amount,
                'reported_amount'    => 0.00,
                'status'             => AgentPayoutReconciliationException::STATUS_OPEN,
                'description'        => "Payout #{$locked->payout_ref} failed: {$failureReason}",
                'created_by_user_id' => $admin->id,
            ]);

            AgentIncentiveAuditLog::logAction(
                'payout',
                $locked->id,
                'payout_failed',
                $admin,
                $prev,
                AgentPayout::STATUS_FAILED,
                $failureReason
            );

            return $locked->fresh(['items', 'exceptions', 'agent']);
        });

        $this->dispatchPayoutNotification($failed, 'failed');

        return $failed;
    }

    /**
     * Log an unmatched external bank statement/provider transaction reference for reconciliation.
     */
    public function recordUnmatchedExternalReference(string $externalRef, float $reportedAmount, User $admin, ?string $notes = null, ?int $fieldAgentId = null): AgentPayoutReconciliationException
    {
        $this->ensureAdmin($admin);

        return AgentPayoutReconciliationException::create([
            'exception_ref'      => 'EX-UNM-' . strtoupper(Str::random(6)),
            'field_agent_id'     => $fieldAgentId,
            'exception_type'     => AgentPayoutReconciliationException::TYPE_UNMATCHED_EXTERNAL_REF,
            'external_reference' => trim($externalRef),
            'reported_amount'    => round($reportedAmount, 2),
            'status'             => AgentPayoutReconciliationException::STATUS_OPEN,
            'description'        => $notes ?: "External payout reference '{$externalRef}' (₹{$reportedAmount}) could not be matched to any active agent payout record.",
            'created_by_user_id' => $admin->id,
        ]);
    }

    public function resolveException(AgentPayoutReconciliationException $exception, string $newStatus, string $resolutionNotes, User $admin): AgentPayoutReconciliationException
    {
        $this->ensureAdmin($admin);

        if (!in_array($newStatus, [AgentPayoutReconciliationException::STATUS_UNDER_REVIEW, AgentPayoutReconciliationException::STATUS_RESOLVED, AgentPayoutReconciliationException::STATUS_DISMISSED], true)) {
            throw ValidationException::withMessages(['status' => ['Invalid exception resolution status.']]);
        }

        $exception->update([
            'status'           => $newStatus,
            'resolution_notes' => $resolutionNotes,
            'resolved_by_user_id' => $admin->id,
            'resolved_at'      => in_array($newStatus, [AgentPayoutReconciliationException::STATUS_RESOLVED, AgentPayoutReconciliationException::STATUS_DISMISSED], true) ? now() : null,
        ]);

        AgentIncentiveAuditLog::logAction(
            'reconciliation_exception',
            $exception->id,
            'exception_' . $newStatus,
            $admin,
            null,
            $newStatus,
            $resolutionNotes
        );

        return $exception->fresh();
    }

    public function listPayouts(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = AgentPayout::with(['agent:id,agent_code,name,territory', 'items', 'initiator:id,name', 'authorizer:id,name'])
            ->orderByDesc('id');

        if (!empty($filters['field_agent_id'])) {
            $query->where('field_agent_id', (int)$filters['field_agent_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['reconciliation_status'])) {
            $query->where('reconciliation_status', $filters['reconciliation_status']);
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
                $q->where('payout_ref', 'like', "%{$term}%")
                  ->orWhere('external_transaction_ref', 'like', "%{$term}%")
                  ->orWhereHas('agent', fn($a) => $a->where('agent_code', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%"));
            });
        }

        return $query->paginate(min(max(1, $perPage), 100));
    }

    /**
     * Authoritative Finance Dashboard KPIs & Reconciliation Totals derived purely from DB ledgers.
     */
    public function getFinanceDashboardSummary(array $filters = []): array
    {
        $report = app(AgentIncentiveCalculationService::class)->getLedgerReconciledReport($filters)['summary'];

        $payoutQuery = AgentPayout::query();
        if (!empty($filters['field_agent_id'])) {
            $payoutQuery->where('field_agent_id', (int)$filters['field_agent_id']);
        }
        if (!empty($filters['date_from'])) {
            $payoutQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $payoutQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        $payouts = $payoutQuery->get();
        $unreconciledCount = $payouts->where('reconciliation_status', '!=', AgentPayout::RECON_MATCHED)->count();
        $failedPayoutsCount = $payouts->where('status', AgentPayout::STATUS_FAILED)->count();
        $confirmedPayoutsTotal = round((float)$payouts->where('status', AgentPayout::STATUS_PAID_CONFIRMED)->sum('net_payable_amount'), 2);
        $openExceptionsCount = AgentPayoutReconciliationException::whereIn('status', [AgentPayoutReconciliationException::STATUS_OPEN, AgentPayoutReconciliationException::STATUS_UNDER_REVIEW])->count();

        // Count payout-ready approved entries
        $approvedEntries = AgentIncentiveLedger::where('entry_type', AgentIncentiveLedger::TYPE_EARNED)
            ->where('status', AgentIncentiveLedger::STATUS_APPROVED)
            ->when(!empty($filters['field_agent_id']), fn($q) => $q->where('field_agent_id', (int)$filters['field_agent_id']))
            ->get();

        $readyCount = 0;
        foreach ($approvedEntries as $entry) {
            if ($this->checkLedgerEntryReadiness($entry)['ready']) {
                $readyCount++;
            }
        }

        return array_merge($report, [
            'approved_unpaid_total'   => $report['approved_total'],
            'confirmed_paid_total'    => $report['paid_total'],
            'payout_batches_paid_sum' => $confirmedPayoutsTotal,
            'payout_ready_count'      => $readyCount,
            'unreconciled_payouts'    => $unreconciledCount,
            'failed_payouts_count'    => $failedPayoutsCount,
            'open_exceptions_count'   => $openExceptionsCount,
            'payout_execution_enabled'=> (bool)config('marketplace_agents.payout_execution_enabled', false),
            'payout_execution_mode'   => config('marketplace_agents.payout_execution_mode', 'awaiting_policy'),
        ]);
    }

    /**
     * Existing project fputcsv CSV export stream helper for Finance Reconciliation & Payout Audit.
     */
    public function streamReconciledCsvExport(array $filters = []): StreamedResponse
    {
        $filename = 'apnifactory_agent_incentives_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Ledger Ref',
                'Entry Type',
                'Agent Code',
                'Agent Name',
                'Territory',
                'Buyer Name',
                'Parent Order No',
                'Sequence Slot',
                'Rule Version',
                'Calculation Base (INR)',
                'Net Incentive Amount (INR)',
                'Expense Account',
                'Ledger Status',
                'Payout Ref',
                'External Bank Ref',
                'Created At',
            ]);

            $entries = AgentIncentiveLedger::with([
                'agent:id,agent_code,name,territory',
                'customer:id,name',
                'parentOrder:id,orderno',
                'payout:id,payout_ref,external_transaction_ref',
            ])
                ->when(!empty($filters['field_agent_id']), fn($q) => $q->where('field_agent_id', (int)$filters['field_agent_id']))
                ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
                ->when(!empty($filters['date_from']), fn($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
                ->when(!empty($filters['date_to']), fn($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
                ->orderBy('id')
                ->get();

            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->ledger_ref,
                    $e->entry_type,
                    $e->agent?->agent_code,
                    $e->agent?->name,
                    $e->agent?->territory,
                    $e->customer?->name,
                    $e->parentOrder?->orderno,
                    $e->eligible_order_sequence . '/3',
                    $e->rule_code . ' v' . $e->rule_version,
                    number_format((float)$e->calculation_base_amount, 2, '.', ''),
                    number_format((float)$e->calculated_incentive_amount, 2, '.', ''),
                    $e->funding_expense_account,
                    $e->status,
                    $e->payout_reference ?? '',
                    $e->payout?->external_transaction_ref ?? '',
                    $e->created_at?->toIso8601String(),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function ensureExecutionPermitted(string $actionLabel): void
    {
        if (!config('marketplace_agents.funding_policy_approved', false)) {
            throw ValidationException::withMessages([
                'funding_policy' => ["Cannot {$actionLabel}: Funding policy approval is missing."],
            ]);
        }

        if (!config('marketplace_agents.payout_execution_enabled', false) ||
            config('marketplace_agents.payout_execution_mode', 'awaiting_policy') === 'awaiting_policy'
        ) {
            throw ValidationException::withMessages([
                'payout_execution' => [
                    "Cannot {$actionLabel}: Real payment execution & confirmation are disabled until written commercial, finance, and payment-method approvals enable `payout_execution_enabled`.",
                ],
            ]);
        }
    }

    protected function dispatchPayoutNotification(AgentPayout $payout, string $event): void
    {
        try {
            $payout->loadMissing('agent');
            $agent = $payout->agent;
            if (!$agent || !$agent->user_id) {
                return;
            }

            if ($event === 'processing_initiated') {
                SendAgentIncentiveNotificationJob::dispatch(
                    (int)$agent->user_id,
                    'agent',
                    "Payout Processing Initiated: #{$payout->payout_ref}",
                    "Your approved incentive payout batch #{$payout->payout_ref} has entered finance processing. Confirmation will follow once bank settlement clears.",
                    [
                        'Payout Ref' => $payout->payout_ref,
                        'Status'     => 'Processing Initiated',
                    ],
                    route('agent.incentives.index'),
                    "payout_proc_{$payout->id}"
                );
            } elseif ($event === 'paid_confirmed') {
                SendAgentIncentiveNotificationJob::dispatch(
                    (int)$agent->user_id,
                    'agent',
                    "Payout Confirmed: #{$payout->payout_ref}",
                    "Incentive payout #{$payout->payout_ref} for INR " . number_format((float)$payout->net_payable_amount, 2) . " has been confirmed paid (Ref: {$payout->external_transaction_ref}).",
                    [
                        'Payout Ref'  => $payout->payout_ref,
                        'Bank / UTR'  => (string)$payout->external_transaction_ref,
                        'Net Paid'    => 'INR ' . number_format((float)$payout->net_payable_amount, 2),
                        'Payment Date'=> $payout->payment_date?->toDateString() ?? date('Y-m-d'),
                    ],
                    route('agent.incentives.index'),
                    "payout_paid_{$payout->id}"
                );
            } elseif ($event === 'failed') {
                SendAgentIncentiveNotificationJob::dispatch(
                    (int)$agent->user_id,
                    'agent',
                    "Payout Requires Finance Review: #{$payout->payout_ref}",
                    "Payout #{$payout->payout_ref} could not be completed and is under finance review.",
                    [
                        'Payout Ref' => $payout->payout_ref,
                        'Status'     => 'Under Finance Review',
                    ],
                    route('agent.incentives.index'),
                    "payout_fail_{$payout->id}"
                );
            }
        } catch (\Throwable $e) {
            // Notification failures must never roll back payout transactions
        }
    }
}

