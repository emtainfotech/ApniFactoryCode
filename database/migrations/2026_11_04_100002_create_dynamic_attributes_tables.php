<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicAttributesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Attribute Master
        if (!Schema::hasTable('attributes')) {
            Schema::create('attributes', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 50)->unique();
                $table->enum('type', ['text', 'number', 'boolean', 'single_select', 'multi_select', 'date', 'measurement'])->default('text');
                $table->string('unit', 50)->nullable()->comment('Explicit unit e.g. Ltr, kg, mm, inch');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->json('validation_rules')->nullable();
                $table->enum('status', ['Active', 'Deactive', 'Archived'])->default('Active')->index();
                $table->timestamps();
            });
        }

        // 2. Attribute Options (for select/multi-select)
        if (!Schema::hasTable('attribute_options')) {
            Schema::create('attribute_options', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attribute_id')->index();
                $table->string('label', 150);
                $table->string('value', 150);
                $table->string('color_hex', 50)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_default')->default(false);
                $table->enum('status', ['Active', 'Deactive', 'Archived'])->default('Active');
                $table->timestamps();

                $table->unique(['attribute_id', 'value'], 'uq_attr_option_val');
            });
        }

        // 3. Category Attribute Mapping
        if (!Schema::hasTable('category_attributes')) {
            Schema::create('category_attributes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->index();
                $table->unsignedBigInteger('attribute_id')->index();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_filterable')->default(false);
                $table->boolean('is_searchable')->default(false);
                $table->boolean('is_variant_generating')->default(false);
                $table->integer('sort_order')->default(0);
                $table->json('validation_rules')->nullable();
                $table->enum('status', ['Active', 'Deactive', 'Archived'])->default('Active')->index();
                $table->timestamps();

                $table->unique(['category_id', 'attribute_id'], 'uq_cat_attr_mapping');
            });
        }

        // 4. Product Attribute Values
        if (!Schema::hasTable('product_attribute_values')) {
            Schema::create('product_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('product_variant_id')->nullable()->index();
                $table->unsignedBigInteger('attribute_id')->index();
                $table->unsignedBigInteger('attribute_option_id')->nullable()->index();
                $table->text('text_value')->nullable();
                $table->decimal('number_value', 12, 4)->nullable();
                $table->boolean('boolean_value')->nullable();
                $table->date('date_value')->nullable();
                $table->json('json_value')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'attribute_id'], 'idx_p_val_prod_attr');
                $table->index(['product_variant_id', 'attribute_id'], 'idx_p_val_var_attr');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('category_attributes');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attributes');
    }
}
