<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('topic')->nullable();
            $table->text('msg')->nullable();
            $table->text('adminmsg')->nullable();
            $table->string('addby')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('status',['Pending', 'Completed','Reject'])->default('Pending');
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
        Schema::dropIfExists('tickets');
    }
}
