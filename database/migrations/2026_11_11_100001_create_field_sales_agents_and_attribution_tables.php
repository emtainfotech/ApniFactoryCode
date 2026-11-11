<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Milestone M6 Phase 6.1: Field Sales Agent Master & Buyer Attribution.
     */
    public function up(): void
    {
        // 1. Field Sales Agent Master Table
        if (!Schema::hasTable('field_agents')) {
            Schema::create('field_agents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->unique()
                    ->comment('Links to existing users table for unified authentication');
                $table->string('agent_code', 40)->unique()
                    ->comment('Unique business agent identifier e.g. FSA-2026-0001');
                $table->string('name', 255);
                $table->string('email', 255)->nullable()->unique();
                $table->string('mobile', 20)->unique();
                $table->string('alternate_mobile', 20)->nullable();
                $table->string('territory', 255)->index()
                    ->comment('Assigned geographical area or cluster');
                $table->string('territory_city', 100)->nullable()->index();
                $table->string('territory_state', 100)->nullable()->index();

                $table->enum('status', ['pending', 'active', 'inactive', 'suspended'])
                    ->default('active')->index();
                $table->date('joining_date')->nullable();

                $table->unsignedBigInteger('supervisor_user_id')->nullable()->index();
                $table->string('supervisor_name', 255)->nullable();

                // Confidential Payout & KYC/Contract Governance Fields (encrypted / restricted)
                $table->text('payout_details')->nullable()
                    ->comment('Encrypted confidential bank/UPI details per company policy');
                $table->enum('kyc_status', ['not_submitted', 'pending_review', 'verified', 'rejected', 'expired'])
                    ->default('not_submitted')->index();
                $table->enum('contract_status', ['not_signed', 'pending', 'active', 'terminated', 'expired'])
                    ->default('not_signed')->index();
                $table->string('kyc_reference', 120)->nullable()
                    ->comment('Restricted reference to company KYC/contract file');
                $table->text('notes')->nullable()
                    ->comment('Confidential internal admin notes - never exposed to agents');

                $table->timestamp('deactivated_at')->nullable();
                $table->text('deactivation_reason')->nullable();

                $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
                $table->unsignedBigInteger('updated_by_user_id')->nullable()->index();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('supervisor_user_id')->references('id')->on('users')->nullOnDelete();
                $table->index(['status', 'territory'], 'idx_agent_status_territory');
            });
        }

        // 2. Agent Status & Profile Audit History (Immutable)
        if (!Schema::hasTable('field_agent_status_history')) {
            Schema::create('field_agent_status_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('field_agent_id')->index();
                $table->string('action', 60)->index();
                $table->string('previous_status', 40)->nullable();
                $table->string('new_status', 40)->nullable();
                $table->unsignedBigInteger('actor_user_id')->nullable()->index();
                $table->string('actor_name', 255)->nullable();
                $table->string('actor_role', 40)->default('admin');
                $table->text('reason')->nullable();
                $table->json('previous_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('field_agent_id')->references('id')->on('field_agents')->cascadeOnDelete();
            });
        }

        // 3. Buyer / Dealer Attribution Records (One Active Primary Agent per Buyer, History Preserved)
        if (!Schema::hasTable('buyer_agent_attributions')) {
            Schema::create('buyer_agent_attributions', function (Blueprint $table) {
                $table->id();
                $table->string('attribution_ref', 64)->unique();
                $table->unsignedBigInteger('customer_id')->index()
                    ->comment('Linked buyer/dealer account in customers table');
                $table->unsignedBigInteger('field_agent_id')->index();

                // Nullable unique lock enforces max 1 active/pending attribution per buyer at DB level
                // while allowing unlimited preserved historical (superseded/rejected/revoked) records.
                $table->unsignedBigInteger('active_customer_lock')->nullable()->unique()
                    ->comment('Equals customer_id when status in (pending, active); NULL when historical');

                $table->enum('status', ['pending', 'active', 'rejected', 'superseded', 'revoked'])
                    ->default('active')->index();
                $table->enum('approval_status', ['pending_approval', 'approved', 'rejected'])
                    ->default('approved')->index();
                $table->enum('attribution_source', ['onboarding', 'activation', 'admin_linking', 'reassignment'])
                    ->default('admin_linking');

                $table->string('evidence_reference', 255)->nullable();
                $table->text('remarks')->nullable();
                $table->text('admin_notes')->nullable()
                    ->comment('Confidential internal admin notes - excluded from agent dashboard');

                $table->unsignedBigInteger('attributed_by_user_id')->index();
                $table->timestamp('attributed_at')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('rejected_by_user_id')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();

                // Documented Reassignment Linkage (non-destructive history)
                $table->unsignedBigInteger('superseded_by_attribution_id')->nullable()->index();
                $table->unsignedBigInteger('reassigned_from_attribution_id')->nullable()->index();
                $table->text('reassignment_reason')->nullable();
                $table->boolean('is_exception_reassignment')->default(false);
                $table->string('exception_approval_ref', 120)->nullable();

                // Schema placeholder for BRD 3-order scheme; calculation deferred to Phase 6.2
                $table->unsignedTinyInteger('eligible_order_limit')->default(3);

                // Retry idempotency protection
                $table->string('idempotency_key', 100)->nullable()->unique();

                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
                $table->foreign('field_agent_id')->references('id')->on('field_agents')->restrictOnDelete();
                $table->index(['field_agent_id', 'status'], 'idx_agent_attribution_status');
                $table->index(['customer_id', 'status'], 'idx_customer_attribution_status');
            });
        }

        // 4. Immutable Attribution Audit Trail
        if (!Schema::hasTable('buyer_attribution_history')) {
            Schema::create('buyer_attribution_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('buyer_agent_attribution_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('field_agent_id')->index();
                $table->string('action', 64)->index();
                $table->string('previous_status', 40)->nullable();
                $table->string('new_status', 40)->nullable();
                $table->unsignedBigInteger('previous_agent_id')->nullable();
                $table->unsignedBigInteger('new_agent_id')->nullable();
                $table->unsignedBigInteger('actor_user_id')->nullable()->index();
                $table->string('actor_name', 255)->nullable();
                $table->string('actor_role', 40)->default('admin');
                $table->text('reason')->nullable();
                $table->string('evidence_reference', 255)->nullable();
                $table->json('payload')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('buyer_agent_attribution_id', 'fk_attr_hist_attr_id')
                    ->references('id')->on('buyer_agent_attributions')->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_attribution_history');
        Schema::dropIfExists('buyer_agent_attributions');
        Schema::dropIfExists('field_agent_status_history');
        Schema::dropIfExists('field_agents');
    }
};

