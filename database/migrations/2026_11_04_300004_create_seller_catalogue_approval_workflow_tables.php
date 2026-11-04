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
        // 1. Add approval & review workflow columns to products table if not present
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'approval_status')) {
                $table->enum('approval_status', [
                    'draft',
                    'pending_review',
                    'approved',
                    'rejected',
                    'corrections_requested',
                    'resubmitted'
                ])->default('approved')->after('status')->index();
            }

            if (!Schema::hasColumn('products', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('approval_status')->index();
            }

            if (!Schema::hasColumn('products', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            }

            if (!Schema::hasColumn('products', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at')->index();
            }

            if (!Schema::hasColumn('products', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('reviewed_by');
            }

            if (!Schema::hasColumn('products', 'correction_notes')) {
                $table->text('correction_notes')->nullable()->after('rejection_reason');
            }

            if (!Schema::hasColumn('products', 'last_validated_at')) {
                $table->timestamp('last_validated_at')->nullable()->after('correction_notes');
            }
        });

        // 2. Create product_validation_results table
        if (!Schema::hasTable('product_validation_results')) {
            Schema::create('product_validation_results', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->enum('status', ['passed', 'failed', 'passed_with_warnings'])->default('failed')->index();
                $table->json('passed_checks')->nullable();
                $table->json('failed_checks')->nullable();
                $table->json('warnings')->nullable();
                $table->unsignedInteger('error_count')->default(0);
                $table->unsignedInteger('warning_count')->default(0);
                $table->timestamps();

                $table->index(['product_id', 'created_at'], 'idx_pvr_prod_created');
                $table->index(['company_id', 'status'], 'idx_pvr_comp_status');
            });
        }

        // 3. Create product_review_histories table
        if (!Schema::hasTable('product_review_histories')) {
            Schema::create('product_review_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('seller_user_id')->index();
                $table->unsignedBigInteger('reviewer_id')->nullable()->index();
                $table->string('action', 50)->index(); // submitted, approved, rejected, corrections_requested, resubmitted
                $table->string('previous_status', 50)->nullable();
                $table->string('new_status', 50);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('validation_result_id')->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'created_at'], 'idx_prh_prod_created');
                $table->index(['company_id', 'created_at'], 'idx_prh_comp_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review_histories');
        Schema::dropIfExists('product_validation_results');

        Schema::table('products', function (Blueprint $table) {
            $cols = [
                'approval_status',
                'submitted_at',
                'reviewed_at',
                'reviewed_by',
                'rejection_reason',
                'correction_notes',
                'last_validated_at',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

