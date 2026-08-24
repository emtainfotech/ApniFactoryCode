<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaintPriceAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paint_price_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('adjustment_type'); // per_litre, percentage, fixed
            $table->decimal('adjustment_value', 10, 2);
            $table->string('scope_type')->default('family'); // family, shades, packings, skus
            $table->json('scope_json')->nullable();
            $table->integer('affected_count')->default(0);
            $table->longText('preview_data')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('paint_price_adjustments');
    }
}
