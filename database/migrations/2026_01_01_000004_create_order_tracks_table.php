<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_tracks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('orderno')->nullable();
            $table->text('text')->nullable();
            $table->string('transcontact')->nullable();
            $table->string('lrno')->nullable();
            $table->string('invoiceno')->nullable();
            $table->string('status')->nullable();
            $table->decimal('creditamnt', 12, 2)->default(0);
            $table->string('billty')->nullable();
            $table->string('invoice')->nullable();
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
        Schema::dropIfExists('order_tracks');
    }
}
