<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionAndPlatformFeeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_code', 50)->unique();
            $table->string('name', 255);
            $table->enum('scope_type', ['sku', 'seller', 'subcategory', 'category', 'global'])->index();
            $table->unsignedBigInteger('scope_id')->nullable()->index();
            $table->decimal('commission_percentage', 5, 2)->default(0.00);
            $table->decimal('fixed_fee', 12, 2)->default(0.00);
            $table->enum('calculation_basis', [
                'taxable_product_value',
                'selling_price_gross',
                'net_seller_payable'
            ])->default('taxable_product_value');
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->boolean('is_gst_inclusive')->default(false);
            $table->timestamp('effective_from')->index();
            $table->timestamp('effective_to')->nullable()->index();
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'active',
                'scheduled',
                'expired',
                'deactivated'
            ])->default('draft')->index();
            $table->unsignedBigInteger('created_by_user_id')->index();
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->index();
            $table->string('agreement_ref', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('order_commission_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('order_detail_id')->nullable()->index();
            $table->string('orderno', 100)->index();
            $table->unsignedBigInteger('seller_user_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('seller_listing_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->unsignedBigInteger('commission_rule_id')->nullable()->index();
            $table->string('commission_rule_code', 50)->nullable();
            $table->string('rule_scope_type', 50);
            $table->unsignedBigInteger('rule_scope_id')->nullable();
            $table->decimal('commission_percentage', 5, 2);
            $table->decimal('fixed_fee', 12, 2)->default(0.00);
            $table->string('calculation_basis', 50);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('base_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->decimal('gst_amount', 12, 2)->default(0.00);
            $table->decimal('total_platform_fee', 12, 2);
            $table->decimal('seller_payable', 12, 2);
            $table->json('tax_treatment')->nullable();
            $table->timestamp('snapshot_timestamp');
            $table->timestamps();
        });

        Schema::create('commission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commission_rule_id')->nullable()->index();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->string('event_type', 50)->index();
            $table->json('previous_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commission_audit_logs');
        Schema::dropIfExists('order_commission_snapshots');
        Schema::dropIfExists('commission_rules');
    }
}
