<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for V2 B2B features: RFQ and Tiered Volume Slab Pricing.
     *
     * @return void
     */
    public function up()
    {
        // 1. RFQ Requests Table (Wholesale inquiries from buyers)
        if (!Schema::hasTable('rfq_requests')) {
            Schema::create('rfq_requests', function (Blueprint $table) {
                $table->id();
                $table->string('rfq_number')->unique();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('seller_user_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('category_name')->nullable();
                $table->string('product_title')->nullable();
                $table->decimal('target_quantity', 12, 2);
                $table->string('unit_of_measure', 50)->default('pcs'); // pcs, litres, meters, rolls
                $table->decimal('target_unit_price', 12, 2)->nullable();
                $table->string('delivery_pincode', 10)->nullable();
                $table->date('delivery_deadline')->nullable();
                $table->text('technical_specifications')->nullable();
                $table->enum('status', ['draft', 'submitted', 'quoted', 'accepted', 'rejected', 'expired'])->default('submitted');
                $table->timestamps();

                $table->index(['customer_id', 'status']);
                $table->index(['seller_user_id', 'status']);
            });
        }

        // 2. RFQ Quotations Table (Seller counter-bids and pricing quotes)
        if (!Schema::hasTable('rfq_quotes')) {
            Schema::create('rfq_quotes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rfq_request_id');
                $table->unsignedBigInteger('seller_user_id');
                $table->unsignedBigInteger('company_id');
                $table->decimal('offered_unit_price', 12, 2);
                $table->decimal('total_amount', 14, 2);
                $table->decimal('gst_amount', 12, 2)->default(0);
                $table->decimal('shipping_charges', 12, 2)->default(0);
                $table->integer('lead_time_days')->default(7);
                $table->date('valid_until');
                $table->text('seller_notes')->nullable();
                $table->text('payment_terms')->nullable();
                $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
                $table->timestamps();

                $table->index(['rfq_request_id', 'seller_user_id']);
            });
        }

        // 3. Product Volume Slab Pricing (Tiered wholesale volume discounts)
        if (!Schema::hasTable('product_volume_slabs')) {
            Schema::create('product_volume_slabs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('seller_user_id');
                $table->integer('min_quantity');
                $table->integer('max_quantity')->nullable(); // null represents "and above"
                $table->enum('discount_type', ['percentage', 'fixed_unit_price'])->default('percentage');
                $table->decimal('discount_value', 10, 2); // e.g. 5.00 for 5% off, or 350.00 for fixed unit price
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();

                $table->index(['product_id', 'status']);
                $table->index(['seller_user_id', 'product_id']);
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
        Schema::dropIfExists('product_volume_slabs');
        Schema::dropIfExists('rfq_quotes');
        Schema::dropIfExists('rfq_requests');
    }
};
