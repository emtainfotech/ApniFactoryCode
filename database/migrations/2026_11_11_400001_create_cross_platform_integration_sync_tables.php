<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Milestone M7 Phase 7.1: Cross-Platform Canonical Channel Mappings,
     * Integration Sync Outbox Events, Reconciliation Diffs & Audit Logs.
     */
    public function up(): void
    {
        // 1. Canonical <-> Channel Identifier Mappings (prevents duplicate masters)
        if (!Schema::hasTable('integration_channel_mappings')) {
            Schema::create('integration_channel_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 40);
                $table->unsignedBigInteger('canonical_id');
                $table->string('canonical_ref', 120)->nullable();
                $table->string('channel', 40);
                $table->string('external_id', 120);
                $table->string('external_ref', 120)->nullable();
                $table->string('payload_hash', 64)->nullable();
                $table->enum('mapping_status', ['active', 'conflict', 'inactive'])->default('active');
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->unique(['entity_type', 'canonical_id', 'channel'], 'uq_icm_entity_canon_chan');
                $table->index(['entity_type', 'canonical_ref'], 'idx_icm_entity_ref');
                $table->index(['channel', 'external_id'], 'idx_icm_chan_ext');
            });
        }

        // 2. Transactional Integration Sync Events (Outbox + Inbound + Projection)
        if (!Schema::hasTable('integration_sync_events')) {
            Schema::create('integration_sync_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_ref', 64)->unique('uq_ise_event_ref');
                $table->string('idempotency_key', 120)->unique('uq_ise_idempotency_key');
                $table->enum('direction', ['outbound', 'inbound', 'internal_projection'])->default('outbound');
                $table->string('source_channel', 40)->default('laravel_backend');
                $table->string('target_channel', 40);
                $table->string('entity_type', 40);
                $table->unsignedBigInteger('canonical_id')->nullable();
                $table->string('canonical_ref', 120)->nullable();
                $table->string('action', 60);
                $table->json('payload_json')->nullable();
                $table->string('payload_hash', 64)->nullable();
                $table->enum('status', [
                    'pending',
                    'processing',
                    'processed',
                    'failed',
                    'retrying',
                    'dead_letter',
                    'skipped_disabled',
                    'rejected_conflict',
                ])->default('pending');
                $table->unsignedSmallInteger('attempts')->default(0);
                $table->unsignedSmallInteger('max_attempts')->default(3);
                $table->timestamp('next_retry_at')->nullable();
                $table->text('last_error')->nullable();
                $table->unsignedSmallInteger('last_http_status')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->unsignedBigInteger('actor_user_id')->nullable();
                $table->string('actor_channel', 40)->nullable();
                $table->string('conflict_policy', 60)->default('authoritative_reject_and_log_diff');
                $table->timestamps();

                $table->index(['target_channel', 'status'], 'idx_ise_chan_status');
                $table->index(['entity_type', 'canonical_id'], 'idx_ise_entity_id');
                $table->index(['status', 'next_retry_at'], 'idx_ise_retry');
                $table->foreign('actor_user_id', 'fk_ise_actor')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 3. Cross-Channel Discrepancy & Conflict Reconciliation Records
        if (!Schema::hasTable('integration_reconciliation_records')) {
            Schema::create('integration_reconciliation_records', function (Blueprint $table) {
                $table->id();
                $table->string('reconciliation_ref', 64)->unique('uq_irr_ref');
                $table->string('entity_type', 40);
                $table->unsignedBigInteger('canonical_id')->nullable();
                $table->string('canonical_ref', 120)->nullable();
                $table->string('source_channel', 40);
                $table->string('target_channel', 40);
                $table->enum('mismatch_type', [
                    'price_drift',
                    'stock_drift',
                    'status_drift',
                    'unmapped_legacy_sku',
                    'conflicting_concurrent_update',
                    'unauthorized_field_override',
                    'crm_sync_mismatch',
                    'payment_webhook_mismatch',
                ]);
                $table->json('authoritative_snapshot')->nullable();
                $table->json('channel_snapshot')->nullable();
                $table->text('discrepancy_summary');
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->enum('status', ['open', 'investigating', 'reconciled', 'ignored', 'escalated'])->default('open');
                $table->string('resolution_action', 60)->nullable();
                $table->text('resolution_notes')->nullable();
                $table->unsignedBigInteger('resolved_by_user_id')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['entity_type', 'status'], 'idx_irr_entity_status');
                $table->index(['target_channel', 'status'], 'idx_irr_chan_status');
                $table->foreign('resolved_by_user_id', 'fk_irr_resolver')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 4. Immutable Integration Sync & Reconciliation Audit Logs
        if (!Schema::hasTable('integration_sync_audit_logs')) {
            Schema::create('integration_sync_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sync_event_id')->nullable();
                $table->unsignedBigInteger('reconciliation_record_id')->nullable();
                $table->string('entity_type', 40);
                $table->string('canonical_ref', 120)->nullable();
                $table->string('action', 60);
                $table->unsignedBigInteger('actor_user_id')->nullable();
                $table->string('actor_role', 40)->nullable();
                $table->string('channel', 40)->nullable();
                $table->json('before_state')->nullable();
                $table->json('after_state')->nullable();
                $table->text('notes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['entity_type', 'action'], 'idx_isal_entity_act');
                $table->foreign('sync_event_id', 'fk_isal_event')->references('id')->on('integration_sync_events')->nullOnDelete();
                $table->foreign('reconciliation_record_id', 'fk_isal_recon')->references('id')->on('integration_reconciliation_records')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_sync_audit_logs');
        Schema::dropIfExists('integration_reconciliation_records');
        Schema::dropIfExists('integration_sync_events');
        Schema::dropIfExists('integration_channel_mappings');
    }
};

