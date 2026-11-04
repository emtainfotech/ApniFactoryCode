<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\SellerCatalogueExport;
use App\Models\SellerListing;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SubCatgory;
use App\Models\User;
use App\Services\SellerCatalogueExportService;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Phase2_2_SellerCatalogueExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;

    protected Category $category;
    protected SubCatgory $subcategory;
    protected Brand $brand;
    protected Product $product1;
    protected Product $product2;
    protected ProductVariant $variant1;
    protected ProductVariant $variant2;
    protected SellerListing $listingA1;
    protected SellerListing $listingA2;
    protected SellerListing $listingB1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SellerRolesAndPermissionsSeeder::class);

        // Seller A (Owner)
        $this->sellerA  = User::factory()->create(['email' => 'seller_a@test.com', 'name' => 'Seller A Owner']);
        $this->companyA = Company::factory()->create([
            'user_id' => $this->sellerA->id,
            'name'    => 'Alpha Industrial Supplies',
        ]);

        // Seller B (Owner)
        $this->sellerB  = User::factory()->create(['email' => 'seller_b@test.com', 'name' => 'Seller B Owner']);
        $this->companyB = Company::factory()->create([
            'user_id' => $this->sellerB->id,
            'name'    => 'Beta Tools & Hardware',
        ]);

        // Shared Catalogue Taxonomy
        $this->category = Category::create([
            'name'   => 'Paints & Coatings',
            'status' => 'Active',
        ]);

        $this->subcategory = SubCatgory::create([
            'category_id' => $this->category->id,
            'name'        => 'Industrial Enamels',
            'status'      => 'Active',
        ]);

        $this->brand = Brand::create([
            'category_id' => $this->category->id,
            'name'        => 'Asian Paints Pro',
            'status'      => 'Active',
        ]);

        // Attribute: Finish (Gloss, Matte)
        $attr = Attribute::create([
            'name'        => 'Finish Type',
            'code'        => 'finish_type',
            'type'        => 'select',
            'is_active'   => true,
        ]);
        $opt = AttributeOption::create([
            'attribute_id' => $attr->id,
            'label'        => 'High Gloss Mirror',
            'value'        => 'high_gloss',
        ]);

        // Product 1
        $this->product1 = Product::create([
            'product_id'     => 'PRD-APC-001',
            'name'           => 'Apcolite Synthetic Enamel',
            'slug'           => 'apcolite-synthetic-enamel',
            'title'          => 'Apcolite High Gloss Industrial Paint',
            'category_id'    => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'brand_id'       => $this->brand->id,
            'user_id'        => $this->sellerA->id,
            'hsncode'        => '3208',
            'tax'            => 18.00,
            'status'         => 'Active',
        ]);

        // Dynamic attribute on product
        ProductAttributeValue::create([
            'product_id'          => $this->product1->id,
            'attribute_id'        => $attr->id,
            'attribute_option_id' => $opt->id,
        ]);

        // Variants for Product 1
        $this->variant1 = ProductVariant::create([
            'product_id'   => $this->product1->id,
            'variant_sku'  => 'APC-WHT-1L',
            'variant_name' => 'Super White - 1 Litre',
            'pack_litres'  => 1.0,
            'status'       => 'Active',
        ]);

        $this->variant2 = ProductVariant::create([
            'product_id'   => $this->product1->id,
            'variant_sku'  => 'APC-BLU-4L',
            'variant_name' => 'Royal Blue - 4 Litre',
            'pack_litres'  => 4.0,
            'status'       => 'Active',
        ]);

        // Product 2 (Belongs to Seller B)
        $this->product2 = Product::create([
            'product_id'     => 'PRD-BET-002',
            'name'           => 'Beta Heavy Duty Primer',
            'slug'           => 'beta-heavy-duty-primer',
            'title'          => 'Beta Anti-Rust Epoxy Primer',
            'category_id'    => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'brand_id'       => $this->brand->id,
            'user_id'        => $this->sellerB->id,
            'hsncode'        => '3209',
            'tax'            => 18.00,
            'status'         => 'Active',
        ]);

        $variantB = ProductVariant::create([
            'product_id'   => $this->product2->id,
            'variant_sku'  => 'BET-PRM-20L',
            'variant_name' => 'Standard Grey - 20 Litre',
            'pack_litres'  => 20.0,
            'status'       => 'Active',
        ]);

        // Seller Listings for Seller A
        $this->listingA1 = SellerListing::create([
            'company_id'          => $this->companyA->id,
            'seller_user_id'      => $this->sellerA->id,
            'product_id'          => $this->product1->id,
            'product_variant_id'  => $this->variant1->id,
            'seller_sku'          => 'ALPH-APC-WHT-1L',
            'barcode'             => '8901234567890',
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 6,
            'min_order_quantity'  => 2,
            'order_multiple'      => 2,
            'allow_unit_orders'   => true,
            'allow_carton_orders' => true,
            'seller_price'        => 350.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 385.00,
            'mrp'                 => 450.00,
            'stock_quantity'      => 100,
            'reserved_stock'      => 10,
            'low_stock_threshold' => 15,
            'dispatch_lead_days'  => 2,
            'status'              => 'Active',
        ]);

        $this->listingA2 = SellerListing::create([
            'company_id'          => $this->companyA->id,
            'seller_user_id'      => $this->sellerA->id,
            'product_id'          => $this->product1->id,
            'product_variant_id'  => $this->variant2->id,
            'seller_sku'          => 'ALPH-APC-BLU-4L',
            'barcode'             => '8901234567891',
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 4,
            'min_order_quantity'  => 1,
            'order_multiple'      => 1,
            'allow_unit_orders'   => true,
            'allow_carton_orders' => true,
            'seller_price'        => 1400.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 1540.00,
            'mrp'                 => 1800.00,
            'stock_quantity'      => 50,
            'reserved_stock'      => 5,
            'low_stock_threshold' => 10,
            'dispatch_lead_days'  => 2,
            'status'              => 'Active',
        ]);

        // Seller Listing for Seller B (Competitor)
        $this->listingB1 = SellerListing::create([
            'company_id'          => $this->companyB->id,
            'seller_user_id'      => $this->sellerB->id,
            'product_id'          => $this->product2->id,
            'product_variant_id'  => $variantB->id,
            'seller_sku'          => 'BETA-PRM-20L',
            'barcode'             => '8909876543210',
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 1,
            'min_order_quantity'  => 1,
            'order_multiple'      => 1,
            'allow_unit_orders'   => true,
            'allow_carton_orders' => false,
            'seller_price'        => 4200.00,
            'commission_rate'     => 12.00,
            'selling_price'       => 4704.00,
            'mrp'                 => 5500.00,
            'stock_quantity'      => 20,
            'reserved_stock'      => 0,
            'low_stock_threshold' => 5,
            'dispatch_lead_days'  => 3,
            'status'              => 'Active',
        ]);
    }

    // ==========================================
    // 1. SUCCESSFUL EXPORT FOR AUTHENTICATED SELLER
    // ==========================================

    /** @test */
    public function seller_can_successfully_export_catalogue_as_xlsx()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/export', [
            'format' => 'xlsx',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.export_format', 'xlsx')
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.total_records', 2);

        $exportId = $response->json('data.export_id');
        $this->assertDatabaseHas('seller_catalogue_exports', [
            'id'            => $exportId,
            'company_id'    => $this->companyA->id,
            'export_format' => 'xlsx',
            'status'        => 'completed',
        ]);

        // Verify physical file was generated on disk
        $export = SellerCatalogueExport::find($exportId);
        $this->assertFileExists(storage_path('app/' . $export->file_path));
    }

    /** @test */
    public function seller_can_successfully_export_catalogue_as_csv()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/export', [
            'format' => 'csv',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.export_format', 'csv')
            ->assertJsonPath('data.status', 'completed');

        $exportId = $response->json('data.export_id');
        $export = SellerCatalogueExport::find($exportId);
        $fullPath = storage_path('app/' . $export->file_path);
        $this->assertFileExists($fullPath);

        // Verify CSV contents contain Seller A's SKU and NOT Seller B's SKU
        $contents = file_get_contents($fullPath);
        $this->assertStringContainsString('ALPH-APC-WHT-1L', $contents);
        $this->assertStringNotContainsString('BETA-PRM-20L', $contents);
    }

    // ==========================================
    // 2. SELLER ISOLATION & DATA SECURITY
    // ==========================================

    /** @test */
    public function seller_a_export_never_contains_seller_b_data()
    {
        $service = app(SellerCatalogueExportService::class);
        $export = $service->initiateExport($this->companyA, $this->sellerA, [], 'csv');

        $fullPath = storage_path('app/' . $export->file_path);
        $content = file_get_contents($fullPath);

        $this->assertStringContainsString($this->listingA1->seller_sku, $content);
        $this->assertStringContainsString($this->listingA2->seller_sku, $content);
        $this->assertStringNotContainsString($this->listingB1->seller_sku, $content);
        $this->assertStringNotContainsString('Beta Heavy Duty Primer', $content);
    }

    /** @test */
    public function seller_b_cannot_download_seller_a_export_file_via_api()
    {
        $service = app(SellerCatalogueExportService::class);
        $exportA = $service->initiateExport($this->companyA, $this->sellerA, [], 'xlsx');

        $tokenB = $this->sellerB->createToken('test')->plainTextToken;

        // Seller B attempts to access Seller A's export ID
        $response = $this->withToken($tokenB)->getJson("/api/v2/seller/catalogue/exports/{$exportA->id}/download");
        $response->assertStatus(404);
    }

    /** @test */
    public function seller_b_cannot_inspect_seller_a_export_status()
    {
        $service = app(SellerCatalogueExportService::class);
        $exportA = $service->initiateExport($this->companyA, $this->sellerA, [], 'xlsx');

        $tokenB = $this->sellerB->createToken('test')->plainTextToken;
        $response = $this->withToken($tokenB)->getJson("/api/v2/seller/catalogue/exports/{$exportA->id}");
        $response->assertStatus(404);
    }

    // ==========================================
    // 3. SELECTED PRODUCT EXPORT & VALIDATION
    // ==========================================

    /** @test */
    public function seller_can_export_selected_products_by_id()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/export', [
            'product_ids' => [$this->product1->id],
            'format'      => 'csv',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.total_records', 2);
    }

    /** @test */
    public function export_rejects_unowned_product_ids()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        // Seller A tries to specify Seller B's product ID
        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/export', [
            'product_ids' => [$this->product2->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_ids']);
    }

    // ==========================================
    // 4. FILTERED PRODUCT EXPORT
    // ==========================================

    /** @test */
    public function export_supports_category_and_sku_filters()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        // Filter for specific SKU
        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/export', [
            'format'     => 'csv',
            'seller_sku' => 'ALPH-APC-WHT',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.total_records', 1);

        $export = SellerCatalogueExport::find($response->json('data.export_id'));
        $content = file_get_contents(storage_path('app/' . $export->file_path));

        $this->assertStringContainsString('ALPH-APC-WHT-1L', $content);
        $this->assertStringNotContainsString('ALPH-APC-BLU-4L', $content);
    }

    // ==========================================
    // 5. PERMISSION-BASED SENSITIVE FIELDS
    // ==========================================

    /** @test */
    public function readonly_staff_without_pricing_permission_does_not_see_pricing_columns()
    {
        // Staff user with pricing explicitly denied via override
        $staffUser = User::factory()->create(['email' => 'readonly_staff@test.com']);
        $readonlyRole = SellerRole::where('name', 'readonly_staff')->first();

        $staff = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $staffUser->id,
            'seller_role_id' => $readonlyRole->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        // Grant excel.export
        $exportPerm = \App\Models\SellerPermission::where('name', 'excel.export')->first();
        \App\Models\SellerStaffPermission::create([
            'seller_staff_id'      => $staff->id,
            'seller_permission_id' => $exportPerm->id,
            'granted'              => true,
        ]);

        // Explicitly deny pricing.view
        $pricingPerm = \App\Models\SellerPermission::where('name', 'pricing.view')->first();
        \App\Models\SellerStaffPermission::create([
            'seller_staff_id'      => $staff->id,
            'seller_permission_id' => $pricingPerm->id,
            'granted'              => false,
        ]);

        $token = $staffUser->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/export', [
            'format' => 'csv',
        ]);

        $response->assertOk();
        $export = SellerCatalogueExport::find($response->json('data.export_id'));
        $content = file_get_contents(storage_path('app/' . $export->file_path));

        // Pricing column headers should NOT be present
        $this->assertStringNotContainsString('Seller Price (INR)', $content);
        $this->assertStringNotContainsString('Commission Rate (%)', $content);
    }

    /** @test */
    public function owner_with_pricing_and_inventory_permission_sees_all_sensitive_columns()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/export', [
            'format' => 'csv',
        ]);

        $response->assertOk();
        $export = SellerCatalogueExport::find($response->json('data.export_id'));
        $content = file_get_contents(storage_path('app/' . $export->file_path));

        // Both Pricing and Inventory columns should be present
        $this->assertStringContainsString('Seller Price (INR)', $content);
        $this->assertStringContainsString('Stock Quantity', $content);
        $this->assertStringContainsString('350.00', $content);
        $this->assertStringContainsString('100', $content);
    }

    // ==========================================
    // 6. FORMULA INJECTION DEFENSE (CWE-1236)
    // ==========================================

    /** @test */
    public function it_sanitizes_formula_injection_characters()
    {
        $variantMalicious = ProductVariant::create([
            'product_id'   => $this->product1->id,
            'variant_sku'  => 'APC-MAL-99',
            'variant_name' => 'Malicious Variant',
            'status'       => 'Active',
        ]);

        // Malicious SKU starting with '=' or '@'
        $maliciousListing = SellerListing::create([
            'company_id'          => $this->companyA->id,
            'seller_user_id'      => $this->sellerA->id,
            'product_id'          => $this->product1->id,
            'product_variant_id'  => $variantMalicious->id,
            'seller_sku'          => '=cmd|"/C calc"!A0',
            'barcode'             => '+123456789',
            'unit_of_measurement' => '@unit',
            'status'              => 'Active',
        ]);

        $service = app(SellerCatalogueExportService::class);
        $export = $service->initiateExport($this->companyA, $this->sellerA, ['listing_ids' => [$maliciousListing->id]], 'csv');

        $content = file_get_contents(storage_path('app/' . $export->file_path));

        // Ensure formula trigger characters are escaped with a leading single quote
        $this->assertStringContainsString("'=cmd|", $content);
        $this->assertStringContainsString("'+123456789", $content);
        $this->assertStringContainsString("'@unit", $content);
    }

    // ==========================================
    // 7. UNICODE & SPECIAL CHARACTERS
    // ==========================================

    /** @test */
    public function it_handles_unicode_and_hindi_characters_properly()
    {
        $this->product1->update([
            'name'  => 'अग्नि पेंट - Special Hindi Character',
            'title' => 'Paint with Quotes "100%" & Ampersand',
        ]);

        $service = app(SellerCatalogueExportService::class);
        $export = $service->initiateExport($this->companyA, $this->sellerA, [], 'csv');

        $content = file_get_contents(storage_path('app/' . $export->file_path));

        $this->assertStringContainsString('अग्नि पेंट', $content);
        $this->assertStringContainsString('Paint with Quotes', $content);
    }

    // ==========================================
    // 8. EMPTY CATALOGUE HANDLING
    // ==========================================

    /** @test */
    public function empty_catalogue_export_generates_valid_file_with_headers()
    {
        // Create Seller C with 0 listings
        $sellerC  = User::factory()->create(['email' => 'seller_c@test.com']);
        $companyC = Company::factory()->create(['user_id' => $sellerC->id]);

        $token = $sellerC->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/export', [
            'format' => 'xlsx',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.total_records', 0)
            ->assertJsonPath('data.status', 'completed');

        $export = SellerCatalogueExport::find($response->json('data.export_id'));
        $this->assertFileExists(storage_path('app/' . $export->file_path));
    }

    // ==========================================
    // 9. EXPORT HISTORY APIS & OWNERSHIP
    // ==========================================

    /** @test */
    public function seller_can_list_own_export_history_only()
    {
        $service = app(SellerCatalogueExportService::class);
        $service->initiateExport($this->companyA, $this->sellerA, [], 'xlsx');
        $service->initiateExport($this->companyB, $this->sellerB, [], 'xlsx');

        $tokenA = $this->sellerA->createToken('test')->plainTextToken;
        $response = $this->withToken($tokenA)->getJson('/api/v2/seller/catalogue/exports');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data');

        $this->assertEquals($this->companyA->id, SellerCatalogueExport::find($response->json('data.data.0.id'))->company_id);
    }

    // ==========================================
    // 10. WEB ROUTES & PORTAL ACCESS
    // ==========================================

    /** @test */
    public function seller_owner_can_access_web_export_interface()
    {
        $response = $this->actingAs($this->sellerA)->get('/seller/catalogue/export');
        $response->assertOk();
        $response->assertSee('Catalogue Excel');
        $response->assertSee('Export Facility');
    }

    /** @test */
    public function seller_owner_can_view_web_export_history()
    {
        $response = $this->actingAs($this->sellerA)->get('/seller/catalogue/export/history');
        $response->assertOk();
        $response->assertSee('Catalogue Export Audit');
    }

    /** @test */
    public function seller_can_download_export_via_web_route()
    {
        $service = app(SellerCatalogueExportService::class);
        $export = $service->initiateExport($this->companyA, $this->sellerA, [], 'xlsx');

        $response = $this->actingAs($this->sellerA)->get("/seller/catalogue/export/{$export->id}/download");
        $response->assertOk();
        $this->assertStringContainsString('catalogue_alpha-industrial-supplies', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function unauthorized_seller_cannot_download_another_sellers_file_via_web()
    {
        $service = app(SellerCatalogueExportService::class);
        $export = $service->initiateExport($this->companyA, $this->sellerA, [], 'xlsx');

        // Seller B attempts to download Seller A's file
        $response = $this->actingAs($this->sellerB)->get("/seller/catalogue/export/{$export->id}/download");
        $response->assertStatus(404);
    }
}
