<?php

namespace Tests\Feature;

use App\Jobs\SendAgentIncentiveNotificationJob;
use App\Models\AgentEligibleOrder;
use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentIncentiveRule;
use App\Models\AgentPayout;
use App\Models\AgentPayoutItem;
use App\Models\AgentPayoutReconciliationException;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Models\FinancialLedger;
use App\Models\Order;
use App\Models\SellerFulfilment;
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
use App\Services\FinancialLedgerService;
use App\Services\PaymentRefundService;
use App\Services\SellerFulfilmentService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Phase6_3_AgentPayoutAndReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $seller1;
    protected Company $company1;
    protected User $agentUserA;
    protected User $agentUserB;
    protected User $buyerUser1;
    protected Customer $customer1;
    protected Customer $customer2;
    protected FieldAgent $agentA;
    protected FieldAgent $agentB;

    protected FieldAgentService $agentService;
    protected BuyerAttributionService $attributionService;
    protected AgentOrderEligibilityService $eligibilityService;
    protected AgentIncentiveRuleService $ruleService;
    protected AgentIncentiveCalculationService $calculationService;
    protected AgentPayoutService $payoutService;
    protected SellerFulfilmentService $fulfilmentService;
    protected BuyerDisputeService $disputeService;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Config::set('marketplace_agents.eligible_order_tracking_implemented', true);
        // Safe defaults in production before written commercial & execution approvals
        Config::set('marketplace_agents.funding_policy_approved', false);
        Config::set('marketplace_agents.incentive_calculation_enabled', false);
        Config::set('marketplace_agents.payout_execution_enabled', false);
        Config::set('marketplace_agents.payout_execution_mode', 'awaiting_policy');

        $this->agentService       = app(FieldAgentService::class);
        $this->attributionService = app(BuyerAttributionService::class);
        $this->eligibilityService = app(AgentOrderEligibilityService::class);
        $this->ruleService        = app(AgentIncentiveRuleService::class);
        $this->calculationService = app(AgentIncentiveCalculationService::class);
        $this->payoutService      = app(AgentPayoutService::class);
        $this->fulfilmentService  = app(SellerFulfilmentService::class);
        $this->disputeService     = app(BuyerDisputeService::class);

        $this->adminUser = User::create([
            'name'     => 'Finance Controller Admin',
            'email'    => 'admin.payouts63@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        $this->seller1 = User::create([
            'name'     => 'Aravalli Chemicals Pvt Ltd',
            'email'    => 'seller1.63@factory.in',
            'password' => bcrypt('password123'),
        ]);
        $this->company1 = Company::create([
            'user_id'   => $this->seller1->id,
            'name'      => 'Aravalli Works',
            'address'   => 'Plot 8, Bhiwadi',
            'city'      => 'Bhiwadi',
            'state'     => 'Rajasthan',
            'pincode'   => '301019',
            'phone'     => '0149300112',
            'comission' => 10,
            'status'    => 'active',
        ]);

        $this->agentA = $this->agentService->createAgent([
            'agent_code'          => 'FSA-63-001',
            'name'                => 'Vikas Verma',
            'email'               => 'vikas.63@apnifactory.in',
            'mobile'              => '9829063001',
            'territory'           => 'Jaipur South',
            'status'              => 'active',
            'kyc_status'          => 'verified',
            'contract_status'     => 'active',
            'create_user_account' => true,
            'payout_details'      => [
                'account_holder_name' => 'Vikas Verma',
                'bank_name'           => 'HDFC Bank',
                'account_number'      => '50100234567890',
                'ifsc_code'           => 'HDFC0001234',
            ],
        ], $this->adminUser);
        $this->agentUserA = $this->agentA->user;

        $this->agentB = $this->agentService->createAgent([
            'agent_code'          => 'FSA-63-002',
            'name'                => 'Nitin Joshi',
            'email'               => 'nitin.63@apnifactory.in',
            'mobile'              => '9829063002',
            'territory'           => 'Ahmedabad West',
            'status'              => 'active',
            'kyc_status'          => 'verified',
            'contract_status'     => 'active',
            'create_user_account' => true,
            'payout_details'      => [
                'account_holder_name' => 'Nitin Joshi',
                'bank_name'           => 'ICICI Bank',
                'account_number'      => '000401556677',
                'ifsc_code'           => 'ICIC0000004',
            ],
        ], $this->adminUser);
        $this->agentUserB = $this->agentB->user;

        $this->buyerUser1 = User::create([
            'name'     => 'Mahadev Builders',
            'email'    => 'buyer1.63@dealer.com',
            'password' => bcrypt('password123'),
        ]);

        $this->customer1 = Customer::create([
            'name'     => 'Mahadev Builders',
            'email'    => 'buyer1.63@dealer.com',
            'mobile'   => '9876063001',
            'password' => bcrypt('password123'),
            'location' => 'Jaipur',
            'status'   => 'active',
        ]);
        $this->customer2 = Customer::create([
            'name'     => 'Patel Infra Mart',
            'email'    => 'buyer2.63@dealer.com',
            'mobile'   => '9876063002',
            'password' => bcrypt('password123'),
            'location' => 'Ahmedabad',
            'status'   => 'active',
        ]);
    }

    protected function enableFullApprovedCommercialAndPayoutPolicy(): AgentIncentiveRule
    {
        Config::set('marketplace_agents.funding_policy_approved', true);
        Config::set('marketplace_agents.incentive_calculation_enabled', true);
        Config::set('marketplace_agents.payout_execution_enabled', true);
        Config::set('marketplace_agents.payout_execution_mode', 'manual_finance_recording');
        Config::set('marketplace_agents.payout_tax_withholding_approved', true);
        Config::set('marketplace_agents.expose_agent_incentive_amounts', true);

        $rule = $this->ruleService->createRule([
            'rule_code'          => 'INC-63-' . strtoupper(uniqid()),
            'name'               => 'Approved Phase 6.3 Scheme Rule',
            'calculation_method' => AgentIncentiveRule::METHOD_PERCENTAGE_ORDER,
            'calculation_base'   => AgentIncentiveRule::BASE_TAXABLE_ORDER,
            'rate_percentage'    => 5.0,
            'rounding_rule'      => AgentIncentiveRule::ROUND_HALF_UP_2,
            'tax_treatment_code' => AgentIncentiveRule::TAX_EXCLUSIVE_NO_GST,
            'effective_from'     => now()->subDay(),
            'agreement_ref'      => 'BRD-FIN-PAYOUT-2026',
        ], $this->adminUser);

        $this->ruleService->recordBusinessApproval($rule, $this->adminUser, 'BUS-63-01');
        $this->ruleService->recordFinanceApproval($rule->fresh(), $this->adminUser, 'FIN-63-01');

        return $this->ruleService->activateRule($rule->fresh(), $this->adminUser);
    }

    protected function createEligibleOrderAndLedger(Customer $customer, FieldAgent $agent, float $netAmount = 10000.00): AgentIncentiveLedger
    {
        if (!\App\Models\BuyerAgentAttribution::where('customer_id', $customer->id)->where('status', 'active')->exists()) {
            $this->attributionService->createAttribution([
                'customer_id'    => $customer->id,
                'field_agent_id' => $agent->id,
            ], $this->adminUser);
        }

        $grand = round($netAmount * 1.18, 2);
        $orderNo = 'ORD-63-' . strtoupper(uniqid());
        $order = Order::create([
            'orderno'        => $orderNo,
            'customer_id'    => $customer->id,
            'user_id'        => $this->seller1->id,
            'address'        => 'Industrial Area, Jaipur',
            'netamount'      => $netAmount,
            'taxamount'      => round($grand - $netAmount, 2),
            'grandtotal'     => $grand,
            'payment_status' => 'Paid',
            'paid_at'        => now(),
        ]);

        $txn = Transection::create([
            'payment_ref'           => 'PAY-63-' . strtoupper(uniqid()),
            'order_id'              => $order->id,
            'parent_order_id'       => $order->id,
            'order_no'              => $orderNo,
            'customer_id'           => $customer->id,
            'user_id'               => $this->seller1->id,
            'gateway_name'          => 'razorpay',
            'amount'                => $grand,
            'currency'              => 'INR',
            'status'                => 'successful',
            'verification_status'   => 'verified',
            'reconciliation_status' => 'matched',
            'verified_at'           => now(),
            'txnid'                 => 'pay_mock_' . uniqid(),
        ]);

        app(FinancialLedgerService::class)->recordOrderPayment($order, $txn);

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $this->fulfilmentService->updateStatus($fulfilment, SellerFulfilment::STATUS_ACCEPTED, $this->seller1->id, 'seller', 'Seller1');
        $this->fulfilmentService->recordDispatch($fulfilment->fresh(), ['transporter_name' => 'SafeExpress', 'lr_number' => 'LR-63-' . rand(100, 999)], $this->seller1->id, 'Seller1');
        $this->disputeService->acknowledgeReceipt($fulfilment->fresh(), $customer->id, $customer->name, 'OK');

        return AgentIncentiveLedger::where('parent_order_id', $order->id)
            ->where('entry_type', AgentIncentiveLedger::TYPE_EARNED)
            ->firstOrFail();
    }

    /**
     * Test 1: Only authorized finance/admin users can approve incentives.
     */
    public function test_01_only_authorized_finance_admin_users_can_approve_incentives(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);

        foreach ([$this->agentUserA, $this->seller1, $this->buyerUser1] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser);
            $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/approve")->assertStatus(403);
            $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/hold", ['reason' => 'Unauthorized'])->assertStatus(403);
            $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/reject", ['reason' => 'Unauthorized'])->assertStatus(403);
            $this->postJson('/api/v2/admin/payouts', ['field_agent_id' => $this->agentA->id, 'ledger_ids' => [$ledger->id]])->assertStatus(403);
        }

        $this->actingAs($this->adminUser);
        $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/approve", ['notes' => 'Finance checked'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', AgentIncentiveLedger::STATUS_APPROVED);

        $this->assertEquals($this->adminUser->id, $ledger->fresh()->approved_by_user_id);
        $this->assertNotNull($ledger->fresh()->approved_at);
    }

    /**
     * Test 2: Ineligible incentives cannot be approved.
     */
    public function test_02_ineligible_incentives_cannot_be_approved(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);

        // Corrupt / revert eligible order back to pending_review or ineligible
        $ledger->eligibleOrder->update(['eligibility_status' => AgentEligibleOrder::STATUS_PENDING_REVIEW]);

        $this->actingAs($this->adminUser);
        $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/approve")
            ->assertStatus(422);

        // Restore eligible order, reject ledger, then try approving a rejected ledger
        $ledger->eligibleOrder->update(['eligibility_status' => AgentEligibleOrder::STATUS_ELIGIBLE]);
        $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/reject", ['reason' => 'Policy mismatch'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', AgentIncentiveLedger::STATUS_REJECTED);

        $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/approve")
            ->assertStatus(422);
    }

    /**
     * Test 3: Duplicate approval requests are idempotent.
     */
    public function test_03_duplicate_approval_requests_are_idempotent(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);

        $this->actingAs($this->adminUser);
        $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/approve")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $auditCountAfterFirst = AgentIncentiveAuditLog::where('entity_type', 'ledger')->where('entity_id', $ledger->id)->where('action', 'ledger_approved')->count();

        // Replay second approval
        $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/approve")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $this->assertEquals(
            $auditCountAfterFirst,
            AgentIncentiveAuditLog::where('entity_type', 'ledger')->where('entity_id', $ledger->id)->where('action', 'ledger_approved')->count()
        );
    }

    /**
     * Test 4: Payout readiness rejects missing required approvals, missing payout details, inactive agent, or unverified KYC.
     */
    public function test_04_payout_readiness_rejects_missing_required_approvals(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);

        $this->actingAs($this->adminUser);

        // 1. Unapproved ledger fails readiness
        $resPending = $this->postJson('/api/v2/admin/payouts/readiness', ['ledger_ids' => [$ledger->id]])
            ->assertStatus(200);
        $this->assertFalse($resPending->json('data.all_ready'));

        // Approve ledger -> Ready!
        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);
        $this->postJson('/api/v2/admin/payouts/readiness', ['ledger_ids' => [$ledger->id]])
            ->assertStatus(200)
            ->assertJsonPath('data.all_ready', true);

        // 2. When KYC check mandated by policy and agent is pending_review -> blocked!
        Config::set('marketplace_agents.payout_require_kyc_verified', true);
        $this->agentA->update(['kyc_status' => FieldAgent::KYC_PENDING_REVIEW]);
        $this->assertFalse($this->payoutService->checkLedgerEntryReadiness($ledger->fresh())['ready']);

        // 3. Batch creation rejected while readiness fails
        $this->postJson('/api/v2/admin/payouts', [
            'field_agent_id' => $this->agentA->id,
            'ledger_ids'     => [$ledger->id],
        ])->assertStatus(422);

        // 4. Restore KYC, clear bank payout_details -> blocked!
        $this->agentA->update(['kyc_status' => FieldAgent::KYC_VERIFIED, 'payout_details' => null]);
        $this->assertFalse($this->payoutService->checkLedgerEntryReadiness($ledger->fresh())['ready']);
    }

    /**
     * Test 5: Same incentive cannot be included in two payable batches (app check + DB constraint).
     */
    public function test_05_same_incentive_cannot_be_included_in_two_payable_batches(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);

        $batch1 = $this->payoutService->createPayoutBatch($this->agentA->id, [$ledger->id], $this->adminUser, ['idempotency_key' => 'batch-one']);
        $this->assertNotNull($batch1);

        // API second attempt with same ledger must fail cleanly (422)
        $this->actingAs($this->adminUser);
        $this->postJson('/api/v2/admin/payouts', [
            'field_agent_id'  => $this->agentA->id,
            'ledger_ids'      => [$ledger->id],
            'idempotency_key' => 'batch-two-different-key',
        ])->assertStatus(422);

        // Direct DB constraint attempt must throw QueryException
        $secondPayout = AgentPayout::create([
            'payout_ref'             => 'PAYOUT-FORCE-DUP',
            'field_agent_id'         => $this->agentA->id,
            'net_payable_amount'     => 500,
        ]);

        $this->expectException(QueryException::class);
        AgentPayoutItem::create([
            'agent_payout_id'           => $secondPayout->id,
            'agent_incentive_ledger_id' => $ledger->id,
            'active_ledger_lock'        => $ledger->id,
            'parent_order_id'           => $ledger->parent_order_id,
            'eligible_order_sequence'   => 1,
            'ledger_gross_amount'       => 500,
            'item_net_payable_amount'   => 500,
        ]);
    }

    /**
     * Test 6: Payout totals reconcile strictly with included ledger entries & approved adjustments.
     */
    public function test_06_payout_totals_reconcile_with_included_ledger_entries(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();

        $l1 = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000); // 5% = 500
        $l2 = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 15000); // 5% = 750

        $this->calculationService->approveLedgerEntry($l1, $this->adminUser);
        $this->calculationService->approveLedgerEntry($l2, $this->adminUser);
        $this->calculationService->adjustLedgerEntry($l1, 100.00, $this->adminUser, 'Approved slab bonus adjustment');

        $payout = $this->payoutService->createPayoutBatch($this->agentA->id, [$l1->id, $l2->id], $this->adminUser);

        $this->assertEquals(1250.00, $payout->gross_incentive_amount);
        $this->assertEquals(100.00, $payout->approved_adjustments_amount);
        $this->assertEquals(1350.00, $payout->net_payable_amount);
        $this->assertEquals(1350.00, round((float)$payout->items()->sum('item_net_payable_amount'), 2));
    }

    /**
     * Test 7: Duplicate payout requests (same idempotency key) do not create duplicate payment instructions.
     */
    public function test_07_duplicate_payout_requests_do_not_create_duplicate_payment_instructions(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);

        $this->actingAs($this->adminUser);
        $res1 = $this->postJson('/api/v2/admin/payouts', [
            'field_agent_id'  => $this->agentA->id,
            'ledger_ids'      => [$ledger->id],
            'idempotency_key' => 'idem-payout-req-63-01',
        ])->assertStatus(201);

        $res2 = $this->postJson('/api/v2/admin/payouts', [
            'field_agent_id'  => $this->agentA->id,
            'ledger_ids'      => [$ledger->id],
            'idempotency_key' => 'idem-payout-req-63-01',
        ])->assertStatus(201);

        $this->assertEquals($res1->json('data.payout_ref'), $res2->json('data.payout_ref'));
        $this->assertEquals(1, AgentPayout::count());
    }

    /**
     * Test 8: Unverified payment status or client-reported confirmation cannot mark a payout paid.
     */
    public function test_08_unverified_payment_status_cannot_mark_a_payout_paid(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);

        $payout = $this->payoutService->createPayoutBatch($this->agentA->id, [$ledger->id], $this->adminUser, [
            'payment_method' => AgentPayout::METHOD_MANUAL_BANK,
        ]);
        $this->payoutService->authorizePayout($payout, $this->adminUser);
        $this->payoutService->recordPaymentInitiation($payout->fresh(), $this->adminUser, ['payment_method' => AgentPayout::METHOD_MANUAL_BANK]);

        $this->actingAs($this->adminUser);

        // 1. Unverified client status rejected
        $this->postJson("/api/v2/admin/payouts/{$payout->id}/confirm", [
            'confirmation_source'      => 'client_reported',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-UNVERIFIED-1',
        ])->assertStatus(422);

        $this->postJson("/api/v2/admin/payouts/{$payout->id}/confirm", [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'pending',
            'external_transaction_ref' => 'UTR-UNVERIFIED-2',
        ])->assertStatus(422);

        // 2. Mismatched amount triggers reconciliation exception and blocks marking paid
        $this->postJson("/api/v2/admin/payouts/{$payout->id}/confirm", [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-MISMATCH-1',
            'confirmed_amount'         => 450.00,
        ])->assertStatus(422);

        $this->assertEquals(AgentPayout::STATUS_RECONCILIATION_EXCEPTION, $payout->fresh()->status);
        $this->assertEquals(AgentIncentiveLedger::STATUS_APPROVED, $ledger->fresh()->status);
    }

    /**
     * Test 9: Failed payouts remain permanently visible for reconciliation and unlock items safely.
     */
    public function test_09_failed_payouts_remain_visible_for_reconciliation(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);

        $payout = $this->payoutService->createPayoutBatch($this->agentA->id, [$ledger->id], $this->adminUser);
        $this->payoutService->authorizePayout($payout, $this->adminUser);

        $this->actingAs($this->adminUser);
        $this->postJson("/api/v2/admin/payouts/{$payout->id}/fail", [
            'failure_reason' => 'IFSC beneficiary account frozen by bank',
        ])->assertStatus(200)
          ->assertJsonPath('data.status', AgentPayout::STATUS_FAILED);

        $this->assertEquals(1, AgentPayout::where('id', $payout->id)->where('status', AgentPayout::STATUS_FAILED)->count());
        $this->assertEquals(1, AgentPayoutReconciliationException::where('agent_payout_id', $payout->id)->where('exception_type', AgentPayoutReconciliationException::TYPE_FAILED_PAYOUT)->count());

        // Replacement batch can safely be created after agent updates bank details
        $replacement = $this->payoutService->createPayoutBatch($this->agentA->id, [$ledger->id], $this->adminUser);
        $this->assertNotEquals($payout->id, $replacement->id);
        $this->assertEquals(2, AgentPayout::count());
    }

    /**
     * Test 10: Reversals preserve original financial history and handle pre/post-payout exceptions cleanly.
     */
    public function test_10_reversals_preserve_original_financial_history(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $lPre = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $this->calculationService->approveLedgerEntry($lPre, $this->adminUser);
        $unreleasedBatch = $this->payoutService->createPayoutBatch($this->agentA->id, [$lPre->id], $this->adminUser);

        // Pre-payout order refund reverses ledger and flags unreleased batch cleanly!
        app(PaymentRefundService::class)->processRefund($lPre->parentOrder, 'Pre-payout cancellation refund');
        $this->assertEquals(500.00, $lPre->fresh()->calculated_incentive_amount);
        $this->assertEquals(AgentIncentiveLedger::STATUS_REVERSED, $lPre->fresh()->status);
        $this->assertEquals(AgentPayout::STATUS_RECONCILIATION_EXCEPTION, $unreleasedBatch->fresh()->status);

        // Post-payout refund after confirmation creates exception instead of silent clawback
        $lPost = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 12000);
        $this->calculationService->approveLedgerEntry($lPost, $this->adminUser);
        $paidBatch = $this->payoutService->createPayoutBatch($this->agentA->id, [$lPost->id], $this->adminUser);
        $this->payoutService->authorizePayout($paidBatch, $this->adminUser);
        $this->payoutService->confirmPayoutPayment($paidBatch->fresh(), [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-POSTREV-63',
            'confirmed_amount'         => 600.00,
        ], $this->adminUser);

        app(PaymentRefundService::class)->processRefund($lPost->parentOrder, 'Post-payout customer return');
        $this->assertEquals(
            1,
            AgentPayoutReconciliationException::where('agent_incentive_ledger_id', $lPost->id)
                ->where('exception_type', AgentPayoutReconciliationException::TYPE_POST_PAYOUT_REVERSAL)
                ->count()
        );
        $this->assertEquals(600.00, $lPost->fresh()->calculated_incentive_amount);
    }

    /**
     * Test 11: Unapproved tax or withholding rules cannot be applied.
     */
    public function test_11_unapproved_tax_or_withholding_rules_cannot_be_applied(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);

        // Explicitly disable withholding tax approval
        Config::set('marketplace_agents.payout_tax_withholding_approved', false);

        $this->actingAs($this->adminUser);
        $this->postJson('/api/v2/admin/payouts', [
            'field_agent_id'       => $this->agentA->id,
            'ledger_ids'           => [$ledger->id],
            'withholding_tax_rate' => 10.0,
        ])->assertStatus(422);

        $readiness = $this->payoutService->checkLedgerEntryReadiness($ledger->fresh(), 10.0);
        $this->assertFalse($readiness['ready']);
        $this->assertFalse($readiness['checks']['tax_withholding_approved']);
    }

    /**
     * Test 12: Agent A cannot view Agent B's payout history or unmasked bank secrets.
     */
    public function test_12_agent_a_cannot_view_agent_bs_payout_history(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $lA = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $lB = $this->createEligibleOrderAndLedger($this->customer2, $this->agentB, 20000);
        $this->calculationService->approveLedgerEntry($lA, $this->adminUser);
        $this->calculationService->approveLedgerEntry($lB, $this->adminUser);

        $payoutA = $this->payoutService->createPayoutBatch($this->agentA->id, [$lA->id], $this->adminUser, ['admin_notes' => 'CONFIDENTIAL-FINANCE-NOTE-A']);
        $payoutB = $this->payoutService->createPayoutBatch($this->agentB->id, [$lB->id], $this->adminUser, ['admin_notes' => 'CONFIDENTIAL-FINANCE-NOTE-B']);

        $this->actingAs($this->agentUserA);
        $listRes = $this->getJson('/api/v2/agent/payouts')->assertStatus(200);

        $refs = collect($listRes->json('data'))->pluck('payout_ref')->all();
        $this->assertContains($payoutA->payout_ref, $refs);
        $this->assertNotContains($payoutB->payout_ref, $refs);

        $body = $listRes->getContent();
        $this->assertStringNotContainsString('50100234567890', $body); // Raw account number never exposed
        $this->assertStringNotContainsString('CONFIDENTIAL-FINANCE-NOTE-A', $body);

        // Cross-agent IDOR attempt returns 403
        $this->getJson("/api/v2/agent/payouts/{$payoutB->id}")->assertStatus(403);
    }

    /**
     * Test 13: Seller users cannot access agent incentive payouts or reconciliation endpoints.
     */
    public function test_13_seller_users_cannot_access_agent_incentive_payouts(): void
    {
        $this->actingAs($this->seller1);

        $this->getJson('/api/v2/admin/payouts')->assertStatus(403);
        $this->getJson('/api/v2/admin/payouts/summary')->assertStatus(403);
        $this->getJson('/api/v2/admin/incentives/approval-queue')->assertStatus(403);
        $this->getJson('/api/v2/agent/payouts')->assertStatus(403);
        $this->get('/admin/incentives/payouts')->assertStatus(403);
    }

    /**
     * Test 14: Seller payable and commission records remain strictly unchanged.
     */
    public function test_14_seller_payable_and_commission_records_remain_unchanged(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 20000);

        $settlement = SellerSettlement::where('order_id', $ledger->parent_order_id)->firstOrFail();
        $netBefore = (float)$settlement->net_seller_payable;
        $deductBefore = (float)$settlement->total_platform_deduction;
        $ledgerEntriesBefore = FinancialLedger::where('order_id', $ledger->parent_order_id)->count();

        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);
        $payout = $this->payoutService->createPayoutBatch($this->agentA->id, [$ledger->id], $this->adminUser);
        $this->payoutService->authorizePayout($payout, $this->adminUser);
        $this->payoutService->confirmPayoutPayment($payout->fresh(), [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-SELLER-ISO-01',
            'confirmed_amount'         => 1000.00,
        ], $this->adminUser);

        $settlementAfter = $settlement->fresh();
        $this->assertEquals($netBefore, (float)$settlementAfter->net_seller_payable);
        $this->assertEquals($deductBefore, (float)$settlementAfter->total_platform_deduction);
        $this->assertEquals($ledgerEntriesBefore, FinancialLedger::where('order_id', $ledger->parent_order_id)->count());
        $this->assertEquals(AgentIncentiveLedger::EXPENSE_APNI_FACTORY_PLATFORM, $payout->fresh()->funding_expense_account);
    }

    /**
     * Test 15: Missing commercial / execution approval blocks financial execution.
     */
    public function test_15_missing_commercial_approval_blocks_financial_execution(): void
    {
        // Step 1: Temporarily allow calculation to produce an approved ledger entry, then test execution gate = false
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);

        // Enforce default Stage 5 Stop Condition flags!
        Config::set('marketplace_agents.payout_execution_enabled', false);
        Config::set('marketplace_agents.payout_execution_mode', 'awaiting_policy');

        $payout = $this->payoutService->createPayoutBatch($this->agentA->id, [$ledger->id], $this->adminUser);
        $this->assertEquals(AgentPayout::STATUS_DRAFT_AWAITING_POLICY, $payout->status);
        $this->assertEquals(AgentPayout::METHOD_AWAITING_POLICY, $payout->payment_method);

        $this->actingAs($this->adminUser);
        $this->postJson("/api/v2/admin/payouts/{$payout->id}/authorize")->assertStatus(422);
        $this->postJson("/api/v2/admin/payouts/{$payout->id}/confirm", [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-BLOCKED',
        ])->assertStatus(422);
    }

    /**
     * Test 16: Replayed callbacks and duplicate UTR references do not duplicate payouts.
     */
    public function test_16_replayed_events_do_not_duplicate_payouts(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $l1 = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $l2 = $this->createEligibleOrderAndLedger($this->customer2, $this->agentB, 10000);
        $this->calculationService->approveLedgerEntry($l1, $this->adminUser);
        $this->calculationService->approveLedgerEntry($l2, $this->adminUser);

        $p1 = $this->payoutService->createPayoutBatch($this->agentA->id, [$l1->id], $this->adminUser);
        $p2 = $this->payoutService->createPayoutBatch($this->agentB->id, [$l2->id], $this->adminUser);
        $this->payoutService->authorizePayout($p1, $this->adminUser);
        $this->payoutService->authorizePayout($p2, $this->adminUser);

        $this->actingAs($this->adminUser);
        $this->postJson("/api/v2/admin/payouts/{$p1->id}/confirm", [
            'confirmation_source'      => 'approved_provider_callback',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-REPLAY-63',
            'callback_event_id'        => 'cb_evt_unique_63',
            'confirmed_amount'         => 500.00,
        ])->assertStatus(200)
          ->assertJsonPath('data.status', AgentPayout::STATUS_PAID_CONFIRMED);

        // Identical callback replay succeeds idempotently without modifying state
        $this->postJson("/api/v2/admin/payouts/{$p1->id}/confirm", [
            'confirmation_source'      => 'approved_provider_callback',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-REPLAY-63',
            'callback_event_id'        => 'cb_evt_unique_63',
            'confirmed_amount'         => 500.00,
        ])->assertStatus(200);

        // Using same external UTR on Agent B's payout batch is rejected & flagged as duplicate_payment_detected!
        $this->postJson("/api/v2/admin/payouts/{$p2->id}/confirm", [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-REPLAY-63',
            'confirmed_amount'         => 500.00,
        ])->assertStatus(422);

        $this->assertEquals(
            1,
            AgentPayoutReconciliationException::where('agent_payout_id', $p2->id)
                ->where('exception_type', AgentPayoutReconciliationException::TYPE_DUPLICATE_PAYMENT)
                ->count()
        );
    }

    /**
     * Test 17: Historical incentive snapshots and locked payouts remain immutable.
     */
    public function test_17_historical_incentive_snapshots_remain_immutable(): void
    {
        $rule = $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);
        $this->calculationService->approveLedgerEntry($ledger, $this->adminUser);

        $payout = $this->payoutService->createPayoutBatch($this->agentA->id, [$ledger->id], $this->adminUser);
        $this->payoutService->authorizePayout($payout, $this->adminUser);
        $this->payoutService->confirmPayoutPayment($payout->fresh(), [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-IMMUT-63',
            'confirmed_amount'         => 500.00,
        ], $this->adminUser);

        // New rule v2 activated -> paid payout and ledger remain unchanged
        $v2 = $this->ruleService->createNewVersion($rule, ['rate_percentage' => 1.0, 'effective_from' => now()->subMinute(), 'agreement_ref' => 'AGR-V2'], $this->adminUser);
        $this->ruleService->recordBusinessApproval($v2, $this->adminUser, 'B-V2');
        $this->ruleService->recordFinanceApproval($v2->fresh(), $this->adminUser, 'F-V2');
        $this->ruleService->activateRule($v2->fresh(), $this->adminUser);

        $this->assertEquals(500.00, $payout->fresh()->net_payable_amount);
        $this->assertEquals(1, $ledger->fresh()->rule_version);

        // Attempting to overwrite payout net_payable_amount throws LogicException
        $this->expectException(\LogicException::class);
        $payout->fresh()->update(['net_payable_amount' => 1200.00]);
    }

    /**
     * Test 18: Finance reports, CSV export & UI screens match authoritative incentive & payout ledgers.
     */
    public function test_18_reports_match_authoritative_incentive_and_payout_ledgers(): void
    {
        $this->enableFullApprovedCommercialAndPayoutPolicy();

        $lPaid = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000); // 500
        $lApprovedUnpaid = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 20000); // 1000
        $lPending = $this->createEligibleOrderAndLedger($this->customer2, $this->agentB, 10000); // 500

        $this->calculationService->approveLedgerEntry($lPaid, $this->adminUser);
        $this->calculationService->approveLedgerEntry($lApprovedUnpaid, $this->adminUser);

        $payout = $this->payoutService->createPayoutBatch($this->agentA->id, [$lPaid->id], $this->adminUser);
        $this->payoutService->authorizePayout($payout, $this->adminUser);
        $this->payoutService->confirmPayoutPayment($payout->fresh(), [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-RPT-63',
            'confirmed_amount'         => 500.00,
        ], $this->adminUser);

        $this->actingAs($this->adminUser);
        $sumRes = $this->getJson('/api/v2/admin/payouts/summary')->assertStatus(200);
        $s = $sumRes->json('data');

        $this->assertEquals(2000.00, $s['earned_total']);
        $this->assertEquals(500.00, $s['pending_approval_total']);
        $this->assertEquals(1000.00, $s['approved_unpaid_total']);
        $this->assertEquals(500.00, $s['confirmed_paid_total']);
        $this->assertEquals(500.00, $s['payout_batches_paid_sum']);
        $this->assertEquals(1, $s['payout_ready_count']);

        // CSV Export works
        $csvRes = $this->get('/admin/incentives/reports/export')->assertStatus(200);
        $csvContent = $csvRes->streamedContent();
        $this->assertStringContainsString('UTR-RPT-63', $csvContent);
        $this->assertStringContainsString('apnifactory_platform_expense', $csvContent);

        // Finance Admin & Agent Blade pages render cleanly
        $this->get('/admin/incentives/payouts')->assertStatus(200)->assertSee($payout->payout_ref);
        $this->get("/admin/incentives/payouts/{$payout->id}")->assertStatus(200)->assertSee('UTR-RPT-63');
        $this->get('/admin/incentives/exceptions')->assertStatus(200);

        $this->actingAs($this->agentUserA);
        $this->get('/agent/incentives')->assertStatus(200)->assertSee($payout->payout_ref)->assertSee('UTR-RPT-63');
    }

    /**
     * Test 19: Hold workflow, unmatched external UTR exception resolution, and deduplicated notifications work.
     */
    public function test_19_hold_unmatched_exception_resolution_and_notifications_work(): void
    {
        Queue::fake();

        $this->enableFullApprovedCommercialAndPayoutPolicy();
        $ledger = $this->createEligibleOrderAndLedger($this->customer1, $this->agentA, 10000);

        $this->actingAs($this->adminUser);

        // Hold workflow
        $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/hold", ['reason' => 'Awaiting GST invoice copy'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', AgentIncentiveLedger::STATUS_ON_HOLD);

        // Approve -> Initiate -> Confirm dispatches deduplicated queued jobs (In-App + Email, no SMS/WhatsApp)
        $this->postJson("/api/v2/admin/incentives/ledger/{$ledger->id}/approve")->assertStatus(200);
        $payout = $this->payoutService->createPayoutBatch($this->agentA->id, [$ledger->id], $this->adminUser);
        $this->payoutService->authorizePayout($payout, $this->adminUser);
        $this->payoutService->recordPaymentInitiation($payout->fresh(), $this->adminUser);
        $this->payoutService->confirmPayoutPayment($payout->fresh(), [
            'confirmation_source'      => 'verified_bank_statement',
            'verification_status'      => 'verified',
            'external_transaction_ref' => 'UTR-NOTIF-63',
            'confirmed_amount'         => 500.00,
        ], $this->adminUser);

        Queue::assertPushed(SendAgentIncentiveNotificationJob::class);

        // Log unmatched external UTR & resolve it
        $unmatchedRes = $this->postJson('/api/v2/admin/payouts/exceptions/unmatched-ref', [
            'external_reference' => 'UTR-ORPHAN-999',
            'reported_amount'    => 1234.50,
            'notes'              => 'Found on HDFC statement without batch ID',
        ])->assertStatus(201);

        $exId = $unmatchedRes->json('data.id');
        $this->postJson("/api/v2/admin/payouts/exceptions/{$exId}/resolve", [
            'status'           => 'resolved',
            'resolution_notes' => 'Reversed at bank end as non-agent transfer.',
        ])->assertStatus(200)
          ->assertJsonPath('data.status', AgentPayoutReconciliationException::STATUS_RESOLVED);
    }
}

