<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('mid')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('addby')->nullable();
            $table->string('image')->nullable();
            $table->string('trademarkno')->nullable();
            $table->string('file')->nullable();
            $table->string('type')->nullable();
            $table->string('adminresponse')->nullable();
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
        Schema::dropIfExists('brands');
    }
}
