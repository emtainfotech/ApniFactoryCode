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
            $table->integer('product_id');
            $table->integer('mid');
            $table->integer('cid');
            $table->integer('sid');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('image');
            $table->decimal('price', 5, 2);
            $table->decimal('oldprice', 5, 2);
            $table->string('capacity');
            $table->string('colorcode');
            $table->string('colorimage');
            $table->string('material');
            $table->string('assurance');
            $table->text('highlights');
            $table->text('description');
            $table->text('multipleimages');
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
        Schema::dropIfExists('products');
    }
}
