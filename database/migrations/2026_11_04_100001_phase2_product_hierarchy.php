<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Phase2ProductHierarchy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create product_variants table
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('variant_sku', 100)->unique();
                $table->unsignedBigInteger('shade_card_id')->nullable()->index();
                $table->unsignedBigInteger('box_packing_id')->nullable()->index();
                $table->decimal('pack_litres', 8, 2)->default(1.00);
                $table->string('variant_name', 255)->nullable();
                $table->string('image', 255)->nullable();
                $table->string('barcode', 100)->nullable()->index();
                $table->enum('status', ['Active', 'Deactive', 'Archived'])->default('Active')->index();
                $table->timestamps();

                $table->unique(['product_id', 'shade_card_id', 'box_packing_id'], 'uq_prod_variant_combo');
            });
        }

        // 2. Create seller_listings table
        if (!Schema::hasTable('seller_listings')) {
            Schema::create('seller_listings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seller_user_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('product_variant_id')->index();
                $table->string('seller_sku', 100)->nullable();
                $table->decimal('seller_price', 12, 2)->comment('Factory wholesale rate');
                $table->decimal('commission_rate', 5, 2)->default(25.00);
                $table->decimal('selling_price', 12, 2)->comment('Customer unit price');
                $table->decimal('mrp', 12, 2)->nullable();
                $table->unsignedInteger('min_order_quantity')->default(1);
                $table->unsignedInteger('stock_quantity')->default(9999);
                $table->unsignedSmallInteger('dispatch_lead_days')->default(2);
                $table->enum('status', ['Active', 'Deactive', 'Archived'])->default('Active')->index();
                $table->timestamps();

                $table->unique(['seller_user_id', 'product_variant_id'], 'uq_seller_variant_listing');
                $table->index(['product_variant_id', 'status', 'selling_price'], 'idx_variant_price_discovery');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_listings');
        Schema::dropIfExists('product_variants');
    }
}
