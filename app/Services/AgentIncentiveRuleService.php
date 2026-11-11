<?php

namespace App\Services;

use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveRule;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\SellerSettlement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgentIncentiveRuleService
{
    protected function ensureAdmin(User $actor): void
    {
        if (!$actor->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Only authorized administrators can manage agent incentive rules.'],
            ]);
        }
    }

    /**
     * Create a new draft/pending incentive rule (never active on creation).
     * Prohibits seller-funded rules and refuses to invent rates, caps, rounding, or tax rules.
     */
    public function createRule(array $data, User $admin): AgentIncentiveRule
    {
        $this->ensureAdmin($admin);
        $this->validateRulePayload($data);

        $ruleCode = strtoupper(trim((string)($data['rule_code'] ?? '')));
        if ($ruleCode === '') {
            $ruleCode = 'INC-RULE-' . str_pad((string)(AgentIncentiveRule::max('id') + 1), 3, '0', STR_PAD_LEFT);
        }

        if (AgentIncentiveRule::where('rule_code', $ruleCode)->where('version', 1)->exists()) {
            throw ValidationException::withMessages([
                'rule_code' => ["Incentive rule code '{$ruleCode}' already exists. Use createNewVersion to create v2+."],
            ]);
        }

        return DB::transaction(function () use ($data, $admin, $ruleCode) {
            $initialStatus = ($data['submit_for_approval'] ?? false)
                ? AgentIncentiveRule::STATUS_PENDING_APPROVAL
                : AgentIncentiveRule::STATUS_DRAFT;

            $rule = AgentIncentiveRule::create([
                'rule_code'          => $ruleCode,
                'version'            => 1,
                'name'               => trim((string)$data['name']),
                'funding_source'     => AgentIncentiveRule::FUNDING_APNI_FACTORY,
                'calculation_method' => $data['calculation_method'],
                'calculation_base'   => $data['calculation_base'],
                'rate_percentage'    => isset($data['rate_percentage']) ? round((float)$data['rate_percentage'], 4) : null,
                'fixed_amount'       => isset($data['fixed_amount']) ? round((float)$data['fixed_amount'], 2) : null,
                'max_cap_amount'     => isset($data['max_cap_amount']) && $data['max_cap_amount'] !== '' ? round((float)$data['max_cap_amount'], 2) : null,
                'rounding_rule'      => $data['rounding_rule'] ?? null,
                'tax_treatment_code' => $data['tax_treatment_code'] ?? AgentIncentiveRule::TAX_PENDING_DECISION,
                'tds_percentage'     => isset($data['tds_percentage']) && $data['tds_percentage'] !== '' ? round((float)$data['tds_percentage'], 2) : null,
                'tax_notes'          => $data['tax_notes'] ?? null,
                'effective_from'     => Carbon::parse($data['effective_from']),
                'effective_to'       => !empty($data['effective_to']) ? Carbon::parse($data['effective_to']) : null,
                'status'             => $initialStatus,
                'agreement_ref'      => $data['agreement_ref'] ?? null,
                'reason'             => $data['reason'] ?? 'Initial rule configuration.',
                'created_by_user_id' => $admin->id,
                'updated_by_user_id' => $admin->id,
            ]);

            AgentIncentiveAuditLog::logAction(
                'rule',
                $rule->id,
                'rule_created',
                $admin,
                null,
                $rule->status,
                $rule->reason,
                null,
                $rule->toArray()
            );

            return $rule->fresh();
        });
    }

    /**
     * Create a new immutable version of an existing rule without altering prior snapshots.
     */
    public function createNewVersion(AgentIncentiveRule $baseRule, array $data, User $admin): AgentIncentiveRule
    {
        $this->ensureAdmin($admin);

        $merged = [
            'rule_code'          => $baseRule->rule_code,
            'name'               => $data['name'] ?? $baseRule->name,
            'funding_source'     => $data['funding_source'] ?? $baseRule->funding_source,
            'calculation_method' => $data['calculation_method'] ?? $baseRule->calculation_method,
            'calculation_base'   => $data['calculation_base'] ?? $baseRule->calculation_base,
            'rate_percentage'    => array_key_exists('rate_percentage', $data) ? $data['rate_percentage'] : $baseRule->rate_percentage,
            'fixed_amount'       => array_key_exists('fixed_amount', $data) ? $data['fixed_amount'] : $baseRule->fixed_amount,
            'max_cap_amount'     => array_key_exists('max_cap_amount', $data) ? $data['max_cap_amount'] : $baseRule->max_cap_amount,
            'rounding_rule'      => $data['rounding_rule'] ?? $baseRule->rounding_rule,
            'tax_treatment_code' => $data['tax_treatment_code'] ?? $baseRule->tax_treatment_code,
            'tds_percentage'     => array_key_exists('tds_percentage', $data) ? $data['tds_percentage'] : $baseRule->tds_percentage,
            'tax_notes'          => $data['tax_notes'] ?? $baseRule->tax_notes,
            'effective_from'     => $data['effective_from'] ?? now(),
            'effective_to'       => array_key_exists('effective_to', $data) ? $data['effective_to'] : $baseRule->effective_to,
            'agreement_ref'      => $data['agreement_ref'] ?? $baseRule->agreement_ref,
            'reason'             => $data['reason'] ?? "New version cloned from v{$baseRule->version}.",
        ];

        $this->validateRulePayload($merged);

        return DB::transaction(function () use ($baseRule, $merged, $data, $admin) {
            $maxVersion = (int)AgentIncentiveRule::where('rule_code', $baseRule->rule_code)
                ->lockForUpdate()
                ->max('version');

            $newVersion = AgentIncentiveRule::create([
                'rule_code'          => $baseRule->rule_code,
                'version'            => $maxVersion + 1,
                'parent_rule_id'     => $baseRule->id,
                'name'               => trim((string)$merged['name']),
                'funding_source'     => AgentIncentiveRule::FUNDING_APNI_FACTORY,
                'calculation_method' => $merged['calculation_method'],
                'calculation_base'   => $merged['calculation_base'],
                'rate_percentage'    => isset($merged['rate_percentage']) ? round((float)$merged['rate_percentage'], 4) : null,
                'fixed_amount'       => isset($merged['fixed_amount']) ? round((float)$merged['fixed_amount'], 2) : null,
                'max_cap_amount'     => isset($merged['max_cap_amount']) && $merged['max_cap_amount'] !== '' ? round((float)$merged['max_cap_amount'], 2) : null,
                'rounding_rule'      => $merged['rounding_rule'],
                'tax_treatment_code' => $merged['tax_treatment_code'],
                'tds_percentage'     => isset($merged['tds_percentage']) && $merged['tds_percentage'] !== '' ? round((float)$merged['tds_percentage'], 2) : null,
                'tax_notes'          => $merged['tax_notes'],
                'effective_from'     => Carbon::parse($merged['effective_from']),
                'effective_to'       => !empty($merged['effective_to']) ? Carbon::parse($merged['effective_to']) : null,
                'status'             => ($data['submit_for_approval'] ?? true)
                    ? AgentIncentiveRule::STATUS_PENDING_APPROVAL
                    : AgentIncentiveRule::STATUS_DRAFT,
                'agreement_ref'      => $merged['agreement_ref'],
                'reason'             => $merged['reason'],
                'created_by_user_id' => $admin->id,
                'updated_by_user_id' => $admin->id,
            ]);

            AgentIncentiveAuditLog::logAction(
                'rule',
                $newVersion->id,
                'rule_version_created',
                $admin,
                null,
                $newVersion->status,
                $merged['reason'],
                ['parent_rule_id' => $baseRule->id, 'parent_version' => $baseRule->version],
                $newVersion->toArray()
            );

            return $newVersion->fresh();
        });
    }

    /**
     * Record Business Owner approval sign-off for a rule.
     */
    public function recordBusinessApproval(AgentIncentiveRule $rule, User $approver, string $approvalRef, ?string $reason = null): AgentIncentiveRule
    {
        $this->ensureAdmin($approver);
        if (trim($approvalRef) === '') {
            throw ValidationException::withMessages(['business_approval_ref' => ['Business approval reference is required.']]);
        }

        return DB::transaction(function () use ($rule, $approver, $approvalRef, $reason) {
            $prevStatus = $rule->status;
            $newStatus = $rule->hasFinanceApproval()
                ? AgentIncentiveRule::STATUS_APPROVED
                : AgentIncentiveRule::STATUS_PENDING_APPROVAL;

            $rule->update([
                'business_approved_by_user_id' => $approver->id,
                'business_approved_at'         => now(),
                'business_approval_ref'        => trim($approvalRef),
                'status'                       => $newStatus,
                'approved_by_user_id'          => $rule->hasFinanceApproval() ? $approver->id : $rule->approved_by_user_id,
                'approved_at'                  => $rule->hasFinanceApproval() ? now() : $rule->approved_at,
                'updated_by_user_id'           => $approver->id,
            ]);

            AgentIncentiveAuditLog::logAction(
                'rule',
                $rule->id,
                'rule_business_approved',
                $approver,
                $prevStatus,
                $newStatus,
                $reason ?? "Business approval recorded ({$approvalRef})."
            );

            return $rule->fresh();
        });
    }

    /**
     * Record Finance Owner approval sign-off for a rule.
     */
    public function recordFinanceApproval(AgentIncentiveRule $rule, User $approver, string $approvalRef, ?string $reason = null): AgentIncentiveRule
    {
        $this->ensureAdmin($approver);
        if (trim($approvalRef) === '') {
            throw ValidationException::withMessages(['finance_approval_ref' => ['Finance approval reference is required.']]);
        }

        return DB::transaction(function () use ($rule, $approver, $approvalRef, $reason) {
            $prevStatus = $rule->status;
            $newStatus = $rule->hasBusinessApproval()
                ? AgentIncentiveRule::STATUS_APPROVED
                : AgentIncentiveRule::STATUS_PENDING_APPROVAL;

            $rule->update([
                'finance_approved_by_user_id' => $approver->id,
                'finance_approved_at'         => now(),
                'finance_approval_ref'        => trim($approvalRef),
                'status'                      => $newStatus,
                'approved_by_user_id'         => $rule->hasBusinessApproval() ? $approver->id : $rule->approved_by_user_id,
                'approved_at'                 => $rule->hasBusinessApproval() ? now() : $rule->approved_at,
                'updated_by_user_id'          => $approver->id,
            ]);

            AgentIncentiveAuditLog::logAction(
                'rule',
                $rule->id,
                'rule_finance_approved',
                $approver,
                $prevStatus,
                $newStatus,
                $reason ?? "Finance approval recorded ({$approvalRef})."
            );

            return $rule->fresh();
        });
    }

    /**
     * Activate an approved rule.
     * Enforces:
     * 1. Platform funding policy approved flag must be true (resolving BRD Section 12 vs seller-funded conflict).
     * 2. Both Business and Finance approvals must be recorded.
     * 3. Commercial completeness (method, base, rate/amount, rounding, explicit non-pending tax treatment, agreement ref).
     * 4. Supersedes older active versions of same rule_code and blocks overlapping active rules of different rule_code.
     */
    public function activateRule(AgentIncentiveRule $rule, User $admin, ?string $reason = null): AgentIncentiveRule
    {
        $this->ensureAdmin($admin);

        if (!config('marketplace_agents.funding_policy_approved', false)) {
            AgentIncentiveAuditLog::logAction(
                'rule',
                $rule->id,
                'rule_activation_blocked_unapproved_funding',
                $admin,
                $rule->status,
                $rule->status,
                'Activation blocked: BRD funding policy contradiction requires written approval before activating financial rules.'
            );

            throw ValidationException::withMessages([
                'funding_policy' => [
                    'Rule activation blocked: The BRD Section 12 (Apni Factory-funded) vs seller-funded contradiction must be resolved with written approval (`funding_policy_approved = true`) before any incentive rule can be activated.',
                ],
            ]);
        }

        if (!$rule->hasRequiredApprovals()) {
            throw ValidationException::withMessages([
                'approvals' => ['Cannot activate rule: Both Business approval and Finance approval must be recorded first.'],
            ]);
        }

        $this->validateCompletenessForActivation($rule);

        return DB::transaction(function () use ($rule, $admin, $reason) {
            // Check conflicting overlapping active rules of a DIFFERENT rule_code
            $overlap = AgentIncentiveRule::where('status', AgentIncentiveRule::STATUS_ACTIVE)
                ->where('id', '!=', $rule->id)
                ->where('rule_code', '!=', $rule->rule_code)
                ->where(function ($q) use ($rule) {
                    if ($rule->effective_to) {
                        $q->where('effective_from', '<=', $rule->effective_to);
                    }
                    $q->where(function ($sub) use ($rule) {
                        $sub->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $rule->effective_from);
                    });
                })
                ->first();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'effective_from' => ["Effective period overlaps with active rule '{$overlap->rule_code}' v{$overlap->version}. Deactivate it or adjust effective dates first."],
                ]);
            }

            // Supersede earlier active versions of the SAME rule_code cleanly without deleting or editing ledger history
            $priorActiveSameCode = AgentIncentiveRule::where('rule_code', $rule->rule_code)
                ->where('id', '!=', $rule->id)
                ->where('status', AgentIncentiveRule::STATUS_ACTIVE)
                ->get();

            foreach ($priorActiveSameCode as $oldVer) {
                $closeAt = $oldVer->effective_to;
                if (!$closeAt || $closeAt->gt($rule->effective_from)) {
                    $closeAt = $rule->effective_from->copy()->subSecond();
                }

                $oldVer->update([
                    'status'              => AgentIncentiveRule::STATUS_SUPERSEDED,
                    'effective_to'        => $closeAt,
                    'updated_by_user_id'  => $admin->id,
                ]);

                AgentIncentiveAuditLog::logAction(
                    'rule',
                    $oldVer->id,
                    'rule_superseded',
                    $admin,
                    AgentIncentiveRule::STATUS_ACTIVE,
                    AgentIncentiveRule::STATUS_SUPERSEDED,
                    "Superseded by {$rule->rule_code} v{$rule->version}."
                );
            }

            $prevStatus = $rule->status;
            $rule->update([
                'status'              => AgentIncentiveRule::STATUS_ACTIVE,
                'approved_by_user_id' => $rule->approved_by_user_id ?: $admin->id,
                'approved_at'         => $rule->approved_at ?: now(),
                'updated_by_user_id'  => $admin->id,
            ]);

            AgentIncentiveAuditLog::logAction(
                'rule',
                $rule->id,
                'rule_activated',
                $admin,
                $prevStatus,
                AgentIncentiveRule::STATUS_ACTIVE,
                $reason ?? 'Rule activated after dual Business + Finance sign-offs.'
            );

            return $rule->fresh();
        });
    }

    public function deactivateRule(AgentIncentiveRule $rule, User $admin, string $reason): AgentIncentiveRule
    {
        $this->ensureAdmin($admin);

        return DB::transaction(function () use ($rule, $admin, $reason) {
            $prevStatus = $rule->status;
            $rule->update([
                'status'               => AgentIncentiveRule::STATUS_INACTIVE,
                'deactivated_by_user_id' => $admin->id,
                'deactivated_at'       => now(),
                'deactivation_reason'  => $reason,
                'updated_by_user_id'   => $admin->id,
            ]);

            AgentIncentiveAuditLog::logAction(
                'rule',
                $rule->id,
                'rule_deactivated',
                $admin,
                $prevStatus,
                AgentIncentiveRule::STATUS_INACTIVE,
                $reason
            );

            return $rule->fresh();
        });
    }

    /**
     * Resolve the single active rule effective at a specific eligibility timestamp.
     */
    public function resolveEffectiveRule(?Carbon $at = null): ?AgentIncentiveRule
    {
        $ts = $at ? $at->copy() : now();

        return AgentIncentiveRule::activeEffective($ts)
            ->orderByDesc('effective_from')
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Calculate exact incentive breakdown using server-side trusted snapshots ONLY.
     * Supports Alternatives A, B, and C, explicit rounding, cap, and configurable tax deduction.
     */
    public function calculateAmountForOrder(Order $parentOrder, AgentIncentiveRule $rule): array
    {
        $childIds = $parentOrder->childOrders()->pluck('id')->all();
        $orderIds = array_values(array_unique(array_merge([$parentOrder->id], $childIds)));

        $baseAmount = 0.0;

        switch ($rule->calculation_base) {
            case AgentIncentiveRule::BASE_TAXABLE_ORDER:
                $net = (float)$parentOrder->netamount;
                if ($net <= 0 && !empty($childIds)) {
                    $net = (float)Order::whereIn('id', $childIds)->sum('netamount');
                }
                $baseAmount = round($net, 2);
                break;

            case AgentIncentiveRule::BASE_GROSS_ORDER:
                $gross = (float)$parentOrder->grandtotal;
                if ($gross <= 0 && !empty($childIds)) {
                    $gross = (float)Order::whereIn('id', $childIds)->sum('grandtotal');
                }
                $baseAmount = round($gross, 2);
                break;

            case AgentIncentiveRule::BASE_COMMISSION_EX_GST:
            case AgentIncentiveRule::BASE_COMMISSION_INCL_GST:
                $inclGst = ($rule->calculation_base === AgentIncentiveRule::BASE_COMMISSION_INCL_GST);
                $snapshots = OrderCommissionSnapshot::whereIn('order_id', $orderIds)->get();

                if ($snapshots->isNotEmpty()) {
                    $baseAmount = round((float)$snapshots->sum($inclGst ? 'total_platform_fee' : 'commission_amount'), 2);
                } else {
                    $settlements = SellerSettlement::whereIn('order_id', $orderIds)->get();
                    if ($settlements->isEmpty()) {
                        throw ValidationException::withMessages([
                            'calculation_base' => ["Cannot compute '{$rule->calculation_base}': No immutable OrderCommissionSnapshot or SellerSettlement record exists for order #{$parentOrder->orderno}."],
                        ]);
                    }
                    $baseAmount = round((float)$settlements->sum(function ($s) use ($inclGst) {
                        return $inclGst
                            ? ((float)$s->commission_amount + (float)$s->commission_gst_amount)
                            : (float)$s->commission_amount;
                    }), 2);
                }
                break;

            case AgentIncentiveRule::BASE_FIXED_PER_ORDER:
                $baseAmount = round((float)$parentOrder->netamount ?: (float)$parentOrder->grandtotal, 2);
                break;
        }

        // Raw amount before cap & rounding
        if ($rule->calculation_method === AgentIncentiveRule::METHOD_FIXED_AMOUNT) {
            $rawIncentive = (float)$rule->fixed_amount;
        } else {
            $rawIncentive = $baseAmount * ((float)$rule->rate_percentage / 100.0);
        }

        $capApplied = false;
        if ($rule->max_cap_amount !== null && $rawIncentive > (float)$rule->max_cap_amount) {
            $rawIncentive = (float)$rule->max_cap_amount;
            $capApplied = true;
        }

        $grossIncentive = $this->applyRounding($rawIncentive, $rule->rounding_rule);

        // Configurable tax/TDS treatment (ZERO invented rates)
        $taxDeduction = 0.00;
        if ($rule->tax_treatment_code === AgentIncentiveRule::TAX_TDS_CONFIGURABLE && $rule->tds_percentage !== null && $rule->tds_percentage > 0) {
            $taxDeduction = $this->applyRounding($grossIncentive * ((float)$rule->tds_percentage / 100.0), $rule->rounding_rule);
        }

        $netIncentive = round(max(0.0, $grossIncentive - $taxDeduction), 2);

        return [
            'rule_id'                   => $rule->id,
            'rule_code'                 => $rule->rule_code,
            'rule_version'              => (int)$rule->version,
            'calculation_method'        => $rule->calculation_method,
            'calculation_base'          => $rule->calculation_base,
            'calculation_base_amount'   => $baseAmount,
            'applied_rate'              => $rule->rate_percentage,
            'applied_fixed_amount'      => $rule->fixed_amount,
            'max_cap_amount'            => $rule->max_cap_amount,
            'cap_applied'               => $capApplied,
            'rounding_rule'             => $rule->rounding_rule,
            'tax_treatment_snapshot'    => [
                'tax_treatment_code' => $rule->tax_treatment_code,
                'tds_percentage'     => $rule->tds_percentage,
                'tax_notes'          => $rule->tax_notes,
            ],
            'gross_incentive_amount'    => $grossIncentive,
            'tax_deduction_amount'      => $taxDeduction,
            'calculated_incentive_amount' => $netIncentive,
        ];
    }

    public function listRules(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = AgentIncentiveRule::with(['creator:id,name,email', 'businessApprover:id,name', 'financeApprover:id,name'])
            ->orderBy('rule_code')
            ->orderByDesc('version');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['calculation_method'])) {
            $query->where('calculation_method', $filters['calculation_method']);
        }
        if (!empty($filters['search'])) {
            $term = trim((string)$filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('rule_code', 'like', "%{$term}%")
                  ->orWhere('name', 'like', "%{$term}%")
                  ->orWhere('agreement_ref', 'like', "%{$term}%");
            });
        }

        return $query->paginate(min(max(1, $perPage), 100));
    }

    protected function validateRulePayload(array $data): void
    {
        if (!empty($data['funding_source']) && $data['funding_source'] !== AgentIncentiveRule::FUNDING_APNI_FACTORY) {
            throw ValidationException::withMessages([
                'funding_source' => ['Prohibited: Only apnifactory_funded is permitted per Section 12.1 & 21. Seller deductions are forbidden.'],
            ]);
        }

        $method = $data['calculation_method'] ?? '';
        $base = $data['calculation_base'] ?? '';
        $validBases = AgentIncentiveRule::validBasesForMethod($method);

        if (empty($validBases)) {
            throw ValidationException::withMessages([
                'calculation_method' => ["Unsupported calculation method '{$method}'."],
            ]);
        }

        if (!in_array($base, $validBases, true)) {
            throw ValidationException::withMessages([
                'calculation_base' => ["Calculation base '{$base}' is incompatible with method '{$method}'."],
            ]);
        }

        if (in_array($method, [AgentIncentiveRule::METHOD_PERCENTAGE_ORDER, AgentIncentiveRule::METHOD_PERCENTAGE_COMMISSION], true)) {
            $rate = $data['rate_percentage'] ?? null;
            if ($rate === null || $rate === '' || (float)$rate <= 0 || (float)$rate > 100) {
                throw ValidationException::withMessages([
                    'rate_percentage' => ['A percentage rate strictly between 0 and 100 is required for percentage-based methods.'],
                ]);
            }
            if (!empty($data['fixed_amount']) && (float)$data['fixed_amount'] > 0) {
                throw ValidationException::withMessages([
                    'fixed_amount' => ['Fixed amount cannot be combined with percentage calculation method.'],
                ]);
            }
        } else {
            $fixed = $data['fixed_amount'] ?? null;
            if ($fixed === null || $fixed === '' || (float)$fixed <= 0) {
                throw ValidationException::withMessages([
                    'fixed_amount' => ['A positive fixed amount (> 0) is required for fixed_amount rules.'],
                ]);
            }
        }

        if (isset($data['max_cap_amount']) && $data['max_cap_amount'] !== null && $data['max_cap_amount'] !== '' && (float)$data['max_cap_amount'] <= 0) {
            throw ValidationException::withMessages([
                'max_cap_amount' => ['Maximum cap amount must be greater than zero when specified.'],
            ]);
        }

        $validRounding = [
            AgentIncentiveRule::ROUND_HALF_UP_2,
            AgentIncentiveRule::ROUND_FLOOR_0,
            AgentIncentiveRule::ROUND_CEIL_0,
            AgentIncentiveRule::ROUND_NEAREST_RUPEE,
        ];
        if (!empty($data['rounding_rule']) && !in_array($data['rounding_rule'], $validRounding, true)) {
            throw ValidationException::withMessages([
                'rounding_rule' => ['Invalid rounding rule.'],
            ]);
        }

        $validTaxes = [
            AgentIncentiveRule::TAX_PENDING_DECISION,
            AgentIncentiveRule::TAX_EXCLUSIVE_NO_GST,
            AgentIncentiveRule::TAX_TDS_CONFIGURABLE,
            AgentIncentiveRule::TAX_GST_INCLUSIVE,
        ];
        if (!empty($data['tax_treatment_code']) && !in_array($data['tax_treatment_code'], $validTaxes, true)) {
            throw ValidationException::withMessages([
                'tax_treatment_code' => ['Invalid tax treatment selection.'],
            ]);
        }

        if (($data['tax_treatment_code'] ?? '') === AgentIncentiveRule::TAX_TDS_CONFIGURABLE) {
            $tds = $data['tds_percentage'] ?? null;
            if ($tds === null || $tds === '' || (float)$tds < 0 || (float)$tds > 50) {
                throw ValidationException::withMessages([
                    'tds_percentage' => ['Explicit TDS percentage (0-50) is required when tds_applicable_configurable is chosen.'],
                ]);
            }
        }

        if (empty($data['effective_from'])) {
            throw ValidationException::withMessages([
                'effective_from' => ['Effective-from timestamp is required.'],
            ]);
        }

        if (!empty($data['effective_to'])) {
            if (Carbon::parse($data['effective_to'])->lte(Carbon::parse($data['effective_from']))) {
                throw ValidationException::withMessages([
                    'effective_to' => ['Effective-to date must be strictly after effective-from date.'],
                ]);
            }
        }
    }

    protected function validateCompletenessForActivation(AgentIncentiveRule $rule): void
    {
        if (empty($rule->rounding_rule)) {
            throw ValidationException::withMessages([
                'rounding_rule' => ['Rounding rule must be explicitly configured before activation (will not invent default).'],
            ]);
        }

        if ($rule->tax_treatment_code === AgentIncentiveRule::TAX_PENDING_DECISION) {
            throw ValidationException::withMessages([
                'tax_treatment_code' => ['Tax treatment cannot remain pending_tax_decision upon rule activation.'],
            ]);
        }

        if (empty($rule->agreement_ref)) {
            throw ValidationException::withMessages([
                'agreement_ref' => ['Approved commercial agreement reference is required before rule activation.'],
            ]);
        }

        $this->validateRulePayload($rule->toArray());
    }

    protected function applyRounding(float $amount, ?string $roundingRule): float
    {
        return match ($roundingRule) {
            AgentIncentiveRule::ROUND_FLOOR_0       => (float)floor($amount),
            AgentIncentiveRule::ROUND_CEIL_0        => (float)ceil($amount),
            AgentIncentiveRule::ROUND_NEAREST_RUPEE => (float)round($amount, 0, PHP_ROUND_HALF_UP),
            default                                 => round($amount, 2, PHP_ROUND_HALF_UP),
        };
    }
}

