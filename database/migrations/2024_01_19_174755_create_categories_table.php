<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->integer('mid')->nullable();
            $table->integer('maincategory_id')->nullable();
            $table->string('name', 100)->unique();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('addby')->nullable();
            $table->text('adminmsg')->nullable();
            $table->string('adminstatus')->nullable();
            $table->integer('sequence')->nullable();
            $table->enum('status', ['Active', 'Deactive'])->default('Active');
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
        Schema::dropIfExists('categories');
    }
}
