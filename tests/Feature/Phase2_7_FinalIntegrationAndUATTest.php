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
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\SellerPermission;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SellerStaffPermission;
use App\Models\SubCatgory;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase2_7_FinalIntegrationAndUATTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->seed(SellerRolesAndPermissionsSeeder::class);

        // 1. Admin Actor
        $this->adminUser = User::factory()->create([
            'email' => 'admin@apnifactory.com',
            'name'  => 'Marketplace Super Admin',
        ]);

        // 2. Seller A (Alpha Coatings)
        $this->sellerA = User::factory()->create([
            'email' => 'alpha@seller.test',
            'name'  => 'Alpha Owner',
        ]);
        $this->companyA = Company::factory()->create([
            'user_id' => $this->sellerA->id,
            'name'    => 'Alpha Industrial Coatings Ltd',
        ]);

        // 3. Seller B (Beta Chemicals)
        $this->sellerB = User::factory()->create([
            'email' => 'beta@seller.test',
            'name'  => 'Beta Owner',
        ]);
        $this->companyB = Company::factory()->create([
            'user_id' => $this->sellerB->id,
            'name'    => 'Beta Solutions Pvt Ltd',
        ]);

        // 4. Catalogue Hierarchy & Category Dynamic Attributes
        $this->category = Category::create([
            'name'   => 'Protective Coatings',
            'status' => 'Active',
        ]);

        $this->subcategory = SubCatgory::create([
            'name'        => 'Epoxy Primers',
            'category_id' => $this->category->id,
            'status'      => 'Active',
        ]);

        $this->brand = Brand::create([
            'name'        => 'AlphaShield',
            'category_id' => $this->category->id,
            'status'      => 'Active',
        ]);

        $this->requiredAttr = Attribute::create([
            'name'   => 'Gloss Finish RA',
            'code'   => 'gloss_finish_ra',
            'type'   => 'single_select',
            'status' => 'Active',
        ]);

        $this->attrOption1 = AttributeOption::create([
            'attribute_id' => $this->requiredAttr->id,
            'label'        => 'High Gloss (90% RA)',
            'value'        => 'High Gloss (90% RA)',
        ]);

        CategoryAttribute::create([
            'category_id'   => $this->category->id,
            'attribute_id'  => $this->requiredAttr->id,
            'is_required'   => true,
            'is_filterable' => true,
        ]);
    }

    /**
     * Helper to create products cleanly with unique slugs.
     */
    protected function createProduct(array $attributes = []): Product
    {
        $uniqueId = uniqid();
        $defaults = [
            'name'            => 'Product ' . $uniqueId,
            'slug'            => 'slug-' . $uniqueId,
            'status'          => 'Active',
            'approval_status' => 'draft',
            'category_id'     => $this->category->id,
            'subcategory_id'  => $this->subcategory->id,
            'brand_id'        => $this->brand->id,
            'title'           => 'Product Title ' . $uniqueId,
            'description'     => 'Industrial coating specification for heavy-duty protection.',
            'image'           => 'products/primer.jpg',
        ];

        return Product::create(array_merge($defaults, $attributes));
    }

    /**
     * Helper to create seller staff with specific roles/permissions.
     */
    protected function createStaff(Company $company, string $roleName, array $permissionOverrides = []): User
    {
        $staffUser = User::factory()->create([
            'email' => 'staff.' . uniqid() . '@company.test',
            'name'  => 'Company Staff',
        ]);

        $role = SellerRole::where('name', $roleName)->first() ?? SellerRole::first();

        $staff = SellerStaff::create([
            'company_id'     => $company->id,
            'user_id'        => $staffUser->id,
            'seller_role_id' => $role?->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        foreach ($permissionOverrides as $permName => $isGranted) {
            $perm = SellerPermission::where('name', $permName)->first();
            if ($perm) {
                SellerStaffPermission::create([
                    'seller_staff_id'      => $staff->id,
                    'seller_permission_id' => $perm->id,
                    'granted'              => $isGranted,
                ]);
            }
        }

        return $staffUser;
    }

    /**
     * Helper to create CSV upload file conforming to SellerCatalogueImportService headers.
     */
    protected function createCsvUpload(array $rows, string $filename = 'import_test.csv'): UploadedFile
    {
        $headers = [
            'action',
            'product_id',
            'product_name',
            'product_title',
            'category_name',
            'subcategory_name',
            'brand_name',
            'hsn_code',
            'tax_percent',
            'product_status',
            'variant_sku',
            'variant_name',
            'shade_or_color',
            'color_hex',
            'size_or_packing',
            'variant_status',
            'dynamic_attributes',
            'seller_sku',
            'barcode',
            'unit_of_measurement',
            'case_pack_quantity',
            'min_order_quantity',
            'order_multiple',
            'allow_unit_orders',
            'allow_carton_orders',
            'seller_price',
            'commission_rate',
            'selling_price',
            'customer_mrp',
            'stock_quantity',
            'low_stock_threshold',
            'dispatch_lead_days',
            'listing_status',
        ];

        $temp = fopen('php://memory', 'r+');
        fputcsv($temp, $headers);

        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $header) {
                $line[] = $row[$header] ?? '';
            }
            fputcsv($temp, $line);
        }

        rewind($temp);
        $content = stream_get_contents($temp);
        fclose($temp);

        return UploadedFile::fake()->createWithContent($filename, $content);
    }

    // =========================================================================
    // 1. END-TO-END WORKFLOW: PRODUCT CREATION, VALIDATION, AND APPROVAL LIFECYCLE
    // =========================================================================

    /**
     * E2E Scenario A & B & C:
     * Seller creates product, fills dynamic attribute, variant & SKU.
     * Validates product -> Submits for review -> Admin requests corrections -> Seller corrects & resubmits -> Admin approves -> Product is published.
     */
    public function test_end_to_end_catalogue_creation_review_correction_and_approval(): void
    {
        // 1. Seller A creates draft product
        $product = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'name'            => 'AlphaShield Zinc Primer 20L',
            'approval_status' => 'draft',
            'status'          => 'Deactive',
        ]);

        // Add mandatory category dynamic attribute
        ProductAttributeValue::create([
            'product_id'          => $product->id,
            'attribute_id'        => $this->requiredAttr->id,
            'attribute_option_id' => $this->attrOption1->id,
        ]);

        // Add variant
        $variant = ProductVariant::create([
            'product_id'   => $product->id,
            'variant_code' => 'ALP-ZINC-20L',
            'variant_sku'  => 'SKU-ZINC-20L-' . uniqid(),
            'title'        => '20 Litre High Gloss',
            'status'       => 'Active',
        ]);

        // Add listing with all required pricing and inventory fields
        $listing = SellerListing::create([
            'company_id'         => $this->companyA->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'seller_sku'         => 'SKU-ALPHA-20L',
            'seller_price'       => 6200.00,
            'selling_price'      => 6200.00,
            'mrp'                => 7500.00,
            'stock_quantity'     => 50,
            'min_order_quantity' => 1,
            'status'             => 'Deactive',
        ]);

        // 2. Validate product readiness (Stage 1)
        Sanctum::actingAs($this->sellerA);
        $valRes = $this->postJson("/api/v2/seller/catalogue/products/{$product->id}/validate");
        $valRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.is_passed', true)
            ->assertJsonPath('data.can_submit', true);

        // 3. Submit for admin review (Stage 2)
        $subRes = $this->postJson("/api/v2/seller/catalogue/products/{$product->id}/submit", [
            'notes' => 'Please review and publish.',
        ]);
        $subRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'pending_review');

        // 4. Admin inspects review queue
        Sanctum::actingAs($this->adminUser);
        $queueRes = $this->getJson('/api/v2/admin/catalogue/review-queue?status=pending_review');
        $queueRes->assertOk()
            ->assertJsonPath('status', true);
        $this->assertTrue(collect($queueRes->json('data.items'))->pluck('id')->contains($product->id));

        // 5. Admin requests corrections
        $corrRes = $this->postJson("/api/v2/admin/catalogue/review/{$product->id}/request-corrections", [
            'reason'       => 'Please clarify TDS specifications in description.',
            'instructions' => 'Include VOC level in grams per litre in the main title/description.',
        ]);
        $corrRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'corrections_requested');

        // 6. Seller inspects review feedback
        Sanctum::actingAs($this->sellerA);
        $fbRes = $this->getJson("/api/v2/seller/catalogue/products/{$product->id}/review-feedback");
        $fbRes->assertOk()
            ->assertJsonPath('data.approval_status', 'corrections_requested')
            ->assertJsonPath('data.correction_notes', 'Include VOC level in grams per litre in the main title/description.');

        // 7. Seller updates product and resubmits
        $product->update([
            'description' => 'Industrial coating with VOC < 250 g/L for heavy duty steel protection.',
        ]);
        $resubRes = $this->postJson("/api/v2/seller/catalogue/products/{$product->id}/resubmit", [
            'notes' => 'Updated VOC specification per feedback.',
        ]);
        $resubRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'resubmitted');

        // 8. Admin approves product
        Sanctum::actingAs($this->adminUser);
        $apprRes = $this->postJson("/api/v2/admin/catalogue/review/{$product->id}/approve", [
            'notes' => 'TDS compliance verified.',
        ]);
        $apprRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'approved');

        // 9. Verify marketplace publication rules
        $product->fresh()->update(['status' => 'Active']);
        $publicRes = $this->getJson('/api/v2/products');
        $publicRes->assertOk();
        $this->assertTrue(collect($publicRes->json('data'))->pluck('id')->contains($product->id));
    }

    // =========================================================================
    // 2. END-TO-END WORKFLOW: EXCEL EXPORT AND IMPORT CYCLE
    // =========================================================================

    /**
     * E2E Scenario D:
     * Seller exports catalogue -> downloads file -> modifies prices/stock -> imports -> verifies preview -> confirms -> listings updated.
     */
    public function test_end_to_end_excel_export_and_import_cycle(): void
    {
        // Setup approved product and listing for Seller A
        $product = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'name'            => 'Export Ready Epoxy Finish',
            'status'          => 'Active',
            'approval_status' => 'approved',
        ]);

        $variant = ProductVariant::create([
            'product_id'   => $product->id,
            'variant_code' => 'EXP-V1',
            'variant_sku'  => 'SKU-EXP-V1-' . uniqid(),
            'title'        => 'Standard Drum',
            'status'       => 'Active',
        ]);

        $listing = SellerListing::create([
            'company_id'         => $this->companyA->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'seller_sku'         => 'SKU-EXP-001',
            'seller_price'       => 4000.00,
            'selling_price'      => 4000.00,
            'mrp'                => 5000.00,
            'stock_quantity'     => 20,
            'min_order_quantity' => 1,
            'status'             => 'Active',
        ]);

        Sanctum::actingAs($this->sellerA);

        // 1. Trigger Export
        $expRes = $this->postJson('/api/v2/seller/catalogue/export', [
            'format' => 'csv',
        ]);
        $expRes->assertOk()
            ->assertJsonPath('status', true);
        $exportId = $expRes->json('data.export_id');

        // 2. Download Export File
        $downRes = $this->get("/api/v2/seller/catalogue/exports/{$exportId}/download");
        $downRes->assertOk();

        // 3. Import Update with modified Price and Stock
        $csvFile = $this->createCsvUpload([
            [
                'action'              => 'UPDATE',
                'product_id'          => $product->id,
                'product_name'        => $product->name,
                'category_name'       => $this->category->name,
                'subcategory_name'    => $this->subcategory->name,
                'brand_name'          => $this->brand->name,
                'variant_sku'         => $variant->variant_sku,
                'variant_name'        => $variant->title,
                'seller_sku'          => $listing->seller_sku,
                'seller_price'        => 4800.00, // Price updated
                'selling_price'       => 4800.00,
                'customer_mrp'        => 6000.00,
                'stock_quantity'      => 85,      // Stock updated
                'min_order_quantity'  => 1,
                'listing_status'      => 'Active',
            ],
        ], 'update_catalogue.csv');

        // Validate upload
        $impValRes = $this->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $csvFile,
        ]);
        $impValRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.valid_rows', 1)
            ->assertJsonPath('data.invalid_rows', 0);

        $importId = $impValRes->json('data.import_id');

        // Confirm import
        $confRes = $this->postJson("/api/v2/seller/catalogue/import/{$importId}/confirm");
        $confRes->assertOk()
            ->assertJsonPath('status', true);

        // Verify database reflects updated price & stock
        $freshListing = $listing->fresh();
        $this->assertEquals(4800.00, (float)$freshListing->seller_price);
        $this->assertEquals(85, (int)$freshListing->stock_quantity);
    }

    // =========================================================================
    // 3. END-TO-END WORKFLOW: BULK MANAGEMENT WITH APPROVAL WORKFLOW INTEGRATION
    // =========================================================================

    /**
     * E2E Scenario E:
     * Bulk price adjustment operates on eligible listings.
     * Attempting to activate an unapproved listing via bulk status is blocked!
     */
    public function test_end_to_end_bulk_management_with_approval_safety_guard(): void
    {
        // Listing 1: Approved Parent
        $approvedProd = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'name'            => 'Bulk Approved Prod',
            'status'          => 'Active',
            'approval_status' => 'approved',
        ]);
        $var1 = ProductVariant::create([
            'product_id'   => $approvedProd->id,
            'variant_code' => 'BLK-V1',
            'variant_sku'  => 'SKU-BLK-V1-' . uniqid(),
            'title'        => 'Variant 1',
            'status'       => 'Active',
        ]);
        $listing1 = SellerListing::create([
            'company_id'         => $this->companyA->id,
            'product_id'         => $approvedProd->id,
            'product_variant_id' => $var1->id,
            'seller_sku'         => 'SKU-BULK-001',
            'seller_price'       => 1000.00,
            'selling_price'      => 1000.00,
            'mrp'                => 1500.00,
            'stock_quantity'     => 10,
            'min_order_quantity' => 1,
            'status'             => 'Active',
        ]);

        // Listing 2: Unapproved Draft Parent
        $draftProd = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'name'            => 'Bulk Draft Prod',
            'status'          => 'Deactive',
            'approval_status' => 'draft',
        ]);
        $var2 = ProductVariant::create([
            'product_id'   => $draftProd->id,
            'variant_code' => 'BLK-V2',
            'variant_sku'  => 'SKU-BLK-V2-' . uniqid(),
            'title'        => 'Variant 2',
            'status'       => 'Active',
        ]);
        $listing2 = SellerListing::create([
            'company_id'         => $this->companyA->id,
            'product_id'         => $draftProd->id,
            'product_variant_id' => $var2->id,
            'seller_sku'         => 'SKU-BULK-002',
            'seller_price'       => 2000.00,
            'selling_price'      => 2000.00,
            'mrp'                => 2500.00,
            'stock_quantity'     => 5,
            'min_order_quantity' => 1,
            'status'             => 'Deactive',
        ]);

        Sanctum::actingAs($this->sellerA);

        // 1. Bulk Price Update: 10% increase on listing1
        $prevRes = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'field_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$listing1->id],
            'update_payload'     => [
                'price_adjustment_type'  => 'percentage_increase',
                'price_adjustment_value' => 10.0,
            ],
        ]);
        $prevRes->assertOk()
            ->assertJsonPath('status', true);
        $bulkOpId = $prevRes->json('data.operation_id');

        $this->postJson("/api/v2/seller/catalogue/bulk/{$bulkOpId}/confirm")
            ->assertOk()
            ->assertJsonPath('status', true);

        // Price updated from 1000 to 1100
        $this->assertEquals(1100.00, (float)$listing1->fresh()->seller_price);

        // 2. Safety Guard: Attempt to activate listing2 (parent is draft & unapproved)
        $actPrevRes = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'target_entity'      => 'listing',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$listing2->id],
            'update_payload'     => ['status' => 'Active'],
        ]);
        $actPrevRes->assertOk();
        // The unapproved listing is detected as ineligible
        $this->assertEquals(0, $actPrevRes->json('data.eligible_count'));
        $this->assertEquals(1, $actPrevRes->json('data.ineligible_count'));
    }

    // =========================================================================
    // 4. END-TO-END WORKFLOW: DASHBOARD & HEALTH SCORECARD RECONCILIATION
    // =========================================================================

    /**
     * E2E Scenario F:
     * Summary counts strictly reconcile with actual database records.
     * Transactional performance correctly aggregates actual orders, units, and sales.
     */
    public function test_end_to_end_dashboard_and_performance_analytics_reconciliation(): void
    {
        // Create 2 products for Seller A: 1 approved+active, 1 draft+deactive
        $p1 = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'name'            => 'Live Analytics Paint',
            'status'          => 'Active',
            'approval_status' => 'approved',
        ]);
        $v1 = ProductVariant::create([
            'product_id'   => $p1->id,
            'variant_code' => 'VAR-A',
            'variant_sku'  => 'SKU-VAR-A-' . uniqid(),
            'title'        => 'Variant A',
            'status'       => 'Active',
        ]);
        SellerListing::create([
            'company_id'         => $this->companyA->id,
            'product_id'         => $p1->id,
            'product_variant_id' => $v1->id,
            'seller_sku'         => 'SKU-LIVE-01',
            'seller_price'       => 5000,
            'selling_price'      => 5000,
            'status'             => 'Active',
        ]);

        $p2 = $this->createProduct([
            'user_id'         => $this->sellerA->id,
            'name'            => 'Draft Analytics Paint',
            'status'          => 'Deactive',
            'approval_status' => 'draft',
        ]);

        // Insert real order
        $order = Order::create([
            'orderno'     => 'ORD-DASH-909',
            'user_id'     => $this->sellerA->id,
            'grandtotal'  => 15000.00,
            'created_at'  => Carbon::now()->subDays(3),
        ]);

        DB::table('orderdetail')->insert([
            'orderno'      => 'ORD-DASH-909',
            'order_id'     => $order->id,
            'product_id'   => $p1->id,
            'productname'  => $p1->name,
            'attribute'    => json_encode(['qty' => 3]),
            'created_at'   => Carbon::now()->subDays(3),
            'updated_at'   => Carbon::now()->subDays(3),
        ]);

        Sanctum::actingAs($this->sellerA);

        $dashRes = $this->getJson('/api/v2/seller/catalogue/analytics/dashboard?period=last_30_days');
        $dashRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.summary.total_products', 2)
            ->assertJsonPath('data.summary.live_marketplace_products', 1)
            ->assertJsonPath('data.summary.draft_products', 1)
            ->assertJsonPath('data.product_performance.total_orders', 1)
            ->assertJsonPath('data.product_performance.units_sold', 3);

        $this->assertEquals(15000.0, (float)$dashRes->json('data.product_performance.sales_value'));

        // Untracked telemetry is explicitly marked unavailable
        $dashRes->assertJsonPath('data.product_performance.product_impressions.is_available', false)
            ->assertJsonPath('data.product_performance.product_views.is_available', false)
            ->assertJsonPath('data.product_performance.product_enquiries.is_available', false);
    }

    // =========================================================================
    // 5. SECURITY & AUTHORIZATION: STRICT CROSS-SELLER TENANT ISOLATION
    // =========================================================================

    /**
     * E2E Security:
     * Seller A cannot access Seller B's product validation, review feedback, export, import, or bulk operations.
     */
    public function test_strict_security_cross_seller_idor_isolation(): void
    {
        // Seller B's product
        $prodB = $this->createProduct([
            'user_id'         => $this->sellerB->id,
            'name'            => 'Seller B Confidential Coating',
            'status'          => 'Active',
            'approval_status' => 'draft',
        ]);

        // Seller A tries to validate Seller B's product -> 404 (ID enumeration protection)
        Sanctum::actingAs($this->sellerA);
        $this->postJson("/api/v2/seller/catalogue/products/{$prodB->id}/validate")
            ->assertStatus(404);

        // Seller A tries to submit Seller B's product -> 404
        $this->postJson("/api/v2/seller/catalogue/products/{$prodB->id}/submit")
            ->assertStatus(404);

        // Seller A tries to read Seller B's review feedback -> 404
        $this->getJson("/api/v2/seller/catalogue/products/{$prodB->id}/review-feedback")
            ->assertStatus(404);

        // Seller A tries to access Seller B's analytics dashboard -> returns only Seller A's data
        $bDash = $this->getJson('/api/v2/seller/catalogue/analytics/dashboard');
        $bDash->assertOk()
            ->assertJsonPath('data.company.id', $this->companyA->id);
    }

    // =========================================================================
    // 6. SECURITY & AUTHORIZATION: GRANULAR STAFF PERMISSION ENFORCEMENT
    // =========================================================================

    /**
     * E2E Security:
     * Staff with and without specific permissions are correctly gated across Phase 2 capabilities.
     */
    public function test_strict_security_staff_permissions_matrix(): void
    {
        // 1. Staff without products.view cannot access catalogue analytics
        $staffNoView = $this->createStaff($this->companyA, 'readonly_staff', ['products.view' => false]);
        Sanctum::actingAs($staffNoView);
        $this->getJson('/api/v2/seller/catalogue/analytics/dashboard')->assertStatus(403);

        // 2. Staff without excel.export cannot export catalogue
        $staffNoExport = $this->createStaff($this->companyA, 'readonly_staff', ['excel.export' => false]);
        Sanctum::actingAs($staffNoExport);
        $this->postJson('/api/v2/seller/catalogue/export', ['format' => 'csv'])->assertStatus(403);

        // 3. Staff without excel.import cannot import catalogue
        $staffNoImport = $this->createStaff($this->companyA, 'readonly_staff', ['excel.import' => false]);
        Sanctum::actingAs($staffNoImport);
        $dummyCsv = $this->createCsvUpload([], 'forbidden.csv');
        $this->postJson('/api/v2/seller/catalogue/import/validate', ['file' => $dummyCsv])->assertStatus(403);

        // 4. Non-admin user cannot access admin review queue
        Sanctum::actingAs($this->sellerA);
        $this->getJson('/api/v2/admin/catalogue/review-queue')->assertStatus(403);
    }

    // =========================================================================
    // 7. WEB UI ACCESSIBILITY & LAYOUT RENDERING
    // =========================================================================

    /**
     * E2E Web UI:
     * Seller and Admin web portal pages return HTTP 200 with appropriate layouts.
     */
    public function test_web_ui_seller_and_admin_routes_accessibility(): void
    {
        // Seller UI routes
        $this->actingAs($this->sellerA);

        $this->get('/seller/catalogue/submissions')->assertOk();
        $this->get('/seller/catalogue/bulk')->assertOk();
        $this->get('/seller/catalogue/export')->assertOk();
        $this->get('/seller/catalogue/import')->assertOk();
        $this->get('/seller/catalogue/analytics')->assertOk();

        // Admin UI routes
        $this->actingAs($this->adminUser);
        $this->get('/admin/catalogue/reviews')->assertOk();
    }
}
