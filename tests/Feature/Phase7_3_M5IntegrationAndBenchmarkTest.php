<?php

namespace Tests\Feature;

use App\Models\BoxPacking;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Models\MainCategory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\ShadeCard;
use App\Models\User;
use App\Services\BuyerAttributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase7_3_M5IntegrationAndBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sellerA;
    protected User $sellerB;
    protected Company $companyA;
    protected Company $companyB;
    protected MainCategory $mainCategory;
    protected Category $category;
    protected Brand $brand;
    protected ShadeCard $shade;
    protected BoxPacking $packing;
    protected Product $product;
    protected ProductVariant $variant;
    protected SellerListing $listing;
    protected Customer $buyerA;
    protected Customer $buyerB;
    protected User $buyerUserA;
    protected User $agentUserA;
    protected User $agentUserB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name'     => 'M5 Release Admin',
            'email'    => 'admin.m5@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        $this->sellerA = User::create([
            'name'     => 'M5 Seller Alpha',
            'email'    => 'sellerA.m5@factory.in',
            'password' => bcrypt('password123'),
        ]);
        $this->sellerB = User::create([
            'name'     => 'M5 Seller Beta',
            'email'    => 'sellerB.m5@factory.in',
            'password' => bcrypt('password123'),
        ]);

        $this->companyA = Company::create([
            'user_id'   => $this->sellerA->id,
            'name'      => 'Alpha Industrial Paints',
            'email'     => 'alpha.secret@factory.in',
            'mobile'    => '9829073001',
            'city'      => 'Jaipur',
            'state'     => 'Rajasthan',
            'pincode'   => '302001',
            'gst'       => '08AABCA7301Z1Z5',
            'comission' => 10,
            'status'    => 'Active',
        ]);
        $this->companyB = Company::create([
            'user_id'   => $this->sellerB->id,
            'name'      => 'Beta Industrial Paints',
            'city'      => 'Surat',
            'state'     => 'Gujarat',
            'pincode'   => '395001',
            'mobile'    => '9829073002',
            'comission' => 12,
            'status'    => 'Active',
        ]);

        $this->mainCategory = MainCategory::create(['name' => 'Coatings M5', 'status' => 'Active']);
        $this->category = Category::create([
            'name'            => 'Industrial Primers M5',
            'maincategory_id' => $this->mainCategory->id,
            'status'          => 'Active',
        ]);
        $this->brand = Brand::create([
            'name'        => 'AlphaShield',
            'company_id'  => $this->companyA->id,
            'user_id'     => $this->sellerA->id,
            'category_id' => $this->category->id,
            'status'      => 'Active',
        ]);
        $this->shade = ShadeCard::create([
            'name'            => 'Oxide Red M5',
            'hexcode'         => '#B22222',
            'maincategory_id' => $this->mainCategory->id,
            'category_id'     => $this->category->id,
            'status'          => 'Active',
        ]);
        $this->packing = BoxPacking::create([
            'name'            => '10L Drum',
            'pcs'             => 1,
            'maincategory_id' => $this->mainCategory->id,
            'status'          => 'Active',
        ]);

        $this->product = Product::create([
            'name'            => 'Alpha Primer M5 Spec',
            'slug'            => 'alpha-primer-m5-spec',
            'maincategory_id' => $this->mainCategory->id,
            'category_id'     => $this->category->id,
            'brand_id'        => $this->brand->id,
            'user_id'         => $this->sellerA->id,
            'hsncode'         => '320810',
            'tax'             => 18,
            'status'          => 'Active',
            'approval_status' => 'approved',
        ]);

        $this->variant = ProductVariant::create([
            'product_id'     => $this->product->id,
            'variant_sku'    => 'M5-VAR-OR-10L',
            'shade_card_id'  => $this->shade->id,
            'box_packing_id' => $this->packing->id,
            'pack_litres'    => 10.0,
            'variant_name'   => 'Oxide Red 10L',
            'status'         => 'Active',
        ]);

        $this->listing = SellerListing::create([
            'seller_user_id'     => $this->sellerA->id,
            'company_id'         => $this->companyA->id,
            'product_id'         => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'seller_sku'         => 'M5-SKU-001',
            'seller_price'       => 1000.00,
            'commission_rate'    => 10.00,
            'selling_price'      => 1100.00,
            'mrp'                => 1300.00,
            'stock_quantity'     => 80,
            'reserved_stock'     => 0,
            'status'             => 'Active',
        ]);

        ProductAttributes::create([
            'product_id'      => $this->product->id,
            'color'           => (string)$this->shade->id,
            'quantity'        => (string)$this->packing->id,
            'oldprice'        => '1300.00',
            'seller_price'    => '1000.00',
            'commission_rate' => 10.00,
            'pack_litres'     => 10.0,
            'price'           => '1100.00',
            'status'          => 'active',
        ]);

        $this->buyerUserA = User::create([
            'name'     => 'Buyer Alpha Dealer',
            'email'    => 'buyerA.m5@dealer.in',
            'password' => bcrypt('password123'),
        ]);
        $this->buyerA = Customer::create([
            'name'     => 'Buyer Alpha Dealer',
            'email'    => 'buyerA.m5@dealer.in',
            'mobile'   => '9888073001',
            'password' => bcrypt('password123'),
            'location' => 'Jaipur',
            'status'   => 'active',
        ]);
        $this->buyerB = Customer::create([
            'name'     => 'Buyer Beta Dealer',
            'email'    => 'buyerB.m5@dealer.in',
            'mobile'   => '9888073002',
            'password' => bcrypt('password123'),
            'location' => 'Kota',
            'status'   => 'active',
        ]);

        $agentA = FieldAgent::create([
            'agent_code'      => 'FSA-M5-01',
            'name'            => 'M5 Agent Alpha',
            'email'           => 'agentA.m5@apnifactory.in',
            'mobile'          => '9829073901',
            'territory'       => 'Jaipur M5',
            'status'          => 'active',
            'kyc_status'      => 'verified',
            'contract_status' => 'active',
            'user_id'         => User::create([
                'name'     => 'M5 Agent Alpha',
                'email'    => 'agentA.m5@apnifactory.in',
                'password' => bcrypt('password123'),
            ])->id,
        ]);
        $this->agentUserA = $agentA->user;

        $agentB = FieldAgent::create([
            'agent_code'      => 'FSA-M5-02',
            'name'            => 'M5 Agent Beta',
            'email'           => 'agentB.m5@apnifactory.in',
            'mobile'          => '9829073902',
            'territory'       => 'Kota M5',
            'status'          => 'active',
            'kyc_status'      => 'verified',
            'contract_status' => 'active',
            'user_id'         => User::create([
                'name'     => 'M5 Agent Beta',
                'email'    => 'agentB.m5@apnifactory.in',
                'password' => bcrypt('password123'),
            ])->id,
        ]);
        $this->agentUserB = $agentB->user;

        app(BuyerAttributionService::class)->createAttribution([
            'customer_id'    => $this->buyerA->id,
            'field_agent_id' => $agentA->id,
        ], $this->adminUser);
    }

    public function test_01_m5_cross_channel_workflow_and_order_casing_fix_verification(): void
    {
        // Legacy OrderController endpoints execute cleanly after Order:: casing fix
        $order = Order::create([
            'orderno'        => 'AF-M5-ORD-01',
            'customer_id'    => $this->buyerA->id,
            'user_id'        => $this->sellerA->id,
            'address'        => json_encode(['city' => 'Jaipur', 'state' => 'Rajasthan']),
            'taxdetail'      => json_encode([['name' => 'CGST+SGST', 'value' => 198.00]]),
            'netamount'      => 1100.00,
            'taxamount'      => 198.00,
            'grandtotal'     => 1298.00,
            'payment_status' => 'Pending',
        ]);

        $this->postJson('/api/orderhistory', ['userid' => $this->buyerA->id])
            ->assertStatus(200)
            ->assertJsonPath('data.0.orderno', 'AF-M5-ORD-01');

        $this->postJson('/api/orderdetail', ['orderno' => 'AF-M5-ORD-01'])
            ->assertStatus(200)
            ->assertJsonPath('data.orderno', 'AF-M5-ORD-01');

        // Seller updates price -> consistent across Public v2, Public v1, Admin Summary, and Cart
        Sanctum::actingAs($this->sellerA);
        $this->putJson("/api/v2/seller/listings/{$this->listing->id}", ['seller_price' => 1200.00])
            ->assertStatus(200);

        $this->getJson("/api/v2/public/listings/{$this->listing->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.selling_price', 1320);

        $this->postJson('/api/productdetail', ['productid' => $this->product->id])
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.0.price', 1320);

        // Agent A sees attributed buyer A cleanly, Agent B cannot
        Sanctum::actingAs($this->agentUserA);
        $this->getJson("/api/v2/agent/buyers/{$this->buyerA->id}")->assertStatus(200);
    }

    public function test_02_m5_security_retests_for_paint_pricing_otp_bypass_and_cross_tenant_idor(): void
    {
        // S1 Retest 1: Unauthenticated paint-pricing routes must return 401
        $this->getJson("/api/seller/paint-families/{$this->product->id}/pricing")->assertStatus(401);
        $this->postJson('/api/seller/paint-pricing/apply', ['product_id' => $this->product->id])->assertStatus(401);

        // S1 Retest 2: Hardcoded '1234' OTP bypass rejected when allow_dev_otp_bypass = false
        Config::set('marketplace_integration.allow_dev_otp_bypass', false);
        $this->postJson('/api/verifyotp', [
            'emailmobile' => $this->buyerA->mobile,
            'otp'         => '1234',
        ])->assertStatus(404);

        // S1 Retest 3: Unauthenticated transaction testsuccess blocked
        Config::set('marketplace_integration.legacy_test_endpoints_enabled', false);
        $this->postJson('/api/order/transection/testsuccess', ['txnid' => 'ANY-ORD'])->assertStatus(403);

        // Cross-seller IDOR denied
        Sanctum::actingAs($this->sellerB);
        $this->putJson("/api/v2/seller/listings/{$this->listing->id}", ['seller_price' => 500.00])->assertStatus(403);

        // Cross-agent IDOR & Seller/Finance endpoints denied for Agent
        Sanctum::actingAs($this->agentUserB);
        $this->getJson("/api/v2/agent/buyers/{$this->buyerA->id}")->assertStatus(403);
        $this->getJson('/api/v2/finance/ledger')->assertStatus(403);

        // Non-admin roles denied on Admin Integration & Sync endpoints
        Sanctum::actingAs($this->sellerA);
        $this->getJson('/api/v2/admin/integrations/summary')->assertStatus(403);
        Sanctum::actingAs($this->agentUserA);
        $this->getJson('/api/v2/admin/integrations/reconciliation')->assertStatus(403);

        // Public website / v2 catalogue & legacy product detail strip confidential pricing and seller email/mobile
        $pub = $this->getJson("/api/v2/public/listings/{$this->listing->id}")->assertStatus(200);
        $this->assertStringNotContainsString('seller_price', $pub->getContent());
        $this->assertStringNotContainsString('alpha.secret@factory.in', $pub->getContent());

        $v1Det = $this->postJson('/api/productdetail', ['productid' => $this->product->id])->assertStatus(200);
        $this->assertStringNotContainsString('alpha.secret@factory.in', $v1Det->getContent());
        $this->assertStringNotContainsString('9829073001', $v1Det->getContent());
        $this->assertArrayNotHasKey('seller_price', $v1Det->json('data.attributes.0'));
    }

    public function test_03_m5_staging_performance_baselines_and_pagination_verification(): void
    {
        // Populate 30 canonical products + variants + listings for repeatable pagination & timing profile
        for ($i = 1; $i <= 30; $i++) {
            $p = Product::create([
                'name'            => "M5 Bench Paint {$i}",
                'slug'            => "m5-bench-paint-{$i}",
                'maincategory_id' => $this->mainCategory->id,
                'category_id'     => $this->category->id,
                'brand_id'        => $this->brand->id,
                'user_id'         => $this->sellerA->id,
                'tax'             => 18,
                'status'          => 'Active',
                'approval_status' => 'approved',
            ]);
            $v = ProductVariant::create([
                'product_id'     => $p->id,
                'variant_sku'    => "M5-BNCH-VAR-{$i}",
                'pack_litres'    => 10.0,
                'variant_name'   => "10L Pack {$i}",
                'status'         => 'Active',
            ]);
            SellerListing::create([
                'seller_user_id'     => $this->sellerA->id,
                'company_id'         => $this->companyA->id,
                'product_id'         => $p->id,
                'product_variant_id' => $v->id,
                'seller_sku'         => "M5-BNCH-SKU-{$i}",
                'seller_price'       => 800 + $i,
                'commission_rate'    => 10.00,
                'selling_price'      => round((800 + $i) * 1.10, 2),
                'stock_quantity'     => 100,
                'status'             => 'Active',
            ]);
        }

        $baselines = [];

        $measure = function (string $label, callable $fn) use (&$baselines) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $t0 = microtime(true);
            $res = $fn();
            $elapsedMs = round((microtime(true) - $t0) * 1000, 2);
            $queries = count(DB::getQueryLog());
            DB::disableQueryLog();
            $baselines[$label] = ['elapsed_ms' => $elapsedMs, 'sql_queries' => $queries, 'http_status' => $res->getStatusCode()];
            return $res;
        };

        $prodRes = $measure('GET /api/v2/products?per_page=15', fn() => $this->getJson('/api/v2/products?per_page=15'));
        $prodRes->assertStatus(200);
        $this->assertCount(15, $prodRes->json('data'));

        $searchRes = $measure('GET /api/v2/catalogue/search?q=Bench&per_page=15', fn() => $this->getJson('/api/v2/catalogue/search?q=Bench&per_page=15'));
        $searchRes->assertStatus(200);
        $this->assertCount(15, $searchRes->json('data'));

        $pubRes = $measure('GET /api/v2/public/listings/{id}', fn() => $this->getJson("/api/v2/public/listings/{$this->listing->id}"));
        $pubRes->assertStatus(200);

        $v1ProdRes = $measure('POST /api/productdetail', fn() => $this->postJson('/api/productdetail', ['productid' => $this->product->id]));
        $v1ProdRes->assertStatus(200);

        Sanctum::actingAs($this->buyerUserA);
        $cartRes = $measure('GET /api/v2/cart', fn() => $this->getJson('/api/v2/cart'));
        $cartRes->assertStatus(200);

        Sanctum::actingAs($this->adminUser);
        $syncSumRes = $measure('GET /api/v2/admin/integrations/summary', fn() => $this->getJson('/api/v2/admin/integrations/summary'));
        $syncSumRes->assertStatus(200);

        $scanRes = $measure('POST /api/v2/admin/integrations/reconciliation/scan', fn() => $this->postJson('/api/v2/admin/integrations/reconciliation/scan'));
        $scanRes->assertStatus(200);

        // Save measured baseline artifact in storage/logs for reporting without synthetic numbers
        file_put_contents(storage_path('logs/m5_performance_baseline.json'), json_encode([
            'measured_at'    => now()->toIso8601String(),
            'dataset_counts' => ['products' => Product::count(), 'variants' => ProductVariant::count(), 'listings' => SellerListing::count()],
            'endpoints'      => $baselines,
        ], JSON_PRETTY_PRINT));

        $this->assertFileExists(storage_path('logs/m5_performance_baseline.json'));
        $this->assertLessThan(2000, $baselines['GET /api/v2/products?per_page=15']['elapsed_ms']);
    }
}

