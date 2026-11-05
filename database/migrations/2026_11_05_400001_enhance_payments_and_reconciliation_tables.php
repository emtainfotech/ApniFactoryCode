<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Milestone M4 Phase 4.3: Payment Gateway Integration, Verified Callbacks & Reconciliation
     */
    public function up(): void
    {
        // 1. Extend `orders` table with payment tracking and parent-child order relationship
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'parent_order_id')) {
                    $table->unsignedBigInteger('parent_order_id')->nullable()->index()->after('id')
                        ->comment('Parent checkout order reference for seller fulfilment sub-orders');
                }
                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status', 32)->default('Pending')->after('grandtotal')
                        ->comment('Payment state: Pending, Paid, Failed, Refunded');
                }
                if (!Schema::hasColumn('orders', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('payment_status');
                }
            });
        }

        // 2. Extend `transections` table with full gateway references, verification, and reconciliation metadata
        if (Schema::hasTable('transections')) {
            Schema::table('transections', function (Blueprint $table) {
                if (!Schema::hasColumn('transections', 'parent_order_id')) {
                    $table->unsignedBigInteger('parent_order_id')->nullable()->index()->after('order_id');
                }
                if (!Schema::hasColumn('transections', 'payment_ref')) {
                    $table->string('payment_ref', 64)->nullable()->unique()->after('id')
                        ->comment('Authoritative internal payment transaction identifier');
                }
                if (!Schema::hasColumn('transections', 'gateway_name')) {
                    $table->string('gateway_name', 32)->default('razorpay')->after('payment_ref')
                        ->comment('Payment gateway provider: razorpay, payu, cashfree');
                }
                if (!Schema::hasColumn('transections', 'gateway_order_id')) {
                    $table->string('gateway_order_id', 64)->nullable()->index()->after('gateway_name');
                }
                if (!Schema::hasColumn('transections', 'gateway_payment_id')) {
                    $table->string('gateway_payment_id', 64)->nullable()->index()->after('gateway_order_id');
                }
                if (!Schema::hasColumn('transections', 'gateway_event_id')) {
                    $table->string('gateway_event_id', 64)->nullable()->unique()->after('gateway_payment_id')
                        ->comment('Webhook or callback event ID for strict deduplication');
                }
                if (!Schema::hasColumn('transections', 'amount')) {
                    $table->decimal('amount', 12, 2)->default(0.00)->after('gateway_event_id');
                }
                if (!Schema::hasColumn('transections', 'currency')) {
                    $table->string('currency', 10)->default('INR')->after('amount');
                }
                if (!Schema::hasColumn('transections', 'gateway_signature')) {
                    $table->text('gateway_signature')->nullable()->after('currency');
                }
                if (!Schema::hasColumn('transections', 'initiated_at')) {
                    $table->timestamp('initiated_at')->nullable()->after('gateway_signature');
                }
                if (!Schema::hasColumn('transections', 'callback_received_at')) {
                    $table->timestamp('callback_received_at')->nullable()->after('initiated_at');
                }
                if (!Schema::hasColumn('transections', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('callback_received_at');
                }
                if (!Schema::hasColumn('transections', 'gateway_captured_at')) {
                    $table->timestamp('gateway_captured_at')->nullable()->after('verified_at');
                }
                if (!Schema::hasColumn('transections', 'verification_status')) {
                    $table->enum('verification_status', ['unverified', 'verified', 'failed', 'tampered'])
                        ->default('unverified')->after('gateway_captured_at');
                }
                if (!Schema::hasColumn('transections', 'reconciliation_status')) {
                    $table->enum('reconciliation_status', ['pending', 'matched', 'mismatch', 'manual_review'])
                        ->default('pending')->index()->after('verification_status');
                }
                if (!Schema::hasColumn('transections', 'reconciled_at')) {
                    $table->timestamp('reconciled_at')->nullable()->after('reconciliation_status');
                }
                if (!Schema::hasColumn('transections', 'reconciled_by_user_id')) {
                    $table->unsignedBigInteger('reconciled_by_user_id')->nullable()->after('reconciled_at');
                }
                if (!Schema::hasColumn('transections', 'reconciliation_notes')) {
                    $table->text('reconciliation_notes')->nullable()->after('reconciled_by_user_id');
                }
                if (!Schema::hasColumn('transections', 'failure_code')) {
                    $table->string('failure_code', 64)->nullable()->after('reconciliation_notes');
                }
                if (!Schema::hasColumn('transections', 'failure_reason')) {
                    $table->text('failure_reason')->nullable()->after('failure_code');
                }
                if (!Schema::hasColumn('transections', 'idempotency_key')) {
                    $table->string('idempotency_key', 64)->nullable()->unique()->after('failure_reason');
                }

                $table->index(['order_id', 'status'], 'idx_transections_order_status');
            });
        }

        // 3. Create `payment_audit_logs` for immutable tracking of payment events and reconciliation
        if (!Schema::hasTable('payment_audit_logs')) {
            Schema::create('payment_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transection_id')->index();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('actor_name', 100)->default('System');
                $table->string('actor_role', 50)->default('gateway'); // buyer, seller, admin, finance, gateway, system
                $table->string('action', 64)->index();
                $table->string('previous_status', 32)->nullable();
                $table->string('new_status', 32)->nullable();
                $table->json('payload')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_audit_logs');

        if (Schema::hasTable('transections')) {
            Schema::table('transections', function (Blueprint $table) {
                $table->dropIndex('idx_transections_order_status');
                $columns = [
                    'parent_order_id', 'payment_ref', 'gateway_name', 'gateway_order_id',
                    'gateway_payment_id', 'gateway_event_id', 'amount', 'currency',
                    'gateway_signature', 'initiated_at', 'callback_received_at', 'verified_at',
                    'gateway_captured_at', 'verification_status', 'reconciliation_status',
                    'reconciled_at', 'reconciled_by_user_id', 'reconciliation_notes',
                    'failure_code', 'failure_reason', 'idempotency_key'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('transections', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'paid_at')) {
                    $table->dropColumn('paid_at');
                }
                if (Schema::hasColumn('orders', 'payment_status')) {
                    $table->dropColumn('payment_status');
                }
                if (Schema::hasColumn('orders', 'parent_order_id')) {
                    $table->dropColumn('parent_order_id');
                }
            });
        }
    }
};

