<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CommissionAuditLog;
use App\Models\CommissionRule;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\Product;
use App\Models\SellerListing;
use App\Models\SubCatgory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommissionCalculationService
{
    /**
     * Proposed precedence hierarchy priority map (1 is highest priority).
     */
    public const PRECEDENCE_PRIORITY = [
        'sku'         => 1,
        'seller'      => 2,
        'subcategory' => 3,
        'category'    => 4,
        'global'      => 5,
    ];

    /**
     * Detect overlapping active or approved rules with the same scope.
     * Prevents ambiguous conflicting rules.
     */
    public function detectConflicts(CommissionRule $candidate): array
    {
        $query = CommissionRule::where('scope_type', $candidate->scope_type)
            ->whereIn('status', ['active', 'scheduled', 'approved']);

        if ($candidate->id) {
            $query->where('id', '!=', $candidate->id);
        }

        if ($candidate->scope_id !== null) {
            $query->where('scope_id', $candidate->scope_id);
        } else {
            $query->whereNull('scope_id');
        }

        $effectiveFrom = $candidate->effective_from;
        $effectiveTo = $candidate->effective_to;

        // Check date overlap
        $query->where(function ($q) use ($effectiveFrom, $effectiveTo) {
            $q->where(function ($sub) use ($effectiveFrom, $effectiveTo) {
                if ($effectiveTo !== null) {
                    $sub->where('effective_from', '<=', $effectiveTo);
                }
                $sub->where(function ($toCheck) use ($effectiveFrom) {
                    $toCheck->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $effectiveFrom);
                });
            });
        });

        $conflicts = $query->get();

        return $conflicts->map(function ($conf) {
            return [
                'rule_id'        => $conf->id,
                'rule_code'      => $conf->rule_code,
                'name'           => $conf->name,
                'effective_from' => $conf->effective_from->toIso8601String(),
                'effective_to'   => $conf->effective_to ? $conf->effective_to->toIso8601String() : 'open-ended',
                'status'         => $conf->status,
                'message'        => "Overlaps with existing {$conf->scope_type} rule '{$conf->rule_code}' ({$conf->name})",
            ];
        })->toArray();
    }

    /**
     * Resolve the single applicable commission rule for a SKU deterministically
     * based on the 5-tier precedence hierarchy.
     */
    public function resolveRuleForSku(SellerListing $listing, ?Carbon $timestamp = null): array
    {
        $time = $timestamp ? $timestamp->copy() : now();
        $listing->loadMissing(['product.category', 'product.subcategory', 'company']);
        $product = $listing->product;
        $company = $listing->company;

        // Priority 1: SKU-specific agreement
        $skuRule = CommissionRule::active($time)
            ->where('scope_type', 'sku')
            ->where('scope_id', $listing->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($skuRule) {
            return $this->formatResolvedRule($skuRule, 1, "Resolved via Priority 1 (SKU-specific agreement for SKU: {$listing->seller_sku})");
        }

        // Priority 2: Seller/Manufacturer-specific agreement
        if ($company) {
            $sellerRule = CommissionRule::active($time)
                ->where('scope_type', 'seller')
                ->where('scope_id', $company->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($sellerRule) {
                return $this->formatResolvedRule($sellerRule, 2, "Resolved via Priority 2 (Seller agreement for: {$company->name})");
            }
        }

        // Priority 3: Subcategory-level rule
        if ($product && $product->subcategory_id) {
            $subcatRule = CommissionRule::active($time)
                ->where('scope_type', 'subcategory')
                ->where('scope_id', $product->subcategory_id)
                ->orderBy('id', 'desc')
                ->first();

            if ($subcatRule) {
                return $this->formatResolvedRule($subcatRule, 3, "Resolved via Priority 3 (Subcategory rule for Subcategory ID: {$product->subcategory_id})");
            }
        }

        // Priority 4: Category-level rule
        if ($product && $product->category_id) {
            $catRule = CommissionRule::active($time)
                ->where('scope_type', 'category')
                ->where('scope_id', $product->category_id)
                ->orderBy('id', 'desc')
                ->first();

            if ($catRule) {
                return $this->formatResolvedRule($catRule, 4, "Resolved via Priority 4 (Category rule for Category ID: {$product->category_id})");
            }
        }

        // Priority 5: Global default rule
        $globalRule = CommissionRule::active($time)
            ->where('scope_type', 'global')
            ->whereNull('scope_id')
            ->orderBy('id', 'desc')
            ->first();

        if ($globalRule) {
            return $this->formatResolvedRule($globalRule, 5, 'Resolved via Priority 5 (Global Marketplace Default Rule)');
        }

        // Fallback: Default listing/company commission if no centralized rule matched
        $fallbackPct = (float)($listing->commission_rate ?: ($company ? $company->comission : 10.00));
        return [
            'rule'                  => null,
            'rule_id'               => null,
            'rule_code'             => 'COMM-DEFAULT-LEGACY',
            'rule_name'             => 'Default Listing Legacy Commission',
            'scope_type'            => 'listing_default',
            'scope_id'              => $listing->id,
            'priority'              => 6,
            'commission_percentage' => $fallbackPct,
            'fixed_fee'             => 0.00,
            'calculation_basis'     => 'taxable_product_value',
            'gst_rate'              => 18.00,
            'is_gst_inclusive'      => false,
            'reason'                => "Fallback to seller listing markup ({$fallbackPct}%)",
        ];
    }

    /**
     * Format resolved rule payload.
     */
    protected function formatResolvedRule(CommissionRule $rule, int $priority, string $reason): array
    {
        return [
            'rule'                  => $rule,
            'rule_id'               => $rule->id,
            'rule_code'             => $rule->rule_code,
            'rule_name'             => $rule->name,
            'scope_type'            => $rule->scope_type,
            'scope_id'              => $rule->scope_id,
            'priority'              => $priority,
            'commission_percentage' => (float)$rule->commission_percentage,
            'fixed_fee'             => (float)$rule->fixed_fee,
            'calculation_basis'     => $rule->calculation_basis,
            'gst_rate'              => (float)$rule->gst_rate,
            'is_gst_inclusive'      => (bool)$rule->is_gst_inclusive,
            'reason'                => $reason,
        ];
    }

    /**
     * Calculate line-item commission and platform fee breakdown.
     * Formula:
     * - Base Amount = quantity * unit_price (taxable product value excluding tax)
     * - Commission Amount = (Base Amount * Commission % / 100) + Fixed Fee
     * - GST on Commission = Commission Amount * (GST Rate / 100)
     * - Total Platform Fee = Commission Amount + GST on Commission
     * - Seller Payable = Base Amount - Commission Amount
     */
    public function calculateLineCommission(
        SellerListing $listing,
        int $quantity,
        float $unitPrice,
        ?CommissionRule $explicitRule = null,
        ?Carbon $timestamp = null
    ): array {
        $resolved = $explicitRule
            ? $this->formatResolvedRule($explicitRule, self::PRECEDENCE_PRIORITY[$explicitRule->scope_type] ?? 1, 'Explicit Rule Override')
            : $this->resolveRuleForSku($listing, $timestamp);

        $qty = max(1, $quantity);
        $baseAmount = round($qty * $unitPrice, 2);

        $commPct = $resolved['commission_percentage'];
        $fixedFee = $resolved['fixed_fee'];
        $gstRate = $resolved['gst_rate'];
        $isGstInclusive = $resolved['is_gst_inclusive'];

        if ($isGstInclusive) {
            // If commission rate is GST-inclusive
            $grossComm = round(($baseAmount * $commPct / 100) + $fixedFee, 2);
            $commissionAmount = round($grossComm / (1 + ($gstRate / 100)), 2);
            $gstAmount = round($grossComm - $commissionAmount, 2);
            $totalPlatformFee = $grossComm;
        } else {
            // Standard B2B: Commission + 18% GST on platform service
            $commissionAmount = round(($baseAmount * $commPct / 100) + $fixedFee, 2);
            $gstAmount = round($commissionAmount * ($gstRate / 100), 2);
            $totalPlatformFee = round($commissionAmount + $gstAmount, 2);
        }

        $sellerPayable = round($baseAmount - $commissionAmount, 2);

        return [
            'rule_id'               => $resolved['rule_id'],
            'rule_code'             => $resolved['rule_code'],
            'rule_name'             => $resolved['rule_name'],
            'rule_scope_type'       => $resolved['scope_type'],
            'rule_scope_id'         => $resolved['scope_id'],
            'priority'              => $resolved['priority'],
            'resolution_reason'     => $resolved['reason'],
            'calculation_basis'     => $resolved['calculation_basis'],
            'quantity'              => $qty,
            'unit_price'            => round($unitPrice, 2),
            'base_amount'           => $baseAmount,
            'commission_percentage' => $commPct,
            'fixed_fee'             => $fixedFee,
            'commission_amount'     => $commissionAmount,
            'gst_rate'              => $gstRate,
            'gst_amount'            => $gstAmount,
            'is_gst_inclusive'      => $isGstInclusive,
            'total_platform_fee'    => $totalPlatformFee,
            'seller_payable'        => $sellerPayable,
            'calculated_at'         => now()->toIso8601String(),
        ];
    }

    /**
     * Snapshot commission calculation at order creation time.
     * Guaranteed immutable: once written, historical order lines never change!
     */
    public function snapshotOrderCommission(
        Order $order,
        SellerListing $listing,
        int $quantity,
        float $unitPrice,
        ?int $orderDetailId = null,
        ?CommissionRule $explicitRule = null
    ): OrderCommissionSnapshot {
        // Idempotency check: avoid duplicate snapshot creation on retries
        $existing = OrderCommissionSnapshot::where('order_id', $order->id)
            ->where(function ($q) use ($orderDetailId, $listing) {
                if ($orderDetailId) {
                    $q->where('order_detail_id', $orderDetailId);
                } else {
                    $q->where('seller_listing_id', $listing->id);
                }
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        $calc = $this->calculateLineCommission($listing, $quantity, $unitPrice, $explicitRule);

        return OrderCommissionSnapshot::create([
            'order_id'              => $order->id,
            'order_detail_id'       => $orderDetailId,
            'orderno'               => $order->orderno,
            'seller_user_id'        => $listing->seller_user_id,
            'company_id'            => $listing->company_id,
            'seller_listing_id'     => $listing->id,
            'product_id'            => $listing->product_id,
            'product_variant_id'    => $listing->product_variant_id,
            'commission_rule_id'    => $calc['rule_id'],
            'commission_rule_code'  => $calc['rule_code'],
            'rule_scope_type'       => $calc['rule_scope_type'],
            'rule_scope_id'         => $calc['rule_scope_id'],
            'commission_percentage' => $calc['commission_percentage'],
            'fixed_fee'             => $calc['fixed_fee'],
            'calculation_basis'     => $calc['calculation_basis'],
            'quantity'              => $calc['quantity'],
            'unit_price'            => $calc['unit_price'],
            'base_amount'           => $calc['base_amount'],
            'commission_amount'     => $calc['commission_amount'],
            'gst_rate'              => $calc['gst_rate'],
            'gst_amount'            => $calc['gst_amount'],
            'total_platform_fee'    => $calc['total_platform_fee'],
            'seller_payable'        => $calc['seller_payable'],
            'tax_treatment'         => [
                'gst_rate'          => $calc['gst_rate'],
                'is_gst_inclusive'  => $calc['is_gst_inclusive'],
                'resolution_reason' => $calc['resolution_reason'],
            ],
            'snapshot_timestamp'    => now(),
        ]);
    }

    /**
     * Create a new commission rule.
     */
    public function createRule(array $data, User $creator): CommissionRule
    {
        $scopeType = $data['scope_type'] ?? 'global';
        $scopeId = $scopeType === 'global' ? null : ($data['scope_id'] ?? null);

        // Scope relationship validation
        if ($scopeType !== 'global' && empty($scopeId)) {
            throw new \InvalidArgumentException("Scope ID is required for scope type '{$scopeType}'.");
        }

        $effectiveFrom = isset($data['effective_from'])
            ? Carbon::parse($data['effective_from'])
            : now();

        $effectiveTo = !empty($data['effective_to'])
            ? Carbon::parse($data['effective_to'])
            : null;

        if ($effectiveTo && $effectiveTo <= $effectiveFrom) {
            throw new \InvalidArgumentException('Effective-to timestamp must be after effective-from timestamp.');
        }

        $ruleCode = 'CR-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        $initialStatus = $data['status'] ?? 'active';

        return DB::transaction(function () use (
            $data,
            $creator,
            $scopeType,
            $scopeId,
            $effectiveFrom,
            $effectiveTo,
            $ruleCode,
            $initialStatus
        ) {
            $rule = CommissionRule::create([
                'rule_code'             => $ruleCode,
                'name'                  => $data['name'] ?? "Commission Rule {$ruleCode}",
                'scope_type'            => $scopeType,
                'scope_id'              => $scopeId,
                'commission_percentage' => (float)($data['commission_percentage'] ?? 0.0),
                'fixed_fee'             => (float)($data['fixed_fee'] ?? 0.0),
                'calculation_basis'     => $data['calculation_basis'] ?? 'taxable_product_value',
                'gst_rate'              => (float)($data['gst_rate'] ?? 18.00),
                'is_gst_inclusive'      => (bool)($data['is_gst_inclusive'] ?? false),
                'effective_from'        => $effectiveFrom,
                'effective_to'          => $effectiveTo,
                'status'                => $initialStatus,
                'created_by_user_id'    => $creator->id,
                'agreement_ref'         => $data['agreement_ref'] ?? null,
                'remarks'               => $data['remarks'] ?? null,
            ]);

            // Conflict detection
            $conflicts = $this->detectConflicts($rule);
            if (!empty($conflicts)) {
                CommissionAuditLog::create([
                    'commission_rule_id' => $rule->id,
                    'actor_user_id'      => $creator->id,
                    'event_type'         => 'conflict_detected',
                    'new_values'         => $conflicts,
                    'notes'              => 'Overlapping commission rule detected during creation.',
                ]);
            }

            // Audit log
            CommissionAuditLog::create([
                'commission_rule_id' => $rule->id,
                'actor_user_id'      => $creator->id,
                'event_type'         => 'created',
                'new_values'         => $rule->toArray(),
                'notes'              => 'Commission rule created.',
            ]);

            // Notify affected seller if seller-specific or SKU-specific
            $this->notifyAffectedSellers($rule, 'created');

            return $rule;
        });
    }

    /**
     * Approve pending commission rule.
     */
    public function approveRule(CommissionRule $rule, User $approver): CommissionRule
    {
        if ($rule->status !== 'pending_approval') {
            throw new \DomainException("Rule {$rule->rule_code} is not awaiting approval (current state: {$rule->status}).");
        }

        // Check for active conflicts before activation
        $conflicts = $this->detectConflicts($rule);
        if (!empty($conflicts)) {
            CommissionAuditLog::create([
                'commission_rule_id' => $rule->id,
                'actor_user_id'      => $approver->id,
                'event_type'         => 'conflict_detected',
                'new_values'         => $conflicts,
                'notes'              => 'Approval blocked due to active overlapping rule conflict.',
            ]);
            throw new \DomainException("Cannot approve rule {$rule->rule_code}: Conflict detected with existing active rule.");
        }

        $prev = $rule->toArray();
        $targetStatus = $rule->effective_from->isFuture() ? 'scheduled' : 'active';

        $rule->update([
            'status'              => $targetStatus,
            'approved_by_user_id' => $approver->id,
        ]);

        CommissionAuditLog::create([
            'commission_rule_id' => $rule->id,
            'actor_user_id'      => $approver->id,
            'event_type'         => 'approved',
            'previous_values'    => $prev,
            'new_values'         => $rule->fresh()->toArray(),
            'notes'              => "Rule approved by {$approver->name} and transitioned to {$targetStatus}.",
        ]);

        $this->notifyAffectedSellers($rule, 'approved');

        return $rule->fresh();
    }

    /**
     * Reject pending commission rule.
     */
    public function rejectRule(CommissionRule $rule, User $approver, string $reason): CommissionRule
    {
        if ($rule->status !== 'pending_approval') {
            throw new \DomainException("Rule {$rule->rule_code} is not awaiting approval.");
        }

        $prev = $rule->toArray();
        $rule->update([
            'status'              => 'deactivated',
            'approved_by_user_id' => $approver->id,
            'rejection_reason'    => $reason,
        ]);

        CommissionAuditLog::create([
            'commission_rule_id' => $rule->id,
            'actor_user_id'      => $approver->id,
            'event_type'         => 'rejected',
            'previous_values'    => $prev,
            'new_values'         => $rule->fresh()->toArray(),
            'notes'              => "Rule rejected: {$reason}",
        ]);

        return $rule->fresh();
    }

    /**
     * Deactivate an active or scheduled commission rule.
     */
    public function deactivateRule(CommissionRule $rule, User $actor, ?string $reason = null): CommissionRule
    {
        $prev = $rule->toArray();
        $rule->update([
            'status'  => 'deactivated',
            'remarks' => $reason ? ($rule->remarks . " | Deactivated: {$reason}") : $rule->remarks,
        ]);

        CommissionAuditLog::create([
            'commission_rule_id' => $rule->id,
            'actor_user_id'      => $actor->id,
            'event_type'         => 'deactivated',
            'previous_values'    => $prev,
            'new_values'         => $rule->fresh()->toArray(),
            'notes'              => $reason ?: 'Rule manually deactivated.',
        ]);

        return $rule->fresh();
    }

    /**
     * Dispatch in-app notifications to affected seller(s).
     */
    protected function notifyAffectedSellers(CommissionRule $rule, string $action): void
    {
        try {
            $sellerIds = [];

            if ($rule->scope_type === 'seller' && $rule->scope_id) {
                $company = Company::find($rule->scope_id);
                if ($company && $company->user_id) {
                    $sellerIds[] = $company->user_id;
                }
            } elseif ($rule->scope_type === 'sku' && $rule->scope_id) {
                $listing = SellerListing::find($rule->scope_id);
                if ($listing && $listing->seller_user_id) {
                    $sellerIds[] = $listing->seller_user_id;
                }
            }

            foreach (array_unique($sellerIds) as $sellerId) {
                Notification::create([
                    'user_id'     => $sellerId,
                    'customer_id' => $sellerId,
                    'title'       => "Commission Agreement Update: {$rule->name}",
                    'msg'         => sprintf(
                        "Commission rate is set to %.2f%% (effective from %s) under agreement %s.",
                        $rule->commission_percentage,
                        $rule->effective_from->format('d M Y'),
                        $rule->agreement_ref ?: $rule->rule_code
                    ),
                    'msgread'     => 0,
                    'type'        => 'seller',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("Commission notification dispatch failed: {$e->getMessage()}");
        }
    }
}

