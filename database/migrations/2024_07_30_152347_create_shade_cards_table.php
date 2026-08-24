<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShadeCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shade_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subcategoryid')->nullable();
            $table->unsignedBigInteger('maincategory_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name',100)->nullable();
            $table->string('hexcode',100)->nullable();
            $table->string('image',200)->nullable();
            $table->text('adminmsg')->nullable();
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
        Schema::dropIfExists('shade_cards');
    }
}
