<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Orders table indexes
        if (Schema::hasTable('orders')) {
            $this->addIndexSafely('orders', 'user_id', 'idx_orders_user_id');
            $this->addIndexSafely('orders', 'orderno', 'idx_orders_orderno');
            $this->addIndexSafely('orders', 'created_at', 'idx_orders_created_at');
        }

        // 2. Order status table indexes
        if (Schema::hasTable('order_status')) {
            $this->addIndexSafely('order_status', 'order_no', 'idx_order_status_order_no');
            $this->addIndexSafely('order_status', 'status', 'idx_order_status_status');
        }

        // 3. Orderdetail table indexes
        if (Schema::hasTable('orderdetail')) {
            $this->addIndexSafely('orderdetail', 'order_id', 'idx_orderdetail_order_id');
            $this->addIndexSafely('orderdetail', 'product_id', 'idx_orderdetail_product_id');
        }

        // 4. Products table indexes
        if (Schema::hasTable('products')) {
            $this->addIndexSafely('products', 'user_id', 'idx_products_user_id');
            $this->addIndexSafely('products', 'category_id', 'idx_products_category_id');
            $this->addIndexSafely('products', 'brand_id', 'idx_products_brand_id');
            $this->addIndexSafely('products', 'status', 'idx_products_status');
        }

        // 5. Product attributes table indexes
        if (Schema::hasTable('product_attributes')) {
            $this->addIndexSafely('product_attributes', 'product_id', 'idx_product_attr_product_id');
        }

        // 6. India pincode table indexes
        if (Schema::hasTable('india_pincode')) {
            $this->addIndexSafely('india_pincode', 'pincode', 'idx_india_pincode_pincode');
            $this->addIndexSafely('india_pincode', 'state', 'idx_india_pincode_state');
        }

        // 7. Notifications table indexes
        if (Schema::hasTable('notifications')) {
            $this->addIndexSafely('notifications', 'user_id', 'idx_notifications_user_id');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Dropping indexes safely if table exists
        $indexes = [
            'orders'             => ['idx_orders_user_id', 'idx_orders_orderno', 'idx_orders_created_at'],
            'order_status'       => ['idx_order_status_order_no', 'idx_order_status_status'],
            'orderdetail'        => ['idx_orderdetail_order_id', 'idx_orderdetail_product_id'],
            'products'           => ['idx_products_user_id', 'idx_products_category_id', 'idx_products_brand_id', 'idx_products_status'],
            'product_attributes' => ['idx_product_attr_product_id'],
            'india_pincode'      => ['idx_india_pincode_pincode', 'idx_india_pincode_state'],
            'notifications'      => ['idx_notifications_user_id'],
        ];

        foreach ($indexes as $table => $idxList) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($idxList) {
                    foreach ($idxList as $idx) {
                        try {
                            $t->dropIndex($idx);
                        } catch (\Exception $e) {
                            // ignore if not found
                        }
                    }
                });
            }
        }
    }

    /**
     * Helper to safely add an index only if it does not already exist
     */
    protected function addIndexSafely(string $tableName, string $columnName, string $indexName): void
    {
        if (!Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        $existingIndexes = DB::select("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]);
        if (empty($existingIndexes)) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $indexName) {
                $table->index($columnName, $indexName);
            });
        }
    }
};
