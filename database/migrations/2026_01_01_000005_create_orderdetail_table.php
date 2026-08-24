<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->default(0);
            $table->string('orderno')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('productname')->nullable();
            $table->string('hsn')->nullable();
            $table->string('brdcmpcat')->nullable();
            $table->longText('attribute')->nullable();
            $table->text('coupondetail')->nullable();
            $table->string('productimage')->nullable();
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
        Schema::dropIfExists('orderdetail');
    }
}
