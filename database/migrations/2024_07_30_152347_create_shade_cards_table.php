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
            $table->integer('subcategoryid');
            $table->string('name',100);
            $table->string('hexcode',100);
            $table->string('image',200);
            $table->enum('status',['Active', 'Deactive']);
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
