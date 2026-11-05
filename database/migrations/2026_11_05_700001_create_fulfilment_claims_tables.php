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
        // 1. Core Claims & Disputes Table
        Schema::create('fulfilment_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_ref', 64)->unique();
            $table->unsignedBigInteger('seller_fulfilment_id')->index();
            $table->unsignedBigInteger('parent_order_id')->index();
            $table->unsignedBigInteger('order_id')->index(); // child seller order
            $table->unsignedBigInteger('seller_user_id')->index();
            $table->unsignedBigInteger('customer_id')->index(); // buyer user
            $table->string('claim_type', 50)->default('other'); // shortage, damage, wrong_item, other
            $table->string('claim_status', 50)->default('submitted'); // submitted, seller_responded, under_review, resolved_settled, rejected, cancelled
            $table->text('description'); // buyer explanation
            $table->text('seller_response')->nullable(); // seller remarks
            $table->timestamp('seller_responded_at')->nullable();
            $table->unsignedBigInteger('seller_response_by_user_id')->nullable();
            $table->text('admin_notes')->nullable(); // internal operations notes (protected)
            $table->text('resolution_summary')->nullable(); // public resolution visible to parties
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->timestamps();

            $table->foreign('seller_fulfilment_id')->references('id')->on('seller_fulfilments')->onDelete('cascade');
        });

        // 2. Line-Item Level Shortage & Damage Breakdown Table
        Schema::create('fulfilment_claim_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fulfilment_claim_id')->index();
            $table->unsignedBigInteger('order_detail_id')->nullable()->index();
            $table->string('product_name', 255);
            $table->string('sku', 100)->nullable();
            $table->integer('ordered_qty')->default(0);
            $table->integer('received_qty')->default(0);
            $table->integer('affected_qty')->default(0); // quantity short or damaged
            $table->string('condition_notes', 500)->nullable();
            $table->timestamps();

            $table->foreign('fulfilment_claim_id')->references('id')->on('fulfilment_claims')->onDelete('cascade');
        });

        // 3. Supporting Photographic & Document Evidence Table
        Schema::create('fulfilment_claim_evidence', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fulfilment_claim_id')->index();
            $table->unsignedBigInteger('uploader_user_id');
            $table->string('uploader_role', 32)->default('buyer'); // buyer, seller, admin
            $table->string('document_type', 50)->default('damage_photo'); // damage_photo, weighment_slip, transporter_receipt, other
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('fulfilment_claim_id')->references('id')->on('fulfilment_claims')->onDelete('cascade');
        });

        // 4. Immutable Claim Audit Trail & Decision History Table
        Schema::create('fulfilment_claim_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fulfilment_claim_id')->index();
            $table->string('action', 100);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name', 255)->nullable();
            $table->string('actor_role', 50)->default('system'); // buyer, seller, admin, system
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->longText('payload')->nullable(); // json snapshot
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('fulfilment_claim_id')->references('id')->on('fulfilment_claims')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fulfilment_claim_history');
        Schema::dropIfExists('fulfilment_claim_evidence');
        Schema::dropIfExists('fulfilment_claim_items');
        Schema::dropIfExists('fulfilment_claims');
    }
};

