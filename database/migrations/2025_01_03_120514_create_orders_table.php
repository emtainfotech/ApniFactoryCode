<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('orderno')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('address')->nullable();
            $table->decimal('sellercouponamount', 12, 2)->default(0);
            $table->decimal('admincouponamount', 12, 2)->default(0);
            $table->text('admincoupondetail')->nullable();
            $table->decimal('netamount', 12, 2)->default(0);
            $table->longText('taxdetail')->nullable();
            $table->decimal('taxamount', 12, 2)->default(0);
            $table->decimal('grandtotal', 12, 2)->default(0);
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
        Schema::dropIfExists('orders');
    }
}
