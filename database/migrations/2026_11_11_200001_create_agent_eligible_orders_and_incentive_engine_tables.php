<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Milestone M6 Phase 6.2: Eligible Order Tracking, Configurable Incentive Rules,
     * Immutable Apni Factory-Funded Incentive Ledger & Failure/Audit Logs.
     */
    public function up(): void
    {
        // 1. Versioned, Effective-Dated Incentive Rules (No invented default rates or tax rules)
        if (!Schema::hasTable('agent_incentive_rules')) {
            Schema::create('agent_incentive_rules', function (Blueprint $table) {
                $table->id();
                $table->string('rule_code', 40)->index()
                    ->comment('Stable business rule identifier across versions e.g. INC-RULE-001');
                $table->unsignedInteger('version')->default(1);
                $table->unsignedBigInteger('parent_rule_id')->nullable()->index();
                $table->string('name', 255);

                // Governance: Section 12.1 & 21 platform-funded only; seller-funded prohibited
                $table->enum('funding_source', ['apnifactory_funded'])->default('apnifactory_funded');

                // BRD Alternatives A, B, C
                $table->enum('calculation_method', [
                    'percentage_of_order_value',
                    'percentage_of_platform_commission',
                    'fixed_amount',
                ]);
                $table->enum('calculation_base', [
                    'taxable_order_value',
                    'gross_order_value',
                    'platform_commission_ex_gst',
                    'platform_commission_incl_gst',
                    'fixed_per_order',
                ]);

                $table->decimal('rate_percentage', 8, 4)->nullable()
                    ->comment('Required for percentage methods A & B (0 < rate <= 100)');
                $table->decimal('fixed_amount', 12, 2)->nullable()
                    ->comment('Required for fixed_amount method C (> 0)');
                $table->decimal('max_cap_amount', 12, 2)->nullable()
                    ->comment('Optional maximum incentive cap per eligible order');

                // Must be explicitly specified when creating/activating a rule
                $table->enum('rounding_rule', ['half_up_2', 'floor_0', 'ceil_0', 'nearest_rupee'])->nullable();
                $table->enum('tax_treatment_code', [
                    'pending_tax_decision',
                    'exclusive_no_gst',
                    'tds_applicable_configurable',
                    'gst_inclusive',
                ])->default('pending_tax_decision');
                $table->decimal('tds_percentage', 5, 2)->nullable();
                $table->string('tax_notes', 255)->nullable();

                $table->timestamp('effective_from');
                $table->timestamp('effective_to')->nullable();

                $table->enum('status', [
                    'draft',
                    'pending_approval',
                    'approved',
                    'active',
                    'inactive',
                    'superseded',
                    'rejected',
                ])->default('draft')->index();

                $table->string('agreement_ref', 120)->nullable();
                $table->text('reason')->nullable();

                // Audit & Dual Business + Finance Approval Tracking
                $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
                $table->unsignedBigInteger('updated_by_user_id')->nullable()->index();
                $table->unsignedBigInteger('business_approved_by_user_id')->nullable();
                $table->timestamp('business_approved_at')->nullable();
                $table->string('business_approval_ref', 120)->nullable();
                $table->unsignedBigInteger('finance_approved_by_user_id')->nullable();
                $table->timestamp('finance_approved_at')->nullable();
                $table->string('finance_approval_ref', 120)->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('deactivated_by_user_id')->nullable();
                $table->timestamp('deactivated_at')->nullable();
                $table->text('deactivation_reason')->nullable();
                $table->timestamps();

                $table->unique(['rule_code', 'version'], 'uq_incentive_rule_code_version');
                $table->index(['status', 'effective_from', 'effective_to'], 'idx_incentive_rule_effective');
                $table->foreign('parent_rule_id')->references('id')->on('agent_incentive_rules')->nullOnDelete();
                $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('business_approved_by_user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('finance_approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 2. Buyer-Level Eligible Order Tracking (0/3..3/3 across all sellers)
        if (!Schema::hasTable('agent_eligible_orders')) {
            Schema::create('agent_eligible_orders', function (Blueprint $table) {
                $table->id();
                $table->string('eligibility_ref', 64)->unique();
                $table->unsignedBigInteger('customer_id')->index()
                    ->comment('Buyer/dealer account against whom the 1..3 slots are counted');
                $table->unsignedBigInteger('field_agent_id')->index();
                $table->unsignedBigInteger('buyer_agent_attribution_id')->index();

                // Enforces ONE tracking record per parent order so multi-seller orders cannot count per seller
                $table->unsignedBigInteger('parent_order_id')->unique();
                $table->string('orderno', 100)->index();

                // Linked child orders / seller fulfilment references for audit & reporting
                $table->json('seller_fulfilment_refs')->nullable();
                $table->unsignedSmallInteger('seller_count')->default(1);

                $table->enum('eligibility_status', [
                    'pending_eligibility',
                    'pending_review',
                    'eligible',
                    'ineligible_unpaid',
                    'ineligible_cancelled',
                    'ineligible_rejected',
                    'ineligible_refunded',
                    'ineligible_fraudulent',
                    'ineligible_limit_exceeded',
                    'reversed_refunded',
                    'reversed_cancelled',
                ])->default('pending_eligibility')->index();

                // Sequence 1..3 assigned ONLY when order attains 'eligible' status
                $table->unsignedTinyInteger('sequence_number')->nullable()
                    ->comment('1, 2, or 3 when eligible (preserved on reversal)');
                $table->unsignedTinyInteger('active_slot_number')->nullable()
                    ->comment('Equals 1, 2, or 3 while occupying a buyer slot; NULL otherwise');

                $table->string('source_event_type', 64)->nullable();
                $table->string('source_event_ref', 120)->nullable();
                $table->string('idempotency_key', 120)->nullable()->unique();

                $table->timestamp('payment_verified_at')->nullable();
                $table->timestamp('fulfilled_at')->nullable();
                $table->timestamp('cooling_window_ends_at')->nullable();
                $table->timestamp('eligible_at')->nullable();

                $table->enum('incentive_generation_status', [
                    'not_applicable',
                    'pending_eligibility',
                    'blocked_unapproved_funding',
                    'blocked_no_active_rule',
                    'pending_review',
                    'generated',
                    'failed',
                ])->default('pending_eligibility')->index();
                $table->text('incentive_block_reason')->nullable();
                $table->text('evaluation_notes')->nullable();
                $table->text('admin_notes')->nullable()
                    ->comment('Confidential internal notes - never exposed to agents');
                $table->timestamps();

                // DB-level concurrency guarantee: two simultaneous events cannot allocate the same slot
                $table->unique(['customer_id', 'active_slot_number'], 'uq_customer_active_slot');
                $table->index(['field_agent_id', 'eligibility_status'], 'idx_agent_elig_status');
                $table->index(['customer_id', 'eligibility_status'], 'idx_customer_elig_status');

                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
                $table->foreign('field_agent_id')->references('id')->on('field_agents')->cascadeOnDelete();
                $table->foreign('buyer_agent_attribution_id')->references('id')->on('buyer_agent_attributions')->cascadeOnDelete();
                $table->foreign('parent_order_id')->references('id')->on('orders')->cascadeOnDelete();
            });
        }

        // 3. Immutable Apni Factory-Funded Incentive Ledger (100% separate from seller_settlements)
        if (!Schema::hasTable('agent_incentive_ledgers')) {
            Schema::create('agent_incentive_ledgers', function (Blueprint $table) {
                $table->id();
                $table->string('ledger_ref', 64)->unique();
                $table->enum('entry_type', ['earned', 'reversal', 'adjustment'])->default('earned')->index();

                // Nullable unique lock guarantees at most 1 primary 'earned' entry per parent order
                $table->unsignedBigInteger('earned_parent_order_lock')->nullable()->unique()
                    ->comment('Equals parent_order_id for earned entries; NULL for reversals/adjustments');

                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('field_agent_id')->index();
                $table->unsignedBigInteger('buyer_agent_attribution_id')->index();
                $table->unsignedBigInteger('agent_eligible_order_id')->index();
                $table->unsignedBigInteger('parent_order_id')->index();

                $table->json('seller_fulfilment_refs')->nullable();
                $table->unsignedTinyInteger('eligible_order_sequence')
                    ->comment('1, 2, or 3');
                $table->string('eligibility_event_ref', 120)->nullable()->index();
                $table->timestamp('eligibility_event_at')->nullable();

                // Rule Version Snapshot for Historical Reproducibility
                $table->unsignedBigInteger('agent_incentive_rule_id')->nullable()->index();
                $table->string('rule_code', 40);
                $table->unsignedInteger('rule_version');
                $table->string('calculation_method', 64);
                $table->string('calculation_base', 64);
                $table->decimal('calculation_base_amount', 14, 2);
                $table->decimal('applied_rate', 8, 4)->nullable();
                $table->decimal('applied_fixed_amount', 12, 2)->nullable();
                $table->decimal('max_cap_amount', 12, 2)->nullable();
                $table->boolean('cap_applied')->default(false);
                $table->string('rounding_rule', 40);
                $table->json('tax_treatment_snapshot')->nullable();

                $table->decimal('gross_incentive_amount', 12, 2);
                $table->decimal('tax_deduction_amount', 12, 2)->default(0.00);
                $table->decimal('calculated_incentive_amount', 12, 2)
                    ->comment('Signed amount: positive for earned/positive adj, negative for reversals/negative adj');

                // Platform Expense Designation (never deducted from seller settlement)
                $table->string('funding_expense_account', 64)->default('apnifactory_platform_expense');
                $table->boolean('funding_policy_approved_snapshot')->default(false);

                $table->enum('status', [
                    'pending_review',
                    'calculated_pending_approval',
                    'approved',
                    'partially_reversed',
                    'reversed',
                    'cancelled',
                    'paid',
                ])->default('calculated_pending_approval')->index();

                $table->unsignedBigInteger('reference_ledger_id')->nullable()->index();
                $table->string('reversal_or_adjustment_ref', 64)->nullable()->unique();
                $table->string('payout_reference', 100)->nullable()->index();
                $table->string('idempotency_key', 120)->nullable()->unique();

                $table->text('reason')->nullable();
                $table->text('admin_notes')->nullable()
                    ->comment('Confidential finance notes - excluded from agent dashboard/API');
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['field_agent_id', 'status'], 'idx_agent_ledger_status');
                $table->index(['customer_id', 'eligible_order_sequence'], 'idx_customer_ledger_seq');

                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
                $table->foreign('field_agent_id')->references('id')->on('field_agents')->cascadeOnDelete();
                $table->foreign('buyer_agent_attribution_id')->references('id')->on('buyer_agent_attributions')->cascadeOnDelete();
                $table->foreign('agent_eligible_order_id')->references('id')->on('agent_eligible_orders')->cascadeOnDelete();
                $table->foreign('parent_order_id')->references('id')->on('orders')->cascadeOnDelete();
                $table->foreign('agent_incentive_rule_id')->references('id')->on('agent_incentive_rules')->nullOnDelete();
                $table->foreign('reference_ledger_id')->references('id')->on('agent_incentive_ledgers')->nullOnDelete();
            });
        }

        // 4. Immutable Audit Log for Incentive Governance, Rules & Ledger Actions
        if (!Schema::hasTable('agent_incentive_audit_logs')) {
            Schema::create('agent_incentive_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 50)->index(); // rule, eligible_order, ledger, policy
                $table->unsignedBigInteger('entity_id')->index();
                $table->string('action', 80)->index();
                $table->string('previous_status', 40)->nullable();
                $table->string('new_status', 40)->nullable();
                $table->unsignedBigInteger('actor_user_id')->nullable()->index();
                $table->string('actor_name', 255)->nullable();
                $table->string('actor_role', 40)->default('system');
                $table->text('reason')->nullable();
                $table->json('previous_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 5. Event Processing, Deduplication & Failure Recovery Log
        if (!Schema::hasTable('agent_incentive_processing_logs')) {
            Schema::create('agent_incentive_processing_logs', function (Blueprint $table) {
                $table->id();
                $table->string('event_key', 140)->unique();
                $table->string('event_type', 64)->index();
                $table->unsignedBigInteger('parent_order_id')->nullable()->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->json('payload')->nullable();
                $table->enum('status', ['pending', 'processed', 'skipped', 'failed', 'retried'])
                    ->default('pending')->index();
                $table->unsignedSmallInteger('attempts')->default(1);
                $table->text('error_message')->nullable();
                $table->timestamp('last_attempted_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_incentive_processing_logs');
        Schema::dropIfExists('agent_incentive_audit_logs');
        Schema::dropIfExists('agent_incentive_ledgers');
        Schema::dropIfExists('agent_eligible_orders');
        Schema::dropIfExists('agent_incentive_rules');
    }
};

