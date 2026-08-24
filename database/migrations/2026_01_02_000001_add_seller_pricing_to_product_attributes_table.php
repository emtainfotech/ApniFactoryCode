<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellerPricingToProductAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            if (!Schema::hasColumn('product_attributes', 'seller_price')) {
                $table->decimal('seller_price', 12, 2)->nullable()->after('oldprice');
            }
            if (!Schema::hasColumn('product_attributes', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(25.00)->after('seller_price');
            }
            if (!Schema::hasColumn('product_attributes', 'pack_litres')) {
                $table->decimal('pack_litres', 8, 2)->default(1.00)->after('commission_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            if (Schema::hasColumn('product_attributes', 'pack_litres')) {
                $table->dropColumn('pack_litres');
            }
            if (Schema::hasColumn('product_attributes', 'commission_rate')) {
                $table->dropColumn('commission_rate');
            }
            if (Schema::hasColumn('product_attributes', 'seller_price')) {
                $table->dropColumn('seller_price');
            }
        });
    }
}
