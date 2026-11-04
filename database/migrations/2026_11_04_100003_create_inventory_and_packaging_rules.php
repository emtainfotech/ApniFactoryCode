<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryAndPackagingRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Extend seller_listings with packaging and reservation rules
        if (Schema::hasTable('seller_listings')) {
            Schema::table('seller_listings', function (Blueprint $table) {
                if (!Schema::hasColumn('seller_listings', 'unit_of_measurement')) {
                    $table->string('unit_of_measurement', 50)->default('Unit')->after('product_variant_id');
                }
                if (!Schema::hasColumn('seller_listings', 'case_pack_quantity')) {
                    $table->unsignedInteger('case_pack_quantity')->default(1)->comment('Units per carton/case')->after('unit_of_measurement');
                }
                if (!Schema::hasColumn('seller_listings', 'order_multiple')) {
                    $table->unsignedInteger('order_multiple')->default(1)->comment('Allowed order quantity increment')->after('min_order_quantity');
                }
                if (!Schema::hasColumn('seller_listings', 'allow_unit_orders')) {
                    $table->boolean('allow_unit_orders')->default(true)->after('order_multiple');
                }
                if (!Schema::hasColumn('seller_listings', 'allow_carton_orders')) {
                    $table->boolean('allow_carton_orders')->default(false)->after('allow_unit_orders');
                }
                if (!Schema::hasColumn('seller_listings', 'reserved_stock')) {
                    $table->unsignedInteger('reserved_stock')->default(0)->after('stock_quantity');
                }
                if (!Schema::hasColumn('seller_listings', 'low_stock_threshold')) {
                    $table->unsignedInteger('low_stock_threshold')->default(10)->after('reserved_stock');
                }
                if (!Schema::hasColumn('seller_listings', 'barcode')) {
                    $table->string('barcode', 100)->nullable()->index()->after('seller_sku');
                }
            });
        }

        // 2. Inventory Movements (Audit Trail for Real-Time Inventory & Locking)
        if (!Schema::hasTable('inventory_movements')) {
            Schema::create('inventory_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seller_listing_id')->index();
                $table->unsignedBigInteger('seller_user_id')->index();
                $table->enum('movement_type', [
                    'initial',
                    'restock',
                    'order_reserve',
                    'order_deduct',
                    'order_cancel_release',
                    'manual_adjustment'
                ])->index();
                $table->integer('quantity_change');
                $table->unsignedInteger('previous_stock');
                $table->unsignedInteger('new_stock');
                $table->string('reference_order_no', 100)->nullable()->index();
                $table->string('notes', 255)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['seller_listing_id', 'created_at'], 'idx_listing_movement_history');
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
        Schema::dropIfExists('inventory_movements');

        if (Schema::hasTable('seller_listings')) {
            Schema::table('seller_listings', function (Blueprint $table) {
                $columns = [
                    'unit_of_measurement',
                    'case_pack_quantity',
                    'order_multiple',
                    'allow_unit_orders',
                    'allow_carton_orders',
                    'reserved_stock',
                    'low_stock_threshold',
                    'barcode'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('seller_listings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}

