<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartattributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cartattribute', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('product_attributes_id')->nullable();
            $table->integer('qty')->default(1);
            $table->integer('boxpcs')->default(1);
            $table->decimal('unitprice', 12, 2)->default(0);
            $table->decimal('totalprice', 12, 2)->default(0);
            $table->decimal('prprice', 12, 2)->default(0);
            $table->decimal('tax', 8, 2)->default(0);
            $table->decimal('taxamount', 12, 2)->default(0);
            $table->decimal('coupon', 12, 2)->default(0);
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
        Schema::dropIfExists('cartattribute');
    }
}
