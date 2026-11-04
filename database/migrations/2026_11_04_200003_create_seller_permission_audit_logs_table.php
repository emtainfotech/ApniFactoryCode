<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_permission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('acting_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // e.g. "staff.invited", "role.assigned", "permission.granted", "staff.deactivated"
            $table->string('subject')->nullable(); // e.g. permission name, role name
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index('company_id');
            $table->index('acting_user_id');
            $table->index('target_user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_permission_audit_logs');
    }
};

