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
        if (!Schema::hasTable('seller_catalogue_bulk_operations')) {
            Schema::create('seller_catalogue_bulk_operations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('operation_type', 50)->comment('status_update, field_update, archive, restore');
                $table->string('target_entity', 30)->default('listing')->comment('listing, product, variant');
                $table->enum('selection_mode', ['ids', 'filter'])->default('ids');
                $table->json('selection_criteria')->comment('IDs list or filter query parameters');
                $table->json('update_payload')->comment('Requested field changes or status transition');
                $table->string('status', 30)->default('previewed')->comment('previewed, processing, completed, partial_success, failed, cancelled');
                $table->unsignedInteger('total_matching')->default(0);
                $table->unsignedInteger('eligible_count')->default(0);
                $table->unsignedInteger('ineligible_count')->default(0);
                $table->unsignedInteger('processed_count')->default(0);
                $table->unsignedInteger('success_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->json('preview_data')->nullable()->comment('Sample preview items and ineligibility reasons');
                $table->json('results_summary')->nullable()->comment('Per-record execution results and before/after values');
                $table->json('error_summary')->nullable()->comment('Execution failure diagnostics');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

                $table->index(['company_id', 'created_at'], 'idx_bulk_ops_comp_created');
                $table->index('status', 'idx_bulk_ops_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_catalogue_bulk_operations');
    }
};

