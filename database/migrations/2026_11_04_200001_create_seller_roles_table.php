<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. "seller_owner"
            $table->string('label'); // e.g. "Seller Owner"
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // system roles cannot be deleted
            $table->timestamps();
        });

        Schema::create('seller_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // e.g. "products"
            $table->string('action'); // e.g. "view"
            $table->string('name')->unique(); // e.g. "products.view"
            $table->string('label'); // e.g. "View Products"
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_role_id')->constrained('seller_roles')->onDelete('cascade');
            $table->foreignId('seller_permission_id')->constrained('seller_permissions')->onDelete('cascade');
            $table->unique(['seller_role_id', 'seller_permission_id'], 'role_perm_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_role_permissions');
        Schema::dropIfExists('seller_permissions');
        Schema::dropIfExists('seller_roles');
    }
};
