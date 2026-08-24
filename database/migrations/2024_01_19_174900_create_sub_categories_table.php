<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mid')->nullable();
            $table->unsignedBigInteger('cid')->nullable();
            $table->unsignedBigInteger('maincategory_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name',100)->nullable();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('sub_categories');
    }
}
