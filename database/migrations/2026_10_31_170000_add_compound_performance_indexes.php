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
            $this->addIndexSafely('orders', ['customer_id'], 'idx_orders_customer_id');
        }

        // 2. Order status table indexes
        if (Schema::hasTable('order_status')) {
            $this->addIndexSafely('order_status', ['order_no', 'id'], 'idx_order_status_no_id');
            $this->addIndexSafely('order_status', ['order_no', 'status'], 'idx_order_status_no_status');
        }

        // 3. Products table compound indexes
        if (Schema::hasTable('products')) {
            $this->addIndexSafely('products', ['category_id', 'status'], 'idx_products_cat_status');
            $this->addIndexSafely('products', ['brand_id', 'category_id'], 'idx_products_brand_cat');
        }

        // 4. Product attributes table indexes
        if (Schema::hasTable('product_attributes')) {
            $this->addIndexSafely('product_attributes', ['color'], 'idx_product_attr_color');
            $this->addIndexSafely('product_attributes', ['quantity'], 'idx_product_attr_quantity');
            $this->addIndexSafely('product_attributes', ['product_id', 'color'], 'idx_prod_attr_prod_color');
        }

        // 5. Notifications table compound indexes
        if (Schema::hasTable('notifications')) {
            if (Schema::hasColumn('notifications', 'customer_id') && Schema::hasColumn('notifications', 'msgread')) {
                $this->addIndexSafely('notifications', ['customer_id', 'msgread'], 'idx_notif_cust_read');
            }
            if (Schema::hasColumn('notifications', 'user_id') && Schema::hasColumn('notifications', 'msgread')) {
                $this->addIndexSafely('notifications', ['user_id', 'msgread'], 'idx_notif_user_read');
            }
        }

        // 6. India pincode table indexes
        if (Schema::hasTable('india_pincode')) {
            $this->addIndexSafely('india_pincode', ['city'], 'idx_india_pincode_city');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $drops = [
            'orders'             => ['idx_orders_customer_id'],
            'order_status'       => ['idx_order_status_no_id', 'idx_order_status_no_status'],
            'products'           => ['idx_products_cat_status', 'idx_products_brand_cat'],
            'product_attributes' => ['idx_product_attr_color', 'idx_product_attr_quantity', 'idx_prod_attr_prod_color'],
            'notifications'      => ['idx_notif_cust_read', 'idx_notif_user_read'],
            'india_pincode'      => ['idx_india_pincode_city'],
        ];

        foreach ($drops as $table => $idxList) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($idxList) {
                    foreach ($idxList as $idx) {
                        try {
                            $t->dropIndex($idx);
                        } catch (\Exception $e) {
                            // ignore if not present
                        }
                    }
                });
            }
        }
    }

    /**
     * Helper to safely add an index only if it does not already exist
     */
    protected function addIndexSafely(string $tableName, array $columns, string $indexName): void
    {
        foreach ($columns as $col) {
            if (!Schema::hasColumn($tableName, $col)) {
                return;
            }
        }

        // Check if index already exists in information_schema
        $existing = DB::select(
            "SELECT COUNT(1) as cnt FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
            [$tableName, $indexName]
        );

        if (!empty($existing) && $existing[0]->cnt > 0) {
            return;
        }

        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // Already exists or unsupported
        }
    }
};

