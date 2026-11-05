<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Milestone M4 Phase 4.1: Buyer Cart, Seller-Level MOV & Case-Pack Rules
     */
    public function up(): void
    {
        // 1. Add indexes and foreign key references to `cart` table
        if (Schema::hasTable('cart')) {
            Schema::table('cart', function (Blueprint $table) {
                // Check if index exists before adding
                $table->index('customer_id', 'idx_cart_customer');
                $table->index('company_id', 'idx_cart_company');
            });
        }

        // 2. Extend `cartattribute` with modern Phase-II `seller_listing_id`
        if (Schema::hasTable('cartattribute')) {
            Schema::table('cartattribute', function (Blueprint $table) {
                if (!Schema::hasColumn('cartattribute', 'seller_listing_id')) {
                    $table->unsignedBigInteger('seller_listing_id')
                        ->nullable()
                        ->after('product_attributes_id')
                        ->comment('Phase-II seller listing (SKU source of truth)');
                }
                $table->index('cart_id', 'idx_cartattribute_cart');
                $table->index('customer_id', 'idx_cartattribute_customer');
                $table->index('seller_listing_id', 'idx_cartattribute_seller_listing');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cartattribute')) {
            Schema::table('cartattribute', function (Blueprint $table) {
                $table->dropIndex('idx_cartattribute_seller_listing');
                $table->dropIndex('idx_cartattribute_customer');
                $table->dropIndex('idx_cartattribute_cart');
                if (Schema::hasColumn('cartattribute', 'seller_listing_id')) {
                    $table->dropColumn('seller_listing_id');
                }
            });
        }

        if (Schema::hasTable('cart')) {
            Schema::table('cart', function (Blueprint $table) {
                $table->dropIndex('idx_cart_company');
                $table->dropIndex('idx_cart_customer');
            });
        }
    }
};
