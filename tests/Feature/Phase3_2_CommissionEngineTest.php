<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CommissionAuditLog;
use App\Models\CommissionRule;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SubCatgory;
use App\Models\User;
use App\Services\CommissionCalculationService;
use Carbon\Carbon;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase3_2_CommissionEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;
    protected User $staffA;

    protected Category $categoryEpoxy;
    protected SubCatgory $subcatPrimer;
    protected Brand $brandA;

    protected Product $productPaint;
    protected ProductVariant $variant20L;
    protected ProductVariant $variant10L;

    protected SellerListing $listing20L;
    protected SellerListing $listing10L;

    protected CommissionCalculationService $commissionService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(SellerRolesAndPermissionsSeeder::class);
        $this->commissionService = app(CommissionCalculationService::class);

        // 1. Admin Actor
        $this->adminUser = User::factory()->create([
            'email' => 'admin@apnifactory.com',
            'name'  => 'Platform Super Admin',
        ]);

        // 2. Seller A (Alpha Coatings Ltd)
        $this->sellerA = User::factory()->create([
            'email' => 'alpha@seller.test',
            'name'  => 'Alpha Owner',
        ]);
        $this->companyA = Company::factory()->create([
            'user_id' => $this->sellerA->id,
            'name'    => 'Alpha Industrial Coatings Ltd',
        ]);

        // 3. Seller B (Beta Solutions)
        $this->sellerB = User::factory()->create([
            'email' => 'beta@seller.test',
            'name'  => 'Beta Owner',
        ]);
        $this->companyB = Company::factory()->create([
            'user_id' => $this->sellerB->id,
            'name'    => 'Beta Solutions Pvt Ltd',
        ]);

        // 4. Staff for Seller A
        $this->staffA = User::factory()->create([
            'email' => 'pricing.staff@seller.test',
            'name'  => 'Pricing Specialist Staff',
        ]);
        $pricingRole = SellerRole::where('name', 'pricing_manager')->first();
        SellerStaff::create([
            'company_id'       => $this->companyA->id,
            'user_id'          => $this->staffA->id,
            'seller_role_id'   => $pricingRole->id,
            'invited_by'       => $this->sellerA->id,
            'status'           => 'active',
            'joined_at'        => now(),
        ]);

        // 5. Taxonomy & Product
        $this->categoryEpoxy = Category::create(['name' => 'Epoxy Coatings', 'status' => 'Active']);
        $this->subcatPrimer = SubCatgory::create([
            'name'        => 'Floor Primers',
            'category_id' => $this->categoryEpoxy->id,
            'status'      => 'Active',
        ]);
        $this->brandA = Brand::create([
            'name'        => 'AlphaCoat',
            'category_id' => $this->categoryEpoxy->id,
            'status'      => 'Active',
        ]);

        $this->productPaint = Product::create([
            'name'           => 'High Build Epoxy Floor Primer',
            'slug'           => 'high-build-epoxy-primer-' . uniqid(),
            'category_id'    => $this->categoryEpoxy->id,
            'subcategory_id' => $this->subcatPrimer->id,
            'brand_id'       => $this->brandA->id,
            'user_id'        => $this->sellerA->id,
            'status'         => 'Active',
            'tax'            => 18.0,
        ]);

        $this->variant20L = ProductVariant::create([
            'product_id'   => $this->productPaint->id,
            'variant_sku'  => 'HBEP-20L',
            'pack_litres'  => 20.0,
            'variant_name' => '20 Litre Drum',
            'status'       => 'Active',
        ]);

        $this->variant10L = ProductVariant::create([
            'product_id'   => $this->productPaint->id,
            'variant_sku'  => 'HBEP-10L',
            'pack_litres'  => 10.0,
            'variant_name' => '10 Litre Pail',
            'status'       => 'Active',
        ]);

        // 6. Listings
        $this->listing20L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productPaint->id,
            'product_variant_id'  => $this->variant20L->id,
            'seller_sku'          => 'ALPH-HBEP-20L',
            'seller_price'        => 5000.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 5500.00,
            'mrp'                 => 6000.00,
            'unit_of_measurement' => 'Litre',
            'status'              => 'Active',
        ]);

        $this->listing10L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productPaint->id,
            'product_variant_id'  => $this->variant10L->id,
            'seller_sku'          => 'ALPH-HBEP-10L',
            'seller_price'        => 2800.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 3080.00,
            'mrp'                 => 3500.00,
            'unit_of_measurement' => 'Litre',
            'status'              => 'Active',
        ]);
    }

    /**
     * MANDATORY BRD ACCEPTANCE TEST 1 — Rule Precedence:
     * Create a SKU-specific rule (e.g. 6%) and a Category rule (e.g. 12%).
     * Verify that the SKU-specific rule overrides the Category rule according to approved precedence.
     */
    public function test_mandatory_scenario_1_rule_precedence_sku_overrides_category(): void
    {
        // 1. Create Category rule (12%)
        $catRule = CommissionRule::create([
            'rule_code'             => 'CR-CAT-EPOXY-12',
            'name'                  => 'Epoxy Category Standard Commission',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryEpoxy->id,
            'commission_percentage' => 12.00,
            'effective_from'        => now()->subDays(10),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // 2. Create SKU-specific rule for listing20L (6%)
        $skuRule = CommissionRule::create([
            'rule_code'             => 'CR-SKU-HBEP20-06',
            'name'                  => 'Promotional 20L Drum Negotiated Rate',
            'scope_type'            => 'sku',
            'scope_id'              => $this->listing20L->id,
            'commission_percentage' => 6.00,
            'effective_from'        => now()->subDays(5),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // 3. Resolve for listing20L: SKU rule (6%) MUST win over Category rule (12%)
        $resolved20L = $this->commissionService->resolveRuleForSku($this->listing20L);
        $this->assertEquals($skuRule->id, $resolved20L['rule_id'], 'SKU-specific agreement must override Category rule.');
        $this->assertEquals(1, $resolved20L['priority']);
        $this->assertEquals(6.00, $resolved20L['commission_percentage']);
        $this->assertStringContainsString('Priority 1', $resolved20L['reason']);

        // 4. Resolve for listing10L (which does NOT have a SKU rule): Category rule (12%) MUST win
        $resolved10L = $this->commissionService->resolveRuleForSku($this->listing10L);
        $this->assertEquals($catRule->id, $resolved10L['rule_id'], 'Category rule must apply when no SKU-specific rule exists.');
        $this->assertEquals(4, $resolved10L['priority']);
        $this->assertEquals(12.00, $resolved10L['commission_percentage']);
    }

    /**
     * MANDATORY BRD ACCEPTANCE TEST 2 — Historical Order Protection:
     * Create an order with a commission snapshot.
     * Change the category commission.
     * Verify that the historical order's commission snapshot remains completely unchanged.
     */
    public function test_mandatory_scenario_2_historical_order_snapshot_immutability(): void
    {
        // 1. Initial Category rule: 10%
        $catRule = CommissionRule::create([
            'rule_code'             => 'CR-CAT-10',
            'name'                  => 'Category 10% Agreement',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryEpoxy->id,
            'commission_percentage' => 10.00,
            'effective_from'        => now()->subDays(30),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // 2. Historical order placed
        $orderNo = 'AF' . date('Ymd') . '-7777';
        $order = Order::create([
            'orderno'     => $orderNo,
            'customer_id' => 201,
            'user_id'     => $this->sellerA->id,
            'netamount'   => 5000.00,
            'taxamount'   => 900.00,
            'grandtotal'  => 5900.00,
            'created_at'  => now()->subDays(15),
            'updated_at'  => now()->subDays(15),
        ]);

        // Snapshot generated at order time: 10% on ₹5000 = ₹500 commission, 18% GST on fee = ₹90, seller payable = ₹4500
        $snapshot = $this->commissionService->snapshotOrderCommission($order, $this->listing20L, 1, 5000.00);
        $this->assertEquals(5000.00, $snapshot->base_amount);
        $this->assertEquals(10.00, $snapshot->commission_percentage);
        $this->assertEquals(500.00, $snapshot->commission_amount);
        $this->assertEquals(90.00, $snapshot->gst_amount);
        $this->assertEquals(590.00, $snapshot->total_platform_fee);
        $this->assertEquals(4500.00, $snapshot->seller_payable);

        // 3. ADMIN MODIFIES CATEGORY COMMISSION to 18%
        $catRule->update(['commission_percentage' => 18.00]);

        // 4. VERIFY: Historical snapshot remains 100% IMMUTABLE
        $snapshot->refresh();
        $this->assertEquals(10.00, $snapshot->commission_percentage, 'Historical order snapshot commission % must remain frozen.');
        $this->assertEquals(500.00, $snapshot->commission_amount, 'Historical order snapshot commission amount must never change.');
        $this->assertEquals(4500.00, $snapshot->seller_payable, 'Historical order snapshot seller payable must remain intact.');
    }

    /**
     * MANDATORY BRD ACCEPTANCE TEST 3 — Calculation Breakdown:
     * Verify that the calculation output displays the taxable base, applicable rate,
     * commission amount, applicable GST on platform service and resulting seller payable.
     */
    public function test_mandatory_scenario_3_reproducible_calculation_breakdown(): void
    {
        // 8% commission on category
        CommissionRule::create([
            'rule_code'             => 'CR-CAT-08',
            'name'                  => 'Standard 8% Base',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryEpoxy->id,
            'commission_percentage' => 8.00,
            'effective_from'        => now()->subDays(5),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // Quantity: 4 drums @ ₹5000.00 = Taxable Base ₹20,000.00
        $calc = $this->commissionService->calculateLineCommission($this->listing20L, 4, 5000.00);

        // Verification of full breakdown:
        // - Base: 20000.00
        // - Rate: 8.00%
        // - Commission Fee: 20000 * 0.08 = 1600.00
        // - GST (18% on platform service): 1600 * 0.18 = 288.00
        // - Total Platform Fee: 1600 + 288 = 1888.00
        // - Seller Payable: 20000 - 1600 = 18400.00
        $this->assertEquals(20000.00, $calc['base_amount'], 'Taxable base must equal quantity * unit price.');
        $this->assertEquals(8.00, $calc['commission_percentage']);
        $this->assertEquals(1600.00, $calc['commission_amount']);
        $this->assertEquals(18.00, $calc['gst_rate']);
        $this->assertEquals(288.00, $calc['gst_amount']);
        $this->assertEquals(1888.00, $calc['total_platform_fee']);
        $this->assertEquals(18400.00, $calc['seller_payable']);
    }

    /**
     * MANDATORY BRD ACCEPTANCE TEST 4 — Seller Isolation:
     * Verify that an unauthorized seller cannot view another seller's commission agreement or ledger.
     */
    public function test_mandatory_scenario_4_seller_isolation(): void
    {
        // Seller A has a private confidential agreement of 5%
        $ruleA = CommissionRule::create([
            'rule_code'             => 'CR-SELLER-A-CONFIDENTIAL',
            'name'                  => 'Alpha VIP 5% Contract',
            'scope_type'            => 'seller',
            'scope_id'              => $this->companyA->id,
            'commission_percentage' => 5.00,
            'effective_from'        => now()->subDays(5),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // Seller B logs in and fetches agreements
        Sanctum::actingAs($this->sellerB);

        $response = $this->getJson('/api/v2/seller/commissions/agreements');
        $response->assertStatus(200);

        // Seller B's agreements list must NOT contain Seller A's agreement
        $data = $response->json('data');
        $sellerAgreements = collect($data['seller_agreements']);
        $this->assertFalse($sellerAgreements->contains('id', $ruleA->id), 'Seller B must NEVER see Seller A private agreement.');

        // IDOR Test: Seller B attempts to view Seller A order commission snapshot
        $orderA = Order::create([
            'orderno'     => 'AF-ORDER-A',
            'user_id'     => $this->sellerA->id,
            'customer_id' => 101,
            'netamount'   => 5000.00,
            'grandtotal'  => 5000.00,
        ]);
        $this->commissionService->snapshotOrderCommission($orderA, $this->listing20L, 1, 5000.00);

        $idorResp = $this->getJson("/api/v2/seller/commissions/orders/{$orderA->id}/snapshot");
        $idorResp->assertStatus(404); // Returns 404 to prevent resource enumeration
    }

    /**
     * Test 5-Tier Precedence Hierarchy in full sequence:
     * Priority 1 (SKU) > Priority 2 (Seller) > Priority 3 (Subcategory) > Priority 4 (Category) > Priority 5 (Global)
     */
    public function test_full_five_tier_precedence_hierarchy(): void
    {
        // 5. Global Default: 15%
        CommissionRule::create([
            'rule_code'             => 'CR-GLOBAL-15',
            'name'                  => 'Global Default',
            'scope_type'            => 'global',
            'commission_percentage' => 15.00,
            'effective_from'        => now()->subDays(10),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);
        $res = $this->commissionService->resolveRuleForSku($this->listing20L);
        $this->assertEquals(15.00, $res['commission_percentage']);
        $this->assertEquals(5, $res['priority']);

        // 4. Category Rule: 12% overrides Global
        CommissionRule::create([
            'rule_code'             => 'CR-CAT-12',
            'name'                  => 'Category Rule',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryEpoxy->id,
            'commission_percentage' => 12.00,
            'effective_from'        => now()->subDays(10),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);
        $res = $this->commissionService->resolveRuleForSku($this->listing20L);
        $this->assertEquals(12.00, $res['commission_percentage']);
        $this->assertEquals(4, $res['priority']);

        // 3. Subcategory Rule: 10% overrides Category
        CommissionRule::create([
            'rule_code'             => 'CR-SUBCAT-10',
            'name'                  => 'Subcategory Rule',
            'scope_type'            => 'subcategory',
            'scope_id'              => $this->subcatPrimer->id,
            'commission_percentage' => 10.00,
            'effective_from'        => now()->subDays(10),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);
        $res = $this->commissionService->resolveRuleForSku($this->listing20L);
        $this->assertEquals(10.00, $res['commission_percentage']);
        $this->assertEquals(3, $res['priority']);

        // 2. Seller Rule: 8% overrides Subcategory
        CommissionRule::create([
            'rule_code'             => 'CR-SELLER-08',
            'name'                  => 'Seller Agreement',
            'scope_type'            => 'seller',
            'scope_id'              => $this->companyA->id,
            'commission_percentage' => 8.00,
            'effective_from'        => now()->subDays(10),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);
        $res = $this->commissionService->resolveRuleForSku($this->listing20L);
        $this->assertEquals(8.00, $res['commission_percentage']);
        $this->assertEquals(2, $res['priority']);

        // 1. SKU Rule: 5% overrides Seller
        CommissionRule::create([
            'rule_code'             => 'CR-SKU-05',
            'name'                  => 'SKU Rule',
            'scope_type'            => 'sku',
            'scope_id'              => $this->listing20L->id,
            'commission_percentage' => 5.00,
            'effective_from'        => now()->subDays(10),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);
        $res = $this->commissionService->resolveRuleForSku($this->listing20L);
        $this->assertEquals(5.00, $res['commission_percentage']);
        $this->assertEquals(1, $res['priority']);
    }

    /**
     * Test Conflicting Rules at Same Precedence Level:
     * Overlapping active rules for the exact same scope trigger conflict detection.
     */
    public function test_conflict_detection_prevents_ambiguous_activation(): void
    {
        // Rule 1: Category Epoxy active
        $rule1 = CommissionRule::create([
            'rule_code'             => 'CR-CAT-01',
            'name'                  => 'Rule 1',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryEpoxy->id,
            'commission_percentage' => 10.00,
            'effective_from'        => now()->subDays(10),
            'effective_to'          => now()->addDays(30),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // Rule 2: Overlapping candidate rule for Category Epoxy
        $candidate = new CommissionRule([
            'scope_type'     => 'category',
            'scope_id'       => $this->categoryEpoxy->id,
            'effective_from' => now()->subDays(5),
            'effective_to'   => now()->addDays(20),
            'status'         => 'pending_approval',
        ]);

        $conflicts = $this->commissionService->detectConflicts($candidate);
        $this->assertNotEmpty($conflicts, 'Overlapping rule for same scope must be detected as conflict.');
        $this->assertEquals($rule1->id, $conflicts[0]['rule_id']);

        // Approving candidate must be blocked due to active conflict
        $candidate->rule_code = 'CR-CAT-02';
        $candidate->name = 'Conflicting Rule';
        $candidate->commission_percentage = 12.00;
        $candidate->created_by_user_id = $this->adminUser->id;
        $candidate->save();

        $this->expectException(\DomainException::class);
        $this->commissionService->approveRule($candidate, $this->adminUser);
    }

    /**
     * Test Scheduled Rule Activation via Artisan Command.
     */
    public function test_scheduled_rule_activation_via_artisan(): void
    {
        $scheduledRule = CommissionRule::create([
            'rule_code'             => 'CR-SCHED-01',
            'name'                  => 'Future Promo Rate',
            'scope_type'            => 'global',
            'commission_percentage' => 7.50,
            'effective_from'        => now()->addHours(1),
            'status'                => 'scheduled',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // Before effective time: remains scheduled
        Artisan::call('commissions:process-scheduled');
        $scheduledRule->refresh();
        $this->assertEquals('scheduled', $scheduledRule->status);

        // Fast forward past effective time
        Carbon::setTestNow(now()->addHours(2));
        Artisan::call('commissions:process-scheduled');

        $scheduledRule->refresh();
        $this->assertEquals('active', $scheduledRule->status, 'Scheduled rule must activate once effective timestamp is reached.');

        Carbon::setTestNow(); // reset
    }

    /**
     * Test Admin API: Rule CRUD and Approval Workflow.
     */
    public function test_admin_api_rule_crud_and_approval(): void
    {
        Sanctum::actingAs($this->adminUser);

        // 1. Create rule as pending_approval
        $resp = $this->postJson('/api/v2/admin/commissions/rules', [
            'name'                  => 'Floor Coatings Category Agreement',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryEpoxy->id,
            'commission_percentage' => 9.50,
            'calculation_basis'     => 'taxable_product_value',
            'gst_rate'              => 18.00,
            'effective_from'        => now()->toIso8601String(),
            'status'                => 'pending_approval',
            'agreement_ref'         => 'AGR-EPX-2026',
        ]);

        $resp->assertStatus(201);
        $ruleId = $resp->json('data.id');

        // 2. Approve rule
        $approveResp = $this->postJson("/api/v2/admin/commissions/rules/{$ruleId}/approve");
        $approveResp->assertStatus(200);
        $this->assertEquals('active', $approveResp->json('data.status'));

        // 3. Deactivate rule
        $deactResp = $this->postJson("/api/v2/admin/commissions/rules/{$ruleId}/deactivate", [
            'reason' => 'Contract Superseded',
        ]);
        $deactResp->assertStatus(200);
        $this->assertEquals('deactivated', $deactResp->json('data.status'));
    }

    /**
     * Test Non-Admin cannot create or modify rules (403 Forbidden).
     */
    public function test_non_admin_cannot_mutate_commission_rules(): void
    {
        Sanctum::actingAs($this->sellerA);

        $resp = $this->postJson('/api/v2/admin/commissions/rules', [
            'name'                  => 'Unauthorized Rule Hike',
            'scope_type'            => 'global',
            'commission_percentage' => 2.00,
            'effective_from'        => now()->toIso8601String(),
        ]);

        $resp->assertStatus(403);
    }

    /**
     * Test Idempotency on Snapshot Generation (Duplicate Execution Safety).
     */
    public function test_order_snapshot_idempotency_prevents_duplicate_records(): void
    {
        CommissionRule::create([
            'rule_code'             => 'CR-CAT-09',
            'name'                  => 'Cat 9%',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryEpoxy->id,
            'commission_percentage' => 9.00,
            'effective_from'        => now()->subDays(5),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        $order = Order::create([
            'orderno'    => 'AF-IDEMP-01',
            'user_id'    => $this->sellerA->id,
            'netamount'  => 5000.00,
            'grandtotal' => 5000.00,
        ]);

        // Generate snapshot 3 times in a row (e.g. webhook retries)
        $snap1 = $this->commissionService->snapshotOrderCommission($order, $this->listing20L, 1, 5000.00);
        $snap2 = $this->commissionService->snapshotOrderCommission($order, $this->listing20L, 1, 5000.00);
        $snap3 = $this->commissionService->snapshotOrderCommission($order, $this->listing20L, 1, 5000.00);

        $this->assertEquals($snap1->id, $snap2->id);
        $this->assertEquals($snap1->id, $snap3->id);

        $count = OrderCommissionSnapshot::where('order_id', $order->id)->count();
        $this->assertEquals(1, $count, 'Duplicate snapshot requests must return existing record without creating duplicates.');
    }

    /**
     * Test Web Portal Routes Accessibility.
     */
    public function test_web_portal_routes_accessibility(): void
    {
        // Admin views
        $this->actingAs($this->adminUser);
        $adminIndex = $this->get(route('admin.commissions.rules.index'));
        $adminIndex->assertStatus(200);
        $adminIndex->assertSee('Commission & Platform Fee Rules');

        $adminCreate = $this->get(route('admin.commissions.rules.create'));
        $adminCreate->assertStatus(200);
        $adminCreate->assertSee('Scope Type (Precedence Level)');

        // Seller views
        $this->actingAs($this->sellerA);
        $sellerAgr = $this->get(route('seller.commissions.agreements'));
        $sellerAgr->assertStatus(200);
        $sellerAgr->assertSee('My Commission Agreements');
    }
}

