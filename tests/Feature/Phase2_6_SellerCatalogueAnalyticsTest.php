<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductReviewHistory;
use App\Models\ProductReviews;
use App\Models\ProductValidationResult;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\SellerPermission;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SellerStaffPermission;
use App\Models\SubCatgory;
use App\Models\User;
use App\Services\SellerCatalogueAnalyticsService;
use Carbon\Carbon;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase2_6_SellerCatalogueAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;

    protected Category $category;
    protected SubCatgory $subcategory;
    protected Brand $brand;
    protected Attribute $requiredAttr;
    protected AttributeOption $attrOption1;

    protected Product $productA1;
    protected ProductVariant $variantA1;
    protected SellerListing $listingA1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SellerRolesAndPermissionsSeeder::class);

        // Admin
        $this->adminUser = User::factory()->create([
            'email' => 'admin@apnifactory.com',
            'name'  => 'System Super Admin',
        ]);

        // Seller A
        $this->sellerA = User::factory()->create([
            'email' => 'sellerA@apnifactory.test',
            'name'  => 'Alpha Seller Owner',
        ]);
        $this->companyA = Company::factory()->create([
            'user_id' => $this->sellerA->id,
            'name'    => 'Alpha Industrial Coatings Ltd',
        ]);

        // Seller B
        $this->sellerB = User::factory()->create([
            'email' => 'sellerB@apnifactory.test',
            'name'  => 'Beta Seller Owner',
        ]);
        $this->companyB = Company::factory()->create([
            'user_id' => $this->sellerB->id,
            'name'    => 'Beta Solutions Pvt Ltd',
        ]);

        // Category & Brand
        $this->category = Category::create([
            'name'   => 'Epoxy Coatings',
            'status' => 'Active',
        ]);

        $this->subcategory = SubCatgory::create([
            'name'        => 'High Build Epoxies',
            'category_id' => $this->category->id,
            'status'      => 'Active',
        ]);

        $this->brand = Brand::create([
            'name'        => 'AlphaCoat Pro',
            'category_id' => $this->category->id,
            'status'      => 'Active',
        ]);

        // Required Dynamic Category Attribute
        $this->requiredAttr = Attribute::create([
            'name'      => 'Gloss Finish RA',
            'code'      => 'gloss_finish_ra',
            'type'      => 'single_select',
            'status'    => 'Active',
        ]);

        $this->attrOption1 = AttributeOption::create([
            'attribute_id' => $this->requiredAttr->id,
            'label'        => 'High Gloss 90% RA',
            'value'        => 'High Gloss 90% RA',
        ]);

        CategoryAttribute::create([
            'category_id'  => $this->category->id,
            'attribute_id' => $this->requiredAttr->id,
            'is_required'  => true,
            'is_filterable'=> true,
        ]);

        // Product A1 for Seller A (Live on Marketplace: Active + Approved)
        $this->productA1 = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'category_id'     => $this->category->id,
            'subcategory_id'  => $this->subcategory->id,
            'brand_id'        => $this->brand->id,
            'name'            => 'AlphaCoat Epoxy Primer 20L',
            'title'           => 'Two-component zinc phosphate epoxy primer',
            'description'     => 'Heavy-duty industrial anticorrosive coating with superb adhesion.',
            'image'           => 'products/alpha_primer.jpg',
            'status'          => 'Active',
            'approval_status' => 'approved',
            'submitted_at'    => Carbon::now()->subDays(5),
            'reviewed_at'     => Carbon::now()->subDays(4),
        ]);

        ProductAttributeValue::create([
            'product_id'          => $this->productA1->id,
            'attribute_id'        => $this->requiredAttr->id,
            'attribute_option_id' => $this->attrOption1->id,
        ]);

        $this->variantA1 = ProductVariant::create([
            'product_id'   => $this->productA1->id,
            'variant_code' => 'ALP-EP-20L-HG',
            'variant_sku'  => 'SKU-ALP-EP-20L-HG',
            'title'        => '20 Litre Drum High Gloss',
            'status'       => 'Active',
        ]);

        $this->listingA1 = SellerListing::create([
            'company_id'         => $this->companyA->id,
            'product_id'         => $this->productA1->id,
            'product_variant_id' => $this->variantA1->id,
            'seller_sku'         => 'SKU-ALP-EP-20L',
            'price'              => 5500.00,
            'status'             => 'Active',
        ]);
    }

    /**
     * Helper to safely create unique products.
     */
    protected function createProduct(array $attributes = []): Product
    {
        $uniqueId = uniqid();
        $defaults = [
            'name'            => 'Product ' . $uniqueId,
            'slug'            => 'prod-' . $uniqueId,
            'status'          => 'Active',
            'approval_status' => 'draft',
            'category_id'     => $this->category->id,
        ];

        return Product::create(array_merge($defaults, $attributes));
    }

    /**
     * Helper to create seller staff member.
     */
    protected function createStaff(Company $company, bool $grantProductsView = true): User
    {
        $staffUser = User::factory()->create([
            'email' => 'staff.' . uniqid() . '@seller.test',
            'name'  => 'Staff Member',
        ]);

        $role = SellerRole::where('name', $grantProductsView ? 'product_manager' : 'readonly_staff')->first()
            ?? SellerRole::first();

        $staff = SellerStaff::create([
            'company_id'     => $company->id,
            'user_id'        => $staffUser->id,
            'seller_role_id' => $role?->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        if (!$grantProductsView) {
            // Explicitly deny products.view
            $perm = SellerPermission::where('name', 'products.view')->first();
            if ($perm) {
                SellerStaffPermission::create([
                    'seller_staff_id'      => $staff->id,
                    'seller_permission_id' => $perm->id,
                    'granted'              => false,
                ]);
            }
        }

        return $staffUser;
    }

    /**
     * 1. Test Seller can retrieve full catalogue analytics dashboard payload.
     */
    public function test_seller_can_retrieve_full_catalogue_analytics_dashboard_payload(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->getJson('/api/v2/seller/catalogue/analytics/dashboard');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'company' => ['id', 'name', 'user_id'],
                    'date_range' => ['period', 'label', 'start_date', 'end_date'],
                    'summary' => [
                        'total_products',
                        'active_products',
                        'inactive_products',
                        'draft_products',
                        'pending_approval_products',
                        'approved_products',
                        'rejected_products',
                        'corrections_required_products',
                        'live_marketplace_products',
                        'total_variants',
                        'total_seller_skus',
                        'active_seller_skus',
                        'inactive_seller_skus',
                        'archived_seller_skus',
                    ],
                    'status_distribution' => [
                        'approval_distribution',
                        'approval_percentages',
                        'active_vs_inactive',
                        'reconciled_total',
                    ],
                    'catalogue_health' => [
                        'total_assessed',
                        'health_score',
                        'missing_images_count',
                        'missing_descriptions_count',
                        'missing_required_attributes_count',
                        'healthy_products_count',
                        'needs_attention_count',
                    ],
                    'approval_performance' => [
                        'products_submitted',
                        'products_approved',
                        'products_rejected',
                        'average_turnaround_hours',
                        'average_turnaround_formatted',
                    ],
                    'product_performance' => [
                        'total_orders',
                        'units_sold',
                        'sales_value',
                        'currency',
                        'product_impressions' => ['is_available', 'status', 'description'],
                        'product_views' => ['is_available', 'status', 'description'],
                        'product_enquiries' => ['is_available', 'status', 'description'],
                    ],
                    'category_insights',
                    'brand_insights',
                    'generated_at',
                ],
            ]);
    }

    /**
     * 2. Test summary counts accurately reflect catalogue without double-counting variants.
     */
    public function test_summary_counts_accurately_reflect_catalogue_without_double_counting_variants(): void
    {
        // Add draft product with 2 variants for Seller A
        $draftProduct = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'category_id'     => $this->category->id,
            'name'            => 'Draft Enamel Paint',
            'status'          => 'Deactive',
            'approval_status' => 'draft',
        ]);

        ProductVariant::create([
            'product_id'   => $draftProduct->id,
            'variant_code' => 'DFT-V1',
            'variant_sku'  => 'DFT-SKU-V1-' . uniqid(),
            'title'        => 'Draft Variant 1',
            'status'       => 'Active',
        ]);
        ProductVariant::create([
            'product_id'   => $draftProduct->id,
            'variant_code' => 'DFT-V2',
            'variant_sku'  => 'DFT-SKU-V2-' . uniqid(),
            'title'        => 'Draft Variant 2',
            'status'       => 'Active',
        ]);

        // Add pending review product for Seller A
        $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'category_id'     => $this->category->id,
            'name'            => 'Pending Review Thinner',
            'status'          => 'Active',
            'approval_status' => 'pending_review',
        ]);

        // Add corrections requested product for Seller A
        $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'category_id'     => $this->category->id,
            'name'            => 'Corrections Required Primer',
            'status'          => 'Deactive',
            'approval_status' => 'corrections_requested',
        ]);

        Sanctum::actingAs($this->sellerA);

        $response = $this->getJson('/api/v2/seller/catalogue/analytics/summary');

        $response->assertOk()
            ->assertJsonPath('status', true)
            // Seller A has 4 products total (1 approved, 1 draft, 1 pending, 1 corrections)
            ->assertJsonPath('data.total_products', 4)
            ->assertJsonPath('data.approved_products', 1)
            ->assertJsonPath('data.draft_products', 1)
            ->assertJsonPath('data.pending_approval_products', 1)
            ->assertJsonPath('data.corrections_required_products', 1)
            // Live on marketplace = Approved AND Active = 1
            ->assertJsonPath('data.live_marketplace_products', 1)
            // Total variants = 1 (from setup) + 2 (from draft) = 3
            ->assertJsonPath('data.total_variants', 3)
            ->assertJsonPath('data.total_seller_skus', 1);
    }

    /**
     * 3. Multi-tenant data isolation: Seller A cannot see Seller B's data.
     */
    public function test_multi_tenant_isolation_seller_a_cannot_see_seller_b_data(): void
    {
        // Create 3 products for Seller B
        for ($i = 1; $i <= 3; $i++) {
            $this->createProduct([
                'user_id'         => $this->sellerB->id,
                'category_id'     => $this->category->id,
                'name'            => "Beta Product {$i}",
                'status'          => 'Active',
                'approval_status' => 'approved',
            ]);
        }

        // Add order for Seller B
        Order::create([
            'orderno'     => 'ORD-BETA-001',
            'user_id'     => $this->sellerB->id,
            'grandtotal'  => 25000,
            'created_at'  => now(),
        ]);

        // Authenticate as Seller A
        Sanctum::actingAs($this->sellerA);
        $resA = $this->getJson('/api/v2/seller/catalogue/analytics/summary');
        $resA->assertOk()->assertJsonPath('data.total_products', 1);

        $perfA = $this->getJson('/api/v2/seller/catalogue/analytics/product-performance');
        $perfA->assertOk()->assertJsonPath('data.total_orders', 0);

        // Authenticate as Seller B
        Sanctum::actingAs($this->sellerB);
        $resB = $this->getJson('/api/v2/seller/catalogue/analytics/summary');
        $resB->assertOk()->assertJsonPath('data.total_products', 3);

        $perfB = $this->getJson('/api/v2/seller/catalogue/analytics/product-performance');
        $perfB->assertOk()
            ->assertJsonPath('data.total_orders', 1);
        $this->assertEquals(25000.0, (float)$perfB->json('data.sales_value'));
    }

    /**
     * 4. Status distribution reconciles with summary counts.
     */
    public function test_status_distribution_strictly_reconciles_with_summary_counts(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->getJson('/api/v2/seller/catalogue/analytics/status-distribution');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.reconciled_total', 1)
            ->assertJsonPath('data.approval_distribution.approved', 1)
            ->assertJsonPath('data.approval_distribution.draft', 0)
            ->assertJsonPath('data.active_vs_inactive.active', 1)
            ->assertJsonPath('data.active_vs_inactive.inactive', 0);

        $this->assertEquals(100.0, (float)$response->json('data.approval_percentages.approved'));
    }

    /**
     * 5. Catalogue completeness & health metrics scoring.
     */
    public function test_catalogue_health_and_completeness_scoring(): void
    {
        // Add a defective product for Seller A: missing image, missing description, missing required attribute
        $defective = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'category_id'     => $this->category->id,
            'name'            => 'Defective Epoxy Coating',
            'title'           => '',
            'description'     => '',
            'image'           => null,
            'status'          => 'Deactive',
            'approval_status' => 'draft',
        ]);

        // Add validation failure result
        ProductValidationResult::create([
            'product_id'     => $defective->id,
            'company_id'     => $this->companyA->id,
            'status'         => 'failed',
            'error_count'    => 2,
            'warning_count'  => 1,
            'failed_checks'  => ['image' => 'Primary product image is required.'],
        ]);

        Sanctum::actingAs($this->sellerA);

        $response = $this->getJson('/api/v2/seller/catalogue/analytics/health');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.total_assessed', 2)
            ->assertJsonPath('data.missing_images_count', 1)
            ->assertJsonPath('data.missing_descriptions_count', 1)
            ->assertJsonPath('data.missing_required_attributes_count', 1)
            ->assertJsonPath('data.products_with_validation_errors', 1)
            ->assertJsonPath('data.healthy_products_count', 1);

        $healthScore = (float)$response->json('data.health_score');
        $this->assertLessThan(100.0, $healthScore);
        $this->assertGreaterThan(0.0, $healthScore);
    }

    /**
     * 6. Empty catalogue returns zero counts and 100% health score without division by zero.
     */
    public function test_empty_catalogue_returns_zero_counts_and_safe_health_score_without_division_by_zero(): void
    {
        // Fresh seller with 0 products
        $emptyUser = User::factory()->create(['email' => 'empty@seller.test']);
        Company::factory()->create([
            'user_id' => $emptyUser->id,
            'name'    => 'Empty Coatings Corp',
        ]);

        Sanctum::actingAs($emptyUser);

        $response = $this->getJson('/api/v2/seller/catalogue/analytics/dashboard');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.summary.total_products', 0)
            ->assertJsonPath('data.summary.active_products', 0)
            ->assertJsonPath('data.summary.live_marketplace_products', 0)
            ->assertJsonPath('data.catalogue_health.total_assessed', 0);
        $this->assertEquals(100.0, (float)$response->json('data.catalogue_health.health_score'));
    }

    /**
     * 7. Approval turnaround calculation and history trends.
     */
    public function test_approval_performance_calculates_turnaround_time_and_trends(): void
    {
        // Record review history events for Company A
        ProductReviewHistory::create([
            'company_id'      => $this->companyA->id,
            'product_id'      => $this->productA1->id,
            'user_id'         => $this->sellerA->id,
            'action'          => 'submitted',
            'previous_status' => 'draft',
            'new_status'      => 'pending_review',
            'created_at'      => Carbon::now()->subDays(5),
        ]);

        ProductReviewHistory::create([
            'company_id'      => $this->companyA->id,
            'product_id'      => $this->productA1->id,
            'user_id'         => $this->adminUser->id,
            'action'          => 'approved',
            'previous_status' => 'pending_review',
            'new_status'      => 'approved',
            'created_at'      => Carbon::now()->subDays(4),
        ]);

        Sanctum::actingAs($this->sellerA);

        $response = $this->getJson('/api/v2/seller/catalogue/analytics/approval-performance?period=last_7_days');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.products_submitted', 1)
            ->assertJsonPath('data.products_approved', 1)
            ->assertJsonPath('data.has_turnaround_data', true);

        // Turnaround between subDays(5) and subDays(4) is 24 hours (1.0 days)
        $avgHours = (float)$response->json('data.average_turnaround_hours');
        $this->assertEquals(24.0, $avgHours);
        $this->assertStringContainsString('1 days', $response->json('data.average_turnaround_formatted'));
    }

    /**
     * 8. Product commercial performance tracks real orders, units sold, and cart count.
     */
    public function test_product_performance_tracks_real_orders_revenue_units_and_carts(): void
    {
        // Create an Order for Seller A
        $order = Order::create([
            'orderno'     => 'ORD-REAL-101',
            'user_id'     => $this->sellerA->id,
            'grandtotal'  => 12500.00,
            'netamount'   => 12500.00,
            'created_at'  => Carbon::now()->subDays(2),
        ]);

        // Insert orderdetail line item with quantity in attribute JSON
        DB::table('orderdetail')->insert([
            'orderno'      => 'ORD-REAL-101',
            'order_id'     => $order->id,
            'product_id'   => $this->productA1->id,
            'productname'  => $this->productA1->name,
            'attribute'    => json_encode(['qty' => 3, 'price' => 5500]),
            'created_at'   => Carbon::now()->subDays(2),
            'updated_at'   => Carbon::now()->subDays(2),
        ]);

        // Insert active cart item
        DB::table('cart')->insert([
            'product_id'   => $this->productA1->id,
            'company_id'   => $this->companyA->id,
            'customer_id'  => 999,
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);

        // Insert product review
        ProductReviews::create([
            'product_id'   => $this->productA1->id,
            'customer_id'  => 999,
            'rating'       => 5,
            'review'       => 'Superb primer quality and coverage.',
            'status'       => 'Active',
        ]);

        Sanctum::actingAs($this->sellerA);

        $response = $this->getJson('/api/v2/seller/catalogue/analytics/product-performance?period=last_30_days');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.total_orders', 1)
            ->assertJsonPath('data.units_sold', 3)
            ->assertJsonPath('data.active_cart_count', 1)
            ->assertJsonPath('data.product_reviews.total_reviews', 1);

        $this->assertEquals(12500.00, (float)$response->json('data.sales_value'));
        $this->assertEquals(5.0, (float)$response->json('data.product_reviews.average_rating'));
    }

    /**
     * 9. Untracked metrics are explicitly documented as unavailable (no fabricated zeros).
     */
    public function test_untracked_metrics_are_explicitly_documented_as_unavailable(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->getJson('/api/v2/seller/catalogue/analytics/product-performance');

        $response->assertOk()
            ->assertJsonPath('data.product_impressions.is_available', false)
            ->assertJsonPath('data.product_impressions.status', 'unavailable')
            ->assertJsonPath('data.product_views.is_available', false)
            ->assertJsonPath('data.product_views.status', 'unavailable')
            ->assertJsonPath('data.product_enquiries.is_available', false)
            ->assertJsonPath('data.product_enquiries.status', 'unavailable');
    }

    /**
     * 10. Date range filtering limits events and records within requested window.
     */
    public function test_date_range_filtering_filters_events_properly(): void
    {
        // Old order (45 days ago)
        Order::create([
            'orderno'     => 'ORD-OLD-001',
            'user_id'     => $this->sellerA->id,
            'grandtotal'  => 5000,
            'created_at'  => Carbon::now()->subDays(45),
        ]);

        // Recent order (2 days ago)
        Order::create([
            'orderno'     => 'ORD-RECENT-001',
            'user_id'     => $this->sellerA->id,
            'grandtotal'  => 7000,
            'created_at'  => Carbon::now()->subDays(2),
        ]);

        Sanctum::actingAs($this->sellerA);

        // Period: Last 7 Days (should only include the recent order)
        $res7 = $this->getJson('/api/v2/seller/catalogue/analytics/product-performance?period=last_7_days');
        $res7->assertOk()
            ->assertJsonPath('data.total_orders', 1);
        $this->assertEquals(7000.0, (float)$res7->json('data.sales_value'));

        // Period: Last 90 Days (should include both orders)
        $res90 = $this->getJson('/api/v2/seller/catalogue/analytics/product-performance?period=last_90_days');
        $res90->assertOk()
            ->assertJsonPath('data.total_orders', 2);
        $this->assertEquals(12000.0, (float)$res90->json('data.sales_value'));

        // Period: Custom Range (between 40 and 50 days ago)
        $dateFrom = Carbon::now()->subDays(50)->format('Y-m-d');
        $dateTo = Carbon::now()->subDays(40)->format('Y-m-d');
        $resCustom = $this->getJson("/api/v2/seller/catalogue/analytics/product-performance?period=custom&date_from={$dateFrom}&date_to={$dateTo}");
        $resCustom->assertOk()
            ->assertJsonPath('data.total_orders', 1);
        $this->assertEquals(5000.0, (float)$resCustom->json('data.sales_value'));
    }

    /**
     * 11. Staff permission enforcement: products.view required.
     */
    public function test_staff_permissions_enforced_products_view_required(): void
    {
        // Staff without products.view
        $staffWithout = $this->createStaff($this->companyA, false);
        Sanctum::actingAs($staffWithout);

        $resDenied = $this->getJson('/api/v2/seller/catalogue/analytics/dashboard');
        $resDenied->assertStatus(403);

        // Staff with products.view
        $staffWith = $this->createStaff($this->companyA, true);
        Sanctum::actingAs($staffWith);

        $resAllowed = $this->getJson('/api/v2/seller/catalogue/analytics/dashboard');
        $resAllowed->assertOk()->assertJsonPath('status', true);
    }

    /**
     * 12. Cache invalidation on catalogue updates.
     */
    public function test_cache_invalidation_works(): void
    {
        $service = app(SellerCatalogueAnalyticsService::class);

        // First call caches the summary
        $initial = $service->getCatalogueSummary($this->companyA);
        $this->assertEquals(1, $initial['total_products']);

        // Insert new product directly in DB
        $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'category_id'     => $this->category->id,
            'name'            => 'Cached Test Coating',
            'status'          => 'Active',
            'approval_status' => 'draft',
        ]);

        // Second call without cache clear returns cached count
        $cached = $service->getCatalogueSummary($this->companyA);
        $this->assertEquals(1, $cached['total_products']);

        // Invalidate cache
        $service->invalidateSellerCache($this->companyA->id);

        // Fresh call returns updated count
        $fresh = $service->getCatalogueSummary($this->companyA);
        $this->assertEquals(2, $fresh['total_products']);
    }

    /**
     * 13. Web analytics dashboard route renders HTTP 200 with required views.
     */
    public function test_web_analytics_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->sellerA)
            ->get('/seller/catalogue/analytics');

        $response->assertOk()
            ->assertSee('Catalogue Analytics')
            ->assertSee('Catalogue Health Score')
            ->assertSee('Live on Marketplace')
            ->assertSee('Diagnostic Breakdown')
            ->assertSee('Enterprise Data Integrity Notice');
    }
}
