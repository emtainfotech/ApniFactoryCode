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
        // 1. Seller Fulfilment & Dispatch Records Table
        if (!Schema::hasTable('seller_fulfilments')) {
            Schema::create('seller_fulfilments', function (Blueprint $table) {
                $table->id();
                $table->string('fulfilment_ref', 64)->unique();
                $table->unsignedBigInteger('parent_order_id')->index();
                $table->unsignedBigInteger('order_id')->unique()->index(); // 1:1 with seller child order
                $table->unsignedBigInteger('seller_user_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();

                // Dispatch Origin Snapshot
                $table->enum('dispatch_origin_type', ['factory', 'godown', 'warehouse'])->default('factory');
                $table->string('dispatch_origin_name', 255)->nullable();
                $table->text('dispatch_origin_address')->nullable();
                $table->string('dispatch_origin_city', 100)->nullable();
                $table->string('dispatch_origin_state', 100)->nullable();
                $table->string('dispatch_origin_pincode', 16)->nullable();
                $table->string('dispatch_origin_contact', 32)->nullable();

                // Delivery / Hand-Over Point Snapshot
                $table->enum('handover_type', ['transport_nagar', 'buyer_godown', 'factory_gate'])->default('transport_nagar');
                $table->string('handover_location_name', 255)->nullable();
                $table->text('handover_address')->nullable();
                $table->string('handover_city', 100)->nullable();
                $table->string('handover_state', 100)->nullable();
                $table->string('handover_pincode', 16)->nullable();
                $table->text('handover_instructions')->nullable();

                // Transporter Details
                $table->string('transporter_name', 255)->nullable();
                $table->string('transporter_contact', 64)->nullable();
                $table->string('transporter_tracking_no', 100)->nullable();

                // LR / Bilty Document Metadata
                $table->string('lr_number', 100)->nullable()->index();
                $table->string('lr_document_path', 255)->nullable();
                $table->string('lr_document_name', 255)->nullable();
                $table->unsignedBigInteger('lr_file_size')->nullable();
                $table->string('lr_mime_type', 100)->nullable();
                $table->datetime('lr_uploaded_at')->nullable();
                $table->unsignedBigInteger('lr_uploaded_by_user_id')->nullable();

                // Dispatch Details
                $table->datetime('dispatched_at')->nullable();
                $table->unsignedInteger('package_count')->default(1);
                $table->string('weight_volume', 100)->nullable();
                $table->string('vehicle_number', 50)->nullable();
                $table->date('estimated_delivery_date')->nullable();

                // Freight Terms Baseline
                $table->string('freight_terms', 100)->default('To-Pay (Buyer-Paid Freight)');
                $table->text('freight_notes')->nullable();

                // Fulfilment Progression Status
                $table->enum('fulfilment_status', [
                    'pending_acceptance',
                    'accepted',
                    'processing_packed',
                    'dispatched',
                    'buyer_acknowledged',
                    'delivered',
                    'rejected',
                    'cancelled',
                ])->default('pending_acceptance')->index();

                // Buyer Acknowledgement & Dispute Foundation
                $table->datetime('buyer_acknowledged_at')->nullable();
                $table->text('buyer_acknowledgement_notes')->nullable();
                $table->enum('dispute_status', ['none', 'shortage_reported', 'damage_reported', 'disputed'])->default('none')->index();
                $table->text('dispute_reason')->nullable();
                $table->datetime('dispute_reported_at')->nullable();

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['seller_user_id', 'fulfilment_status'], 'idx_seller_fulfilment_status');
                $table->index(['parent_order_id', 'fulfilment_status'], 'idx_parent_fulfilment_status');
            });
        }

        // 2. Seller Fulfilment Audit & History
        if (!Schema::hasTable('seller_fulfilment_history')) {
            Schema::create('seller_fulfilment_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seller_fulfilment_id')->index();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('action', 64);
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('actor_name', 100);
                $table->string('actor_role', 32);
                $table->string('previous_status', 32)->nullable();
                $table->string('new_status', 32)->nullable();
                $table->json('payload')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_fulfilment_history');
        Schema::dropIfExists('seller_fulfilments');
    }
};
