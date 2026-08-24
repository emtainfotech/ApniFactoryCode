<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_details', function (Blueprint $table) {
            $table->id();
            $table->string('accountholder')->nullable();
            $table->string('accountno')->nullable();
            $table->string('bankname')->nullable();
            $table->string('branch')->nullable();
            $table->string('ifsc')->nullable();
            $table->enum('isprimary',['N', 'Y'])->default('N');
            $table->string('addby')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
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
        Schema::dropIfExists('bank_details');
    }
}
