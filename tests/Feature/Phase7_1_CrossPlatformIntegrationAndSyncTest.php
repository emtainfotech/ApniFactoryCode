<?php

namespace Tests\Feature;

use App\Models\BoxPacking;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Models\IntegrationChannelMapping;
use App\Models\IntegrationReconciliationRecord;
use App\Models\IntegrationSyncAuditLog;
use App\Models\IntegrationSyncEvent;
use App\Models\MainCategory;
use App\Models\Order;
use App\Models\PriceRevision;
use App\Models\PriceRevisionItem;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\ShadeCard;
use App\Models\TaxMaster;
use App\Models\Transection;
use App\Models\User;
use App\Services\CrossPlatformSyncService;
use App\Services\DynamicPriceRevisionService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Phase7_1_CrossPlatformIntegrationAndSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sellerUser;
    protected User $otherSellerUser;
    protected User $agentUser;
    protected Company $company;
    protected MainCategory $mainCategory;
    protected Category $category;
    protected Brand $brand;
    protected ShadeCard $shade;
    protected BoxPacking $packing;
    protected Product $product;
    protected ProductVariant $variant;
    protected SellerListing $listing;
    protected ProductAttributes $legacyAttr;
    protected Customer $customer;
    protected Order $order;
    protected CommissionRule $commissionRule;
    protected TaxMaster $taxMaster;
    protected CrossPlatformSyncService $syncService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->syncService = app(CrossPlatformSyncService::class);

        $this->adminUser = User::create([
            'name'     => 'Integration Admin',
            'email'    => 'admin.sync71@apnifactory.com',
            'password' => bcrypt('password123'),
        ]);

        $this->sellerUser = User::create([
            'name'     => 'Apex Coatings Seller',
            'email'    => 'seller1.71@factory.in',
            'password' => bcrypt('password123'),
        ]);

        $this->otherSellerUser = User::create([
            'name'     => 'Other Coatings Seller',
            'email'    => 'seller2.71@factory.in',
            'password' => bcrypt('password123'),
        ]);

        $this->company = Company::create([
            'user_id'   => $this->sellerUser->id,
            'name'      => 'Apex Coatings Pvt Ltd',
            'email'     => 'private-seller@apexcoatings.in',
            'mobile'    => '9829071001',
            'city'      => 'Jaipur',
            'state'     => 'Rajasthan',
            'pincode'   => '302001',
            'gst'       => '08AABCA1234Z1Z5',
            'comission' => 10,
            'status'    => 'Active',
        ]);

        $agent = FieldAgent::create([
            'agent_code'      => 'FSA-71-001',
            'name'            => 'Restricted Field Agent',
            'email'           => 'agent.71@apnifactory.in',
            'mobile'          => '9829071999',
            'territory'       => 'Jaipur West',
            'status'          => 'active',
            'kyc_status'      => 'verified',
            'contract_status' => 'active',
            'user_id'         => User::create([
                'name'     => 'Agent User 71',
                'email'    => 'agent.user71@apnifactory.in',
                'password' => bcrypt('password123'),
            ])->id,
        ]);
        $this->agentUser = $agent->user;

        $this->mainCategory = MainCategory::create(['name' => 'Architectural Paints', 'status' => 'Active']);
        $this->category = Category::create([
            'name'            => 'Exterior Emulsions 71',
            'maincategory_id' => $this->mainCategory->id,
            'status'          => 'Active',
        ]);
        $this->brand = Brand::create([
            'name'        => 'Apex Shield',
            'company_id'  => $this->company->id,
            'user_id'     => $this->sellerUser->id,
            'category_id' => $this->category->id,
            'status'      => 'Active',
        ]);
        $this->shade = ShadeCard::create([
            'name'            => 'Signal White 71',
            'hexcode'         => '#FAFAFA',
            'maincategory_id' => $this->mainCategory->id,
            'category_id'     => $this->category->id,
            'status'          => 'Active',
        ]);
        $this->packing = BoxPacking::create([
            'name'            => '20L Bucket',
            'pcs'             => 1,
            'maincategory_id' => $this->mainCategory->id,
            'status'          => 'Active',
        ]);

        $this->product = Product::create([
            'name'            => 'Apex Elastomeric Coat',
            'slug'            => 'apex-elastomeric-coat-71',
            'maincategory_id' => $this->mainCategory->id,
            'category_id'     => $this->category->id,
            'brand_id'        => $this->brand->id,
            'user_id'         => $this->sellerUser->id,
            'hsncode'         => '320810',
            'tax'             => 18,
            'status'          => 'Active',
            'approval_status' => 'approved',
        ]);

        $this->variant = ProductVariant::create([
            'product_id'     => $this->product->id,
            'variant_sku'    => 'VAR-71-SW-20L',
            'shade_card_id'  => $this->shade->id,
            'box_packing_id' => $this->packing->id,
            'pack_litres'    => 20.0,
            'variant_name'   => 'Signal White 20L',
            'status'         => 'Active',
        ]);

        $this->listing = SellerListing::create([
            'seller_user_id'     => $this->sellerUser->id,
            'company_id'         => $this->company->id,
            'product_id'         => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'seller_sku'         => 'APEX-SKU-71',
            'seller_price'       => 2000.00,
            'commission_rate'    => 10.00,
            'selling_price'      => 2200.00,
            'mrp'                => 2600.00,
            'stock_quantity'     => 100,
            'reserved_stock'     => 0,
            'status'             => 'Active',
        ]);

        // Pre-existing legacy v1 SKU projection row matching shade + packing
        $this->legacyAttr = ProductAttributes::create([
            'product_id'      => $this->product->id,
            'color'           => (string)$this->shade->id,
            'quantity'        => (string)$this->packing->id,
            'oldprice'        => '2600.00',
            'seller_price'    => '2000.00',
            'commission_rate' => 10.00,
            'pack_litres'     => 20.0,
            'price'           => '2200.00',
            'status'          => 'active',
        ]);

        $this->customer = Customer::create([
            'name'     => 'Metro Hardware Dealers',
            'email'    => 'dealer71@metrohardware.in',
            'mobile'   => '9888071001',
            'password' => bcrypt('password123'),
            'location' => 'Jaipur',
            'status'   => 'active',
        ]);

        $this->order = Order::create([
            'orderno'        => 'AF-ORD-71001',
            'customer_id'    => $this->customer->id,
            'user_id'        => $this->sellerUser->id,
            'address'        => 'VKIA Area, Jaipur',
            'netamount'      => 2200.00,
            'taxamount'      => 396.00,
            'grandtotal'     => 2596.00,
            'payment_status' => 'Pending',
        ]);

        $this->commissionRule = CommissionRule::create([
            'rule_code'             => 'COMM-GLOBAL-71',
            'name'                  => 'Standard Platform Commission 10%',
            'scope_type'            => 'global',
            'commission_percentage' => 10.00,
            'status'                => 'active',
            'effective_from'        => now()->subDay(),
        ]);

        $this->taxMaster = TaxMaster::create([
            'hsn_code'        => '320810',
            'description'     => 'Synthetic Paints & Enamels',
            'gst_rate'        => 18.00,
            'type'            => 'Goods',
            'approval_status' => 'approved',
            'effective_from'  => now()->subDay(),
        ]);
    }

    public function test_01_canonical_identifiers_resolved_for_all_11_entities_without_duplicate_masters(): void
    {
        $this->actingAs($this->adminUser);

        $entities = [
            ['seller',     $this->company->id,        '08AABCA1234Z1Z5'],
            ['product',    $this->product->id,        'apex-elastomeric-coat-71'],
            ['variant',    $this->variant->id,        'VAR-71-SW-20L'],
            ['sku',        $this->listing->id,        'APEX-SKU-71'],
            ['category',   $this->category->id,       'Exterior Emulsions 71'],
            ['customer',   $this->customer->id,       'dealer71@metrohardware.in'],
            ['price',      $this->listing->id,        'APEX-SKU-71'],
            ['stock',      $this->listing->id,        'APEX-SKU-71'],
            ['order',      $this->order->id,          'AF-ORD-71001'],
            ['commission', $this->commissionRule->id, 'COMM-GLOBAL-71'],
            ['tax',        $this->taxMaster->id,      '320810'],
        ];

        foreach ($entities as [$type, $id, $expectedRef]) {
            $res = $this->getJson("/api/v2/admin/integrations/canonical/{$type}/{$id}")->assertStatus(200);
            $this->assertEquals($expectedRef, $res->json('data.canonical_ref'));
        }

        // Map canonical customer to external CRM ID without duplicating Customer row
        $beforeCount = Customer::count();
        $mapRes = $this->postJson('/api/v2/admin/integrations/mappings', [
            'entity_type'   => 'customer',
            'canonical_id'  => $this->customer->id,
            'canonical_ref' => $this->customer->email,
            'channel'       => 'crm_external',
            'external_id'   => 'CRM-LEAD-9001',
            'external_ref'  => 'lead_9001',
        ])->assertStatus(201);

        $this->assertEquals($beforeCount, Customer::count());
        $this->assertEquals('CRM-LEAD-9001', $mapRes->json('data.external_id'));
    }

    public function test_02_product_price_and_stock_updates_sync_canonically_and_project_to_legacy_channel(): void
    {
        // 1. Admin updates product via v2 API -> emits safe CRM outbox sync event
        $this->actingAs($this->adminUser);
        $this->putJson("/api/v2/admin/products/{$this->product->id}", [
            'name' => 'Apex Elastomeric Coat Pro',
        ])->assertStatus(200);

        $this->assertDatabaseHas('integration_sync_events', [
            'target_channel' => 'crm_external',
            'entity_type'    => 'product',
            'canonical_id'   => $this->product->id,
            'action'         => 'product_updated',
            'status'         => IntegrationSyncEvent::STATUS_SKIPPED_DISABLED, // Safe gate: CRM push disabled
        ]);

        // 1b. Admin updates category via v2 API -> emits category sync event cleanly
        $this->putJson("/api/v2/admin/categories/{$this->category->id}", [
            'title' => 'Weatherproof Exterior Series',
        ])->assertStatus(200);

        $this->assertDatabaseHas('integration_sync_events', [
            'target_channel' => 'crm_external',
            'entity_type'    => 'category',
            'canonical_id'   => $this->category->id,
            'action'         => 'category_updated',
        ]);

        // 2. Seller updates SKU price via v2 API -> SellerListing is authoritative + projects to legacy ProductAttributes
        $this->actingAs($this->sellerUser);
        $this->putJson("/api/v2/seller/listings/{$this->listing->id}", [
            'seller_price' => 2100.00,
        ])->assertStatus(200);

        $this->assertEquals(2310.00, (float)$this->listing->fresh()->selling_price);
        $this->assertEquals(2310.00, (float)$this->legacyAttr->fresh()->price);
        $this->assertEquals(2100.00, (float)$this->legacyAttr->fresh()->seller_price);

        // 3. DynamicPriceRevision execution syncs canonical SellerListing + projects to legacy ProductAttributes
        $rev = PriceRevision::create([
            'revision_code'        => 'REV-71-TEST',
            'company_id'           => $this->company->id,
            'created_by_user_id'   => $this->sellerUser->id,
            'title'                => 'Phase 7.1 Sync Revision',
            'revision_type'        => 'percentage',
            'adjustment_value'     => 10.0,
            'scope_type'           => 'product',
            'scope_id'             => $this->product->id,
            'effective_at'         => now()->subMinute(),
            'status'               => 'scheduled',
        ]);
        PriceRevisionItem::create([
            'price_revision_id' => $rev->id,
            'seller_listing_id' => $this->listing->id,
            'old_seller_price'  => 2100.00,
            'new_seller_price'  => 2200.00,
            'commission_rate'   => 10.00,
            'old_selling_price' => 2310.00,
            'new_selling_price' => 2420.00,
            'is_unit_compatible'=> true,
            'status'            => 'pending',
        ]);

        app(DynamicPriceRevisionService::class)->executeRevision($rev);
        $this->assertEquals(2420.00, (float)$this->listing->fresh()->selling_price);
        $this->assertEquals(2420.00, (float)$this->legacyAttr->fresh()->price);

        // 4. InventoryService stock adjustment emits processed stock projection sync event
        app(InventoryService::class)->adjustStock($this->listing->id, 150, 'Physical cycle count sync', $this->sellerUser->id);
        $this->assertEquals(150, (int)$this->listing->fresh()->stock_quantity);
        $this->assertDatabaseHas('integration_sync_events', [
            'entity_type'  => 'sku',
            'canonical_id' => $this->listing->id,
            'action'       => 'stock_adjustment',
            'status'       => IntegrationSyncEvent::STATUS_PROCESSED,
        ]);
    }

    public function test_03_public_v2_and_hardened_v1_product_endpoints_never_leak_seller_costs_or_private_pii(): void
    {
        // v2 public listing endpoint
        $v2Public = $this->getJson("/api/v2/public/listings/{$this->listing->id}")->assertStatus(200);
        $v2Content = $v2Public->getContent();
        $this->assertStringNotContainsString('seller_price', $v2Content);
        $this->assertStringNotContainsString('commission_rate', $v2Content);
        $this->assertStringNotContainsString('private-seller@apexcoatings.in', $v2Content);
        $this->assertEquals(2200.00, (float)$v2Public->json('data.selling_price'));

        // Hardened legacy v1 /api/productdetail (unauthenticated buyer call must NOT expose seller_price, email, mobile)
        $v1Public = $this->postJson('/api/productdetail', ['productid' => $this->product->id])->assertStatus(200);
        $v1Content = $v1Public->getContent();
        $this->assertStringNotContainsString('private-seller@apexcoatings.in', $v1Content);
        $this->assertStringNotContainsString('9829071001', $v1Content);
        $this->assertArrayNotHasKey('seller_price', $v1Public->json('data.attributes.0'));
        $this->assertEquals(2200.00, (float)$v1Public->json('data.attributes.0.price'));
        $this->assertEquals('Apex Coatings Pvt Ltd', $v1Public->json('data.sellerdetail.name'));

        // Customer serialization must never expose password hash or OTP
        $profileRes = $this->getJson("/api/viewprofile/{$this->customer->id}")->assertStatus(200);
        $this->assertArrayNotHasKey('password', $profileRes->json('data'));
        $this->assertArrayNotHasKey('otp', $profileRes->json('data'));
    }

    public function test_04_legacy_payment_bypass_disabled_and_webhook_hardened_with_secret_redaction(): void
    {
        // Unauthenticated payment test conversion bypass must be blocked
        Config::set('marketplace_integration.legacy_test_endpoints_enabled', false);
        $this->postJson('/api/order/transection/testsuccess', ['txnid' => $this->order->orderno])
            ->assertStatus(403);

        // Legacy /api/payment/webhook enforces HMAC signature when webhook secret is configured
        Config::set('services.razorpay.webhook_secret', 'rzp_wh_phase71_secret');
        Config::set('marketplace_integration.require_legacy_webhook_signature', true);

        $payload = [
            'event'    => 'payment.captured',
            'event_id' => 'evt_wh_71_01',
            'payload'  => [
                'payment' => [
                    'entity' => [
                        'id'    => 'pay_71_xyz',
                        'notes' => ['order_no' => $this->order->orderno],
                    ],
                ],
            ],
        ];

        $badRes = $this->postJson('/api/payment/webhook', $payload)->assertStatus(200);
        $this->assertFalse((bool)$badRes->json('status'));
        $this->assertEquals('INVALID_SIGNATURE', $badRes->json('code'));
        $this->assertEquals('Pending', $this->order->fresh()->payment_status);

        // Valid HMAC signature marks order Paid without broken orders.status column error
        $sig = hash_hmac('sha256', json_encode($payload), 'rzp_wh_phase71_secret');
        $goodRes = $this->withHeaders(['X-Razorpay-Signature' => $sig])
            ->postJson('/api/payment/webhook', $payload)
            ->assertStatus(200);

        $this->assertTrue((bool)$goodRes->json('status'));
        $this->assertEquals('Paid', $this->order->fresh()->payment_status);

        // Verify error/payload sanitizer redacts tokens & secrets
        $sanitized = $this->syncService->sanitizeErrorMessage('cURL failed with Authorization: Bearer SECRET_TOKEN_12345 password=myPass');
        $this->assertStringNotContainsString('SECRET_TOKEN_12345', $sanitized);
        $this->assertStringNotContainsString('myPass', $sanitized);
    }

    public function test_05_field_ownership_conflict_rejection_and_idempotency_prevent_silent_overwrites(): void
    {
        $this->actingAs($this->adminUser);

        // CRM attempting to overwrite Commission / Tax or Seller Cost is rejected (422) & logged
        $rejectComm = $this->postJson('/api/v2/admin/integrations/inbound-sync', [
            'source_channel'  => 'crm_external',
            'entity_type'     => 'commission',
            'canonical_id'    => $this->commissionRule->id,
            'fields'          => ['commission_percentage' => 2.5],
            'idempotency_key' => 'inb_crm_comm_1',
        ])->assertStatus(422);

        $this->assertEquals('rejected_ownership', $rejectComm->json('errors.status'));
        $this->assertEquals(10.00, (float)$this->commissionRule->fresh()->commission_percentage);
        $this->assertDatabaseHas('integration_reconciliation_records', [
            'entity_type'   => 'commission',
            'mismatch_type' => IntegrationReconciliationRecord::TYPE_UNAUTHORIZED_OVERRIDE,
            'status'        => IntegrationReconciliationRecord::STATUS_OPEN,
        ]);

        // Valid inbound field with stale expected_updated_at rejected cleanly (never silent last-write-wins!)
        $staleTs = now()->subDay()->toIso8601String();
        $conflictRes = $this->postJson('/api/v2/admin/integrations/inbound-sync', [
            'source_channel'      => 'crm_external',
            'entity_type'         => 'customer',
            'canonical_id'        => $this->customer->id,
            'fields'              => ['location' => 'Mumbai Stale Override'],
            'idempotency_key'     => 'inb_crm_cust_stale',
            'expected_updated_at' => $staleTs,
        ])->assertStatus(422);

        $this->assertEquals('conflict', $conflictRes->json('errors.status'));
        $this->assertEquals('Jaipur', $this->customer->fresh()->location);
        $this->assertDatabaseHas('integration_reconciliation_records', [
            'entity_type'   => 'customer',
            'canonical_id'  => $this->customer->id,
            'mismatch_type' => IntegrationReconciliationRecord::TYPE_CONCURRENT_CONFLICT,
        ]);

        // Matching timestamp succeeds & duplicate replay of same idempotency_key is deduplicated
        $freshTs = $this->customer->fresh()->updated_at->toIso8601String();
        $this->postJson('/api/v2/admin/integrations/inbound-sync', [
            'source_channel'      => 'crm_external',
            'entity_type'         => 'customer',
            'canonical_id'        => $this->customer->id,
            'fields'              => ['location' => 'Jaipur Industrial Area'],
            'idempotency_key'     => 'inb_crm_cust_ok',
            'expected_updated_at' => $freshTs,
        ])->assertStatus(200);

        $this->assertEquals('Jaipur Industrial Area', $this->customer->fresh()->location);

        $replay = $this->postJson('/api/v2/admin/integrations/inbound-sync', [
            'source_channel'      => 'crm_external',
            'entity_type'         => 'customer',
            'canonical_id'        => $this->customer->id,
            'fields'              => ['location' => 'Should Not Apply'],
            'idempotency_key'     => 'inb_crm_cust_ok',
        ])->assertStatus(200);

        $this->assertTrue((bool)$replay->json('data.is_duplicate'));
        $this->assertEquals('Jaipur Industrial Area', $this->customer->fresh()->location);
    }

    public function test_06_discrepancy_scan_safe_retry_policy_rbac_and_admin_dashboard_visibility(): void
    {
        $this->actingAs($this->adminUser);

        // Register mapping and create deliberate price drift on legacy v1 product_attributes
        $this->syncService->registerChannelMapping('sku', $this->listing->id, $this->listing->seller_sku, CrossPlatformSyncService::CHANNEL_LEGACY_V1, (string)$this->legacyAttr->id);
        $this->legacyAttr->update(['price' => '1800.00']);

        $scanRes = $this->postJson('/api/v2/admin/integrations/reconciliation/scan')->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $scanRes->json('data.diffs_recorded'));

        $diff = IntegrationReconciliationRecord::where('entity_type', 'sku')
            ->where('mismatch_type', IntegrationReconciliationRecord::TYPE_PRICE_DRIFT)
            ->firstOrFail();

        // Admin pushes authoritative canonical price to resolve drift cleanly
        $this->postJson("/api/v2/admin/integrations/reconciliation/{$diff->id}/resolve", [
            'action' => 'reconcile_to_authoritative',
            'notes'  => 'Re-projected canonical SellerListing price to legacy attribute projection.',
        ])->assertStatus(200);

        $this->assertEquals(2200.00, (float)$this->legacyAttr->fresh()->price);
        $this->assertEquals(IntegrationReconciliationRecord::STATUS_RECONCILED, $diff->fresh()->status);

        // Safe retry policy: financial/payment sync event retry is blocked without explicit audited override
        $finEvent = IntegrationSyncEvent::create([
            'event_ref'       => 'SYNC-FIN-FAIL-01',
            'idempotency_key' => 'fin_fail_71_01',
            'direction'       => IntegrationSyncEvent::DIRECTION_OUTBOUND,
            'source_channel'  => CrossPlatformSyncService::CHANNEL_LARAVEL_BACKEND,
            'target_channel'  => CrossPlatformSyncService::CHANNEL_CRM_EXTERNAL,
            'entity_type'     => 'payment',
            'canonical_id'    => $this->order->id,
            'canonical_ref'   => $this->order->orderno,
            'action'          => 'payment_verified',
            'status'          => IntegrationSyncEvent::STATUS_FAILED,
            'attempts'        => 1,
            'max_attempts'    => 3,
        ]);

        $this->postJson("/api/v2/admin/integrations/events/{$finEvent->id}/retry")->assertStatus(422);

        // Safe non-financial product sync failure can be retried cleanly
        $prodEvent = IntegrationSyncEvent::create([
            'event_ref'       => 'SYNC-PROD-FAIL-01',
            'idempotency_key' => 'prod_fail_71_01',
            'direction'       => IntegrationSyncEvent::DIRECTION_OUTBOUND,
            'source_channel'  => CrossPlatformSyncService::CHANNEL_LARAVEL_BACKEND,
            'target_channel'  => CrossPlatformSyncService::CHANNEL_CRM_EXTERNAL,
            'entity_type'     => 'product',
            'canonical_id'    => $this->product->id,
            'canonical_ref'   => $this->product->slug,
            'action'          => 'product_updated',
            'status'          => IntegrationSyncEvent::STATUS_FAILED,
            'attempts'        => 1,
            'max_attempts'    => 3,
        ]);

        $this->postJson("/api/v2/admin/integrations/events/{$prodEvent->id}/retry")->assertStatus(200);
        $this->assertEquals(IntegrationSyncEvent::STATUS_SKIPPED_DISABLED, $prodEvent->fresh()->status);

        // Admin summary & rate-limit headers & web views
        $summary = $this->getJson('/api/v2/admin/integrations/summary');
        $summary->assertStatus(200)->assertHeader('X-RateLimit-Limit');
        $this->assertEquals('laravel_backend', $summary->json('data.authoritative_source'));

        $this->get('/admin/integrations/sync')->assertStatus(200)->assertSee('Cross-Platform Sync Outbox');
        $this->get('/admin/integrations/reconciliation')->assertStatus(200)->assertSee('Reconciliation Discrepancies');

        // RBAC / Seller & Agent Isolation
        $this->actingAs($this->sellerUser)->getJson('/api/v2/admin/integrations/summary')->assertStatus(403);
        $this->actingAs($this->agentUser)->getJson('/api/v2/admin/integrations/reconciliation')->assertStatus(403);
        $this->actingAs($this->sellerUser)->get('/admin/integrations/sync')->assertStatus(403);
    }
}

