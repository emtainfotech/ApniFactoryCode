<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); 
            $table->integer('product_id')->nullable();
            $table->integer('mid')->nullable();
            $table->integer('cid')->nullable();
            $table->integer('sid')->nullable();
            $table->unsignedBigInteger('maincategory_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('oldprice', 8, 2)->nullable();
            $table->string('capacity')->nullable();
            $table->string('colorcode')->nullable();
            $table->string('colorimage')->nullable();
            $table->string('material')->nullable();
            $table->string('assurance')->nullable();
            $table->text('highlights')->nullable();
            $table->text('description')->nullable();
            $table->text('multipleimages')->nullable();
            $table->text('shadecard')->nullable();
            $table->string('hsncode')->nullable();
            $table->decimal('tax', 8, 2)->nullable();
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
        Schema::dropIfExists('products');
    }
}
