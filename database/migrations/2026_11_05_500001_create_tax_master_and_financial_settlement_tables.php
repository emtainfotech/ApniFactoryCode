<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Centralized GST / HSN Tax Master Table
        if (!Schema::hasTable('tax_masters')) {
            Schema::create('tax_masters', function (Blueprint $table) {
                $table->id();
                $table->string('hsn_code', 16)->index();
                $table->string('description', 255);
                $table->decimal('gst_rate', 5, 2); // e.g. 18.00, 12.00, 5.00, 28.00, 0.00
                $table->enum('type', ['goods', 'services'])->default('goods');
                $table->date('effective_from')->index();
                $table->date('effective_to')->nullable()->index();
                $table->string('source_reference', 255)->nullable();
                $table->enum('approval_status', ['pending', 'approved', 'rejected', 'inactive'])->default('pending')->index();
                $table->datetime('last_verified_at')->nullable();
                $table->unsignedBigInteger('verified_by_user_id')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->unsignedBigInteger('previous_version_id')->nullable();
                $table->timestamps();

                $table->index(['hsn_code', 'approval_status', 'effective_from'], 'idx_tax_master_lookup');
            });
        }

        // 2. Tax Master Audit Logs
        if (!Schema::hasTable('tax_master_audit_logs')) {
            Schema::create('tax_master_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tax_master_id')->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action', 64);
                $table->string('actor_name', 100)->nullable();
                $table->string('actor_role', 50)->nullable();
                $table->json('previous_payload')->nullable();
                $table->json('new_payload')->nullable();
                $table->text('notes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. Immutable Order Tax Snapshots
        if (!Schema::hasTable('order_tax_snapshots')) {
            Schema::create('order_tax_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('order_detail_id')->nullable()->index();
                $table->string('orderno', 64)->index();
                $table->unsignedBigInteger('seller_user_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name', 255)->nullable();
                $table->string('sku', 100)->nullable();
                $table->string('hsn_code', 16)->index();
                $table->unsignedBigInteger('tax_master_id')->nullable();
                $table->string('tax_description', 255)->nullable();
                $table->decimal('tax_rate', 5, 2);
                $table->decimal('taxable_amount', 12, 2);
                $table->boolean('is_interstate')->default(false);
                $table->string('place_of_supply', 100);
                $table->string('seller_state', 100)->nullable();
                $table->string('buyer_state', 100)->nullable();
                $table->decimal('cgst_rate', 5, 2)->default(0);
                $table->decimal('cgst_amount', 12, 2)->default(0);
                $table->decimal('sgst_rate', 5, 2)->default(0);
                $table->decimal('sgst_amount', 12, 2)->default(0);
                $table->decimal('igst_rate', 5, 2)->default(0);
                $table->decimal('igst_amount', 12, 2)->default(0);
                $table->decimal('total_tax_amount', 12, 2);
                $table->decimal('gross_amount', 12, 2);
                $table->json('tax_master_snapshot')->nullable();
                $table->datetime('calculated_at');
                $table->timestamps();

                $table->index(['order_id', 'seller_user_id'], 'idx_order_seller_tax');
            });
        }

        // 4. Invoices Table
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_no', 64)->unique();
                $table->enum('invoice_type', ['goods_tax_invoice', 'platform_commission_invoice'])->default('goods_tax_invoice');
                $table->unsignedBigInteger('parent_order_id')->index();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('seller_user_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->enum('issuer_type', ['seller', 'platform'])->default('seller');
                $table->string('issuer_name', 255);
                $table->string('issuer_gstin', 32)->nullable();
                $table->text('issuer_address')->nullable();
                $table->string('issuer_state', 100)->nullable();
                $table->string('recipient_name', 255);
                $table->string('recipient_gstin', 32)->nullable();
                $table->text('recipient_address')->nullable();
                $table->string('recipient_state', 100)->nullable();
                $table->string('place_of_supply', 100);
                $table->decimal('taxable_amount', 12, 2);
                $table->decimal('cgst_amount', 12, 2)->default(0);
                $table->decimal('sgst_amount', 12, 2)->default(0);
                $table->decimal('igst_amount', 12, 2)->default(0);
                $table->decimal('total_tax_amount', 12, 2);
                $table->decimal('round_off', 8, 2)->default(0);
                $table->decimal('total_amount', 12, 2);
                $table->enum('status', ['draft', 'issued', 'cancelled', 'amended'])->default('issued')->index();
                $table->datetime('issued_at');
                $table->string('document_path', 255)->nullable();
                $table->boolean('is_regenerated')->default(false);
                $table->timestamps();
            });
        }

        // 5. Invoice Line Items
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->unsignedBigInteger('order_detail_id')->nullable();
                $table->string('product_name', 255);
                $table->string('sku', 100)->nullable();
                $table->string('hsn_code', 16);
                $table->unsignedInteger('quantity');
                $table->string('unit', 32)->default('pcs');
                $table->decimal('unit_price', 12, 2);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('taxable_value', 12, 2);
                $table->decimal('tax_rate', 5, 2);
                $table->decimal('cgst_amount', 12, 2)->default(0);
                $table->decimal('sgst_amount', 12, 2)->default(0);
                $table->decimal('igst_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2);
                $table->timestamps();
            });
        }

        // 6. Double-Entry Financial Ledger
        if (!Schema::hasTable('financial_ledgers')) {
            Schema::create('financial_ledgers', function (Blueprint $table) {
                $table->id();
                $table->string('entry_ref', 64)->unique();
                $table->unsignedBigInteger('seller_user_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('parent_order_id')->nullable()->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('transection_id')->nullable()->index();
                $table->unsignedBigInteger('settlement_id')->nullable()->index();
                $table->string('entry_type', 48)->index(); // 'amount_collected', 'seller_payable', 'platform_commission', 'gst_on_commission', 'gateway_fee', 'refund', 'credit_note_adjustment', 'payout'
                $table->enum('direction', ['credit', 'debit'])->index();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 8)->default('INR');
                $table->decimal('running_balance', 12, 2)->default(0);
                $table->string('source_process', 64);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->timestamps();

                $table->index(['seller_user_id', 'entry_type', 'created_at'], 'idx_seller_ledger_timeline');
            });
        }

        // 7. Seller Settlements & Payout Status
        if (!Schema::hasTable('seller_settlements')) {
            Schema::create('seller_settlements', function (Blueprint $table) {
                $table->id();
                $table->string('settlement_ref', 64)->unique();
                $table->unsignedBigInteger('parent_order_id')->index();
                $table->unsignedBigInteger('order_id')->unique(); // 1:1 with seller child fulfilment order
                $table->unsignedBigInteger('seller_user_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->decimal('gross_order_amount', 12, 2);
                $table->decimal('commission_amount', 12, 2);
                $table->decimal('commission_gst_amount', 12, 2)->default(0);
                $table->decimal('total_platform_deduction', 12, 2);
                $table->decimal('net_seller_payable', 12, 2);
                $table->enum('settlement_status', ['pending', 'on_hold', 'eligible', 'processing', 'completed', 'cancelled'])->default('pending')->index();
                $table->datetime('hold_until')->nullable();
                $table->string('payout_reference', 100)->nullable();
                $table->datetime('processed_at')->nullable();
                $table->unsignedBigInteger('processed_by_user_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['seller_user_id', 'settlement_status'], 'idx_seller_settlement_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_settlements');
        Schema::dropIfExists('financial_ledgers');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('order_tax_snapshots');
        Schema::dropIfExists('tax_master_audit_logs');
        Schema::dropIfExists('tax_masters');
    }
};

