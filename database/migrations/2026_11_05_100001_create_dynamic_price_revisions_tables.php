<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicPriceRevisionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('revision_code', 50)->unique();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by_user_id')->index();
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->index();
            $table->enum('revision_method', ['percentage', 'flat_per_unit', 'explicit_list']);
            $table->enum('revision_direction', ['increase', 'decrease', 'custom'])->default('custom');
            $table->decimal('revision_value', 12, 4)->nullable();
            $table->string('price_basis_unit', 50)->nullable();
            $table->enum('rounding_rule', ['none', 'round_integer', 'round_half', 'round_to_99'])->default('round_half');
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'scheduled',
                'active',
                'cancelled',
                'rejected',
                'failed'
            ])->default('draft')->index();
            $table->boolean('approval_required')->default(false);
            $table->timestamp('effective_at')->nullable()->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('reason', 500)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('selection_criteria')->nullable();
            $table->string('preview_token', 100)->nullable()->index();
            $table->unsignedInteger('total_skus')->default(0);
            $table->unsignedInteger('affected_skus_count')->default(0);
            $table->unsignedInteger('incompatible_skus_count')->default(0);
            $table->unsignedInteger('failed_skus_count')->default(0);
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->json('execution_log')->nullable();
            $table->timestamps();
        });

        Schema::create('price_revision_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_revision_id')->index();
            $table->unsignedBigInteger('seller_listing_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->string('seller_sku', 100)->nullable();
            $table->string('unit_of_measurement', 50)->nullable();
            $table->boolean('is_unit_compatible')->default(true);
            $table->decimal('old_seller_price', 12, 2);
            $table->decimal('new_seller_price', 12, 2);
            $table->decimal('old_selling_price', 12, 2);
            $table->decimal('new_selling_price', 12, 2);
            $table->decimal('old_mrp', 12, 2)->nullable();
            $table->decimal('new_mrp', 12, 2)->nullable();
            $table->decimal('rounding_difference', 10, 4)->default(0.0000);
            $table->enum('status', ['pending', 'applied', 'skipped', 'failed'])->default('pending')->index();
            $table->string('error_message', 500)->nullable();
            $table->timestamps();

            $table->foreign('price_revision_id')
                  ->references('id')
                  ->on('price_revisions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_revision_items');
        Schema::dropIfExists('price_revisions');
    }
}
