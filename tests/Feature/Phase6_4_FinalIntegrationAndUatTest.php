<?php

namespace Tests\Feature;

use App\Jobs\SendAgentAttributionNotificationJob;
use App\Jobs\SendAgentIncentiveNotificationJob;
use App\Models\AgentEligibleOrder;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentIncentiveProcessingLog;
use App\Models\AgentIncentiveRule;
use App\Models\AgentPayout;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Models\FinancialLedger;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerFulfilment;
use App\Models\SellerListing;
use App\Models\SellerSettlement;
use App\Models\Transection;
use App\Models\User;
use App\Services\AgentIncentiveCalculationService;
use App\Services\AgentIncentiveRuleService;
use App\Services\AgentOrderEligibilityService;
use App\Services\AgentPayoutService;
use App\Services\BuyerAttributionService;
use App\Services\BuyerDisputeService;
use App\Services\FieldAgentService;
use App\Services\PaymentGatewayService;
use App\Services\PaymentRefundService;
use App\Services\SellerFulfilmentService;
use Database\Seeders\Phase6AgentModuleUatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class Phase6_4_FinalIntegrationAndUatTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $seller1;
    protected User $seller2;
    protected User $seller3;
    protected Customer $buyer1;
    protected Customer $buyer2;
    protected User $buyerUser1;
    protected FieldAgent $agentA;
    protected FieldAgent $agentB;
    protected User $agentUserA;
    protected User $agentUserB;

    protected FieldAgentService $agentService;
    protected BuyerAttributionService $attributionService;
    protected AgentOrderEligibilityService $eligibilityService;
    protected AgentIncentiveRuleService $ruleService;
    protected AgentIncentiveCalculationService $calculationService;
    protected AgentPayoutService $payoutService;
    protected PaymentGatewayService $paymentService;
    protected SellerFulfilmentService $fulfilmentService;
    protected BuyerDisputeService $disputeService;

    protected string $razorpaySecret = 'rzp_test_phase64_uat_secret';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Config::set('services.razorpay.key', 'rzp_test_mock_key');
        Config::set('services.razorpay.secret', $this->razorpaySecret);
        Config::set('marketplace_agents.eligible_order_tracking_implemented', true);

        $this->agentService       = app(FieldAgentService::class);
        $this->attributionService = app(BuyerAttributionService::class);
        $this->eligibilityService = app(AgentOrderEligibilityService::class);
        $this->ruleService        = app(AgentIncentiveRuleService::class);
        $this->calculationService = app(AgentIncentiveCalculationService::class);
        $this->payoutService      = app(AgentPayoutService::class);
        $this->paymentService     = app(PaymentGatewayService::class);
        $this->fulfilmentService  = app(SellerFulfilmentService::class);
        $this->disputeService     = app(BuyerDisputeService::class);

        $this->adminUser = User::create([
            'name'     => 'UAT Super Admin',
            'email'    => 'admin.uat64@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        $this->seller1 = User::create(['name' => 'Seller One Paints', 'email' => 'seller1.64@factory.in', 'password' => bcrypt('password123')]);
        Company::create(['user_id' => $this->seller1->id, 'name' => 'Seller One Co', 'city' => 'Jaipur', 'state' => 'Rajasthan', 'pincode' => '302001', 'mobile' => '9111111101', 'comission' => 10, 'status' => 'active']);

        $this->seller2 = User::create(['name' => 'Seller Two Ceramics', 'email' => 'seller2.64@factory.in', 'password' => bcrypt('password123')]);
        Company::create(['user_id' => $this->seller2->id, 'name' => 'Seller Two Co', 'city' => 'Morbi', 'state' => 'Gujarat', 'pincode' => '363641', 'mobile' => '9111111102', 'comission' => 8, 'status' => 'active']);

        $this->seller3 = User::create(['name' => 'Seller Three Tools', 'email' => 'seller3.64@factory.in', 'password' => bcrypt('password123')]);
        Company::create(['user_id' => $this->seller3->id, 'name' => 'Seller Three Co', 'city' => 'Pune', 'state' => 'Maharashtra', 'pincode' => '411001', 'mobile' => '9111111103', 'comission' => 12, 'status' => 'active']);

        $this->buyerUser1 = User::create(['name' => 'UAT Buyer Hub', 'email' => 'buyer1.64@dealer.com', 'password' => bcrypt('password123')]);
        $this->buyer1 = Customer::create(['name' => 'UAT Buyer Hub', 'email' => 'buyer1.64@dealer.com', 'mobile' => '9888064001', 'password' => bcrypt('password123'), 'location' => 'Jaipur', 'status' => 'active']);
        $this->buyer2 = Customer::create(['name' => 'UAT Buyer Second', 'email' => 'buyer2.64@dealer.com', 'mobile' => '9888064002', 'password' => bcrypt('password123'), 'location' => 'Surat', 'status' => 'active']);
    }

    protected function enableApprovedFinancialPolicies(): AgentIncentiveRule
    {
        Config::set('marketplace_agents.funding_policy_approved', true);
        Config::set('marketplace_agents.incentive_calculation_enabled', true);
        Config::set('marketplace_agents.payout_execution_enabled', true);
        Config::set('marketplace_agents.payout_execution_mode', 'manual_finance_recording');
        Config::set('marketplace_agents.payout_tax_withholding_approved', true);
        Config::set('marketplace_agents.expose_agent_incentive_amounts', true);

        $rule = $this->ruleService->createRule([
            'rule_code'          => 'INC-E2E-64',
            'name'               => 'UAT Approved 5% Scheme',
            'calculation_method' => AgentIncentiveRule::METHOD_PERCENTAGE_ORDER,
            'calculation_base'   => AgentIncentiveRule::BASE_TAXABLE_ORDER,
            'rate_percentage'    => 5.0,
            'rounding_rule'      => AgentIncentiveRule::ROUND_HALF_UP_2,
            'tax_treatment_code' => AgentIncentiveRule::TAX_EXCLUSIVE_NO_GST,
            'effective_from'     => now()->subDays(2),
            'agreement_ref'      => 'BRD-64-UAT-APPROVAL',
        ], $this->adminUser);
        $this->ruleService->recordBusinessApproval($rule, $this->adminUser, 'BUS-64');
        $this->ruleService->recordFinanceApproval($rule->fresh(), $this->adminUser, 'FIN-64');

        return $this->ruleService->activateRule($rule->fresh(), $this->adminUser);
    }

    protected function executeVerifiedOrderFlow(Customer $buyer, array $sellers, float $netAmount = 10000.00): Order
    {
        $gross = round($netAmount * 1.18, 2);
        $orderNo = 'ORD-E2E-' . strtoupper(uniqid());
        $parent = Order::create([
            'orderno'        => $orderNo,
            'customer_id'    => $buyer->id,
            'user_id'        => $sellers[0]->id,
            'address'        => 'Transport Nagar, ' . $buyer->location,
            'netamount'      => $netAmount,
            'taxamount'      => round($gross - $netAmount, 2),
            'grandtotal'     => $gross,
            'payment_status' => 'Pending',
        ]);

        $targets = [];
        if (count($sellers) === 1) {
            $targets[] = [$parent, $sellers[0]];
        } else {
            foreach ($sellers as $idx => $s) {
                $child = Order::create([
                    'parent_order_id' => $parent->id,
                    'orderno'         => $orderNo . '-C' . ($idx + 1),
                    'customer_id'     => $buyer->id,
                    'user_id'         => $s->id,
                    'address'         => $parent->address,
                    'netamount'       => round($netAmount / count($sellers), 2),
                    'taxamount'       => round(($gross - $netAmount) / count($sellers), 2),
                    'grandtotal'      => round($gross / count($sellers), 2),
                    'payment_status'  => 'Pending',
                ]);
                $targets[] = [$child, $s];
            }
        }

        // Verified Payment Gateway Callback
        $init = $this->paymentService->initiatePayment($parent, 'razorpay', 'idem_' . $orderNo);
        $gwOrder = $init['gateway_order_id'];
        $gwPay = 'pay_' . uniqid();
        $sig = hash_hmac('sha256', "{$gwOrder}|{$gwPay}", $this->razorpaySecret);

        $cb = $this->paymentService->processCallback('razorpay', [
            'razorpay_order_id'   => $gwOrder,
            'razorpay_payment_id' => $gwPay,
            'razorpay_signature'  => $sig,
            'amount'              => $gross,
            'status'              => 'captured',
        ], $sig, 'evt_' . uniqid());
        $this->assertTrue($cb['success']);

        // Complete dispatch + buyer acknowledgement across all seller fulfilments
        foreach ($targets as [$ord, $seller]) {
            $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($ord->fresh());
            $this->fulfilmentService->updateStatus($fulfilment, SellerFulfilment::STATUS_ACCEPTED, $seller->id, 'seller', $seller->name);
            $this->fulfilmentService->recordDispatch($fulfilment->fresh(), ['transporter_name' => 'Delhivery Freight', 'lr_number' => 'LR-' . rand(1000, 9999)], $seller->id, $seller->name);
            $this->disputeService->acknowledgeReceipt($fulfilment->fresh(), $buyer->id, $buyer->name, 'Received verified goods');
        }

        return $parent->fresh();
    }

    /**
     * AGT-01 through AGT-08: Complete End-to-End Business & Financial Integration Verification.
     */
    public function test_e2e_agt_01_to_agt_08_complete_field_agent_lifecycle_and_reconciliation(): void
    {
        Queue::fake();
        $this->enableApprovedFinancialPolicies();

        // AGT-01: Agent creation, activation, inactive attribution rejection, active attribution + approval
        $this->agentA = $this->agentService->createAgent([
            'agent_code'          => 'FSA-E2E-01',
            'name'                => 'Rakesh Solanki',
            'email'               => 'rakesh.e2e@apnifactory.in',
            'mobile'              => '9829090001',
            'territory'           => 'Jaipur Urban',
            'status'              => 'pending',
            'kyc_status'          => 'verified',
            'contract_status'     => 'active',
            'create_user_account' => true,
            'payout_details'      => [
                'account_holder_name' => 'Rakesh Solanki',
                'bank_name'           => 'SBI',
                'account_number'      => '334455667788',
                'ifsc_code'           => 'SBIN0001122',
            ],
        ], $this->adminUser);
        $this->agentUserA = $this->agentA->user;

        // Pending agent cannot receive buyer attribution (AGT-01)
        $this->actingAs($this->adminUser);
        $this->postJson('/api/v2/admin/attributions', [
            'customer_id'    => $this->buyer1->id,
            'field_agent_id' => $this->agentA->id,
        ])->assertStatus(422);

        // Activate agent -> assign primary buyer with approval queue -> approve attribution
        $this->agentService->changeStatus($this->agentA, FieldAgent::STATUS_ACTIVE, $this->adminUser, 'Verified KYC & onboarding');
        $attrRes = $this->postJson('/api/v2/admin/attributions', [
            'customer_id'      => $this->buyer1->id,
            'field_agent_id'   => $this->agentA->id,
            'require_approval' => true,
            'idempotency_key'  => 'attr_e2e_01',
        ])->assertStatus(201);
        $attrId = $attrRes->json('data.id');
        $this->postJson("/api/v2/admin/attributions/{$attrId}/approve")->assertStatus(200);

        // Agent B creation for AGT-08 isolation checks
        $this->agentB = $this->agentService->createAgent([
            'agent_code'          => 'FSA-E2E-02',
            'name'                => 'Other Zone Agent',
            'email'               => 'agentb.e2e@apnifactory.in',
            'mobile'              => '9829090002',
            'territory'           => 'Surat East',
            'status'              => 'active',
            'kyc_status'          => 'verified',
            'contract_status'     => 'active',
            'create_user_account' => true,
            'payout_details'      => ['bank_name' => 'Axis', 'account_number' => '9988776655', 'ifsc_code' => 'UTIB0000100'],
        ], $this->adminUser);
        $this->agentUserB = $this->agentB->user;

        // AGT-02 & AGT-03: Unpaid & Cancelled orders do NOT consume slots; Orders 1 (Seller 1), 2 (Seller 2), and 3 (Multi-seller 3 sellers) reach 3/3; Order 4 exceeds limit!
        $unpaidOrd = Order::create(['orderno' => 'ORD-E2E-UNPAID', 'customer_id' => $this->buyer1->id, 'user_id' => $this->seller1->id, 'netamount' => 5000, 'grandtotal' => 5900, 'payment_status' => 'Pending']);
        $this->eligibilityService->handleOrderLifecycleEvent($unpaidOrd, 'created', 'e2e_unpaid');

        $ord1 = $this->executeVerifiedOrderFlow($this->buyer1, [$this->seller1], 10000.00); // 1/3 -> Rs 500
        $ord2 = $this->executeVerifiedOrderFlow($this->buyer1, [$this->seller2], 20000.00); // 2/3 -> Rs 1000
        $ord3 = $this->executeVerifiedOrderFlow($this->buyer1, [$this->seller1, $this->seller2, $this->seller3], 30000.00); // 3/3 (counts ONCE!) -> Rs 1500
        $ord4 = $this->executeVerifiedOrderFlow($this->buyer1, [$this->seller3], 10000.00); // Over limit -> no slot

        $this->assertEquals('3/3', $this->eligibilityService->getBuyerEligibilityProgress($this->buyer1->id)['display']);
        $this->assertEquals(1, AgentEligibleOrder::where('parent_order_id', $ord1->id)->value('sequence_number'));
        $this->assertEquals(2, AgentEligibleOrder::where('parent_order_id', $ord2->id)->value('sequence_number'));
        $this->assertEquals(3, AgentEligibleOrder::where('parent_order_id', $ord3->id)->value('sequence_number'));
        $this->assertEquals(AgentEligibleOrder::STATUS_INELIGIBLE_LIMIT_EXCEEDED, AgentEligibleOrder::where('parent_order_id', $ord4->id)->value('eligibility_status'));

        // AGT-04 & AGT-05: Effective rule calculates 5% of taxable order value on Apni Factory expense with ZERO seller settlement deductions
        $ledgers = AgentIncentiveLedger::where('customer_id', $this->buyer1->id)->where('entry_type', 'earned')->orderBy('eligible_order_sequence')->get();
        $this->assertCount(3, $ledgers);
        $this->assertEquals([500.00, 1000.00, 1500.00], $ledgers->pluck('calculated_incentive_amount')->all());

        // Seller settlement untouched (10% seller1 commission + 18% GST on commission deducted from seller; ZERO agent deductions!)
        $settlement1 = SellerSettlement::where('order_id', $ord1->id)->firstOrFail();
        $this->assertEquals(11800.00 - 1180.00 - 212.40, (float)$settlement1->net_seller_payable);

        // Approve all 3 ledgers -> Batch Payout -> Authorize -> Initiate -> Confirm Paid with replay safety (AGT-06)
        foreach ($ledgers as $l) {
            $this->postJson("/api/v2/admin/incentives/ledger/{$l->id}/approve")->assertStatus(200);
        }

        $payoutRes = $this->postJson('/api/v2/admin/payouts', [
            'field_agent_id'  => $this->agentA->id,
            'ledger_ids'      => $ledgers->pluck('id')->all(),
            'payment_method'  => AgentPayout::METHOD_MANUAL_BANK,
            'idempotency_key' => 'e2e_payout_batch_1',
        ])->assertStatus(201);
        $payoutId = $payoutRes->json('data.id');

        $this->postJson("/api/v2/admin/payouts/{$payoutId}/authorize")->assertStatus(200);
        $this->postJson("/api/v2/admin/payouts/{$payoutId}/initiate")->assertStatus(200);

        // Replay payment confirmation twice
        foreach ([1, 2] as $attempt) {
            $this->postJson("/api/v2/admin/payouts/{$payoutId}/confirm", [
                'confirmation_source'      => 'verified_bank_statement',
                'verification_status'      => 'verified',
                'external_transaction_ref' => 'UTR-E2E-64001',
                'callback_event_id'        => 'cb_e2e_64_01',
                'confirmed_amount'         => 3000.00,
            ])->assertStatus(200);
        }

        $payoutSummary = $this->getJson('/api/v2/admin/payouts/summary')->assertStatus(200)->json('data');
        $this->assertEquals(3000.00, $payoutSummary['confirmed_paid_total']);
        $this->assertEquals(3000.00, $payoutSummary['payout_batches_paid_sum']);
        $this->assertEquals(0.0, $payoutSummary['reconciled_checksum_diff']);

        // AGT-07 & AGT-08: Agent A sees own buyers, progress & confirmed payout (masked bank), NEVER raw account no; Agent B gets 403 on cross-agent IDOR!
        $this->actingAs($this->agentUserA);
        $agentPayouts = $this->getJson('/api/v2/agent/payouts')->assertStatus(200);
        $this->assertEquals('UTR-E2E-64001', $agentPayouts->json('data.0.external_transaction_ref'));
        $this->assertStringNotContainsString('334455667788', $agentPayouts->getContent());

        $this->actingAs($this->agentUserB);
        $this->getJson("/api/v2/agent/payouts/{$payoutId}")->assertStatus(403);
        $this->getJson("/api/v2/agent/buyers/{$this->buyer1->id}")->assertStatus(403);

        Queue::assertPushed(SendAgentAttributionNotificationJob::class);
        Queue::assertPushed(SendAgentIncentiveNotificationJob::class);
    }

    /**
     * Stage 6, 8 & 10: Zero N+1 queries on Agent Buyers, public endpoint privacy, mass-assignment & rate-limit wiring.
     */
    public function test_performance_n_plus_one_eliminated_and_public_privacy_and_rate_limiter_enforced(): void
    {
        $agent = $this->agentService->createAgent([
            'agent_code'          => 'FSA-PERF-01',
            'name'                => 'Perf Test Agent',
            'email'               => 'perf.agent@apnifactory.in',
            'mobile'              => '9829091001',
            'territory'           => 'Perf Territory',
            'status'              => 'active',
            'create_user_account' => true,
        ], $this->adminUser);

        // Create 15 attributed buyers with orders -> ensure single batch query executes on /api/v2/agent/buyers (NO N+1!)
        for ($i = 1; $i <= 15; $i++) {
            $cust = Customer::create([
                'name'     => "Perf Dealer {$i}",
                'email'    => "perf.dealer{$i}@dealer.com",
                'mobile'   => '9888091' . str_pad((string)$i, 3, '0', STR_PAD_LEFT),
                'password' => bcrypt('password123'),
                'status'   => 'active',
            ]);
            $this->attributionService->createAttribution([
                'customer_id'    => $cust->id,
                'field_agent_id' => $agent->id,
            ], $this->adminUser);
        }

        $this->actingAs($agent->user);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $res = $this->getJson('/api/v2/agent/buyers?per_page=20')->assertStatus(200);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(15, $res->json('data'));
        // Strictly bounded query count (< 8 total queries regardless of 15+ buyers, proving 0 N+1 lookups)
        $this->assertLessThanOrEqual(8, count($queries), 'Expected bounded batch query count without N+1 selects.');

        // Public website & catalogue endpoints must NEVER expose agent/incentive/KYC secrets
        $product = Product::create(['name' => 'Public Coat 10L', 'slug' => 'public-coat-10l-uat', 'status' => 'Active', 'user_id' => $this->seller1->id]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'variant_sku' => 'PUB-64-1', 'variant_name' => '10L']);
        $listing = SellerListing::create([
            'seller_user_id'     => $this->seller1->id,
            'company_id'         => $this->seller1->company->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'seller_sku'         => 'SKU-PUB-64',
            'seller_price'       => 1200.00,
            'selling_price'      => 1500.00,
            'stock_quantity'     => 50,
            'status'             => 'Active',
        ]);

        $pubRes = $this->getJson("/api/v2/public/listings/{$listing->id}")->assertStatus(200);
        $pubBody = $pubRes->getContent();
        $this->assertStringNotContainsString('FSA-PERF-01', $pubBody);
        $this->assertStringNotContainsString('field_agent', $pubBody);
        $this->assertStringNotContainsString('payout_details', $pubBody);

        // Verify sensitive endpoints enforce throttle rate limiting middleware headers
        $this->actingAs($this->adminUser);
        $rulesRes = $this->getJson('/api/v2/admin/payouts');
        $rulesRes->assertHeader('X-RateLimit-Limit');
    }

    /**
     * Stage 10 & 11: Dead-letter web recovery action & Staging UAT Seeder verification.
     */
    public function test_uat_seeder_and_dead_letter_web_retry_recovery(): void
    {
        $this->seed(Phase6AgentModuleUatSeeder::class);
        // Second seed run is safely idempotent
        $this->seed(Phase6AgentModuleUatSeeder::class);

        $uatAgent = FieldAgent::where('agent_code', 'FSA-UAT-001')->first();
        $this->assertNotNull($uatAgent);
        $this->assertEquals(AgentIncentiveRule::STATUS_DRAFT, AgentIncentiveRule::where('rule_code', 'INC-RULE-UAT-01')->value('status'));
        $this->assertEquals(0, AgentIncentiveLedger::count()); // Safe default: zero financial liabilities until policy signed

        $dealer1 = Customer::where('email', 'uat.dealer1@apnifactory.test')->firstOrFail();
        $this->assertEquals('1/3', $this->eligibilityService->getBuyerEligibilityProgress($dealer1->id)['display']);

        // Simulate failed event log visible on Admin screen + recover via web POST
        $pendingOrder = Order::where('orderno', 'ORD-UAT-PEND-02')->firstOrFail();
        $failLog = AgentIncentiveProcessingLog::create([
            'event_key'       => 'uat_fail_recover_01',
            'event_type'      => 'payment_verified',
            'parent_order_id' => $pendingOrder->id,
            'order_id'        => $pendingOrder->id,
            'status'          => AgentIncentiveProcessingLog::STATUS_FAILED,
            'error_message'   => 'Simulated UAT worker interruption',
        ]);

        $this->actingAs($this->adminUser);
        $this->get('/admin/incentives/eligible-orders')
            ->assertStatus(200)
            ->assertSee('Dead-Letter')
            ->assertSee('uat_fail_recover_01');

        $this->post("/admin/incentives/failures/{$failLog->id}/retry")
            ->assertRedirect();
        $this->assertEquals(AgentIncentiveProcessingLog::STATUS_PROCESSED, $failLog->fresh()->status);
    }
}

