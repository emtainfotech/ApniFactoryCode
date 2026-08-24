<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->default(0);
            $table->string('orderno')->nullable();
            $table->decimal('value', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);
            $table->decimal('refundtobuyer', 12, 2)->default(0);
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('type')->nullable();
            $table->string('action')->nullable();
            $table->string('addby')->nullable();
            $table->text('msg')->nullable();
            $table->string('creditcreated')->default('N');
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
        Schema::dropIfExists('wallet');
    }
}
