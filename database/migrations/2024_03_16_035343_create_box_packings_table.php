<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoxPackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('box_packings', function (Blueprint $table) {
            $table->id();
            $table->string('name',100)->nullable();
            $table->integer('pcs')->nullable();
            $table->unsignedBigInteger('maincategory_id')->nullable();
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
        Schema::dropIfExists('box_packings');
    }
}
