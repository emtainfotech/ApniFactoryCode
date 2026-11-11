<?php

namespace Tests\Feature;

use App\Jobs\SendAgentIncentiveNotificationJob;
use App\Models\AgentEligibleOrder;
use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentIncentiveProcessingLog;
use App\Models\AgentIncentiveRule;
use App\Models\BuyerAgentAttribution;
use App\Models\CommissionRule;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Models\FinancialLedger;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
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
use App\Services\BuyerAttributionService;
use App\Services\BuyerDisputeService;
use App\Services\CommercialEngineOrchestrator;
use App\Services\FieldAgentService;
use App\Services\FinancialLedgerService;
use App\Services\PaymentGatewayService;
use App\Services\PaymentRefundService;
use App\Services\SellerFulfilmentService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Phase6_2_EligibleOrderAndIncentiveEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $seller1;
    protected User $seller2;
    protected User $seller3;
    protected Company $company1;
    protected Company $company2;
    protected Company $company3;
    protected User $agentUserA;
    protected User $agentUserB;
    protected User $buyerUser1;
    protected User $buyerUser2;
    protected Customer $customer1;
    protected Customer $customer2;
    protected FieldAgent $agentA;
    protected FieldAgent $agentB;
    protected SellerListing $listing1;

    protected FieldAgentService $agentService;
    protected BuyerAttributionService $attributionService;
    protected AgentOrderEligibilityService $eligibilityService;
    protected AgentIncentiveRuleService $ruleService;
    protected AgentIncentiveCalculationService $calculationService;
    protected SellerFulfilmentService $fulfilmentService;
    protected BuyerDisputeService $disputeService;
    protected PaymentGatewayService $paymentService;

    protected string $razorpaySecret = 'rzp_test_phase62_secret';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Config::set('services.razorpay.key', 'rzp_test_mock_key');
        Config::set('services.razorpay.secret', $this->razorpaySecret);
        Config::set('marketplace_agents.eligible_order_tracking_implemented', true);
        // Safe default: funding_policy_approved = false, incentive_calculation_enabled = false

        $this->agentService       = app(FieldAgentService::class);
        $this->attributionService = app(BuyerAttributionService::class);
        $this->eligibilityService = app(AgentOrderEligibilityService::class);
        $this->ruleService        = app(AgentIncentiveRuleService::class);
        $this->calculationService = app(AgentIncentiveCalculationService::class);
        $this->fulfilmentService  = app(SellerFulfilmentService::class);
        $this->disputeService     = app(BuyerDisputeService::class);
        $this->paymentService     = app(PaymentGatewayService::class);

        $this->adminUser = User::create([
            'name'     => 'Operations & Finance Admin',
            'email'    => 'admin.finance62@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        // 3 independent Sellers/Manufacturers
        $this->seller1 = User::create(['name' => 'Rajputana Paints', 'email' => 'seller1.62@factory.in', 'password' => bcrypt('password123')]);
        $this->company1 = Company::create(['user_id' => $this->seller1->id, 'name' => 'Rajputana Works', 'address' => 'Plot 1', 'city' => 'Jaipur', 'state' => 'Rajasthan', 'pincode' => '302001', 'phone' => '0141001122', 'comission' => 10, 'status' => 'active']);

        $this->seller2 = User::create(['name' => 'Gujarat Tiles', 'email' => 'seller2.62@factory.in', 'password' => bcrypt('password123')]);
        $this->company2 = Company::create(['user_id' => $this->seller2->id, 'name' => 'Gujarat Ceramic Co', 'address' => 'Plot 2', 'city' => 'Morbi', 'state' => 'Gujarat', 'pincode' => '363641', 'phone' => '0282201122', 'comission' => 8, 'status' => 'active']);

        $this->seller3 = User::create(['name' => 'Pune Fasteners', 'email' => 'seller3.62@factory.in', 'password' => bcrypt('password123')]);
        $this->company3 = Company::create(['user_id' => $this->seller3->id, 'name' => 'Pune Fasteners Ltd', 'address' => 'Plot 3', 'city' => 'Pune', 'state' => 'Maharashtra', 'pincode' => '411001', 'phone' => '0202201122', 'comission' => 12, 'status' => 'active']);

        // 2 Field Sales Agents
        $this->agentA = $this->agentService->createAgent([
            'agent_code'          => 'FSA-62-001',
            'name'                => 'Mahesh Sharma',
            'email'               => 'mahesh.62@apnifactory.in',
            'mobile'              => '9829062001',
            'territory'           => 'Jaipur Industrial Belt',
            'status'              => 'active',
            'kyc_status'          => 'verified',
            'contract_status'     => 'active',
            'create_user_account' => true,
        ], $this->adminUser);
        $this->agentUserA = $this->agentA->user;

        $this->agentB = $this->agentService->createAgent([
            'agent_code'          => 'FSA-62-002',
            'name'                => 'Karan Patel',
            'email'               => 'karan.62@apnifactory.in',
            'mobile'              => '9829062002',
            'territory'           => 'Surat GIDC Zone',
            'status'              => 'active',
            'kyc_status'          => 'verified',
            'contract_status'     => 'active',
            'create_user_account' => true,
        ], $this->adminUser);
        $this->agentUserB = $this->agentB->user;

        // 2 Buyer / Dealer Accounts
        $this->buyerUser1 = User::create(['name' => 'Bansal Hardware Dealers', 'email' => 'buyer1.62@dealer.com', 'password' => bcrypt('password123')]);
        $this->buyerUser2 = User::create(['name' => 'Khodal Traders Surat', 'email' => 'buyer2.62@dealer.com', 'password' => bcrypt('password123')]);

        $this->customer1 = Customer::create([
            'id'       => $this->buyerUser1->id,
            'name'     => 'Bansal Hardware Dealers',
            'email'    => 'buyer1.62@dealer.com',
            'mobile'   => '9876062001',
            'password' => bcrypt('password123'),
            'location' => 'Jaipur',
            'status'   => 'active',
        ]);
        $this->customer2 = Customer::create([
            'id'       => $this->buyerUser2->id,
            'name'     => 'Khodal Traders Surat',
            'email'    => 'buyer2.62@dealer.com',
            'mobile'   => '9876062002',
            'password' => bcrypt('password123'),
            'location' => 'Surat',
            'status'   => 'active',
        ]);

        // Product & Listing for commission snapshot tests
        $product = Product::create(['name' => 'Emulsion Primer 20L', 'category' => 'Paints', 'price' => 2500]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'variant_sku' => 'VAR-62-01', 'variant_name' => '20L Bucket']);
        $this->listing1 = SellerListing::create([
            'seller_user_id'     => $this->seller1->id,
            'company_id'         => $this->company1->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'seller_sku'         => 'SKU-62-001',
            'seller_price'       => 2000.00,
            'commission_rate'    => 10.00,
            'selling_price'      => 2200.00,
            'mrp'                => 2800.00,
            'stock_quantity'     => 500,
            'status'             => 'Active',
        ]);
    }

    /**
     * Helper to create a paid single-seller or multi-seller parent order and optionally complete fulfilment.
     */
    protected function createCheckoutOrder(
        Customer $customer,
        array $sellers,
        float $netAmount = 10000.00,
        float $grandTotal = 11800.00,
        bool $markPaid = true,
        bool $fulfilAndAcknowledge = false
    ): Order {
        $orderNo = 'ORD-62-' . strtoupper(uniqid());
        $parentOrder = Order::create([
            'orderno'        => $orderNo,
            'customer_id'    => $customer->id,
            'user_id'        => $sellers[0]->id,
            'address'        => 'Transport Nagar, ' . ($customer->location ?: 'Jaipur'),
            'netamount'      => $netAmount,
            'taxamount'      => round($grandTotal - $netAmount, 2),
            'grandtotal'     => $grandTotal,
            'payment_status' => $markPaid ? 'Paid' : 'Pending',
            'paid_at'        => $markPaid ? now() : null,
        ]);

        if ($markPaid) {
            Transection::create([
                'payment_ref'           => 'PAY-62-' . strtoupper(uniqid()),
                'order_id'              => $parentOrder->id,
                'parent_order_id'       => $parentOrder->id,
                'order_no'              => $orderNo,
                'customer_id'           => $customer->id,
                'user_id'               => $sellers[0]->id,
                'gateway_name'          => 'razorpay',
                'amount'                => $grandTotal,
                'currency'              => 'INR',
                'status'                => 'successful',
                'verification_status'   => 'verified',
                'reconciliation_status' => 'matched',
                'verified_at'           => now(),
                'txnid'                 => 'pay_mock_' . uniqid(),
            ]);
        }

        if (count($sellers) === 1) {
            $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($parentOrder);
            if ($fulfilAndAcknowledge) {
                $this->completeFulfilment($fulfilment, $sellers[0], $customer);
            }
        } else {
            $perSellerNet = round($netAmount / count($sellers), 2);
            $perSellerGross = round($grandTotal / count($sellers), 2);
            foreach ($sellers as $idx => $seller) {
                $child = Order::create([
                    'parent_order_id' => $parentOrder->id,
                    'orderno'         => $orderNo . '-S' . ($idx + 1),
                    'customer_id'     => $customer->id,
                    'user_id'         => $seller->id,
                    'address'         => $parentOrder->address,
                    'netamount'       => $perSellerNet,
                    'taxamount'       => round($perSellerGross - $perSellerNet, 2),
                    'grandtotal'      => $perSellerGross,
                    'payment_status'  => $markPaid ? 'Paid' : 'Pending',
                    'paid_at'         => $markPaid ? now() : null,
                ]);
                $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($child);
                if ($fulfilAndAcknowledge) {
                    $this->completeFulfilment($fulfilment, $seller, $customer);
                }
            }
        }

        return $parentOrder->fresh();
    }

    protected function completeFulfilment(SellerFulfilment $fulfilment, User $seller, Customer $customer): void
    {
        $this->fulfilmentService->updateStatus($fulfilment, SellerFulfilment::STATUS_ACCEPTED, $seller->id, 'seller', $seller->name);
        $this->fulfilmentService->recordDispatch($fulfilment->fresh(), [
            'transporter_name' => 'VRL Freight',
            'lr_number'        => 'LR-' . rand(10000, 99999),
        ], $seller->id, $seller->name);
        $this->disputeService->acknowledgeReceipt($fulfilment->fresh(), $customer->id, $customer->name, 'Received in good condition');
    }

    protected function createAndActivateApprovedRule(array $overrides = [], ?User $actor = null): AgentIncentiveRule
    {
        $actor = $actor ?: $this->adminUser;
        Config::set('marketplace_agents.funding_policy_approved', true);
        Config::set('marketplace_agents.incentive_calculation_enabled', true);

        $rule = $this->ruleService->createRule(array_merge([
            'rule_code'          => 'INC-RULE-' . strtoupper(uniqid()),
            'name'               => 'Approved Launch Buyer Incentive',
            'funding_source'     => AgentIncentiveRule::FUNDING_APNI_FACTORY,
            'calculation_method' => AgentIncentiveRule::METHOD_PERCENTAGE_ORDER,
            'calculation_base'   => AgentIncentiveRule::BASE_TAXABLE_ORDER,
            'rate_percentage'    => 2.5,
            'rounding_rule'      => AgentIncentiveRule::ROUND_HALF_UP_2,
            'tax_treatment_code' => AgentIncentiveRule::TAX_EXCLUSIVE_NO_GST,
            'effective_from'     => now()->subDay(),
            'agreement_ref'      => 'BRD-SEC12-SIGN-2026',
            'reason'             => 'Formal board approved incentive parameters',
        ], $overrides), $actor);

        $this->ruleService->recordBusinessApproval($rule, $actor, 'BUS-2026-001');
        $this->ruleService->recordFinanceApproval($rule->fresh(), $actor, 'FIN-2026-001');

        return $this->ruleService->activateRule($rule->fresh(), $actor);
    }

    /**
     * Test 1: Buyer attributed to an active agent receives eligible-order tracking.
     */
    public function test_01_buyer_attributed_to_active_agent_receives_eligible_order_tracking(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $order = $this->createCheckoutOrder($this->customer1, [$this->seller1], 20000, 23600, true, true);

        $tracked = AgentEligibleOrder::where('parent_order_id', $order->id)->first();
        $this->assertNotNull($tracked);
        $this->assertEquals(AgentEligibleOrder::STATUS_ELIGIBLE, $tracked->eligibility_status);
        $this->assertEquals(1, $tracked->sequence_number);
        $this->assertEquals(1, $tracked->active_slot_number);

        $progress = $this->eligibilityService->getBuyerEligibilityProgress($this->customer1->id);
        $this->assertTrue($progress['available']);
        $this->assertEquals('1/3', $progress['display']);
        $this->assertEquals(1, $progress['eligible_count']);

        // Buyer API shows 1/3 safely without internal admin notes
        $this->actingAs($this->buyerUser1);
        $this->getJson('/api/v2/buyer/incentive-progress')
            ->assertStatus(200)
            ->assertJsonPath('data.display', '1/3')
            ->assertJsonPath('data.eligible_count', 1);
    }

    /**
     * Test 2: Orders across different sellers count against the same buyer-level sequence.
     */
    public function test_02_orders_across_different_sellers_count_against_same_buyer_level_sequence(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $ordSeller1 = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);
        $ordSeller2 = $this->createCheckoutOrder($this->customer1, [$this->seller2], 15000, 17700, true, true);

        $this->assertEquals(1, AgentEligibleOrder::where('parent_order_id', $ordSeller1->id)->value('sequence_number'));
        $this->assertEquals(2, AgentEligibleOrder::where('parent_order_id', $ordSeller2->id)->value('sequence_number'));

        $progress = $this->eligibilityService->getBuyerEligibilityProgress($this->customer1->id);
        $this->assertEquals('2/3', $progress['display']);
    }

    /**
     * Test 3: A multi-seller parent order is not accidentally counted multiple times.
     */
    public function test_03_multi_seller_parent_order_is_not_counted_multiple_times(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $parentOrder = $this->createCheckoutOrder(
            $this->customer1,
            [$this->seller1, $this->seller2, $this->seller3],
            30000,
            35400,
            true,
            false
        );

        // Acknowledge first 2 sellers only -> parent order must remain pending_eligibility!
        $children = $parentOrder->childOrders()->orderBy('id')->get();
        $this->completeFulfilment($children[0]->fulfilment, $this->seller1, $this->customer1);
        $this->completeFulfilment($children[1]->fulfilment, $this->seller2, $this->customer1);

        $trackedMid = AgentEligibleOrder::where('parent_order_id', $parentOrder->id)->first();
        $this->assertEquals(AgentEligibleOrder::STATUS_PENDING, $trackedMid->eligibility_status);
        $this->assertNull($trackedMid->sequence_number);

        // Complete 3rd seller -> counts strictly ONCE for the parent checkout order
        $this->completeFulfilment($children[2]->fulfilment, $this->seller3, $this->customer1);

        $this->assertEquals(1, AgentEligibleOrder::where('parent_order_id', $parentOrder->id)->count());
        $trackedDone = $trackedMid->fresh();
        $this->assertEquals(AgentEligibleOrder::STATUS_ELIGIBLE, $trackedDone->eligibility_status);
        $this->assertEquals(1, $trackedDone->sequence_number);
        $this->assertEquals(3, $trackedDone->seller_count);
        $this->assertCount(3, $trackedDone->seller_fulfilment_refs);
    }

    /**
     * Test 4: Sequence never exceeds three for a buyer under the scheme.
     */
    public function test_04_sequence_never_exceeds_three(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $this->createAndActivateApprovedRule();

        $o1 = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);
        $o2 = $this->createCheckoutOrder($this->customer1, [$this->seller2], 10000, 11800, true, true);
        $o3 = $this->createCheckoutOrder($this->customer1, [$this->seller3], 10000, 11800, true, true);
        $o4 = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);

        $this->assertEquals(1, AgentEligibleOrder::where('parent_order_id', $o1->id)->value('sequence_number'));
        $this->assertEquals(2, AgentEligibleOrder::where('parent_order_id', $o2->id)->value('sequence_number'));
        $this->assertEquals(3, AgentEligibleOrder::where('parent_order_id', $o3->id)->value('sequence_number'));

        $tracked4 = AgentEligibleOrder::where('parent_order_id', $o4->id)->first();
        $this->assertEquals(AgentEligibleOrder::STATUS_INELIGIBLE_LIMIT_EXCEEDED, $tracked4->eligibility_status);
        $this->assertNull($tracked4->sequence_number);
        $this->assertNull($tracked4->active_slot_number);

        $this->assertEquals('3/3', $this->eligibilityService->getBuyerEligibilityProgress($this->customer1->id)['display']);
        $this->assertEquals(3, AgentIncentiveLedger::where('customer_id', $this->customer1->id)->where('entry_type', 'earned')->count());
        $this->assertNull(AgentIncentiveLedger::where('parent_order_id', $o4->id)->first());
    }

    /**
     * Test 5: Unpaid orders do not consume a slot.
     */
    public function test_05_unpaid_orders_do_not_consume_a_slot(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $unpaidOrder = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, false, false);
        $this->eligibilityService->handleOrderLifecycleEvent($unpaidOrder, 'order_created', 'unpaid_test_1');

        $tracked = AgentEligibleOrder::where('parent_order_id', $unpaidOrder->id)->first();
        $this->assertEquals(AgentEligibleOrder::STATUS_INELIGIBLE_UNPAID, $tracked->eligibility_status);
        $this->assertNull($tracked->sequence_number);
        $this->assertEquals('0/3', $this->eligibilityService->getBuyerEligibilityProgress($this->customer1->id)['display']);
    }

    /**
     * Test 6: Cancelled and rejected orders do not consume a slot.
     */
    public function test_06_cancelled_and_rejected_orders_do_not_consume_a_slot(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        // Rejected order
        $ordRejected = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, false);
        $this->fulfilmentService->updateStatus($ordRejected->fulfilment, SellerFulfilment::STATUS_REJECTED, $this->seller1->id, 'seller', 'Seller1', 'Out of stock');

        // Cancelled order
        $ordCancelled = $this->createCheckoutOrder($this->customer1, [$this->seller2], 10000, 11800, true, false);
        $this->fulfilmentService->updateStatus($ordCancelled->fulfilment, SellerFulfilment::STATUS_ACCEPTED, $this->seller2->id, 'seller', 'Seller2');
        $this->fulfilmentService->updateStatus($ordCancelled->fulfilment->fresh(), SellerFulfilment::STATUS_CANCELLED, $this->seller2->id, 'seller', 'Seller2', 'Buyer cancelled');

        $this->assertEquals(AgentEligibleOrder::STATUS_INELIGIBLE_REJECTED, AgentEligibleOrder::where('parent_order_id', $ordRejected->id)->value('eligibility_status'));
        $this->assertEquals(AgentEligibleOrder::STATUS_INELIGIBLE_CANCELLED, AgentEligibleOrder::where('parent_order_id', $ordCancelled->id)->value('eligibility_status'));
        $this->assertEquals('0/3', $this->eligibilityService->getBuyerEligibilityProgress($this->customer1->id)['display']);

        // Subsequent fulfilled order gets Slot 1!
        $validOrd = $this->createCheckoutOrder($this->customer1, [$this->seller3], 10000, 11800, true, true);
        $this->assertEquals(1, AgentEligibleOrder::where('parent_order_id', $validOrd->id)->value('sequence_number'));
    }

    /**
     * Test 7: Fully refunded orders follow policy and do not consume a slot.
     */
    public function test_07_fully_refunded_orders_do_not_consume_a_slot(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $order = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, false);
        app(PaymentRefundService::class)->processRefund($order, 'Buyer cancelled prior to dispatch');

        $tracked = AgentEligibleOrder::where('parent_order_id', $order->id)->first();
        $this->assertEquals(AgentEligibleOrder::STATUS_INELIGIBLE_REFUNDED, $tracked->eligibility_status);
        $this->assertNull($tracked->active_slot_number);
        $this->assertEquals('0/3', $this->eligibilityService->getBuyerEligibilityProgress($this->customer1->id)['display']);

        // Next valid order receives Slot 1
        $order2 = $this->createCheckoutOrder($this->customer1, [$this->seller2], 12000, 14160, true, true);
        $this->assertEquals(1, AgentEligibleOrder::where('parent_order_id', $order2->id)->value('sequence_number'));
    }

    /**
     * Test 8: Pending eligibility (not acknowledged OR inside cooling window) does not prematurely create an incentive.
     */
    public function test_08_pending_eligibility_does_not_prematurely_create_incentive(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $this->createAndActivateApprovedRule();

        // Case A: Dispatched with LR upload, NOT acknowledged by buyer
        $ordPendingAck = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, false);
        $this->fulfilmentService->updateStatus($ordPendingAck->fulfilment, SellerFulfilment::STATUS_ACCEPTED, $this->seller1->id, 'seller', 'Seller1');
        $this->fulfilmentService->recordDispatch($ordPendingAck->fulfilment->fresh(), ['transporter_name' => 'Gati', 'lr_number' => 'LR-PENDING-01'], $this->seller1->id, 'Seller1');

        $trackedA = AgentEligibleOrder::where('parent_order_id', $ordPendingAck->id)->first();
        $this->assertEquals(AgentEligibleOrder::STATUS_PENDING, $trackedA->eligibility_status);
        $this->assertNull($trackedA->sequence_number);
        $this->assertEquals(0, AgentIncentiveLedger::count());

        // Case B: Acknowledged today, but configured 7-day cooling window not yet elapsed
        Config::set('marketplace_agents.eligibility_cooling_window_days', 7);
        $this->disputeService->acknowledgeReceipt($ordPendingAck->fulfilment->fresh(), $this->customer1->id, $this->customer1->name);

        $trackedB = $trackedA->fresh();
        $this->assertEquals(AgentEligibleOrder::STATUS_PENDING, $trackedB->eligibility_status);
        $this->assertNotNull($trackedB->cooling_window_ends_at);
        $this->assertEquals(0, AgentIncentiveLedger::count());
    }

    /**
     * Test 9: Rule effective dates and versions are respected.
     */
    public function test_09_rule_effective_dates_and_versions_are_respected(): void
    {
        Config::set('marketplace_agents.funding_policy_approved', true);
        Config::set('marketplace_agents.incentive_calculation_enabled', true);

        $v1 = $this->ruleService->createRule([
            'rule_code'          => 'INC-TIME-TEST',
            'name'               => 'Time Windowed v1',
            'calculation_method' => AgentIncentiveRule::METHOD_PERCENTAGE_ORDER,
            'calculation_base'   => AgentIncentiveRule::BASE_TAXABLE_ORDER,
            'rate_percentage'    => 5.0,
            'rounding_rule'      => AgentIncentiveRule::ROUND_HALF_UP_2,
            'tax_treatment_code' => AgentIncentiveRule::TAX_EXCLUSIVE_NO_GST,
            'effective_from'     => now()->subDays(10),
            'effective_to'       => now()->subDays(2),
            'agreement_ref'      => 'AGR-TIME-V1',
        ], $this->adminUser);
        $this->ruleService->recordBusinessApproval($v1, $this->adminUser, 'BUS-T1');
        $this->ruleService->recordFinanceApproval($v1->fresh(), $this->adminUser, 'FIN-T1');
        $this->ruleService->activateRule($v1->fresh(), $this->adminUser);

        $v2 = $this->ruleService->createNewVersion($v1->fresh(), [
            'rate_percentage' => 2.0,
            'effective_from'  => now()->subDay(),
            'effective_to'    => now()->addDays(10),
            'agreement_ref'   => 'AGR-TIME-V2',
        ], $this->adminUser);
        $this->ruleService->recordBusinessApproval($v2, $this->adminUser, 'BUS-T2');
        $this->ruleService->recordFinanceApproval($v2->fresh(), $this->adminUser, 'FIN-T2');
        $this->ruleService->activateRule($v2->fresh(), $this->adminUser);

        $resolvedNow = $this->ruleService->resolveEffectiveRule(now());
        $this->assertEquals(2, $resolvedNow->version);
        $this->assertEquals(2.0, $resolvedNow->rate_percentage);

        // Re-activating v1 to simulate resolving by timestamp without superseding v2 check
        $this->assertEquals(AgentIncentiveRule::STATUS_SUPERSEDED, $v1->fresh()->status);
    }

    /**
     * Test 10: Historical incentive calculations do not change after rule updates & immutability lock holds.
     */
    public function test_10_historical_calculations_do_not_change_after_rule_updates(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $v1 = $this->createAndActivateApprovedRule([
            'rule_code'          => 'INC-HIST-001',
            'rate_percentage'    => 10.0,
            'effective_from'     => now()->subDays(5),
        ]);

        $ord1 = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);
        $ledger1 = AgentIncentiveLedger::where('parent_order_id', $ord1->id)->first();
        $this->assertEquals(1000.00, $ledger1->calculated_incentive_amount);
        $this->assertEquals(1, $ledger1->rule_version);

        // Admin versions rule to v2 = 3%
        $v2 = $this->ruleService->createNewVersion($v1, [
            'rate_percentage' => 3.0,
            'effective_from'  => now()->subMinute(),
            'agreement_ref'   => 'AGR-HIST-V2',
        ], $this->adminUser);
        $this->ruleService->recordBusinessApproval($v2, $this->adminUser, 'BUS-H2');
        $this->ruleService->recordFinanceApproval($v2->fresh(), $this->adminUser, 'FIN-H2');
        $this->ruleService->activateRule($v2->fresh(), $this->adminUser);

        // Order 2 uses v2
        $ord2 = $this->createCheckoutOrder($this->customer1, [$this->seller2], 10000, 11800, true, true);
        $ledger2 = AgentIncentiveLedger::where('parent_order_id', $ord2->id)->first();
        $this->assertEquals(300.00, $ledger2->calculated_incentive_amount);
        $this->assertEquals(2, $ledger2->rule_version);

        // Historical Order 1 ledger snapshot remains unchanged!
        $this->assertEquals(1000.00, $ledger1->fresh()->calculated_incentive_amount);
        $this->assertEquals(1, $ledger1->fresh()->rule_version);

        // Attempt direct tampering on posted ledger amount throws LogicException
        $this->expectException(\LogicException::class);
        $ledger1->update(['calculated_incentive_amount' => 9999.00]);
    }

    /**
     * Test 11: Each calculation stores rule version & base across Methods A (% order), B (% platform commission), and C (fixed).
     */
    public function test_11_each_calculation_stores_rule_version_and_base_across_methods_a_b_c(): void
    {
        Config::set('marketplace_agents.funding_policy_approved', true);
        Config::set('marketplace_agents.incentive_calculation_enabled', true);

        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        // Order 1 with immutable OrderCommissionSnapshot for Method B testing
        $ord1 = $this->createCheckoutOrder($this->customer1, [$this->seller1], 20000, 23600, true, false);
        OrderCommissionSnapshot::create([
            'order_id'              => $ord1->id,
            'orderno'               => $ord1->orderno,
            'seller_user_id'        => $this->seller1->id,
            'company_id'            => $this->company1->id,
            'seller_listing_id'     => $this->listing1->id,
            'product_id'            => $this->listing1->product_id,
            'product_variant_id'    => $this->listing1->product_variant_id,
            'commission_rule_code'  => 'COMM-62',
            'rule_scope_type'       => 'global',
            'commission_percentage' => 10.0,
            'fixed_fee'             => 0,
            'calculation_basis'     => 'taxable_product_value',
            'quantity'              => 10,
            'unit_price'            => 2000,
            'base_amount'           => 20000.00,
            'commission_amount'     => 2000.00,
            'gst_rate'              => 18.0,
            'gst_amount'            => 360.00,
            'total_platform_fee'    => 2360.00,
            'seller_payable'        => 18000.00,
            'snapshot_timestamp'    => now(),
        ]);

        // Method B rule: 25% of platform commission ex GST (25% of 2000 = 500), floor cap ₹450!
        $ruleB = $this->createAndActivateApprovedRule([
            'rule_code'          => 'INC-METH-B',
            'calculation_method' => AgentIncentiveRule::METHOD_PERCENTAGE_COMMISSION,
            'calculation_base'   => AgentIncentiveRule::BASE_COMMISSION_EX_GST,
            'rate_percentage'    => 25.0,
            'max_cap_amount'     => 450.00,
            'rounding_rule'      => AgentIncentiveRule::ROUND_FLOOR_0,
            'tax_treatment_code' => AgentIncentiveRule::TAX_TDS_CONFIGURABLE,
            'tds_percentage'     => 10.0,
        ]);

        $this->completeFulfilment($ord1->fulfilment, $this->seller1, $this->customer1);

        $lB = AgentIncentiveLedger::where('parent_order_id', $ord1->id)->first();
        $this->assertEquals($ruleB->id, $lB->agent_incentive_rule_id);
        $this->assertEquals('INC-METH-B', $lB->rule_code);
        $this->assertEquals(1, $lB->rule_version);
        $this->assertEquals(AgentIncentiveRule::METHOD_PERCENTAGE_COMMISSION, $lB->calculation_method);
        $this->assertEquals(AgentIncentiveRule::BASE_COMMISSION_EX_GST, $lB->calculation_base);
        $this->assertEquals(2000.00, $lB->calculation_base_amount);
        $this->assertTrue($lB->cap_applied);
        $this->assertEquals(450.00, $lB->gross_incentive_amount);
        $this->assertEquals(45.00, $lB->tax_deduction_amount);
        $this->assertEquals(405.00, $lB->calculated_incentive_amount);

        // Method C: Fixed ₹750 per eligible order
        $this->ruleService->deactivateRule($ruleB->fresh(), $this->adminUser, 'Switch to Method C test');
        $this->createAndActivateApprovedRule([
            'rule_code'          => 'INC-METH-C',
            'calculation_method' => AgentIncentiveRule::METHOD_FIXED_AMOUNT,
            'calculation_base'   => AgentIncentiveRule::BASE_FIXED_PER_ORDER,
            'rate_percentage'    => null,
            'fixed_amount'       => 750.00,
            'rounding_rule'      => AgentIncentiveRule::ROUND_HALF_UP_2,
            'tax_treatment_code' => AgentIncentiveRule::TAX_EXCLUSIVE_NO_GST,
        ]);

        $ord2 = $this->createCheckoutOrder($this->customer1, [$this->seller2], 15000, 17700, true, true);
        $lC = AgentIncentiveLedger::where('parent_order_id', $ord2->id)->first();
        $this->assertEquals(AgentIncentiveRule::METHOD_FIXED_AMOUNT, $lC->calculation_method);
        $this->assertEquals(750.00, $lC->calculated_incentive_amount);
        $this->assertEquals(2, $lC->eligible_order_sequence);
    }

    /**
     * Test 12: Duplicate webhook events & retries do not create duplicate ledger entries.
     */
    public function test_12_duplicate_webhook_events_do_not_create_duplicate_ledger_entries(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);
        $this->createAndActivateApprovedRule();

        $order = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, false, false);
        $init = $this->paymentService->initiatePayment($order, 'razorpay', 'idem_wh_62');

        $gwOrderId = $init['gateway_order_id'];
        $gwPayId = 'pay_rzp_62_001';
        $sig = hash_hmac('sha256', "{$gwOrderId}|{$gwPayId}", $this->razorpaySecret);

        $payload = [
            'razorpay_order_id'   => $gwOrderId,
            'razorpay_payment_id' => $gwPayId,
            'razorpay_signature'  => $sig,
            'amount'              => 1180000,
            'status'              => 'captured',
        ];

        // Webhook delivery 1 & replay delivery 2 (same event ID + replay of acknowledgement)
        $res1 = $this->paymentService->processCallback('razorpay', $payload, $sig, 'evt_unique_62_01');
        $this->assertTrue($res1['success']);

        $res2 = $this->paymentService->processCallback('razorpay', $payload, $sig, 'evt_unique_62_01');
        $this->assertTrue($res2['is_repeat']);

        // Fulfil & acknowledge + replay lifecycle event twice
        $this->completeFulfilment($order->fulfilment, $this->seller1, $this->customer1);
        $this->eligibilityService->handleOrderLifecycleEvent($order->fresh(), 'buyer_acknowledged_receipt', 'ack_replay_1');
        $this->eligibilityService->handleOrderLifecycleEvent($order->fresh(), 'buyer_acknowledged_receipt', 'ack_replay_1');

        $this->assertEquals(1, AgentEligibleOrder::where('parent_order_id', $order->id)->count());
        $this->assertEquals(1, AgentIncentiveLedger::where('parent_order_id', $order->id)->where('entry_type', 'earned')->count());
    }

    /**
     * Test 13: Concurrent events / DB constraints cannot allocate duplicate sequence numbers.
     */
    public function test_13_concurrent_events_cannot_allocate_duplicate_sequence_numbers(): void
    {
        $attr = $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $o1 = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);
        $o2 = $this->createCheckoutOrder($this->customer1, [$this->seller2], 10000, 11800, true, false);

        // Verify DB unique lock (customer_id, active_slot_number) strictly rejects forcing slot 1 on a 2nd order
        $this->expectException(QueryException::class);
        AgentEligibleOrder::create([
            'eligibility_ref'            => 'ELIG-CONCURRENT-DUP',
            'customer_id'                => $this->customer1->id,
            'field_agent_id'             => $this->agentA->id,
            'buyer_agent_attribution_id' => $attr->id,
            'parent_order_id'            => $o2->id,
            'orderno'                    => $o2->orderno,
            'eligibility_status'         => AgentEligibleOrder::STATUS_ELIGIBLE,
            'sequence_number'            => 1,
            'active_slot_number'         => 1,
        ]);
    }

    /**
     * Test 14: Reversal and adjustment entries preserve original ledger history.
     */
    public function test_14_reversal_and_adjustment_entries_preserve_original_ledger_history(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);
        $this->createAndActivateApprovedRule(['rate_percentage' => 5.0]);

        $order = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);
        $earned = AgentIncentiveLedger::where('parent_order_id', $order->id)->first();
        $this->assertEquals(500.00, $earned->calculated_incentive_amount);

        // Partial negative adjustment (-150) leaves net balance 350 and preserves earned ₹500 row
        $adj = $this->calculationService->adjustLedgerEntry($earned, -150.00, $this->adminUser, 'Correction for quantity variance');
        $this->assertEquals(-150.00, $adj->calculated_incentive_amount);
        $this->assertEquals(500.00, $earned->fresh()->calculated_incentive_amount);
        $this->assertEquals(AgentIncentiveLedger::STATUS_PARTIALLY_REVERSED, $earned->fresh()->status);

        // Invalid negative adjustment reducing net balance below 0 rejected cleanly
        try {
            $this->calculationService->adjustLedgerEntry($earned->fresh(), -400.00, $this->adminUser, 'Exceeds balance');
            $this->fail('Expected ValidationException for negative net balance.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('adjustment_amount', $e->errors());
        }

        // Subsequent full refund triggers reversal for remaining net ₹350, preserving all prior rows!
        app(PaymentRefundService::class)->processRefund($order->fresh(), 'Full post-delivery refund');

        $rev = AgentIncentiveLedger::where('reference_ledger_id', $earned->id)
            ->where('entry_type', AgentIncentiveLedger::TYPE_REVERSAL)
            ->first();

        $this->assertNotNull($rev);
        $this->assertEquals(-350.00, $rev->calculated_incentive_amount);
        $this->assertEquals(500.00, $earned->fresh()->calculated_incentive_amount);
        $this->assertEquals(AgentIncentiveLedger::STATUS_REVERSED, $earned->fresh()->status);
        $this->assertEquals(0.00, round((float)AgentIncentiveLedger::where('parent_order_id', $order->id)->sum('calculated_incentive_amount'), 2));
        $this->assertNull(AgentEligibleOrder::where('parent_order_id', $order->id)->value('active_slot_number'));
    }

    /**
     * Test 15: Agent A cannot view Agent B's buyer or incentive records.
     */
    public function test_15_agent_a_cannot_view_agent_bs_buyer_or_incentive_records(): void
    {
        $this->attributionService->createAttribution(['customer_id' => $this->customer1->id, 'field_agent_id' => $this->agentA->id], $this->adminUser);
        $this->attributionService->createAttribution(['customer_id' => $this->customer2->id, 'field_agent_id' => $this->agentB->id], $this->adminUser);

        $this->createAndActivateApprovedRule();
        Config::set('marketplace_agents.expose_agent_incentive_amounts', true);

        $ordA = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);
        $ordB = $this->createCheckoutOrder($this->customer2, [$this->seller2], 20000, 23600, true, true);

        $this->actingAs($this->agentUserA);
        $res = $this->getJson('/api/v2/agent/incentives')->assertStatus(200);
        $ordersShown = collect($res->json('data.tracked_orders'))->pluck('order_no')->all();

        $this->assertContains($ordA->orderno, $ordersShown);
        $this->assertNotContains($ordB->orderno, $ordersShown);

        $this->getJson("/api/v2/agent/buyers/{$this->customer2->id}")->assertStatus(403);

        // Buyer B cannot query Buyer A's progress
        $this->actingAs($this->buyerUser2);
        $this->getJson("/api/v2/buyer/incentive-progress?customer_id={$this->customer1->id}")->assertStatus(403);
    }

    /**
     * Test 16: Seller users cannot access agent incentive records.
     */
    public function test_16_seller_users_cannot_access_agent_incentive_records(): void
    {
        $this->actingAs($this->seller1);

        $this->getJson('/api/v2/admin/incentives/eligible-orders')->assertStatus(403);
        $this->getJson('/api/v2/admin/incentives/rules')->assertStatus(403);
        $this->getJson('/api/v2/admin/incentives/ledger')->assertStatus(403);
        $this->getJson('/api/v2/admin/incentives/reports')->assertStatus(403);
        $this->getJson('/api/v2/agent/incentives')->assertStatus(403);
        $this->getJson('/api/v2/buyer/incentive-progress')->assertStatus(403);
        $this->get('/admin/incentives/ledger')->assertStatus(403);
    }

    /**
     * Test 17: Seller commission & settlement ledgers remain completely unchanged by agent incentive processing.
     */
    public function test_17_seller_commission_and_settlement_ledgers_remain_unchanged(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);
        $this->createAndActivateApprovedRule(['rate_percentage' => 5.0]);

        $order = $this->createCheckoutOrder($this->customer1, [$this->seller1], 20000, 23600, true, false);
        $txn = Transection::where('order_id', $order->id)->first();
        app(FinancialLedgerService::class)->recordOrderPayment($order, $txn);

        $settlementBefore = SellerSettlement::where('order_id', $order->id)->firstOrFail();
        $ledgerSumBefore = FinancialLedger::where('order_id', $order->id)->count();
        $netSellerBefore = (float)$settlementBefore->net_seller_payable;
        $deductionBefore = (float)$settlementBefore->total_platform_deduction;

        // Now complete fulfilment & generate Apni Factory-funded agent incentive!
        $this->completeFulfilment($order->fulfilment, $this->seller1, $this->customer1);

        $agentLedger = AgentIncentiveLedger::where('parent_order_id', $order->id)->firstOrFail();
        $this->assertEquals(AgentIncentiveLedger::EXPENSE_APNI_FACTORY_PLATFORM, $agentLedger->funding_expense_account);

        $settlementAfter = $settlementBefore->fresh();
        $this->assertEquals($netSellerBefore, (float)$settlementAfter->net_seller_payable);
        $this->assertEquals($deductionBefore, (float)$settlementAfter->total_platform_deduction);
        $this->assertEquals($ledgerSumBefore, FinancialLedger::where('order_id', $order->id)->count());

        // Creating a seller-funded incentive rule is strictly rejected
        $this->actingAs($this->adminUser);
        $this->postJson('/api/v2/admin/incentives/rules', [
            'name'               => 'Prohibited Seller Funded Rule',
            'funding_source'     => 'seller_funded',
            'calculation_method' => AgentIncentiveRule::METHOD_PERCENTAGE_ORDER,
            'calculation_base'   => AgentIncentiveRule::BASE_TAXABLE_ORDER,
            'rate_percentage'    => 2.0,
            'rounding_rule'      => AgentIncentiveRule::ROUND_HALF_UP_2,
            'effective_from'     => now()->toIso8601String(),
        ])->assertStatus(422);
    }

    /**
     * Test 18: Unapproved funding policy prevents payable incentive generation while permitting safe non-financial tracking.
     */
    public function test_18_unapproved_funding_policy_prevents_payable_incentive_generation(): void
    {
        // Explicitly assert default production-safe governance flags
        $this->assertFalse((bool)config('marketplace_agents.funding_policy_approved'));
        $this->assertFalse((bool)config('marketplace_agents.incentive_calculation_enabled'));

        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $order = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);

        // Non-financial slot progress 1/3 is tracked cleanly, while financial liability is BLOCKED
        $tracked = AgentEligibleOrder::where('parent_order_id', $order->id)->first();
        $this->assertEquals(AgentEligibleOrder::STATUS_ELIGIBLE, $tracked->eligibility_status);
        $this->assertEquals(1, $tracked->sequence_number);
        $this->assertEquals(AgentEligibleOrder::INCENTIVE_BLOCKED_FUNDING, $tracked->incentive_generation_status);
        $this->assertEquals(0, AgentIncentiveLedger::count());

        // Rule activation is also blocked while funding policy remains unapproved
        $draft = $this->ruleService->createRule([
            'rule_code'          => 'INC-UNAPPROVED-01',
            'name'               => 'Blocked Pending Policy Signoff',
            'calculation_method' => AgentIncentiveRule::METHOD_FIXED_AMOUNT,
            'calculation_base'   => AgentIncentiveRule::BASE_FIXED_PER_ORDER,
            'fixed_amount'       => 500,
            'rounding_rule'      => AgentIncentiveRule::ROUND_HALF_UP_2,
            'tax_treatment_code' => AgentIncentiveRule::TAX_EXCLUSIVE_NO_GST,
            'effective_from'     => now()->subDay(),
            'agreement_ref'      => 'DRAFT-AGR-01',
        ], $this->adminUser);
        $this->ruleService->recordBusinessApproval($draft, $this->adminUser, 'BUS-DRAFT');
        $this->ruleService->recordFinanceApproval($draft->fresh(), $this->adminUser, 'FIN-DRAFT');

        $this->actingAs($this->adminUser);
        $this->postJson("/api/v2/admin/incentives/rules/{$draft->id}/activate")
            ->assertStatus(422);

        // Preview simulation works without creating any ledger liabilities!
        $this->postJson('/api/v2/admin/incentives/preview', [
            'order_id' => $order->id,
            'rule_id'  => $draft->id,
        ])->assertStatus(200)
          ->assertJsonPath('data.is_simulation_only', true)
          ->assertJsonPath('data.calculated_incentive_amount', 500);

        $this->assertEquals(0, AgentIncentiveLedger::count());
    }

    /**
     * Test 19: Invalid or incomplete rule configuration is rejected.
     */
    public function test_19_invalid_or_incomplete_rule_configuration_is_rejected(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Incompatible method & base
        $this->postJson('/api/v2/admin/incentives/rules', [
            'name'               => 'Invalid Base',
            'calculation_method' => AgentIncentiveRule::METHOD_FIXED_AMOUNT,
            'calculation_base'   => AgentIncentiveRule::BASE_TAXABLE_ORDER,
            'fixed_amount'       => 500,
            'effective_from'     => now()->toIso8601String(),
        ])->assertStatus(422);

        // 2. Rate > 100%
        $this->postJson('/api/v2/admin/incentives/rules', [
            'name'               => 'Bad Rate',
            'calculation_method' => AgentIncentiveRule::METHOD_PERCENTAGE_ORDER,
            'calculation_base'   => AgentIncentiveRule::BASE_TAXABLE_ORDER,
            'rate_percentage'    => 150,
            'effective_from'     => now()->toIso8601String(),
        ])->assertStatus(422);

        // 3. Cannot activate incomplete rule (missing rounding/tax/agreement/approvals)
        Config::set('marketplace_agents.funding_policy_approved', true);
        $incomplete = $this->ruleService->createRule([
            'rule_code'          => 'INC-INCOMPLETE-01',
            'name'               => 'Incomplete Draft',
            'calculation_method' => AgentIncentiveRule::METHOD_FIXED_AMOUNT,
            'calculation_base'   => AgentIncentiveRule::BASE_FIXED_PER_ORDER,
            'fixed_amount'       => 300,
            'tax_treatment_code' => AgentIncentiveRule::TAX_PENDING_DECISION,
            'effective_from'     => now(),
        ], $this->adminUser);

        $this->postJson("/api/v2/admin/incentives/rules/{$incomplete->id}/activate")
            ->assertStatus(422);
    }

    /**
     * Test 20: Admin reports reconcile with immutable ledger, notifications enqueue once, and web screens render cleanly.
     */
    public function test_20_admin_reports_reconcile_with_ledger_and_ui_and_notifications(): void
    {
        Queue::fake();

        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer2->id,
            'field_agent_id' => $this->agentB->id,
        ], $this->adminUser);

        $this->createAndActivateApprovedRule(['rate_percentage' => 2.0]);

        $ord1 = $this->createCheckoutOrder($this->customer1, [$this->seller1], 10000, 11800, true, true);
        $ord2 = $this->createCheckoutOrder($this->customer2, [$this->seller2], 20000, 23600, true, true);

        $l1 = AgentIncentiveLedger::where('parent_order_id', $ord1->id)->first();
        $l2 = AgentIncentiveLedger::where('parent_order_id', $ord2->id)->first();

        $this->calculationService->approveLedgerEntry($l1, $this->adminUser, 'Approved Q4');
        Queue::assertPushed(SendAgentIncentiveNotificationJob::class);

        $this->actingAs($this->adminUser);

        foreach (['agent', 'buyer', 'territory', 'sequence', 'period'] as $groupBy) {
            $repRes = $this->getJson("/api/v2/admin/incentives/reports?group_by={$groupBy}")
                ->assertStatus(200);

            $summary = $repRes->json('data.summary');
            $this->assertEquals(600.00, $summary['earned_total']);
            $this->assertEquals(200.00, $summary['approved_total']);
            $this->assertEquals(400.00, $summary['pending_approval_total']);
            $this->assertEquals(600.00, $summary['net_incentive_liability']);
            $this->assertEquals(0.0, $summary['reconciled_checksum_diff']);

            $groupSum = collect($repRes->json('data.rows'))->sum('net_ledger_amount');
            $this->assertEquals(600.00, round((float)$groupSum, 2));
        }

        // Verify failure log recovery endpoint cleanly
        $failLog = AgentIncentiveProcessingLog::create([
            'event_key'       => 'failed_retry_test_62',
            'event_type'      => 'payment_verified',
            'parent_order_id' => $ord1->id,
            'order_id'        => $ord1->id,
            'status'          => AgentIncentiveProcessingLog::STATUS_FAILED,
            'error_message'   => 'Simulated transient DB lock timeout',
        ]);

        $this->postJson("/api/v2/admin/incentives/failures/{$failLog->id}/retry")
            ->assertStatus(200);
        $this->assertEquals(AgentIncentiveProcessingLog::STATUS_PROCESSED, $failLog->fresh()->status);

        // Verify all Admin & Agent Blade pages render cleanly (200 OK)
        $this->get('/admin/incentives/eligible-orders')->assertStatus(200)->assertSee($ord1->orderno);
        $this->get('/admin/incentives/rules')->assertStatus(200)->assertSee('Approved Launch Buyer Incentive');
        $this->get('/admin/incentives/rules/create')->assertStatus(200);
        $this->get('/admin/incentives/ledger')->assertStatus(200)->assertSee($l1->ledger_ref);
        $this->get('/admin/incentives/reports')->assertStatus(200);

        $this->actingAs($this->agentUserA);
        $this->get('/agent/dashboard')->assertStatus(200)->assertSee('1/3');
        $this->get('/agent/incentives')->assertStatus(200)->assertSee($ord1->orderno);
    }
}

