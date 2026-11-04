<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_role_id')->constrained('seller_roles')->onDelete('restrict');
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'user_id'], 'company_user_unique');
            $table->index('company_id');
            $table->index('user_id');
        });

        Schema::create('seller_staff_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_staff_id')->constrained('seller_staff')->onDelete('cascade');
            $table->foreignId('seller_permission_id')->constrained('seller_permissions')->onDelete('cascade');
            $table->boolean('granted')->default(true); // true=grant, false=deny (overrides role)
            $table->timestamps();
            $table->unique(['seller_staff_id', 'seller_permission_id'], 'staff_perm_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_staff_permissions');
        Schema::dropIfExists('seller_staff');
    }
};
