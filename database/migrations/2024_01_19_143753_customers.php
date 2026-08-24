<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->timestamp('lastlogin')->nullable();
            $table->string('password')->nullable();
            $table->enum('type',['user','vendor'])->default('user');
            $table->enum('status',['active','deactive'])->default('active');
            $table->string('deviceid')->nullable();
            $table->string('location')->nullable();
            $table->integer('followers')->default(0);
            $table->integer('followings')->default(0);
            $table->string('image')->nullable();
            $table->string('otp')->nullable();
            $table->string('regby')->nullable();
            $table->rememberToken();
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
         Schema::dropIfExists('customers');
    }
}
