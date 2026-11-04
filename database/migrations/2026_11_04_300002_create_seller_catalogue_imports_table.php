<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_catalogue_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('original_filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('error_report_path')->nullable();
            $table->string('import_format', 10)->default('xlsx'); // xlsx, csv
            $table->string('status', 30)->default('uploaded'); // uploaded, validating, validated, validation_failed, processing, completed, partial_success, failed
            $table->integer('total_rows')->default(0);
            $table->integer('valid_rows')->default(0);
            $table->integer('invalid_rows')->default(0);
            $table->integer('created_products')->default(0);
            $table->integer('updated_products')->default(0);
            $table->integer('created_variants')->default(0);
            $table->integer('updated_variants')->default(0);
            $table->integer('created_listings')->default(0);
            $table->integer('updated_listings')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('validation_summary')->nullable();
            $table->json('error_summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at'], 'seller_imports_comp_created_idx');
            $table->index('status', 'seller_imports_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_catalogue_imports');
    }
};
