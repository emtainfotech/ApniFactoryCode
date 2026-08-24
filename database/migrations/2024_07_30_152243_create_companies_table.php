<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name',100)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->unsignedBigInteger('maincategory_id')->nullable();
            $table->string('gst')->nullable();
            $table->string('crn')->nullable();
            $table->decimal('minordervalue', 12, 2)->nullable();
            $table->string('photo')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->decimal('comission', 8, 2)->nullable();
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
        Schema::dropIfExists('companies');
    }
}
