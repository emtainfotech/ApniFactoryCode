<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('expiry')->nullable();
            $table->string('image')->nullable();
            $table->string('couponon')->nullable();
            $table->string('couponapplyon')->nullable();
            $table->date('startdate')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('Active');
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
        Schema::dropIfExists('coupons');
    }
}
