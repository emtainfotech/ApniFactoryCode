<?php

namespace Tests\Feature;

use App\Jobs\SendAgentAttributionNotificationJob;
use App\Mail\AgentAttributionMail;
use App\Models\BuyerAgentAttribution;
use App\Models\BuyerAttributionHistory;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Models\FieldAgentStatusHistory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\User;
use App\Services\BuyerAttributionService;
use App\Services\FieldAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class Phase6_1_FieldAgentAndAttributionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sellerUser;
    protected Company $sellerCompany;
    protected User $agentUserA;
    protected User $agentUserB;
    protected User $buyerUser;
    protected Customer $customer1;
    protected Customer $customer2;
    protected Customer $customer3;
    protected FieldAgent $agentA;
    protected FieldAgent $agentB;
    protected FieldAgent $inactiveAgent;

    protected FieldAgentService $agentService;
    protected BuyerAttributionService $attributionService;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->agentService = app(FieldAgentService::class);
        $this->attributionService = app(BuyerAttributionService::class);

        // 1. Admin User
        $this->adminUser = User::create([
            'name'     => 'Operations Super Admin',
            'email'    => 'admin.fieldops@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. Seller User & Company
        $this->sellerUser = User::create([
            'name'     => 'Jaipur Coats Pvt Ltd',
            'email'    => 'seller.coats@jaipur.in',
            'password' => bcrypt('password123'),
        ]);
        $this->sellerCompany = Company::create([
            'user_id' => $this->sellerUser->id,
            'name'    => 'Jaipur Coats Factory',
            'email'   => 'seller.coats@jaipur.in',
            'mobile'  => '9829011111',
            'city'    => 'Jaipur',
            'state'   => 'Rajasthan',
            'pincode' => '302022',
            'status'  => 'Active',
        ]);

        // 3. Regular Buyer User (for non-admin authorization test)
        $this->buyerUser = User::create([
            'name'     => 'Unauthorized Buyer Account',
            'email'    => 'buyer.user@retailer.in',
            'password' => bcrypt('password123'),
        ]);

        // 4. Field Agent Portal Users
        $this->agentUserA = User::create([
            'name'     => 'Rajesh Sharma (Agent A)',
            'email'    => 'agent.rajesh@apnifactory.in',
            'password' => bcrypt('password123'),
        ]);
        $this->agentUserB = User::create([
            'name'     => 'Vikram Patel (Agent B)',
            'email'    => 'agent.vikram@apnifactory.in',
            'password' => bcrypt('password123'),
        ]);

        // 5. Buyers / Dealers in Customers table
        $this->customer1 = Customer::create([
            'name'     => 'Shree Ganesh Hardware & Paints',
            'email'    => 'ganesh.hardware@dealer.in',
            'mobile'   => '9811100001',
            'location' => 'Jaipur, Rajasthan',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);
        $this->customer2 = Customer::create([
            'name'     => 'Ambika Traders Surat',
            'email'    => 'ambika.traders@dealer.in',
            'mobile'   => '9811100002',
            'location' => 'Surat, Gujarat',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);
        $this->customer3 = Customer::create([
            'name'     => 'Mahalaxmi Depot Pune',
            'email'    => 'mahalaxmi.depot@dealer.in',
            'mobile'   => '9811100003',
            'location' => 'Pune, Maharashtra',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);

        // 6. Seed Field Agents via Admin Service
        $this->agentA = $this->agentService->createAgent([
            'agent_code'      => 'FSA-2026-0001',
            'name'            => 'Rajesh Sharma',
            'email'           => 'agent.rajesh@apnifactory.in',
            'mobile'          => '9900112201',
            'territory'       => 'Jaipur & Sikar Belt',
            'territory_city'  => 'Jaipur',
            'territory_state' => 'Rajasthan',
            'status'          => FieldAgent::STATUS_ACTIVE,
            'user_id'         => $this->agentUserA->id,
            'kyc_status'      => FieldAgent::KYC_VERIFIED,
            'contract_status' => 'active',
            'kyc_reference'   => 'KYC-DOC-REF-1001',
            'notes'           => 'INTERNAL ONLY: High priority Jaipur cluster agent.',
            'payout_details'  => [
                'account_holder_name' => 'Rajesh Sharma',
                'bank_name'           => 'HDFC Bank',
                'account_number'      => '50100998877665',
                'ifsc_code'           => 'HDFC0001234',
                'upi_id'              => 'rajeshsharma@okhdfc',
            ],
        ], $this->adminUser);

        $this->agentB = $this->agentService->createAgent([
            'agent_code'      => 'FSA-2026-0002',
            'name'            => 'Vikram Patel',
            'email'           => 'agent.vikram@apnifactory.in',
            'mobile'          => '9900112202',
            'territory'       => 'Surat Industrial Corridor',
            'territory_city'  => 'Surat',
            'territory_state' => 'Gujarat',
            'status'          => FieldAgent::STATUS_ACTIVE,
            'user_id'         => $this->agentUserB->id,
            'kyc_status'      => FieldAgent::KYC_PENDING_REVIEW,
            'contract_status' => 'active',
        ], $this->adminUser);

        $this->inactiveAgent = $this->agentService->createAgent([
            'agent_code'      => 'FSA-2026-0003',
            'name'            => 'Suresh Verma',
            'email'           => 'agent.suresh@apnifactory.in',
            'mobile'          => '9900112203',
            'territory'       => 'Pune West',
            'status'          => FieldAgent::STATUS_INACTIVE,
        ], $this->adminUser);
    }

    /**
     * Scenario 1: Admin can create an agent with a unique agent ID.
     */
    public function test_admin_can_create_agent_with_unique_agent_id(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/api/v2/admin/agents', [
            'agent_code'          => 'FSA-2026-9001',
            'name'                => 'Anil Kumar Meena',
            'email'               => 'anil.meena@apnifactory.in',
            'mobile'              => '9988776655',
            'territory'           => 'Kota Industrial Zone',
            'territory_city'      => 'Kota',
            'territory_state'     => 'Rajasthan',
            'status'              => 'active',
            'joining_date'        => '2026-11-01',
            'supervisor_name'     => 'Regional Manager North',
            'kyc_status'          => 'verified',
            'contract_status'     => 'active',
            'create_user_account' => true,
            'password'            => 'AgentPass#2026',
            'payout_details'      => [
                'account_holder_name' => 'Anil Kumar Meena',
                'bank_name'           => 'SBI',
                'account_number'      => '301122334455',
                'ifsc_code'           => 'SBIN0004321',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.agent_code', 'FSA-2026-9001')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.payout_summary.account_masked', '********4455');

        $this->assertDatabaseHas('field_agents', [
            'agent_code' => 'FSA-2026-9001',
            'mobile'     => '9988776655',
            'territory'  => 'Kota Industrial Zone',
        ]);

        $agent = FieldAgent::where('agent_code', 'FSA-2026-9001')->firstOrFail();
        $this->assertNotNull($agent->user_id);
        $this->assertDatabaseHas('field_agent_status_history', [
            'field_agent_id' => $agent->id,
            'action'         => 'created',
            'new_status'     => 'active',
        ]);
    }

    /**
     * Scenario 2: Duplicate agent ID is rejected.
     */
    public function test_duplicate_agent_id_is_rejected(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/api/v2/admin/agents', [
            'agent_code' => 'FSA-2026-0001', // Duplicate of Agent A
            'name'       => 'Duplicate Agent Test',
            'mobile'     => '9988001122',
            'territory'  => 'Ajmer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['agent_code']);
    }

    /**
     * Scenario 3: Inactive agents cannot receive new buyer attributions.
     */
    public function test_inactive_agent_cannot_receive_new_buyer_attributions(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->postJson('/api/v2/admin/attributions', [
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->inactiveAgent->id,
            'remarks'        => 'Trying to attribute inactive agent',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['field_agent_id']);

        $this->assertDatabaseMissing('buyer_agent_attributions', [
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->inactiveAgent->id,
        ]);
    }

    /**
     * Scenario 4: Active authorized agents can be attributed to buyer accounts by authorized admins (including approval workflow).
     */
    public function test_active_authorized_agent_can_be_attributed_and_approved_by_admin(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create with explicit approval workflow requested
        $createRes = $this->postJson('/api/v2/admin/attributions', [
            'customer_id'        => $this->customer1->id,
            'field_agent_id'     => $this->agentA->id,
            'attribution_source' => 'onboarding',
            'evidence_reference' => 'ONB-FORM-2026-88',
            'remarks'            => 'Agent Rajesh assisted dealer registration at Sitapura.',
            'admin_notes'        => 'CONFIDENTIAL: Admin verification completed via dealer callback.',
            'require_approval'   => true,
        ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.approval_status', 'pending_approval');

        $attrId = (int)$createRes->json('data.id');

        // Pending admin notification logged in DB & Mail queued
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->adminUser->id,
            'type'    => 'admin',
        ]);

        // 2. Admin approves attribution
        $approveRes = $this->postJson("/api/v2/admin/attributions/{$attrId}/approve", [
            'notes' => 'Verified dealer visit photo and GST onboarding.',
        ]);

        $approveRes->assertStatus(200)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.approval_status', 'approved');

        $this->assertDatabaseHas('buyer_agent_attributions', [
            'id'                   => $attrId,
            'customer_id'          => $this->customer1->id,
            'field_agent_id'       => $this->agentA->id,
            'active_customer_lock' => $this->customer1->id,
            'status'               => 'active',
            'approval_status'      => 'approved',
        ]);

        // Agent receives in-app notification + Email (no SMS/WhatsApp)
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->agentUserA->id,
            'type'    => 'agent',
        ]);
        Mail::assertSent(AgentAttributionMail::class, 2); // pending to admin + approved to agent A
    }

    /**
     * Scenario 5: Buyer cannot have conflicting primary attributions.
     */
    public function test_buyer_cannot_have_conflicting_primary_attributions(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
            'remarks'        => 'Initial active link to Agent A',
        ], $this->adminUser);

        $this->actingAs($this->adminUser);

        $conflictRes = $this->postJson('/api/v2/admin/attributions', [
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentB->id,
            'remarks'        => 'Conflicting link attempt to Agent B',
        ]);

        $conflictRes->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);

        $this->assertEquals(1, BuyerAgentAttribution::where('customer_id', $this->customer1->id)->count());
    }

    /**
     * Scenario 6: Duplicate attribution is rejected safely.
     */
    public function test_duplicate_attribution_same_agent_rejected_safely(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $this->actingAs($this->adminUser);

        $dupRes = $this->postJson('/api/v2/admin/attributions', [
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ]);

        $dupRes->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);

        $this->assertEquals(1, BuyerAgentAttribution::where('customer_id', $this->customer1->id)->count());
    }

    /**
     * Scenario 7: Unauthorized users cannot create or approve attribution (or manage agents).
     */
    public function test_unauthorized_users_cannot_create_or_approve_attribution(): void
    {
        $pendingAttr = $this->attributionService->createAttribution([
            'customer_id'      => $this->customer1->id,
            'field_agent_id'   => $this->agentA->id,
            'require_approval' => true,
        ], $this->adminUser);

        foreach ([$this->sellerUser, $this->buyerUser, $this->agentUserA] as $nonAdmin) {
            $this->actingAs($nonAdmin);

            $this->postJson('/api/v2/admin/agents', [
                'name'      => 'Rogue Agent',
                'mobile'    => '9000090000',
                'territory' => 'Delhi',
            ])->assertStatus(403);

            $this->postJson('/api/v2/admin/attributions', [
                'customer_id'    => $this->customer2->id,
                'field_agent_id' => $this->agentA->id,
            ])->assertStatus(403);

            $this->postJson("/api/v2/admin/attributions/{$pendingAttr->id}/approve")
                ->assertStatus(403);

            $this->get('/admin/agents')->assertStatus(403);
            $this->get('/admin/attributions')->assertStatus(403);
        }
    }

    /**
     * Scenario 8: Agent can view only assigned buyers (with unavailable eligibility progress placeholder).
     */
    public function test_agent_can_view_only_assigned_buyers(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer2->id,
            'field_agent_id' => $this->agentB->id,
        ], $this->adminUser);

        // Verify Phase 6.1 disabled-tracking compatibility mode renders Unavailable placeholder cleanly
        Config::set('marketplace_agents.eligible_order_tracking_implemented', false);

        $this->actingAs($this->agentUserA);

        $buyersRes = $this->getJson('/api/v2/agent/buyers');
        $buyersRes->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.buyer.id', $this->customer1->id)
            ->assertJsonPath('data.0.buyer.name', 'Shree Ganesh Hardware & Paints')
            ->assertJsonPath('data.0.eligible_order_progress.available', false)
            ->assertJsonPath('data.0.eligible_order_progress.display', 'Unavailable (Pending Phase 6.2 Eligible-Order Rules)');

        // Detail endpoint works for assigned buyer
        $this->getJson("/api/v2/agent/buyers/{$this->customer1->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.buyer.id', $this->customer1->id);

        // Agent web dashboard works cleanly
        $webRes = $this->get('/agent/dashboard');
        $webRes->assertStatus(200)
            ->assertSee('FSA-2026-0001')
            ->assertSee('Shree Ganesh Hardware')
            ->assertSee('Unavailable (Pending Phase 6.2 Eligible-Order Rules)')
            ->assertDontSee('Ambika Traders Surat');
    }

    /**
     * Scenario 9: Agent A cannot access Agent B's buyer records.
     */
    public function test_agent_a_cannot_access_agent_bs_buyer_records(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer2->id,
            'field_agent_id' => $this->agentB->id,
        ], $this->adminUser);

        // Verify Phase 6.1 disabled-tracking compatibility mode renders Unavailable placeholder cleanly
        Config::set('marketplace_agents.eligible_order_tracking_implemented', false);

        $this->actingAs($this->agentUserA);

        // API IDOR attempt on Agent B's buyer
        $this->getJson("/api/v2/agent/buyers/{$this->customer2->id}")
            ->assertStatus(403);

        // Web IDOR attempt on Agent B's buyer
        $this->get("/agent/buyers/{$this->customer2->id}")
            ->assertStatus(403);
    }

    /**
     * Scenario 10: Agent cannot access confidential seller prices or unrelated financial data.
     */
    public function test_agent_cannot_access_confidential_seller_prices_or_financial_data(): void
    {
        $product = Product::create([
            'name'     => 'Apex Wall Primer 20L',
            'category' => 'Paints',
            'price'    => 2800,
        ]);
        $variant = ProductVariant::create([
            'product_id'   => $product->id,
            'variant_sku'  => 'VAR-PRIMER-20L',
            'variant_name' => '20L Drum',
        ]);
        $listing = SellerListing::create([
            'seller_user_id'     => $this->sellerUser->id,
            'company_id'         => $this->sellerCompany->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'seller_sku'         => 'SKU-PRIV-20L',
            'seller_price'       => 2250.00, // Strictly confidential wholesale rate
            'commission_rate'    => 10.00,   // Confidential margin
            'selling_price'      => 2475.00,
            'mrp'                => 3200.00,
            'stock_quantity'     => 100,
            'status'             => 'Active',
        ]);

        // Verify Phase 6.1 disabled-tracking compatibility mode renders Unavailable placeholder cleanly
        Config::set('marketplace_agents.eligible_order_tracking_implemented', false);

        $this->actingAs($this->agentUserA);

        // 1. Agent User::isSeller() must return false for pure field agent
        $this->assertFalse($this->agentUserA->isSeller());

        // 2. Blocked from seller listing private pricing endpoints
        $this->getJson('/api/v2/seller/listings')->assertStatus(403);
        $this->getJson("/api/v2/seller/listings/{$listing->id}")->assertStatus(403);

        // 3. Blocked from seller commissions & settlements & finance ledgers
        $this->getJson('/api/v2/seller/commissions/agreements')->assertStatus(403);
        $this->getJson('/api/v2/seller/settlements')->assertStatus(403);
        $this->getJson('/api/v2/finance/ledger')->assertStatus(403);
        $this->getJson('/api/v2/finance/settlements')->assertStatus(403);
    }

    /**
     * Scenario 11: Attribution history remains available after reassignment or agent deactivation.
     */
    public function test_attribution_history_remains_available_after_reassignment_or_deactivation(): void
    {
        $attr1 = $this->attributionService->createAttribution([
            'customer_id'        => $this->customer1->id,
            'field_agent_id'     => $this->agentA->id,
            'evidence_reference' => 'VISIT-001',
        ], $this->adminUser);

        // Reassign before eligible order
        $attr2 = $this->attributionService->reassignAttribution($this->customer1, $this->agentB, $this->adminUser, [
            'reason'             => 'Territory reassignment to Gujarat belt',
            'evidence_reference' => 'MEMO-REASSIGN-09',
        ]);

        // Deactivate Agent A afterwards
        $this->agentService->changeStatus($this->agentA, FieldAgent::STATUS_INACTIVE, $this->adminUser, 'Agent resigned');

        // Historical attribution remains preserved (not deleted or overwritten)
        $attr1->refresh();
        $this->assertEquals(BuyerAgentAttribution::STATUS_SUPERSEDED, $attr1->status);
        $this->assertEquals($this->agentA->id, $attr1->field_agent_id);
        $this->assertNull($attr1->active_customer_lock);

        // History endpoint shows complete non-destructive trail
        $this->actingAs($this->adminUser);
        $histRes = $this->getJson("/api/v2/admin/customers/{$this->customer1->id}/attribution-history");
        $histRes->assertStatus(200)
            ->assertJsonCount(2, 'data.attributions');

        $actions = collect($histRes->json('data.audit_logs'))->pluck('action')->all();
        $this->assertContains('attribution_created', $actions);
        $this->assertContains('attribution_superseded', $actions);
        $this->assertContains('attribution_reassigned', $actions);

        // Verify ORM immutability prevents tampering/deleting
        $this->expectException(\LogicException::class);
        $attr1->delete();
    }

    /**
     * Scenario 12: Reassignment before the first eligible order follows approved authorization.
     */
    public function test_reassignment_before_first_eligible_order_succeeds_when_authorized(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        // Unpaid/Pending order does NOT lock reassignment
        Order::create([
            'orderno'        => 'ORD-UNPAID-001',
            'customer_id'    => $this->customer1->id,
            'user_id'        => $this->sellerUser->id,
            'grandtotal'     => 15000.00,
            'netamount'      => 12711.86,
            'payment_status' => 'Pending',
        ]);

        $this->actingAs($this->adminUser);

        $res = $this->postJson('/api/v2/admin/attributions/reassign', [
            'customer_id'        => $this->customer1->id,
            'new_field_agent_id' => $this->agentB->id,
            'reason'             => 'Authorized territory handover before any paid eligible order.',
            'evidence_reference' => 'OPS-MEMO-22',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('data.field_agent_id', $this->agentB->id)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.attribution_source', 'reassignment');
    }

    /**
     * Scenario 13: Reassignment after an eligible order is blocked unless an approved exception exists.
     */
    public function test_reassignment_after_eligible_order_is_blocked_unless_exception_approved(): void
    {
        $this->attributionService->createAttribution([
            'customer_id'    => $this->customer1->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        // Buyer places a Paid eligible order
        Order::create([
            'orderno'        => 'ORD-PAID-ELIG-001',
            'customer_id'    => $this->customer1->id,
            'user_id'        => $this->sellerUser->id,
            'grandtotal'     => 45000.00,
            'netamount'      => 38135.59,
            'payment_status' => 'Paid',
            'paid_at'        => now(),
        ]);

        $this->actingAs($this->adminUser);

        // 1. Standard reassignment blocked!
        $blockedRes = $this->postJson('/api/v2/admin/attributions/reassign', [
            'customer_id'        => $this->customer1->id,
            'new_field_agent_id' => $this->agentB->id,
            'reason'             => 'Trying to move buyer after paid order.',
        ]);

        $blockedRes->assertStatus(422)
            ->assertJsonValidationErrors(['eligible_order']);

        $this->assertDatabaseHas('buyer_attribution_history', [
            'customer_id' => $this->customer1->id,
            'action'      => 'reassignment_blocked_eligible_order',
        ]);

        // 2. When approved exception policy is enabled & explicitly documented, reassignment is permitted
        Config::set('marketplace_agents.allow_reassignment_after_eligible_order_exception', true);

        $exceptionRes = $this->postJson('/api/v2/admin/attributions/reassign', [
            'customer_id'            => $this->customer1->id,
            'new_field_agent_id'     => $this->agentB->id,
            'reason'                 => 'Executive exception: previous agent left company.',
            'is_exception_approved'  => true,
            'exception_approval_ref' => 'EXEC-DIR-EXC-2026-07',
        ]);

        $exceptionRes->assertStatus(200)
            ->assertJsonPath('data.field_agent_id', $this->agentB->id)
            ->assertJsonPath('data.is_exception_reassignment', true)
            ->assertJsonPath('data.exception_approval_ref', 'EXEC-DIR-EXC-2026-07');
    }

    /**
     * Scenario 14: Historical order data is not modified by attribution changes.
     */
    public function test_historical_order_data_is_not_modified_by_attribution_changes(): void
    {
        $order = Order::create([
            'orderno'        => 'ORD-HIST-IMMUT-01',
            'customer_id'    => $this->customer3->id,
            'user_id'        => $this->sellerUser->id,
            'grandtotal'     => 92500.50,
            'netamount'      => 78390.25,
            'taxamount'      => 14110.25,
            'payment_status' => 'Pending',
        ]);

        $originalUpdatedAt = $order->updated_at;

        $attr = $this->attributionService->createAttribution([
            'customer_id'    => $this->customer3->id,
            'field_agent_id' => $this->agentA->id,
        ], $this->adminUser);

        $this->attributionService->reassignAttribution($this->customer3, $this->agentB, $this->adminUser, [
            'reason' => 'Reassignment before eligible order.',
        ]);

        $order->refresh();
        $this->assertEquals('92500.50', $order->grandtotal);
        $this->assertEquals('Pending', $order->payment_status);
        $this->assertEquals($this->sellerUser->id, $order->user_id);
        $this->assertEquals($originalUpdatedAt->timestamp, $order->updated_at->timestamp);
        $this->assertNotNull($attr->id);
    }

    /**
     * Scenario 15: API retries do not create duplicate attribution records (or notifications).
     */
    public function test_api_retries_with_idempotency_key_do_not_create_duplicates(): void
    {
        $this->actingAs($this->adminUser);
        $idemKey = 'IDEM-ATTR-2026-XYZ-101';

        $payload = [
            'customer_id'        => $this->customer1->id,
            'field_agent_id'     => $this->agentA->id,
            'attribution_source' => 'onboarding',
            'idempotency_key'    => $idemKey,
        ];

        $res1 = $this->postJson('/api/v2/admin/attributions', $payload);
        $res1->assertStatus(201);

        $res2 = $this->postJson('/api/v2/admin/attributions', $payload);
        $res2->assertStatus(201);

        $this->assertEquals($res1->json('data.id'), $res2->json('data.id'));
        $this->assertEquals(1, BuyerAgentAttribution::where('idempotency_key', $idemKey)->count());

        // Email sent once, deduplicated cleanly on retry
        Mail::assertSent(AgentAttributionMail::class, 1);
    }

    /**
     * Scenario 16: Sensitive payout and KYC information is restricted.
     */
    public function test_sensitive_payout_and_kyc_information_is_restricted(): void
    {
        // 1. Payout details are encrypted in the raw database column (not plaintext)
        $rawPayout = DB::table('field_agents')->where('id', $this->agentA->id)->value('payout_details');
        $this->assertStringNotContainsString('50100998877665', (string)$rawPayout);
        $this->assertStringNotContainsString('rajeshsharma@okhdfc', (string)$rawPayout);

        // 2. Agent viewing own profile sees masked payout summary, NOT raw account number / KYC ref / admin notes
        // Verify Phase 6.1 disabled-tracking compatibility mode renders Unavailable placeholder cleanly
        Config::set('marketplace_agents.eligible_order_tracking_implemented', false);

        $this->actingAs($this->agentUserA);
        $selfRes = $this->getJson('/api/v2/agent/profile');
        $selfRes->assertStatus(200);
        $json = $selfRes->getContent();
        $this->assertStringNotContainsString('50100998877665', $json);
        $this->assertStringNotContainsString('KYC-DOC-REF-1001', $json);
        $this->assertStringNotContainsString('High priority Jaipur cluster agent', $json);

        // 3. Non-admins cannot view Admin agent detail endpoint
        $this->actingAs($this->agentUserB);
        $this->getJson("/api/v2/admin/agents/{$this->agentA->id}")->assertStatus(403);

        $this->actingAs($this->sellerUser);
        $this->getJson("/api/v2/admin/agents/{$this->agentA->id}")->assertStatus(403);
    }

    /**
     * Scenario 17: Existing buyer onboarding and order workflows continue working without leaking internal agent data.
     */
    public function test_existing_buyer_onboarding_and_order_workflows_continue_working(): void
    {
        // 1. Existing mobile registration API works untouched
        $regRes = $this->postJson('/api/register', [
            'name'     => 'New Bharat Retailers',
            'email'    => 'bharat.retail@dealer.in',
            'mobile'   => '9829099887',
            'password' => 'BuyerPass123',
            'regby'    => 'app',
        ]);

        $regRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('code', 100);

        $regContent = $regRes->getContent();
        $this->assertStringNotContainsString('field_agent', $regContent);

        // 2. Existing customer login API continues cleanly without agent exposure
        $loginRes = $this->postJson('/api/login', [
            'email'    => 'bharat.retail@dealer.in',
            'password' => 'BuyerPass123',
        ]);

        $loginRes->assertStatus(200)
            ->assertJsonPath('status', true);
        $this->assertArrayNotHasKey('field_agent', $loginRes->json('data'));
    }

    /**
     * Scenario 18: Admin & Agent web consoles render cleanly and dispatch queued notifications.
     */
    public function test_admin_and_agent_web_screens_and_queue_notifications(): void
    {
        Queue::fake();

        $attr = $this->attributionService->createAttribution([
            'customer_id'        => $this->customer1->id,
            'field_agent_id'     => $this->agentA->id,
            'evidence_reference' => 'WEB-UI-TEST-01',
        ], $this->adminUser);

        Queue::assertPushed(SendAgentAttributionNotificationJob::class);

        // Admin Screens
        $this->actingAs($this->adminUser);
        $this->get('/admin/agents')->assertStatus(200)->assertSee('FSA-2026-0001');
        $this->get('/admin/agents/create')->assertStatus(200)->assertSee('Field Sales Agent Master Profile');
        $this->get("/admin/agents/{$this->agentA->id}")->assertStatus(200)->assertSee('FSA-2026-0001');
        $this->get("/admin/agents/{$this->agentA->id}/edit")->assertStatus(200)->assertSee('Edit Field Sales Agent');
        $this->get('/admin/attributions')->assertStatus(200)->assertSee($attr->attribution_ref);
        $this->get("/admin/attributions/{$attr->id}")->assertStatus(200)->assertSee('Immutable Attribution History');

        // Agent Screens
        // Verify Phase 6.1 disabled-tracking compatibility mode renders Unavailable placeholder cleanly
        Config::set('marketplace_agents.eligible_order_tracking_implemented', false);

        $this->actingAs($this->agentUserA);
        $this->get('/agent/dashboard')->assertStatus(200)
            ->assertSee('FSA-2026-0001')
            ->assertSee('Shree Ganesh Hardware')
            ->assertDontSee('INTERNAL ONLY: High priority Jaipur cluster agent');

        $this->get("/agent/buyers/{$this->customer1->id}")->assertStatus(200)
            ->assertSee('Shree Ganesh Hardware')
            ->assertSee('Unavailable (Pending Phase 6.2 Eligible-Order Rules)');
    }
}

