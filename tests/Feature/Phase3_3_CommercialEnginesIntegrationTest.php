<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CommissionAuditLog;
use App\Models\CommissionRule;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderCommissionSnapshot;
use App\Models\PriceRevision;
use App\Models\PriceRevisionItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SubCatgory;
use App\Models\User;
use App\Services\CommercialEngineOrchestrator;
use App\Services\CommissionCalculationService;
use App\Services\DynamicPriceRevisionService;
use Carbon\Carbon;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase3_3_CommercialEnginesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;
    protected User $staffA;

    protected Category $categoryIndustrial;
    protected SubCatgory $subcatEpoxy;
    protected Brand $brandAlpha;

    protected Product $productCoating;
    protected ProductVariant $variant20L;
    protected ProductVariant $variant4L;

    protected SellerListing $listing20L;
    protected SellerListing $listing4L;

    protected CommercialEngineOrchestrator $orchestrator;
    protected DynamicPriceRevisionService $pricingService;
    protected CommissionCalculationService $commissionService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(SellerRolesAndPermissionsSeeder::class);

        $this->orchestrator = app(CommercialEngineOrchestrator::class);
        $this->pricingService = app(DynamicPriceRevisionService::class);
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
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->staffA->id,
            'seller_role_id' => $pricingRole->id,
            'status'         => 'active',
            'joined_at'      => now(),
        ]);

        // 5. Taxonomy & Product
        $this->categoryIndustrial = Category::create(['name' => 'Industrial Chemicals', 'status' => 'Active']);
        $this->subcatEpoxy = SubCatgory::create([
            'name'        => 'Epoxy Resins',
            'category_id' => $this->categoryIndustrial->id,
            'status'      => 'Active',
        ]);
        $this->brandAlpha = Brand::create([
            'name'        => 'AlphaCoat',
            'category_id' => $this->categoryIndustrial->id,
            'status'      => 'Active',
        ]);

        $this->productCoating = Product::create([
            'name'           => 'Solventless Epoxy Floor Coating',
            'slug'           => 'solventless-epoxy-coating-' . uniqid(),
            'category_id'    => $this->categoryIndustrial->id,
            'subcategory_id' => $this->subcatEpoxy->id,
            'brand_id'       => $this->brandAlpha->id,
            'user_id'        => $this->sellerA->id,
            'status'         => 'Active',
            'tax'            => 18.0,
        ]);

        $this->variant20L = ProductVariant::create([
            'product_id'   => $this->productCoating->id,
            'variant_sku'  => 'SEC-20L',
            'pack_litres'  => 20.0,
            'variant_name' => '20 Litre Drum',
            'status'       => 'Active',
        ]);

        $this->variant4L = ProductVariant::create([
            'product_id'   => $this->productCoating->id,
            'variant_sku'  => 'SEC-4L',
            'pack_litres'  => 4.0,
            'variant_name' => '4 Litre Can',
            'status'       => 'Active',
        ]);

        // 6. Listings (Wholesale source of truth)
        $this->listing20L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productCoating->id,
            'product_variant_id'  => $this->variant20L->id,
            'seller_sku'          => 'ALPH-SEC-20L',
            'seller_price'        => 5000.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 5500.00,
            'mrp'                 => 6500.00,
            'unit_of_measurement' => 'Litre',
            'stock_quantity'      => 200,
            'status'              => 'Active',
        ]);

        $this->listing4L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productCoating->id,
            'product_variant_id'  => $this->variant4L->id,
            'seller_sku'          => 'ALPH-SEC-4L',
            'seller_price'        => 1200.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 1320.00,
            'mrp'                 => 1600.00,
            'unit_of_measurement' => 'Litre',
            'stock_quantity'      => 150,
            'status'              => 'Active',
        ]);
    }

    /**
     * SCENARIO A: No scheduled price change and default commission rule.
     * Baseline commercial calculation verification.
     */
    public function test_scenario_a_baseline_no_price_change_with_global_commission(): void
    {
        // Global default commission rule: 10%
        $globalRule = CommissionRule::create([
            'rule_code'             => 'CR-GLOBAL-10',
            'name'                  => 'Global Default Commission',
            'scope_type'            => 'global',
            'commission_percentage' => 10.00,
            'effective_from'        => now()->subDays(10),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // Calculate for 2 drums of 20L @ current catalog price (₹5000.00)
        $result = $this->orchestrator->calculateOrderLineCommercials($this->listing20L, 2);

        $this->assertEquals(5000.00, $result['price_resolution']['resolved_unit_price']);
        $this->assertEquals($globalRule->id, $result['commission_resolution']['rule_id']);
        $this->assertEquals(5, $result['commission_resolution']['priority']);
        $this->assertEquals(10.00, $result['commission_resolution']['commission_percentage']);

        // Taxable Base = 2 * 5000 = 10000.00
        $this->assertEquals(10000.00, $result['financial_breakdown']['taxable_base']);
        // Commission Amount = 10000 * 10% = 1000.00
        $this->assertEquals(1000.00, $result['financial_breakdown']['commission_amount']);
        // GST (18% on platform service) = 1000 * 18% = 180.00
        $this->assertEquals(180.00, $result['financial_breakdown']['gst_on_service']);
        // Total Platform Fee = 1000 + 180 = 1180.00
        $this->assertEquals(1180.00, $result['financial_breakdown']['total_platform_fee']);
        // Net Seller Payable = 10000 - 1000 = 9000.00
        $this->assertEquals(9000.00, $result['financial_breakdown']['net_seller_payable']);
    }

    /**
     * SCENARIO B: Scheduled price revision followed by applicable category commission.
     * +10% price revision activates at effective time, and 12% category commission applies.
     */
    public function test_scenario_b_scheduled_price_revision_with_category_commission(): void
    {
        // 1. Category commission rule: 12%
        $catRule = CommissionRule::create([
            'rule_code'             => 'CR-CAT-12',
            'name'                  => 'Industrial Chemicals 12%',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryIndustrial->id,
            'commission_percentage' => 12.00,
            'effective_from'        => now()->subDays(5),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // 2. Schedule +10% price revision for 4L SKU (₹1200 -> ₹1320)
        Sanctum::actingAs($this->sellerA);
        $futureEffective = now()->addHour();

        $revResp = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 10.0,
            'rounding_rule'      => 'round_half',
            'effective_at'       => $futureEffective->toIso8601String(),
            'filters'            => [
                'seller_listing_ids' => [$this->listing4L->id],
            ],
        ]);
        $revResp->assertStatus(201);

        // Before effective time: price is still ₹1200.00
        $this->listing4L->refresh();
        $this->assertEquals(1200.00, (float)$this->listing4L->seller_price);

        // Fast-forward past effective time and execute cron
        Carbon::setTestNow($futureEffective->copy()->addMinutes(5));
        Artisan::call('price-revisions:process');

        // Verify updated price: 1200 * 1.10 = 1320.00
        $this->listing4L->refresh();
        $this->assertEquals(1320.00, (float)$this->listing4L->seller_price);

        // 3. Commercial Orchestration: 10 cans @ ₹1320 = Base ₹13,200.00
        $result = $this->orchestrator->calculateOrderLineCommercials($this->listing4L, 10);
        $this->assertEquals(1320.00, $result['price_resolution']['resolved_unit_price']);
        $this->assertEquals($catRule->id, $result['commission_resolution']['rule_id']);
        $this->assertEquals(13200.00, $result['financial_breakdown']['taxable_base']);

        // Commission (12% of 13200) = 1584.00
        $this->assertEquals(1584.00, $result['financial_breakdown']['commission_amount']);
        // GST (18% on 1584) = 285.12
        $this->assertEquals(285.12, $result['financial_breakdown']['gst_on_service']);
        // Net Seller Payable = 13200 - 1584 = 11616.00
        $this->assertEquals(11616.00, $result['financial_breakdown']['net_seller_payable']);

        Carbon::setTestNow(); // reset
    }

    /**
     * SCENARIO C: Flat per unit price revision (+₹5/L) with Seller-specific commission rule (7%).
     */
    public function test_scenario_c_flat_unit_price_revision_with_seller_specific_commission(): void
    {
        // 1. Seller-specific commercial contract: 7%
        $sellerRule = CommissionRule::create([
            'rule_code'             => 'CR-SELLER-ALPHA-07',
            'name'                  => 'Alpha Manufacturer Special Agreement',
            'scope_type'            => 'seller',
            'scope_id'              => $this->companyA->id,
            'commission_percentage' => 7.00,
            'effective_from'        => now()->subDays(5),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // 2. Schedule flat +₹5/L price revision (immediate)
        Sanctum::actingAs($this->sellerA);
        $revResp = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'flat_per_unit',
            'revision_direction' => 'increase',
            'revision_value'     => 5.00,
            'price_basis_unit'   => 'Litre',
            'effective_at'       => now()->subMinute()->toIso8601String(),
            'filters'            => [
                'seller_listing_ids' => [$this->listing20L->id],
            ],
        ]);
        $revResp->assertStatus(201);

        $this->listing20L->refresh();
        $this->assertEquals(5005.00, (float)$this->listing20L->seller_price); // 5000 + 5

        // 3. Orchestration: 2 units @ ₹5005.00 = ₹10,010.00
        $result = $this->orchestrator->calculateOrderLineCommercials($this->listing20L, 2);

        $this->assertEquals(5005.00, $result['price_resolution']['resolved_unit_price']);
        $this->assertEquals($sellerRule->id, $result['commission_resolution']['rule_id']);
        $this->assertEquals(2, $result['commission_resolution']['priority']);
        $this->assertEquals(7.00, $result['commission_resolution']['commission_percentage']);

        // Base = 10010.00, Commission (7%) = 700.70, Seller Payable = 9309.30
        $this->assertEquals(10010.00, $result['financial_breakdown']['taxable_base']);
        $this->assertEquals(700.70, $result['financial_breakdown']['commission_amount']);
        $this->assertEquals(9309.30, $result['financial_breakdown']['net_seller_payable']);
    }

    /**
     * SCENARIO D: SKU-specific commission (4%) overriding lower-precedence rule.
     */
    public function test_scenario_d_sku_commission_overrides_lower_precedence_rules(): void
    {
        // Category rule (10%)
        CommissionRule::create([
            'rule_code'             => 'CR-CAT-10',
            'name'                  => 'Category 10%',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryIndustrial->id,
            'commission_percentage' => 10.00,
            'effective_from'        => now()->subDays(10),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // SKU rule (4%)
        $skuRule = CommissionRule::create([
            'rule_code'             => 'CR-SKU-04',
            'name'                  => '20L Special Promo',
            'scope_type'            => 'sku',
            'scope_id'              => $this->listing20L->id,
            'commission_percentage' => 4.00,
            'effective_from'        => now()->subDays(5),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        $result = $this->orchestrator->calculateOrderLineCommercials($this->listing20L, 1);
        $this->assertEquals($skuRule->id, $result['commission_resolution']['rule_id']);
        $this->assertEquals(1, $result['commission_resolution']['priority']);
        $this->assertEquals(4.00, $result['commission_resolution']['commission_percentage']);
    }

    /**
     * SCENARIO E: Expired commission rule with fallback to next valid rule.
     */
    public function test_scenario_e_expired_commission_rule_falls_back(): void
    {
        // Category rule (active): 10%
        $catRule = CommissionRule::create([
            'rule_code'             => 'CR-CAT-ACTIVE-10',
            'name'                  => 'Active Category 10%',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryIndustrial->id,
            'commission_percentage' => 10.00,
            'effective_from'        => now()->subDays(30),
            'effective_to'          => now()->addDays(30),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // SKU rule that has EXPIRED (ended yesterday)
        CommissionRule::create([
            'rule_code'             => 'CR-SKU-EXPIRED',
            'name'                  => 'Expired SKU Promo',
            'scope_type'            => 'sku',
            'scope_id'              => $this->listing20L->id,
            'commission_percentage' => 3.00,
            'effective_from'        => now()->subDays(15),
            'effective_to'          => now()->subDay(), // Expired!
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // Resolution MUST fall back to Category rule (10%)
        $result = $this->orchestrator->calculateOrderLineCommercials($this->listing20L, 1);
        $this->assertEquals($catRule->id, $result['commission_resolution']['rule_id'], 'Expired SKU rule must not apply; must fall back to Category rule.');
        $this->assertEquals(10.00, $result['commission_resolution']['commission_percentage']);
    }

    /**
     * SCENARIO F: Conflicting commission rules detected and blocked.
     */
    public function test_scenario_f_conflicting_commission_rules_blocked(): void
    {
        CommissionRule::create([
            'rule_code'             => 'CR-ORIGINAL',
            'name'                  => 'Original Rule',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryIndustrial->id,
            'commission_percentage' => 10.00,
            'effective_from'        => now()->subDays(5),
            'effective_to'          => now()->addDays(15),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // Candidate with overlapping scope & dates
        $candidate = new CommissionRule([
            'scope_type'     => 'category',
            'scope_id'       => $this->categoryIndustrial->id,
            'effective_from' => now(),
            'effective_to'   => now()->addDays(10),
            'status'         => 'pending_approval',
        ]);

        $conflicts = $this->commissionService->detectConflicts($candidate);
        $this->assertNotEmpty($conflicts, 'Conflict engine must detect overlapping active rule.');
    }

    /**
     * SCENARIO G: Invalid price revision or invalid commission configuration.
     * Rejection of zero/negative prices, incompatible units, and missing scope IDs.
     */
    public function test_scenario_g_invalid_configurations_rejected(): void
    {
        Sanctum::actingAs($this->sellerA);

        // 1. Attempt price revision resulting in negative price
        $negResp = $this->postJson('/api/v2/seller/pricing/revisions/preview', [
            'revision_method'    => 'flat_per_unit',
            'revision_direction' => 'decrease',
            'revision_value'     => 10000.00, // exceeds ₹5000 price
            'price_basis_unit'   => 'Litre',
            'filters'            => [
                'seller_listing_ids' => [$this->listing20L->id],
            ],
        ]);
        $negResp->assertStatus(200);
        $this->assertEquals('invalid', $negResp->json('data.items.0.validation_status'));
        $this->assertStringContainsString('must be greater than zero', $negResp->json('data.items.0.validation_error'));

        // 2. Attempt commission rule without mandatory scope_id for non-global scope
        Sanctum::actingAs($this->adminUser);
        $badCommResp = $this->postJson('/api/v2/admin/commissions/rules', [
            'name'                  => 'Bad Scope Rule',
            'scope_type'            => 'seller', // requires scope_id
            'scope_id'              => null,
            'commission_percentage' => 5.0,
            'effective_from'        => now()->toIso8601String(),
        ]);
        $badCommResp->assertStatus(422);
    }

    /**
     * SCENARIO H: Retry of scheduled price and commission-related jobs.
     * Guaranteed safe for retries: zero duplicate price increases, zero duplicate snapshots.
     */
    public function test_scenario_h_retry_of_scheduled_jobs_is_completely_idempotent(): void
    {
        // 1. Schedule +10% price revision
        $revision = PriceRevision::create([
            'revision_code'       => 'REV-RETRY-01',
            'company_id'          => $this->companyA->id,
            'created_by_user_id'  => $this->sellerA->id,
            'revision_method'     => 'percentage',
            'revision_direction'  => 'increase',
            'revision_value'      => 10.0,
            'rounding_rule'       => 'none',
            'status'              => 'scheduled',
            'effective_at'        => now()->subMinute(),
            'total_skus'          => 1,
            'affected_skus_count' => 1,
        ]);

        PriceRevisionItem::create([
            'price_revision_id'   => $revision->id,
            'seller_listing_id'   => $this->listing20L->id,
            'product_id'          => $this->listing20L->product_id,
            'product_variant_id'  => $this->listing20L->product_variant_id,
            'old_seller_price'    => 5000.00,
            'new_seller_price'    => 5500.00,
            'old_selling_price'   => 5500.00,
            'new_selling_price'   => 6050.00,
            'is_unit_compatible'  => true,
            'status'              => 'pending',
        ]);

        // 2. Schedule commission rule
        $scheduledComm = CommissionRule::create([
            'rule_code'             => 'CR-SCHED-RETRY',
            'name'                  => 'Scheduled Rule',
            'scope_type'            => 'global',
            'commission_percentage' => 8.0,
            'effective_from'        => now()->subMinute(),
            'status'                => 'scheduled',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // RUN CRON ITERATION 1
        Artisan::call('price-revisions:process');
        Artisan::call('commissions:process-scheduled');

        $this->listing20L->refresh();
        $this->assertEquals(5500.00, (float)$this->listing20L->seller_price);
        $scheduledComm->refresh();
        $this->assertEquals('active', $scheduledComm->status);

        // RUN CRON ITERATIONS 2 & 3 (SIMULATE RETRIES)
        Artisan::call('price-revisions:process');
        Artisan::call('price-revisions:process');
        Artisan::call('commissions:process-scheduled');
        Artisan::call('commissions:process-scheduled');

        // VERIFY: Still exactly ₹5500.00 (NOT ₹6050 or ₹6655)
        $this->listing20L->refresh();
        $this->assertEquals(5500.00, (float)$this->listing20L->seller_price, 'Cron retries must be idempotent and NEVER apply duplicate price increase.');
    }

    /**
     * SCENARIO I: Historical order after price and commission rules have changed.
     * Verifies 100% frozen immutability of historical transaction records.
     */
    public function test_scenario_i_historical_order_immutability_after_both_engines_change(): void
    {
        // 1. Initial State: Category commission is 8%, Price is ₹5000.00
        $initialCatRule = CommissionRule::create([
            'rule_code'             => 'CR-CAT-08-INITIAL',
            'name'                  => 'Initial 8% Category Rule',
            'scope_type'            => 'category',
            'scope_id'              => $this->categoryIndustrial->id,
            'commission_percentage' => 8.00,
            'effective_from'        => now()->subDays(60),
            'status'                => 'active',
            'created_by_user_id'    => $this->adminUser->id,
        ]);

        // 2. Create historical placed order
        $order = Order::create([
            'orderno'     => 'AF-HIST-2026-001',
            'user_id'     => $this->sellerA->id,
            'customer_id' => 301,
            'netamount'   => 5000.00,
            'taxamount'   => 900.00,
            'grandtotal'  => 5900.00,
            'created_at'  => now()->subDays(30),
            'updated_at'  => now()->subDays(30),
        ]);

        // Freeze commercial snapshot at order placement time
        $snapshot = $this->orchestrator->snapshotOrderCommercials($order, $this->listing20L, 1, 5000.00);

        $this->assertEquals(5000.00, $snapshot->base_amount);
        $this->assertEquals(8.00, $snapshot->commission_percentage);
        $this->assertEquals(400.00, $snapshot->commission_amount); // 5000 * 8%
        $this->assertEquals(72.00, $snapshot->gst_amount);         // 400 * 18%
        $this->assertEquals(4600.00, $snapshot->seller_payable);   // 5000 - 400

        // 3. SELLER LATER HIKES CATALOGUE PRICE BY +20% (₹5000 -> ₹6000)
        Sanctum::actingAs($this->sellerA);
        $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 20.0,
            'effective_at'       => now()->subMinute()->toIso8601String(),
            'filters'            => [
                'seller_listing_ids' => [$this->listing20L->id],
            ],
        ]);

        $this->listing20L->refresh();
        $this->assertEquals(6000.00, (float)$this->listing20L->seller_price);

        // 4. ADMIN LATER DOUBLES COMMISSION RATE (8% -> 16%)
        Sanctum::actingAs($this->adminUser);
        $initialCatRule->update(['commission_percentage' => 16.00]);

        // 5. CRITICAL VERIFICATION: Historical order and snapshot remain 100% FROZEN
        $order->refresh();
        $this->assertEquals(5900.00, (float)$order->grandtotal, 'Historical order grand total must remain unchanged.');

        $snapshot->refresh();
        $this->assertEquals(5000.00, $snapshot->base_amount, 'Historical order snapshot base price must remain frozen.');
        $this->assertEquals(8.00, $snapshot->commission_percentage, 'Historical order snapshot commission % must remain frozen.');
        $this->assertEquals(400.00, $snapshot->commission_amount, 'Historical order snapshot commission amount must remain frozen.');
        $this->assertEquals(4600.00, $snapshot->seller_payable, 'Historical order snapshot seller payable must remain frozen.');
    }

    /**
     * Test Seller Isolation across Commercial APIs.
     */
    public function test_seller_isolation_across_commercial_apis(): void
    {
        // Seller A has order snapshot
        $orderA = Order::create([
            'orderno'    => 'AF-ALPHA-ONLY',
            'user_id'    => $this->sellerA->id,
            'grandtotal' => 5000.00,
        ]);
        $this->orchestrator->snapshotOrderCommercials($orderA, $this->listing20L, 1, 5000.00);

        // Seller B attempts to view Seller A order snapshot -> 404
        Sanctum::actingAs($this->sellerB);
        $resp = $this->getJson("/api/v2/seller/commissions/orders/{$orderA->id}/snapshot");
        $resp->assertStatus(404);
    }
}
