<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'home_image')) {
                $table->string('home_image')->nullable()->after('multipleimages');
            }
            if (!Schema::hasColumn('products', 'show_on_home')) {
                $table->boolean('show_on_home')->default(true)->after('home_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'home_image')) {
                $table->dropColumn('home_image');
            }
            if (Schema::hasColumn('products', 'show_on_home')) {
                $table->dropColumn('show_on_home');
            }
        });
    }
};

