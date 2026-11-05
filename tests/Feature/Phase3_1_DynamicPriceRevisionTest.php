<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PriceRevision;
use App\Models\PriceRevisionItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\SellerPermission;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SubCatgory;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase3_1_DynamicPriceRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;
    protected User $staffA;

    protected Category $categoryPaint;
    protected Category $categoryTools;
    protected Brand $brandA;

    protected Product $productPaint;
    protected Product $productTool;

    protected ProductVariant $variant20L;
    protected ProductVariant $variant10L;
    protected ProductVariant $variant4L;
    protected ProductVariant $variant1L;
    protected ProductVariant $variantBrush;

    protected SellerListing $listing20L;
    protected SellerListing $listing10L;
    protected SellerListing $listing4L;
    protected SellerListing $listing1L;
    protected SellerListing $listingBrush;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(SellerRolesAndPermissionsSeeder::class);

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

        // 3. Seller B (Beta Paints Ltd)
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
            'company_id'      => $this->companyA->id,
            'user_id'         => $this->staffA->id,
            'seller_role_id'  => $pricingRole->id,
            'invited_by'      => $this->sellerA->id,
            'invitation_token'=> null,
            'status'          => 'active',
            'joined_at'       => now(),
        ]);

        // 5. Taxonomy & Products
        $this->categoryPaint = Category::create(['name' => 'Industrial Paints', 'status' => 'Active']);
        $this->categoryTools = Category::create(['name' => 'Painting Tools', 'status' => 'Active']);
        $this->brandA = Brand::create(['name' => 'AlphaCoat', 'category_id' => $this->categoryPaint->id, 'status' => 'Active']);

        $this->productPaint = Product::create([
            'name'         => 'Epoxy Floor Primer Plus',
            'slug'         => 'epoxy-floor-primer-plus-' . uniqid(),
            'category_id'  => $this->categoryPaint->id,
            'brand_id'     => $this->brandA->id,
            'user_id'      => $this->sellerA->id,
            'status'       => 'Active',
            'approval_status' => 'approved',
            'tax'          => 18.0,
        ]);

        $this->productTool = Product::create([
            'name'         => 'Heavy Duty Roller Brush',
            'slug'         => 'heavy-duty-roller-brush-' . uniqid(),
            'category_id'  => $this->categoryTools->id,
            'brand_id'     => $this->brandA->id,
            'user_id'      => $this->sellerA->id,
            'status'       => 'Active',
            'approval_status' => 'approved',
            'tax'          => 18.0,
        ]);

        // 6. Variants
        $this->variant20L = ProductVariant::create([
            'product_id'   => $this->productPaint->id,
            'variant_sku'  => 'EPP-20L',
            'pack_litres'  => 20.0,
            'variant_name' => '20 Litre Drum',
            'status'       => 'Active',
        ]);

        $this->variant10L = ProductVariant::create([
            'product_id'   => $this->productPaint->id,
            'variant_sku'  => 'EPP-10L',
            'pack_litres'  => 10.0,
            'variant_name' => '10 Litre Pail',
            'status'       => 'Active',
        ]);

        $this->variant4L = ProductVariant::create([
            'product_id'   => $this->productPaint->id,
            'variant_sku'  => 'EPP-4L',
            'pack_litres'  => 4.0,
            'variant_name' => '4 Litre Can',
            'status'       => 'Active',
        ]);

        $this->variant1L = ProductVariant::create([
            'product_id'   => $this->productPaint->id,
            'variant_sku'  => 'EPP-1L',
            'pack_litres'  => 1.0,
            'variant_name' => '1 Litre Tin',
            'status'       => 'Active',
        ]);

        $this->variantBrush = ProductVariant::create([
            'product_id'   => $this->productTool->id,
            'variant_sku'  => 'HDRB-01',
            'pack_litres'  => 1.0,
            'variant_name' => 'Single Roller Brush',
            'status'       => 'Active',
        ]);

        // 7. Seller Listings (Price source of truth)
        $this->listing20L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productPaint->id,
            'product_variant_id'  => $this->variant20L->id,
            'seller_sku'          => 'ALPH-EPP-20L',
            'seller_price'        => 4000.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 4400.00,
            'mrp'                 => 5000.00,
            'unit_of_measurement' => 'Litre',
            'stock_quantity'      => 100,
            'status'              => 'Active',
        ]);

        $this->listing10L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productPaint->id,
            'product_variant_id'  => $this->variant10L->id,
            'seller_sku'          => 'ALPH-EPP-10L',
            'seller_price'        => 2200.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 2420.00,
            'mrp'                 => 2800.00,
            'unit_of_measurement' => 'Litre',
            'stock_quantity'      => 100,
            'status'              => 'Active',
        ]);

        $this->listing4L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productPaint->id,
            'product_variant_id'  => $this->variant4L->id,
            'seller_sku'          => 'ALPH-EPP-4L',
            'seller_price'        => 900.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 990.00,
            'mrp'                 => 1200.00,
            'unit_of_measurement' => 'Litre',
            'stock_quantity'      => 100,
            'status'              => 'Active',
        ]);

        $this->listing1L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productPaint->id,
            'product_variant_id'  => $this->variant1L->id,
            'seller_sku'          => 'ALPH-EPP-1L',
            'seller_price'        => 250.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 275.00,
            'mrp'                 => 350.00,
            'unit_of_measurement' => 'Litre',
            'stock_quantity'      => 100,
            'status'              => 'Active',
        ]);

        $this->listingBrush = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productTool->id,
            'product_variant_id'  => $this->variantBrush->id,
            'seller_sku'          => 'ALPH-TOOL-BRUSH',
            'seller_price'        => 150.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 165.00,
            'mrp'                 => 200.00,
            'unit_of_measurement' => 'Piece',
            'stock_quantity'      => 50,
            'status'              => 'Active',
        ]);
    }

    /**
     * MANDATORY ACCEPTANCE SCENARIO 1:
     * Schedule a future +4% price increase:
     * - Current price remains unchanged until effective timestamp.
     * - Changes exactly once at activation time.
     */
    public function test_mandatory_scenario_1_schedule_future_percentage_increase(): void
    {
        Sanctum::actingAs($this->sellerA);

        $futureEffectiveAt = Carbon::now()->addHours(2);

        // 1. Schedule +4% revision for listing20L (Current: ₹4000)
        $response = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 4.0, // +4%
            'rounding_rule'      => 'round_half',
            'effective_at'       => $futureEffectiveAt->toIso8601String(),
            'reason'             => 'Q4 Price Escalation',
            'filters'            => [
                'seller_listing_ids' => [$this->listing20L->id],
            ],
        ]);

        $response->assertStatus(201);
        $revisionId = $response->json('data.id');
        $this->assertEquals('scheduled', $response->json('data.status'));

        // 2. VERIFY: Catalogue price remains strictly UNCHANGED before effective time
        $this->listing20L->refresh();
        $this->assertEquals(4000.00, (float)$this->listing20L->seller_price, 'Catalogue price must remain unchanged before effective time.');
        $this->assertEquals(4400.00, (float)$this->listing20L->selling_price);

        // 3. Fast-forward time past effective_at and run scheduled processor
        Carbon::setTestNow($futureEffectiveAt->copy()->addMinutes(5));

        Artisan::call('price-revisions:process');

        // 4. VERIFY: Catalogue price is now updated exactly to ₹4160.00 (4000 * 1.04)
        $this->listing20L->refresh();
        $this->assertEquals(4160.00, (float)$this->listing20L->seller_price, 'Catalogue seller price must be updated at effective timestamp.');
        // Selling price = 4160 * (1 + 10%) = 4576.00
        $this->assertEquals(4576.00, (float)$this->listing20L->selling_price, 'Customer-facing selling price must be recomputed with commission rate.');

        $revision = PriceRevision::find($revisionId);
        $this->assertEquals('active', $revision->status);
        $this->assertNotNull($revision->activated_at);

        Carbon::setTestNow(); // reset
    }

    /**
     * MANDATORY ACCEPTANCE SCENARIO 2:
     * Apply +₹4/L flat per unit revision only to SKUs whose price basis is ₹/L.
     * Incompatible units (e.g. 'Piece') must be flagged and NOT converted implicitly.
     */
    public function test_mandatory_scenario_2_flat_per_unit_with_strict_unit_compatibility(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Preview +₹4/L across paint (Litre) and brush (Piece)
        $previewResp = $this->postJson('/api/v2/seller/pricing/revisions/preview', [
            'revision_method'    => 'flat_per_unit',
            'revision_direction' => 'increase',
            'revision_value'     => 4.00, // +₹4.00
            'price_basis_unit'   => 'Litre',
            'rounding_rule'      => 'round_half',
            'filters'            => [
                'seller_listing_ids' => [$this->listing1L->id, $this->listingBrush->id],
            ],
        ]);

        $previewResp->assertStatus(200);
        $data = $previewResp->json('data');

        $this->assertEquals(2, $data['summary']['total_evaluated']);
        $this->assertEquals(1, $data['summary']['total_eligible']);
        $this->assertEquals(1, $data['summary']['total_incompatible']);

        // Check the 1L paint SKU: compatible
        $item1L = collect($data['items'])->firstWhere('seller_listing_id', $this->listing1L->id);
        $this->assertTrue($item1L['is_unit_compatible']);
        $this->assertEquals(254.00, (float)$item1L['new_seller_price']); // 250 + 4

        // Check the brush SKU: INCOMPATIBLE (Piece vs Litre)
        $itemBrush = collect($data['items'])->firstWhere('seller_listing_id', $this->listingBrush->id);
        $this->assertFalse($itemBrush['is_unit_compatible'], 'Piece unit must be flagged as incompatible with Litre basis.');
        $this->assertStringContainsString('Unit mismatch', $itemBrush['validation_error']);

        // Now schedule and activate
        $storeResp = $this->postJson('/api/v2/seller/pricing/revisions', [
            'preview_token' => $data['preview_token'],
            'effective_at'  => now()->subMinute()->toIso8601String(), // immediate
        ]);
        $storeResp->assertStatus(201);

        // VERIFY: Paint SKU is updated (+₹4), brush SKU is completely UNCHANGED
        $this->listing1L->refresh();
        $this->assertEquals(254.00, (float)$this->listing1L->seller_price);

        $this->listingBrush->refresh();
        $this->assertEquals(150.00, (float)$this->listingBrush->seller_price, 'Incompatible SKU price must NEVER be silently updated.');
    }

    /**
     * MANDATORY ACCEPTANCE SCENARIO 3:
     * Set explicit pack-size prices (20L, 10L, 4L, 1L).
     * Each SKU receives the exact approved price subject to approved currency precision.
     */
    public function test_mandatory_scenario_3_explicit_pack_size_prices(): void
    {
        Sanctum::actingAs($this->sellerA);

        $explicitPrices = [
            $this->listing20L->id => 4500.00,
            $this->listing10L->id => 2350.00,
            $this->listing4L->id  => 980.00,
            $this->listing1L->id  => 260.00,
        ];

        $response = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method' => 'explicit_list',
            'explicit_prices' => $explicitPrices,
            'effective_at'    => now()->subMinute()->toIso8601String(),
            'reason'          => 'Exact Pack Price Alignment',
            'filters'         => [
                'seller_listing_ids' => array_keys($explicitPrices),
            ],
        ]);

        $response->assertStatus(201);

        // Verify exact values in database
        $this->listing20L->refresh();
        $this->assertEquals(4500.00, (float)$this->listing20L->seller_price);
        $this->assertEquals(4950.00, (float)$this->listing20L->selling_price);

        $this->listing10L->refresh();
        $this->assertEquals(2350.00, (float)$this->listing10L->seller_price);

        $this->listing4L->refresh();
        $this->assertEquals(980.00, (float)$this->listing4L->seller_price);

        $this->listing1L->refresh();
        $this->assertEquals(260.00, (float)$this->listing1L->seller_price);
    }

    /**
     * MANDATORY ACCEPTANCE SCENARIO 4:
     * Retry a scheduled job: idempotent, no duplicate price increase occurs.
     */
    public function test_mandatory_scenario_4_idempotent_job_retry_prevents_duplicate_price_hike(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Initial price: ₹4000.00
        $response = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 10.0, // +10%
            'rounding_rule'      => 'none',
            'effective_at'       => now()->subMinute()->toIso8601String(),
            'filters'            => [
                'seller_listing_ids' => [$this->listing20L->id],
            ],
        ]);

        $response->assertStatus(201);
        $revisionId = $response->json('data.id');

        // Initial application: 4000 + 10% = ₹4400.00
        $this->listing20L->refresh();
        $this->assertEquals(4400.00, (float)$this->listing20L->seller_price);

        // SIMULATE RETRIES: Run console command 3 times
        Artisan::call('price-revisions:process', ['--revision-id' => $revisionId]);
        Artisan::call('price-revisions:process', ['--revision-id' => $revisionId]);
        Artisan::call('price-revisions:process', ['--revision-id' => $revisionId]);

        // Price MUST REMAIN 4400.00, NOT 4840.00 or 5324.00!
        $this->listing20L->refresh();
        $this->assertEquals(4400.00, (float)$this->listing20L->seller_price, 'Retrying job must be idempotent and NEVER apply duplicate price increase.');
    }

    /**
     * MANDATORY ACCEPTANCE SCENARIO 5:
     * Change a price after order placement: existing historical order line price remains unchanged.
     */
    public function test_mandatory_scenario_5_historical_order_price_immutability(): void
    {
        // 1. Create a historical placed order at initial price (₹4000.00)
        $orderNo = 'AF' . date('Ymd') . '-9999';
        $orderId = DB::table('orders')->insertGetId([
            'orderno'     => $orderNo,
            'customer_id' => 101,
            'user_id'     => $this->sellerA->id,
            'netamount'   => 4400.00,
            'taxamount'   => 792.00,
            'grandtotal'  => 5192.00,
            'created_at'  => now()->subDays(5),
            'updated_at'  => now()->subDays(5),
        ]);

        $orderDetailId = DB::table('orderdetail')->insertGetId([
            'order_id'    => $orderId,
            'orderno'     => $orderNo,
            'customer_id' => 101,
            'product_id'  => $this->productPaint->id,
            'productname' => $this->productPaint->name,
            'attribute'   => json_encode([
                [
                    'qty'        => 1,
                    'unitprice'  => 4400.00,
                    'totalprice' => 4400.00,
                    'tax'        => 18.0,
                    'taxamount'  => 792.00,
                ]
            ]),
            'created_at'  => now()->subDays(5),
            'updated_at'  => now()->subDays(5),
        ]);

        // 2. Seller modifies price via +20% price revision (4000 -> 4800, within MRP 5000)
        Sanctum::actingAs($this->sellerA);
        $response = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 20.0,
            'effective_at'       => now()->subMinute()->toIso8601String(),
            'filters'            => [
                'seller_listing_ids' => [$this->listing20L->id],
            ],
        ]);
        $response->assertStatus(201);

        // Verify catalogue price changed to 4800
        $this->listing20L->refresh();
        $this->assertEquals(4800.00, (float)$this->listing20L->seller_price);

        // 3. VERIFY: Historical order line price snapshot is 100% IMMUTABLE
        $order = DB::table('orders')->where('id', $orderId)->first();
        $this->assertEquals(5192.00, (float)$order->grandtotal, 'Historical order grand total must not be recalculated.');

        $orderDetail = DB::table('orderdetail')->where('id', $orderDetailId)->first();
        $attr = json_decode($orderDetail->attribute, true);
        $this->assertEquals(4400.00, (float)$attr[0]['unitprice'], 'Historical order detail line price must remain frozen.');
    }

    /**
     * Test Percentage Decrease (-5%) and Rounding Rules.
     */
    public function test_percentage_decrease_and_rounding_rules(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Current price: 2200.00. -5% = 2090.00
        $response = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'decrease',
            'revision_value'     => 5.0,
            'rounding_rule'      => 'round_half',
            'effective_at'       => now()->subMinute()->toIso8601String(),
            'filters'            => [
                'seller_listing_ids' => [$this->listing10L->id],
            ],
        ]);

        $response->assertStatus(201);
        $this->listing10L->refresh();
        $this->assertEquals(2090.00, (float)$this->listing10L->seller_price);
    }

    /**
     * Test Preview without database mutation.
     */
    public function test_preview_does_not_mutate_database(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->postJson('/api/v2/seller/pricing/revisions/preview', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 25.0,
            'filters'            => [
                'seller_listing_ids' => [$this->listing20L->id],
            ],
        ]);

        $response->assertStatus(200);

        // Database listing price MUST still be 4000.00
        $this->listing20L->refresh();
        $this->assertEquals(4000.00, (float)$this->listing20L->seller_price, 'Preview must never mutate database records.');
    }

    /**
     * Test Zero or Negative Price Prevention.
     */
    public function test_negative_or_zero_price_prevention(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Flat decrease of ₹300 on a ₹250 SKU results in -₹50
        $response = $this->postJson('/api/v2/seller/pricing/revisions/preview', [
            'revision_method'    => 'flat_per_unit',
            'revision_direction' => 'decrease',
            'revision_value'     => 300.00,
            'price_basis_unit'   => 'Litre',
            'filters'            => [
                'seller_listing_ids' => [$this->listing1L->id],
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $item = $data['items'][0];

        $this->assertFalse($item['is_unit_compatible']);
        $this->assertEquals('invalid', $item['validation_status']);
        $this->assertStringContainsString('must be greater than zero', $item['validation_error']);
    }

    /**
     * Test Multi-Tenant Seller Isolation & IDOR Protection.
     */
    public function test_multi_tenant_seller_isolation(): void
    {
        // Create revision belonging to Seller A
        $revision = PriceRevision::create([
            'revision_code'       => 'REV-A-001',
            'company_id'          => $this->companyA->id,
            'created_by_user_id'  => $this->sellerA->id,
            'revision_method'     => 'percentage',
            'revision_direction'  => 'increase',
            'revision_value'      => 5.0,
            'status'              => 'scheduled',
            'effective_at'        => now()->addDay(),
        ]);

        // Seller B attempts to access Seller A's revision -> 404
        Sanctum::actingAs($this->sellerB);

        $getResp = $this->getJson("/api/v2/seller/pricing/revisions/{$revision->id}");
        $getResp->assertStatus(404);

        $cancelResp = $this->postJson("/api/v2/seller/pricing/revisions/{$revision->id}/cancel");
        $cancelResp->assertStatus(404);
    }

    /**
     * Test RBAC Permission Enforcement: Staff with and without permissions.
     */
    public function test_rbac_permission_enforcement(): void
    {
        // 1. Staff with pricing_manager role succeeds
        Sanctum::actingAs($this->staffA);

        $resp = $this->postJson('/api/v2/seller/pricing/revisions/preview', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 2.0,
        ]);
        $resp->assertStatus(200);

        // 2. Staff without pricing permissions is rejected with 403
        $unauthorizedStaff = User::factory()->create();
        $orderRole = SellerRole::where('name', 'order_manager')->first();
        SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $unauthorizedStaff->id,
            'seller_role_id' => $orderRole->id,
            'status'         => 'active',
        ]);

        Sanctum::actingAs($unauthorizedStaff);
        $deniedResp = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method' => 'percentage',
            'effective_at'    => now()->toIso8601String(),
        ]);
        $deniedResp->assertStatus(403);
    }

    /**
     * Test Platform Admin Approval Workflow.
     */
    public function test_admin_approval_workflow(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Schedule revision requiring admin approval
        $createResp = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 8.0,
            'approval_required'  => true,
            'effective_at'       => now()->subMinute()->toIso8601String(),
            'filters'            => [
                'seller_listing_ids' => [$this->listing4L->id],
            ],
        ]);

        $createResp->assertStatus(201);
        $revisionId = $createResp->json('data.id');
        $this->assertEquals('pending_approval', $createResp->json('data.status'));

        // Verify: Not active yet even though effective time passed
        $this->listing4L->refresh();
        $this->assertEquals(900.00, (float)$this->listing4L->seller_price);

        // Admin queue inspection
        Sanctum::actingAs($this->adminUser);
        $queueResp = $this->getJson('/api/v2/admin/pricing/revisions/queue');
        $queueResp->assertStatus(200);
        $this->assertNotEmpty($queueResp->json('data.data'));

        // Admin approves revision
        $approveResp = $this->postJson("/api/v2/admin/pricing/revisions/{$revisionId}/approve");
        $approveResp->assertStatus(200);

        // Price now applied (900 * 1.08 = 972.00)
        $this->listing4L->refresh();
        $this->assertEquals(972.00, (float)$this->listing4L->seller_price);
    }

    /**
     * Test Cancellation of Scheduled Revisions.
     */
    public function test_cancel_scheduled_revision(): void
    {
        Sanctum::actingAs($this->sellerA);

        $createResp = $this->postJson('/api/v2/seller/pricing/revisions', [
            'revision_method'    => 'percentage',
            'revision_direction' => 'increase',
            'revision_value'     => 15.0,
            'effective_at'       => now()->addDays(3)->toIso8601String(),
            'filters'            => [
                'seller_listing_ids' => [$this->listing20L->id],
            ],
        ]);
        $revisionId = $createResp->json('data.id');

        $cancelResp = $this->postJson("/api/v2/seller/pricing/revisions/{$revisionId}/cancel");
        $cancelResp->assertStatus(200);

        $revision = PriceRevision::find($revisionId);
        $this->assertEquals('cancelled', $revision->status);
    }

    /**
     * Test SKU Price History Endpoint.
     */
    public function test_sku_price_history_endpoint(): void
    {
        Sanctum::actingAs($this->sellerA);

        $resp = $this->getJson("/api/v2/seller/pricing/skus/{$this->listing20L->id}/history");
        $resp->assertStatus(200);
        $resp->assertJsonPath('data.seller_sku', 'ALPH-EPP-20L');
        $this->assertArrayHasKey('history', $resp->json('data'));
    }

    /**
     * Test Web Portal Routes.
     */
    public function test_web_portal_routes(): void
    {
        // Seller web portal
        $this->actingAs($this->sellerA);

        $indexResp = $this->get(route('seller.pricing.revisions.index'));
        $indexResp->assertStatus(200);
        $indexResp->assertSee('Dynamic Price Revisions');

        $createResp = $this->get(route('seller.pricing.revisions.create'));
        $createResp->assertStatus(200);
        $createResp->assertSee('Revision Method');

        // Admin web portal
        $this->actingAs($this->adminUser);
        $adminResp = $this->get(route('admin.pricing.revisions.queue'));
        $adminResp->assertStatus(200);
        $adminResp->assertSee('Pending Seller Price Revisions Requiring Approval');
    }
}
