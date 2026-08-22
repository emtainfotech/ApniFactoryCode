<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->string('landmark1');
            $table->string('landmark2');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->string('country');
            $table->string('phoneno');
            $table->string('location');
            $table->string('name');
            $table->string('identityname');
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
        Schema::dropIfExists('customer_addresses');
    }
}
