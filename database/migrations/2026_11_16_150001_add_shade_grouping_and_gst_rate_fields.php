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
        // 1. Add group_name and gst_rate to product_attributes
        if (Schema::hasTable('product_attributes')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                if (!Schema::hasColumn('product_attributes', 'group_name')) {
                    $table->string('group_name', 150)->nullable()->default(null)->after('color');
                }
                if (!Schema::hasColumn('product_attributes', 'gst_rate')) {
                    $table->decimal('gst_rate', 5, 2)->nullable()->default(null)->after('is_gst_included');
                }
            });
        }

        // 2. Add shade_groups to products
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'shade_groups')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('shade_groups')->nullable()->after('tax');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_attributes')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                if (Schema::hasColumn('product_attributes', 'group_name')) {
                    $table->dropColumn('group_name');
                }
                if (Schema::hasColumn('product_attributes', 'gst_rate')) {
                    $table->dropColumn('gst_rate');
                }
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'shade_groups')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('shade_groups');
            });
        }
    }
};

