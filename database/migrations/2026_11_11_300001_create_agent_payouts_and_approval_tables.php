<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Milestone M6 Phase 6.3: Incentive Approval Workflow, Agent Payout Batches,
     * Double-Payment DB Locks, and Reconciliation Exception Tracking.
     */
    public function up(): void
    {
        // 1. Create Agent Payout Records Table (strictly platform-funded Apni Factory expense)
        if (!Schema::hasTable('agent_payouts')) {
            Schema::create('agent_payouts', function (Blueprint $table) {
                $table->id();
                $table->string('payout_ref', 64)->unique();
                $table->unsignedBigInteger('field_agent_id')->index();
                $table->string('currency', 10)->default('INR');

                // Decimal financial totals reconciled to included agent_payout_items
                $table->decimal('gross_incentive_amount', 14, 2)->default(0.00);
                $table->decimal('approved_adjustments_amount', 14, 2)->default(0.00);
                $table->decimal('withholding_tax_rate', 6, 4)->nullable()
                    ->comment('Applied ONLY when legally approved; never invented');
                $table->decimal('withholding_tax_amount', 14, 2)->default(0.00);
                $table->decimal('net_payable_amount', 14, 2)->default(0.00);

                // Governance & accounting designation
                $table->string('funding_expense_account', 64)->default('apnifactory_platform_expense');
                $table->enum('payment_method', [
                    'unconfigured_awaiting_policy',
                    'manual_bank_transfer',
                    'manual_upi',
                    'approved_bank_api',
                ])->default('unconfigured_awaiting_policy');

                $table->enum('status', [
                    'draft_awaiting_policy',
                    'pending_authorization',
                    'authorized',
                    'processing_initiated',
                    'paid_confirmed',
                    'failed',
                    'rejected',
                    'cancelled',
                    'reconciliation_exception',
                ])->default('draft_awaiting_policy')->index();

                // Encrypted payout destination snapshot (never exposed unmasked in list APIs)
                $table->text('payout_destination_snapshot')->nullable();

                // Authoritative external confirmation & reconciliation metadata
                $table->string('external_transaction_ref', 120)->nullable()->unique();
                $table->string('external_provider_name', 64)->nullable();
                $table->string('payment_confirmation_source', 64)->nullable()
                    ->comment('verified_bank_statement, approved_provider_callback, etc.');
                $table->enum('reconciliation_status', [
                    'unreconciled',
                    'matched',
                    'exception_mismatch',
                    'duplicate_detected',
                    'unmatched_external_ref',
                    'reversed',
                ])->default('unreconciled')->index();
                $table->timestamp('reconciled_at')->nullable();
                $table->unsignedBigInteger('reconciled_by_user_id')->nullable();
                $table->text('reconciliation_notes')->nullable();

                // Idempotency protection for API creation & provider callback replay
                $table->string('idempotency_key', 120)->nullable()->unique();
                $table->string('callback_event_id', 120)->nullable()->unique();

                // Actor & execution timeline
                $table->unsignedBigInteger('initiated_by_user_id')->nullable()->index();
                $table->timestamp('initiated_at')->nullable();
                $table->unsignedBigInteger('authorized_by_user_id')->nullable()->index();
                $table->timestamp('authorized_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->date('payment_date')->nullable();

                $table->text('failure_reason')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('admin_notes')->nullable()
                    ->comment('Confidential internal finance notes - excluded from agent portal');
                $table->timestamps();

                $table->index(['field_agent_id', 'status'], 'idx_agent_payout_status');
                $table->foreign('field_agent_id')->references('id')->on('field_agents')->cascadeOnDelete();
                $table->foreign('initiated_by_user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('authorized_by_user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('reconciled_by_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 2. Extend existing Phase 6.2 `agent_incentive_ledgers` for review/hold/rejection & payout link
        if (Schema::hasTable('agent_incentive_ledgers')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE agent_incentive_ledgers MODIFY COLUMN status ENUM('pending_review','calculated_pending_approval','on_hold','approved','rejected','partially_reversed','reversed','cancelled','paid') NOT NULL DEFAULT 'calculated_pending_approval'");
            }

            Schema::table('agent_incentive_ledgers', function (Blueprint $table) {
                if (!Schema::hasColumn('agent_incentive_ledgers', 'reviewed_by_user_id')) {
                    $table->unsignedBigInteger('reviewed_by_user_id')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('agent_incentive_ledgers', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
                }
                if (!Schema::hasColumn('agent_incentive_ledgers', 'hold_reason')) {
                    $table->text('hold_reason')->nullable()->after('reviewed_at');
                }
                if (!Schema::hasColumn('agent_incentive_ledgers', 'rejected_by_user_id')) {
                    $table->unsignedBigInteger('rejected_by_user_id')->nullable()->after('hold_reason');
                }
                if (!Schema::hasColumn('agent_incentive_ledgers', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by_user_id');
                }
                if (!Schema::hasColumn('agent_incentive_ledgers', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }
                if (!Schema::hasColumn('agent_incentive_ledgers', 'agent_payout_id')) {
                    $table->unsignedBigInteger('agent_payout_id')->nullable()->index()->after('payout_reference');
                    $table->foreign('agent_payout_id')->references('id')->on('agent_payouts')->nullOnDelete();
                }
            });
        }

        // 3. Normalized Payout Line Items with DB-level Double-Payment Lock
        if (!Schema::hasTable('agent_payout_items')) {
            Schema::create('agent_payout_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agent_payout_id')->index();
                $table->unsignedBigInteger('agent_incentive_ledger_id')->index();

                // Nullable unique lock: prevents the same incentive ledger entry from being included
                // in two concurrent/active/paid payouts at the database constraint level.
                $table->unsignedBigInteger('active_ledger_lock')->nullable()->unique()
                    ->comment('Equals agent_incentive_ledger_id while active or paid; NULL only on failed/rejected/cancelled payout');

                $table->unsignedBigInteger('parent_order_id')->index();
                $table->unsignedTinyInteger('eligible_order_sequence');
                $table->decimal('ledger_gross_amount', 12, 2);
                $table->decimal('adjustment_amount', 12, 2)->default(0.00);
                $table->decimal('item_net_payable_amount', 12, 2);
                $table->timestamps();

                $table->foreign('agent_payout_id')->references('id')->on('agent_payouts')->cascadeOnDelete();
                $table->foreign('agent_incentive_ledger_id')->references('id')->on('agent_incentive_ledgers')->cascadeOnDelete();
                $table->foreign('parent_order_id')->references('id')->on('orders')->cascadeOnDelete();
            });
        }

        // 4. Financial Reconciliation Exceptions Queue (Stage 7)
        if (!Schema::hasTable('agent_payout_reconciliation_exceptions')) {
            Schema::create('agent_payout_reconciliation_exceptions', function (Blueprint $table) {
                $table->id();
                $table->string('exception_ref', 64)->unique();
                $table->unsignedBigInteger('agent_payout_id')->nullable();
                $table->unsignedBigInteger('agent_incentive_ledger_id')->nullable();
                $table->unsignedBigInteger('field_agent_id')->nullable();

                $table->enum('exception_type', [
                    'failed_payout',
                    'duplicate_payment_detected',
                    'unmatched_external_reference',
                    'amount_mismatch',
                    'post_payout_refund_reversal',
                    'pre_payout_reversal_impacted',
                    'pending_finance_review',
                ]);

                $table->string('external_reference', 120)->nullable();
                $table->decimal('expected_amount', 12, 2)->nullable();
                $table->decimal('reported_amount', 12, 2)->nullable();

                $table->enum('status', ['open', 'under_review', 'resolved', 'dismissed'])->default('open');
                $table->text('description');
                $table->text('resolution_notes')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('resolved_by_user_id')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index('agent_payout_id', 'idx_apre_payout');
                $table->index('agent_incentive_ledger_id', 'idx_apre_ledger');
                $table->index('field_agent_id', 'idx_apre_agent');
                $table->index('exception_type', 'idx_apre_type');
                $table->index('external_reference', 'idx_apre_extref');
                $table->index('status', 'idx_apre_status');

                $table->foreign('agent_payout_id', 'fk_apre_payout')->references('id')->on('agent_payouts')->nullOnDelete();
                $table->foreign('agent_incentive_ledger_id', 'fk_apre_ledger')->references('id')->on('agent_incentive_ledgers')->nullOnDelete();
                $table->foreign('field_agent_id', 'fk_apre_agent')->references('id')->on('field_agents')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_payout_reconciliation_exceptions');
        Schema::dropIfExists('agent_payout_items');

        if (Schema::hasTable('agent_incentive_ledgers')) {
            Schema::table('agent_incentive_ledgers', function (Blueprint $table) {
                if (Schema::hasColumn('agent_incentive_ledgers', 'agent_payout_id')) {
                    $table->dropForeign(['agent_payout_id']);
                    $table->dropColumn('agent_payout_id');
                }
                foreach (['reviewed_by_user_id', 'reviewed_at', 'hold_reason', 'rejected_by_user_id', 'rejected_at', 'rejection_reason'] as $col) {
                    if (Schema::hasColumn('agent_incentive_ledgers', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('agent_payouts');
    }
};
