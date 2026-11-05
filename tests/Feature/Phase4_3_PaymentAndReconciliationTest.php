<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\Transection;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase4_3_PaymentAndReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $seller1;
    protected User $seller2;
    protected Customer $customer1;
    protected Customer $customer2;
    protected Order $parentOrder;
    protected Order $childOrder1;
    protected Order $childOrder2;

    protected string $razorpaySecret = 'test_razorpay_secret_987654';
    protected string $payuKey = 'test_payu_merchant_key';
    protected string $payuSalt = 'test_payu_merchant_salt_321';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.razorpay.key', 'rzp_test_mock_key');
        Config::set('services.razorpay.secret', $this->razorpaySecret);
        Config::set('services.payu.key', $this->payuKey);
        Config::set('services.payu.salt', $this->payuSalt);

        // Create Admin user
        $this->adminUser = User::create([
            'name'     => 'Finance Admin',
            'email'    => 'admin.finance@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        // Create Sellers
        $this->seller1 = User::create([
            'name'     => 'Industrial Coatings Ltd',
            'email'    => 'seller1@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        $this->seller2 = User::create([
            'name'     => 'Prime Hardware Supplies',
            'email'    => 'seller2@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        // Create Customers (Buyers)
        $this->customer1 = Customer::create([
            'name'     => 'Contractor Buildcorp',
            'email'    => 'buyer1@buildcorp.in',
            'mobile'   => '9876543210',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);

        $this->customer2 = Customer::create([
            'name'     => 'Infra Developers Ltd',
            'email'    => 'buyer2@infradev.in',
            'mobile'   => '9123456780',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);

        // Create Multi-Seller Parent Order & Child Orders
        $this->parentOrder = Order::create([
            'orderno'        => 'ORD-2026-PARENT-001',
            'customer_id'    => $this->customer1->id,
            'user_id'        => $this->seller1->id,
            'address'        => 'Site 44, Industrial Area, Phase II',
            'grandtotal'     => 25000.00,
            'netamount'      => 21186.44,
            'taxamount'      => 3813.56,
            'payment_status' => 'Pending',
        ]);

        $this->childOrder1 = Order::create([
            'parent_order_id' => $this->parentOrder->id,
            'orderno'         => 'ORD-2026-CHILD-001A',
            'customer_id'     => $this->customer1->id,
            'user_id'         => $this->seller1->id,
            'grandtotal'      => 15000.00,
            'payment_status'  => 'Pending',
        ]);

        $this->childOrder2 = Order::create([
            'parent_order_id' => $this->parentOrder->id,
            'orderno'         => 'ORD-2026-CHILD-001B',
            'customer_id'     => $this->customer1->id,
            'user_id'         => $this->seller2->id,
            'grandtotal'      => 10000.00,
            'payment_status'  => 'Pending',
        ]);
    }

    /**
     * Test 1: Payment initiation creates pending transaction with reference and audit log.
     */
    public function test_payment_initiation_creates_pending_transaction_with_valid_reference_and_audit_log(): void
    {
        $response = $this->postJson('/api/v2/payments/initiate', [
            'order_id'    => $this->parentOrder->id,
            'gateway'     => 'razorpay',
            'customer_id' => $this->customer1->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => true,
                'message' => 'Payment initiated successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'payment_ref',
                    'transaction_id',
                    'order_id',
                    'order_no',
                    'amount',
                    'currency',
                    'gateway_name',
                    'gateway_order_id',
                    'status',
                    'gateway_payload',
                ],
            ]);

        $data = $response->json('data');
        $this->assertStringStartsWith('PAY-', $data['payment_ref']);
        $this->assertEquals(25000.00, $data['amount']);
        $this->assertEquals('INR', $data['currency']);
        $this->assertEquals('pending', $data['status']);

        // Verify DB record
        $this->assertDatabaseHas('transections', [
            'payment_ref'          => $data['payment_ref'],
            'order_id'             => $this->parentOrder->id,
            'parent_order_id'      => $this->parentOrder->id,
            'amount'               => 25000.00,
            'status'               => 'pending',
            'verification_status'  => 'unverified',
            'reconciliation_status'=> 'pending',
        ]);

        // Verify Audit Log
        $this->assertDatabaseHas('payment_audit_logs', [
            'order_id'   => $this->parentOrder->id,
            'action'     => 'payment_initiated',
            'new_status' => 'pending',
            'actor_role' => 'buyer',
        ]);
    }

    /**
     * Test 2: Payment initiation with idempotency key avoids duplicate transaction creation.
     */
    public function test_payment_initiation_idempotency_prevents_duplicate_transactions(): void
    {
        $idempotencyKey = 'IDEMP-' . Str::random(16);

        $response1 = $this->postJson('/api/v2/payments/initiate', [
            'order_id'        => $this->parentOrder->id,
            'gateway'         => 'razorpay',
            'customer_id'     => $this->customer1->id,
            'idempotency_key' => $idempotencyKey,
        ]);
        $response1->assertStatus(201);
        $ref1 = $response1->json('data.payment_ref');

        // Second call with same idempotency key
        $response2 = $this->postJson('/api/v2/payments/initiate', [
            'order_id'        => $this->parentOrder->id,
            'gateway'         => 'razorpay',
            'customer_id'     => $this->customer1->id,
            'idempotency_key' => $idempotencyKey,
        ]);
        $response2->assertStatus(201);
        $ref2 = $response2->json('data.payment_ref');

        $this->assertEquals($ref1, $ref2);
        $this->assertEquals(1, Transection::where('idempotency_key', $idempotencyKey)->count());
    }

    /**
     * Test 3: Payment initiation rejects already paid order.
     */
    public function test_payment_initiation_rejects_already_paid_order(): void
    {
        $this->parentOrder->update([
            'payment_status' => 'Paid',
            'paid_at'        => now(),
        ]);

        $response = $this->postJson('/api/v2/payments/initiate', [
            'order_id'    => $this->parentOrder->id,
            'gateway'     => 'razorpay',
            'customer_id' => $this->customer1->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status'  => false,
                'message' => 'Payment initiation rejected.',
            ]);
    }

    /**
     * Test 4: IDOR Protection: Buyer cannot initiate payment for another buyer's order.
     */
    public function test_payment_initiation_rejects_unauthorized_buyer_idor(): void
    {
        $response = $this->postJson('/api/v2/payments/initiate', [
            'order_id'    => $this->parentOrder->id, // belongs to customer1
            'gateway'     => 'razorpay',
            'customer_id' => $this->customer2->id, // unauthorized customer2
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status'  => false,
                'message' => 'You do not have permission to pay for this order.',
            ]);
    }

    /**
     * Test 5: Verified Razorpay callback marks transaction successful and order paid.
     */
    public function test_verified_razorpay_callback_marks_transaction_successful_and_order_paid(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);

        $gatewayOrderId = $initiation['gateway_order_id'];
        $gatewayPaymentId = 'pay_' . Str::random(14);
        $signature = hash_hmac('sha256', $gatewayOrderId . '|' . $gatewayPaymentId, $this->razorpaySecret);

        $response = $this->postJson('/api/v2/payments/verify-client', [
            'gateway'   => 'razorpay',
            'signature' => $signature,
            'payload'   => [
                'razorpay_order_id'   => $gatewayOrderId,
                'razorpay_payment_id' => $gatewayPaymentId,
                'razorpay_signature'  => $signature,
                'amount'              => 25000.00,
                'status'              => 'captured',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'               => true,
                'status'                => 'successful',
                'reconciliation_status' => 'matched',
            ]);

        $txn = Transection::where('gateway_order_id', $gatewayOrderId)->first();
        $this->assertNotNull($txn);
        $this->assertEquals('successful', $txn->status);
        $this->assertEquals('verified', $txn->verification_status);
        $this->assertEquals('matched', $txn->reconciliation_status);
        $this->assertNotNull($txn->verified_at);
        $this->assertNotNull($txn->reconciled_at);

        // Verify parent order updated
        $this->parentOrder->refresh();
        $this->assertTrue($this->parentOrder->isPaid());
        $this->assertNotNull($this->parentOrder->paid_at);

        // Verify audit log
        $this->assertDatabaseHas('payment_audit_logs', [
            'transection_id' => $txn->id,
            'action'         => 'payment_captured_success',
            'new_status'     => 'successful',
        ]);
    }

    /**
     * Test 6: Verified PayU callback with SHA-512 reverse hash captures payment.
     */
    public function test_verified_payu_callback_with_sha512_reverse_hash_captures_payment(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'payu', null, $this->customer1->id);

        $txnid = $initiation['gateway_order_id'];
        $amount = '25000.00';
        $productInfo = "Order_{$this->parentOrder->orderno}";
        $firstname = $this->customer1->name;
        $email = $this->customer1->email;
        $status = 'success';
        $mihpayid = 'payu_pay_' . Str::random(10);

        // Calculate PayU reverse hash
        // Formula: sha512(SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|KEY)
        $reverseString = "{$this->payuSalt}|{$status}|||||||||||{$email}|{$firstname}|{$productInfo}|{$amount}|{$txnid}|{$this->payuKey}";
        $hash = hash('sha512', $reverseString);

        $response = $this->postJson('/api/v2/payments/verify-client', [
            'gateway'   => 'payu',
            'signature' => $hash,
            'payload'   => [
                'key'         => $this->payuKey,
                'txnid'       => $txnid,
                'amount'      => $amount,
                'productinfo' => $productInfo,
                'firstname'   => $firstname,
                'email'       => $email,
                'status'      => $status,
                'mihpayid'    => $mihpayid,
                'hash'        => $hash,
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'               => true,
                'status'                => 'successful',
                'reconciliation_status' => 'matched',
            ]);

        $txn = Transection::where('gateway_order_id', $txnid)->first();
        $this->assertEquals('successful', $txn->status);
        $this->assertEquals('verified', $txn->verification_status);
        $this->assertEquals('matched', $txn->reconciliation_status);
    }

    /**
     * Test 7: Tampered callback signature is rejected and flagged as tampered.
     */
    public function test_tampered_callback_signature_is_rejected_and_flagged(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);

        $gatewayOrderId = $initiation['gateway_order_id'];
        $gatewayPaymentId = 'pay_' . Str::random(14);
        $forgedSignature = 'tampered_bogus_signature_xyz';

        $response = $this->postJson('/api/v2/payments/verify-client', [
            'gateway'   => 'razorpay',
            'signature' => $forgedSignature,
            'payload'   => [
                'razorpay_order_id'   => $gatewayOrderId,
                'razorpay_payment_id' => $gatewayPaymentId,
                'razorpay_signature'  => $forgedSignature,
                'amount'              => 25000.00,
                'status'              => 'captured',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code'    => 'INVALID_SIGNATURE',
            ]);

        $txn = Transection::where('gateway_order_id', $gatewayOrderId)->first();
        $this->assertEquals('pending', $txn->status); // Remains pending, NOT marked successful
        $this->assertEquals('tampered', $txn->verification_status);
        $this->assertEquals('mismatch', $txn->reconciliation_status);
        $this->assertEquals('SIGNATURE_VERIFICATION_FAILED', $txn->failure_code);

        // Order remains unpaid
        $this->parentOrder->refresh();
        $this->assertFalse($this->parentOrder->isPaid());

        // Audit log created for tampering
        $this->assertDatabaseHas('payment_audit_logs', [
            'transection_id' => $txn->id,
            'action'         => 'callback_tampered_signature_failed',
        ]);
    }

    /**
     * Test 8: Amount mismatch in callback is rejected and flagged as mismatch.
     */
    public function test_amount_mismatch_in_callback_is_rejected_and_flagged_as_mismatch(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);

        $gatewayOrderId = $initiation['gateway_order_id'];
        $gatewayPaymentId = 'pay_' . Str::random(14);
        $signature = hash_hmac('sha256', $gatewayOrderId . '|' . $gatewayPaymentId, $this->razorpaySecret);

        // Gateway reports amount 5000.00 instead of expected 25000.00
        $response = $this->postJson('/api/v2/payments/verify-client', [
            'gateway'   => 'razorpay',
            'signature' => $signature,
            'payload'   => [
                'razorpay_order_id'   => $gatewayOrderId,
                'razorpay_payment_id' => $gatewayPaymentId,
                'razorpay_signature'  => $signature,
                'amount'              => 5000.00,
                'status'              => 'captured',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code'    => 'AMOUNT_MISMATCH',
            ]);

        $txn = Transection::where('gateway_order_id', $gatewayOrderId)->first();
        $this->assertEquals('pending', $txn->status);
        $this->assertEquals('verified', $txn->verification_status); // Signature matched, but amount didn't
        $this->assertEquals('mismatch', $txn->reconciliation_status);
        $this->assertEquals('RECONCILIATION_MISMATCH', $txn->failure_code);

        $this->parentOrder->refresh();
        $this->assertFalse($this->parentOrder->isPaid());
    }

    /**
     * Test 9: Currency mismatch in callback is rejected.
     */
    public function test_currency_mismatch_in_callback_is_rejected(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);

        $gatewayOrderId = $initiation['gateway_order_id'];
        $gatewayPaymentId = 'pay_' . Str::random(14);
        $signature = hash_hmac('sha256', $gatewayOrderId . '|' . $gatewayPaymentId, $this->razorpaySecret);

        $response = $this->postJson('/api/v2/payments/verify-client', [
            'gateway'   => 'razorpay',
            'signature' => $signature,
            'payload'   => [
                'razorpay_order_id'   => $gatewayOrderId,
                'razorpay_payment_id' => $gatewayPaymentId,
                'razorpay_signature'  => $signature,
                'amount'              => 25000.00,
                'currency'            => 'USD', // Mismatched currency
                'status'              => 'captured',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code'    => 'AMOUNT_MISMATCH',
            ]);
    }

    /**
     * Test 10: Duplicate callback idempotency does not duplicate state or audit logs.
     */
    public function test_duplicate_callback_idempotency_does_not_duplicate_order_or_audit_state(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);

        $gatewayOrderId = $initiation['gateway_order_id'];
        $gatewayPaymentId = 'pay_' . Str::random(14);
        $signature = hash_hmac('sha256', $gatewayOrderId . '|' . $gatewayPaymentId, $this->razorpaySecret);
        $eventId = 'evt_' . Str::random(12);

        $payload = [
            'razorpay_order_id'   => $gatewayOrderId,
            'razorpay_payment_id' => $gatewayPaymentId,
            'razorpay_signature'  => $signature,
            'amount'              => 25000.00,
            'status'              => 'captured',
            'event_id'            => $eventId,
        ];

        // First callback via webhook
        $resp1 = $this->withHeaders([
            'X-Razorpay-Signature' => $signature,
            'X-Razorpay-Event-Id'  => $eventId,
        ])->postJson('/api/v2/payments/webhook/razorpay', $payload);
        $resp1->assertStatus(200);

        $auditCountAfterFirst = PaymentAuditLog::count();

        // Duplicate callback with same event ID
        $resp2 = $this->withHeaders([
            'X-Razorpay-Signature' => $signature,
            'X-Razorpay-Event-Id'  => $eventId,
        ])->postJson('/api/v2/payments/webhook/razorpay', $payload);
        $resp2->assertStatus(200)
            ->assertJson([
                'success'   => true,
                'is_repeat' => true,
            ]);

        // Assert no new audit logs created for idempotent replay
        $this->assertEquals($auditCountAfterFirst, PaymentAuditLog::count());
    }

    /**
     * Test 11: Delayed failure callback does not downgrade an already paid payment.
     */
    public function test_delayed_failed_callback_does_not_downgrade_already_paid_transaction(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);

        $gatewayOrderId = $initiation['gateway_order_id'];
        $gatewayPaymentId = 'pay_' . Str::random(14);
        $signature = hash_hmac('sha256', $gatewayOrderId . '|' . $gatewayPaymentId, $this->razorpaySecret);

        // Successfully capture payment first
        $this->postJson('/api/v2/payments/verify-client', [
            'gateway'   => 'razorpay',
            'signature' => $signature,
            'payload'   => [
                'razorpay_order_id'   => $gatewayOrderId,
                'razorpay_payment_id' => $gatewayPaymentId,
                'razorpay_signature'  => $signature,
                'amount'              => 25000.00,
                'status'              => 'captured',
            ],
        ])->assertStatus(200);

        // Delayed out-of-order webhook reporting failure arrives
        $response = $this->postJson('/api/v2/payments/webhook/razorpay', [
            'razorpay_order_id'   => $gatewayOrderId,
            'razorpay_payment_id' => $gatewayPaymentId,
            'status'              => 'failed',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'   => true,
                'is_repeat' => true,
            ]);

        $txn = Transection::where('gateway_order_id', $gatewayOrderId)->first();
        $this->assertEquals('successful', $txn->status);
        $this->parentOrder->refresh();
        $this->assertTrue($this->parentOrder->isPaid());
    }

    /**
     * Test 12: Parent order payment propagates to all child seller fulfilment orders.
     */
    public function test_parent_order_payment_propagates_to_child_seller_fulfilment_orders(): void
    {
        $this->assertEquals('Pending', $this->childOrder1->payment_status);
        $this->assertEquals('Pending', $this->childOrder2->payment_status);

        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);

        $gatewayOrderId = $initiation['gateway_order_id'];
        $gatewayPaymentId = 'pay_' . Str::random(14);
        $signature = hash_hmac('sha256', $gatewayOrderId . '|' . $gatewayPaymentId, $this->razorpaySecret);

        $this->postJson('/api/v2/payments/verify-client', [
            'gateway'   => 'razorpay',
            'signature' => $signature,
            'payload'   => [
                'razorpay_order_id'   => $gatewayOrderId,
                'razorpay_payment_id' => $gatewayPaymentId,
                'razorpay_signature'  => $signature,
                'amount'              => 25000.00,
                'status'              => 'captured',
            ],
        ])->assertStatus(200);

        // Refresh child orders
        $this->childOrder1->refresh();
        $this->childOrder2->refresh();

        $this->assertEquals('Paid', $this->childOrder1->payment_status);
        $this->assertNotNull($this->childOrder1->paid_at);

        $this->assertEquals('Paid', $this->childOrder2->payment_status);
        $this->assertNotNull($this->childOrder2->paid_at);
    }

    /**
     * Test 13: Payment status API endpoint returns authoritative state with IDOR protection.
     */
    public function test_payment_status_api_endpoint_and_idor_protection(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);
        $paymentRef = $initiation['payment_ref'];

        // Authorized Buyer (customer1) queries status
        $response1 = $this->getJson("/api/v2/payments/{$paymentRef}/status?customer_id={$this->customer1->id}");
        $response1->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data'   => [
                    'payment_ref' => $paymentRef,
                    'amount'      => 25000.00,
                    'is_paid'     => false,
                ],
            ]);

        // Unauthorized Buyer (customer2) queries status
        $response2 = $this->getJson("/api/v2/payments/{$paymentRef}/status?customer_id={$this->customer2->id}");
        $response2->assertStatus(403)
            ->assertJson([
                'status'  => false,
                'message' => 'Access denied to transaction record.',
            ]);
    }

    /**
     * Test 14: Finance API lists payments with filters and summary statistics.
     */
    public function test_finance_api_lists_payments_and_filters(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);

        $response = $this->getJson('/api/v2/finance/payments');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'summary' => [
                    'total_volume',
                    'total_count',
                    'matched_count',
                    'mismatch_count',
                    'pending_count',
                    'tampered_count',
                ],
                'data',
                'pagination',
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('summary.total_count'));
    }

    /**
     * Test 15: Finance manual reconciliation override with audit trail.
     */
    public function test_finance_manual_reconciliation_override_with_audit_trail(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);
        $txn = Transection::find($initiation['transaction_id']);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v2/finance/payments/{$txn->id}/reconcile", [
                'reconciliation_status' => 'matched',
                'notes'                 => 'Verified against HDFC Bank Statement ref #HDFC998877.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => true,
                'message' => 'Manual reconciliation status updated successfully.',
            ]);

        $txn->refresh();
        $this->assertEquals('matched', $txn->reconciliation_status);
        $this->assertEquals('Verified against HDFC Bank Statement ref #HDFC998877.', $txn->reconciliation_notes);
        $this->assertEquals($this->adminUser->id, $txn->reconciled_by_user_id);
        $this->assertNotNull($txn->reconciled_at);

        // Audit log verified
        $this->assertDatabaseHas('payment_audit_logs', [
            'transection_id' => $txn->id,
            'action'         => 'manual_reconciliation_override',
            'actor_role'     => 'finance',
            'new_status'     => 'matched',
        ]);
    }

    /**
     * Test 16: Admin finance web views are accessible.
     */
    public function test_admin_finance_web_views_accessible(): void
    {
        $service = app(PaymentGatewayService::class);
        $initiation = $service->initiatePayment($this->parentOrder, 'razorpay', null, $this->customer1->id);
        $txn = Transection::find($initiation['transaction_id']);

        // Index page
        $indexResponse = $this->actingAs($this->adminUser)->get('/admin/finance/payments');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Payment Reconciliation');
        $indexResponse->assertSee($txn->payment_ref);

        // Show page
        $showResponse = $this->actingAs($this->adminUser)->get("/admin/finance/payments/{$txn->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee($txn->payment_ref);
        $showResponse->assertSee('Order & Fulfilment Hierarchy', false);
    }
}

