<?php

namespace Database\Seeders;

use App\Models\AgentIncentiveRule;
use App\Models\BuyerAgentAttribution;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Models\Order;
use App\Models\SellerFulfilment;
use App\Models\Transection;
use App\Models\User;
use App\Services\AgentIncentiveRuleService;
use App\Services\BuyerAttributionService;
use App\Services\BuyerDisputeService;
use App\Services\FieldAgentService;
use App\Services\PaymentRefundService;
use App\Services\SellerFulfilmentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Phase6AgentModuleUatSeeder extends Seeder
{
    /**
     * Controlled anonymized staging UAT seed data for Phase 6.1-6.4 verification.
     * Standalone & idempotent — run manually on STAGING only (`php artisan db:seed --class=Phase6AgentModuleUatSeeder`).
     */
    public function run(): void
    {
        $agentService = app(FieldAgentService::class);
        $attrService  = app(BuyerAttributionService::class);
        $ruleService  = app(AgentIncentiveRuleService::class);
        $fulfilService = app(SellerFulfilmentService::class);
        $disputeService = app(BuyerDisputeService::class);

        // 1. Ensure UAT Admin User
        $admin = User::firstOrCreate(
            ['email' => 'uat.admin@apnifactory.test'],
            ['name' => 'UAT Operations & Finance Admin', 'password' => Hash::make('UatPass@2026')]
        );

        // 2. Two UAT Sellers
        $seller1 = User::firstOrCreate(
            ['email' => 'uat.seller1@apnifactory.test'],
            ['name' => 'UAT Seller Alpha Works', 'password' => Hash::make('UatPass@2026')]
        );
        Company::firstOrCreate(
            ['user_id' => $seller1->id],
            ['name' => 'UAT Alpha Paints Plant', 'city' => 'Jaipur', 'state' => 'Rajasthan', 'pincode' => '302001', 'mobile' => '9000000101', 'comission' => 10, 'status' => 'active']
        );

        $seller2 = User::firstOrCreate(
            ['email' => 'uat.seller2@apnifactory.test'],
            ['name' => 'UAT Seller Beta Ceramics', 'password' => Hash::make('UatPass@2026')]
        );
        Company::firstOrCreate(
            ['user_id' => $seller2->id],
            ['name' => 'UAT Beta Tiles Hub', 'city' => 'Morbi', 'state' => 'Gujarat', 'pincode' => '363641', 'mobile' => '9000000102', 'comission' => 8, 'status' => 'active']
        );

        // 3. Two Controlled Test Field Sales Agents
        $agent1 = FieldAgent::where('agent_code', 'FSA-UAT-001')->first();
        if (!$agent1) {
            $agent1 = $agentService->createAgent([
                'agent_code'          => 'FSA-UAT-001',
                'name'                => 'UAT Field Agent Rajasthan',
                'email'               => 'uat.agent1@apnifactory.test',
                'mobile'              => '9999000101',
                'territory'           => 'UAT Jaipur South',
                'status'              => 'active',
                'kyc_status'          => 'verified',
                'contract_status'     => 'active',
                'create_user_account' => true,
                'password'            => 'UatAgent@2026',
                'payout_details'      => [
                    'account_holder_name' => 'UAT Field Agent Rajasthan',
                    'bank_name'           => 'UAT Test Bank',
                    'account_number'      => '0000111122223333',
                    'ifsc_code'           => 'UATB0000001',
                ],
            ], $admin);
        }

        $agent2 = FieldAgent::where('agent_code', 'FSA-UAT-002')->first();
        if (!$agent2) {
            $agent2 = $agentService->createAgent([
                'agent_code'          => 'FSA-UAT-002',
                'name'                => 'UAT Field Agent Gujarat',
                'email'               => 'uat.agent2@apnifactory.test',
                'mobile'              => '9999000102',
                'territory'           => 'UAT Surat Industrial',
                'status'              => 'pending',
                'kyc_status'          => 'pending_review',
                'contract_status'     => 'pending',
                'create_user_account' => true,
                'password'            => 'UatAgent@2026',
            ], $admin);
        }

        // 4. Anonymized Test Buyer / Dealer Accounts
        $dealer1 = Customer::firstOrCreate(
            ['email' => 'uat.dealer1@apnifactory.test'],
            ['name' => 'UAT Test Dealer One', 'mobile' => '9999000201', 'password' => Hash::make('UatPass@2026'), 'location' => 'Jaipur', 'status' => 'active']
        );
        $dealer2 = Customer::firstOrCreate(
            ['email' => 'uat.dealer2@apnifactory.test'],
            ['name' => 'UAT Test Dealer Two', 'mobile' => '9999000202', 'password' => Hash::make('UatPass@2026'), 'location' => 'Kota', 'status' => 'active']
        );
        $dealer3 = Customer::firstOrCreate(
            ['email' => 'uat.dealer3@apnifactory.test'],
            ['name' => 'UAT Test Dealer Three (Unlinked)', 'mobile' => '9999000203', 'password' => Hash::make('UatPass@2026'), 'location' => 'Udaipur', 'status' => 'active']
        );

        // Link Dealer 1 & Dealer 2 -> Active Agent 1 idempotently
        foreach ([$dealer1, $dealer2] as $d) {
            if (!BuyerAgentAttribution::where('customer_id', $d->id)->whereNotNull('active_customer_lock')->exists()) {
                $attrService->createAttribution([
                    'customer_id'        => $d->id,
                    'field_agent_id'     => $agent1->id,
                    'attribution_source' => BuyerAgentAttribution::SOURCE_ONBOARDING,
                    'evidence_reference' => 'UAT-SEED-' . $d->id,
                ], $admin);
            }
        }

        // 5. Draft Incentive Rule (remains Draft/Inactive until UAT sign-off)
        if (!AgentIncentiveRule::where('rule_code', 'INC-RULE-UAT-01')->exists()) {
            $ruleService->createRule([
                'rule_code'          => 'INC-RULE-UAT-01',
                'name'               => 'UAT Sample Draft Rule (Requires Policy Approval)',
                'calculation_method' => AgentIncentiveRule::METHOD_PERCENTAGE_ORDER,
                'calculation_base'   => AgentIncentiveRule::BASE_TAXABLE_ORDER,
                'rate_percentage'    => 2.0,
                'max_cap_amount'     => 1000.00,
                'rounding_rule'      => AgentIncentiveRule::ROUND_HALF_UP_2,
                'tax_treatment_code' => AgentIncentiveRule::TAX_EXCLUSIVE_NO_GST,
                'effective_from'     => now()->subDay(),
                'agreement_ref'      => 'UAT-DRAFT-SIGN-OFF',
                'reason'             => 'Controlled UAT sample rule',
            ], $admin);
        }

        // 6. Controlled Orders Representing Different Eligibility States for Dealer 1
        // 6a. Eligible Order (1/3)
        if (!Order::where('orderno', 'ORD-UAT-ELIG-01')->exists()) {
            $o1 = Order::create([
                'orderno'        => 'ORD-UAT-ELIG-01',
                'customer_id'    => $dealer1->id,
                'user_id'        => $seller1->id,
                'address'        => 'UAT Warehouse Jaipur',
                'netamount'      => 10000.00,
                'taxamount'      => 1800.00,
                'grandtotal'     => 11800.00,
                'payment_status' => 'Paid',
                'paid_at'        => now(),
            ]);
            Transection::create([
                'payment_ref'           => 'PAY-UAT-01',
                'order_id'              => $o1->id,
                'parent_order_id'       => $o1->id,
                'order_no'              => $o1->orderno,
                'customer_id'           => $dealer1->id,
                'user_id'               => $seller1->id,
                'amount'                => 11800.00,
                'status'                => 'successful',
                'verification_status'   => 'verified',
                'reconciliation_status' => 'matched',
                'verified_at'           => now(),
                'txnid'                 => 'uat_txn_01',
            ]);
            $f1 = $fulfilService->getOrCreateFulfilment($o1);
            $fulfilService->updateStatus($f1, SellerFulfilment::STATUS_ACCEPTED, $seller1->id, 'seller', 'UAT Seller 1');
            $fulfilService->recordDispatch($f1->fresh(), ['transporter_name' => 'UAT Cargo', 'lr_number' => 'UAT-LR-001'], $seller1->id, 'UAT Seller 1');
            $disputeService->acknowledgeReceipt($f1->fresh(), $dealer1->id, $dealer1->name, 'UAT Receipt Confirmed');
        }

        // 6b. Pending Eligibility Order (dispatched, awaiting buyer ack)
        if (!Order::where('orderno', 'ORD-UAT-PEND-02')->exists()) {
            $o2 = Order::create([
                'orderno'        => 'ORD-UAT-PEND-02',
                'customer_id'    => $dealer1->id,
                'user_id'        => $seller2->id,
                'address'        => 'UAT Warehouse Jaipur',
                'netamount'      => 15000.00,
                'taxamount'      => 2700.00,
                'grandtotal'     => 17700.00,
                'payment_status' => 'Paid',
                'paid_at'        => now(),
            ]);
            $f2 = $fulfilService->getOrCreateFulfilment($o2);
            $fulfilService->updateStatus($f2, SellerFulfilment::STATUS_ACCEPTED, $seller2->id, 'seller', 'UAT Seller 2');
            $fulfilService->recordDispatch($f2->fresh(), ['transporter_name' => 'UAT Express', 'lr_number' => 'UAT-LR-002'], $seller2->id, 'UAT Seller 2');
        }

        // 6c. Unpaid Order (must NOT consume slot)
        if (!Order::where('orderno', 'ORD-UAT-UNPAID-03')->exists()) {
            $o3 = Order::create([
                'orderno'        => 'ORD-UAT-UNPAID-03',
                'customer_id'    => $dealer2->id,
                'user_id'        => $seller1->id,
                'address'        => 'UAT Warehouse Kota',
                'netamount'      => 5000.00,
                'taxamount'      => 900.00,
                'grandtotal'     => 5900.00,
                'payment_status' => 'Pending',
            ]);
            app(\App\Services\AgentOrderEligibilityService::class)->handleOrderLifecycleEvent($o3, 'uat_seed_unpaid', 'uat_unpaid_03');
        }

        // 6d. Refunded Order (must NOT consume slot)
        if (!Order::where('orderno', 'ORD-UAT-REFUND-04')->exists()) {
            $o4 = Order::create([
                'orderno'        => 'ORD-UAT-REFUND-04',
                'customer_id'    => $dealer2->id,
                'user_id'        => $seller2->id,
                'address'        => 'UAT Warehouse Kota',
                'netamount'      => 8000.00,
                'taxamount'      => 1440.00,
                'grandtotal'     => 9440.00,
                'payment_status' => 'Paid',
                'paid_at'        => now(),
            ]);
            app(PaymentRefundService::class)->processRefund($o4, 'UAT controlled refund verification scenario');
        }
    }
}

