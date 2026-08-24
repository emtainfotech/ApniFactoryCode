<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('content')->nullable();
            $table->string('file')->nullable();
            $table->string('addby')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('adminmsg')->nullable();
            $table->string('screen')->nullable();
            $table->date('startdate')->nullable();
            $table->date('enddate')->nullable();
            $table->integer('sequence')->nullable();
            $table->enum('status',['Active', 'Deactive'])->default('Active');
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
        Schema::dropIfExists('advertisements');
    }
}
