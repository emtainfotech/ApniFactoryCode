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
        // 1. Add commission_rate to main_categories
        if (Schema::hasTable('main_categories') && !Schema::hasColumn('main_categories', 'commission_rate')) {
            Schema::table('main_categories', function (Blueprint $table) {
                $table->decimal('commission_rate', 5, 2)->nullable()->default(null)->after('title');
            });
        }

        // 2. Add commission_rate to categories
        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'commission_rate')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->decimal('commission_rate', 5, 2)->nullable()->default(null)->after('title');
            });
        }

        // 3. Add commission_rate to sub_categories
        if (Schema::hasTable('sub_categories') && !Schema::hasColumn('sub_categories', 'commission_rate')) {
            Schema::table('sub_categories', function (Blueprint $table) {
                $table->decimal('commission_rate', 5, 2)->nullable()->default(null)->after('title');
            });
        }

        // 4. Add is_gst_included and mrp to product_attributes
        if (Schema::hasTable('product_attributes')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                if (!Schema::hasColumn('product_attributes', 'is_gst_included')) {
                    $table->boolean('is_gst_included')->default(false)->after('seller_price');
                }
                if (!Schema::hasColumn('product_attributes', 'mrp')) {
                    $table->decimal('mrp', 10, 2)->nullable()->default(null)->after('price');
                }
            });
        }

        // 5. Add has_gst_included to products
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'has_gst_included')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('has_gst_included')->default(false)->after('approval_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('main_categories') && Schema::hasColumn('main_categories', 'commission_rate')) {
            Schema::table('main_categories', function (Blueprint $table) {
                $table->dropColumn('commission_rate');
            });
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'commission_rate')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('commission_rate');
            });
        }

        if (Schema::hasTable('sub_categories') && Schema::hasColumn('sub_categories', 'commission_rate')) {
            Schema::table('sub_categories', function (Blueprint $table) {
                $table->dropColumn('commission_rate');
            });
        }

        if (Schema::hasTable('product_attributes')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                if (Schema::hasColumn('product_attributes', 'is_gst_included')) {
                    $table->dropColumn('is_gst_included');
                }
                if (Schema::hasColumn('product_attributes', 'mrp')) {
                    $table->dropColumn('mrp');
                }
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'has_gst_included')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('has_gst_included');
            });
        }
    }
};

