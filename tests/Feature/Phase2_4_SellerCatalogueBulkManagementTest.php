<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyAuditLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerCatalogueBulkOperation;
use App\Models\SellerListing;
use App\Models\SellerPermission;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SubCatgory;
use App\Models\User;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase2_4_SellerCatalogueBulkManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;

    protected Category $category;
    protected SubCatgory $subcategory;
    protected Brand $brand;

    protected Product $productA1;
    protected Product $productA2;
    protected ProductVariant $variantA1;
    protected ProductVariant $variantA2;
    protected SellerListing $listingA1;
    protected SellerListing $listingA2;

    protected Product $productB;
    protected ProductVariant $variantB;
    protected SellerListing $listingB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SellerRolesAndPermissionsSeeder::class);

        // Seller A Owner
        $this->sellerA = User::factory()->create(['email' => 'seller_a@test.com', 'name' => 'Seller A Owner']);
        $this->companyA = Company::factory()->create([
            'user_id' => $this->sellerA->id,
            'name'    => 'Alpha Industrial Paints',
        ]);

        // Seller B Owner
        $this->sellerB = User::factory()->create(['email' => 'seller_b@test.com', 'name' => 'Beta Coatings Ltd']);
        $this->companyB = Company::factory()->create([
            'user_id' => $this->sellerB->id,
            'name'    => 'Beta Coatings Ltd',
        ]);

        // Taxonomy
        $this->category = Category::create([
            'name'   => 'Industrial Enamels',
            'status' => 'Active',
        ]);

        $this->subcategory = SubCatgory::create([
            'cid'         => $this->category->id,
            'category_id' => $this->category->id,
            'name'        => 'Synthetic Enamels',
            'status'      => 'Active',
        ]);

        $this->brand = Brand::create([
            'category_id' => $this->category->id,
            'name'        => 'Asian Paints Pro',
            'status'      => 'Active',
        ]);

        // Product A1 (Active)
        $this->productA1 = Product::create([
            'product_id'     => 'PRD-ALP-001',
            'name'           => 'Apcolite Gloss Enamel',
            'slug'           => 'apcolite-gloss-enamel',
            'title'          => 'Apcolite Gloss Enamel 1L',
            'category_id'    => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'brand_id'       => $this->brand->id,
            'user_id'        => $this->sellerA->id,
            'hsncode'        => '3208',
            'tax'            => 18.00,
            'status'         => 'Active',
        ]);

        $this->variantA1 = ProductVariant::create([
            'product_id'   => $this->productA1->id,
            'variant_sku'  => 'ALP-WHT-1L',
            'variant_name' => 'Brilliant White 1L',
            'pack_litres'  => 1.0,
            'status'       => 'Active',
        ]);

        $this->listingA1 = SellerListing::create([
            'company_id'          => $this->companyA->id,
            'seller_user_id'      => $this->sellerA->id,
            'product_id'          => $this->productA1->id,
            'product_variant_id'  => $this->variantA1->id,
            'seller_sku'          => 'SKU-ALP-001',
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 4,
            'min_order_quantity'  => 1,
            'seller_price'        => 300.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 330.00,
            'mrp'                 => 400.00,
            'stock_quantity'      => 50,
            'dispatch_lead_days'  => 2,
            'status'              => 'Active',
        ]);

        // Product A2 (Active)
        $this->productA2 = Product::create([
            'product_id'     => 'PRD-ALP-002',
            'name'           => 'Apcolite Primer Surfacer',
            'slug'           => 'apcolite-primer-surfacer',
            'title'          => 'Apcolite Primer Surfacer 4L',
            'category_id'    => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'brand_id'       => $this->brand->id,
            'user_id'        => $this->sellerA->id,
            'hsncode'        => '3208',
            'tax'            => 18.00,
            'status'         => 'Active',
        ]);

        $this->variantA2 = ProductVariant::create([
            'product_id'   => $this->productA2->id,
            'variant_sku'  => 'ALP-GRY-4L',
            'variant_name' => 'Grey Primer 4L',
            'pack_litres'  => 4.0,
            'status'       => 'Active',
        ]);

        $this->listingA2 = SellerListing::create([
            'company_id'          => $this->companyA->id,
            'seller_user_id'      => $this->sellerA->id,
            'product_id'          => $this->productA2->id,
            'product_variant_id'  => $this->variantA2->id,
            'seller_sku'          => 'SKU-ALP-002',
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 2,
            'min_order_quantity'  => 1,
            'seller_price'        => 600.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 660.00,
            'mrp'                 => 750.00,
            'stock_quantity'      => 80,
            'dispatch_lead_days'  => 3,
            'status'              => 'Active',
        ]);

        // Seller B's Listing (Belongs to Company B)
        $this->productB = Product::create([
            'product_id'     => 'PRD-BET-001',
            'name'           => 'Beta Epoxy Thinner',
            'slug'           => 'beta-epoxy-thinner',
            'title'          => 'Beta Epoxy Thinner 5L',
            'category_id'    => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'brand_id'       => $this->brand->id,
            'user_id'        => $this->sellerB->id,
            'hsncode'        => '3814',
            'tax'            => 18.00,
            'status'         => 'Active',
        ]);

        $this->variantB = ProductVariant::create([
            'product_id'   => $this->productB->id,
            'variant_sku'  => 'BET-THN-5L',
            'variant_name' => 'Thinner 5L',
            'pack_litres'  => 5.0,
            'status'       => 'Active',
        ]);

        $this->listingB = SellerListing::create([
            'company_id'          => $this->companyB->id,
            'seller_user_id'      => $this->sellerB->id,
            'product_id'          => $this->productB->id,
            'product_variant_id'  => $this->variantB->id,
            'seller_sku'          => 'SKU-BET-001',
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 1,
            'min_order_quantity'  => 1,
            'seller_price'        => 450.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 495.00,
            'mrp'                 => 550.00,
            'stock_quantity'      => 20,
            'dispatch_lead_days'  => 1,
            'status'              => 'Active',
        ]);
    }

    /**
     * Helper to create staff user for Seller A with given permissions.
     */
    protected function createStaff(array $permissionSlugs): User
    {
        $staffUser = User::factory()->create(['email' => 'staff_' . uniqid() . '@test.com', 'name' => 'Staff Member']);
        $role = SellerRole::where('name', 'read_only_staff')->first()
            ?: SellerRole::create(['name' => 'read_only_staff', 'label' => 'Read-Only Staff', 'description' => 'Staff', 'is_system' => true]);

        $staff = SellerStaff::create([
            'company_id'     => $this->companyA->id,
            'user_id'        => $staffUser->id,
            'seller_role_id' => $role->id,
            'is_active'      => true,
            'activated_at'   => now(),
        ]);

        foreach ($permissionSlugs as $slug) {
            $perm = SellerPermission::where('name', $slug)->first();
            if ($perm) {
                \App\Models\SellerStaffPermission::create([
                    'seller_staff_id'      => $staff->id,
                    'seller_permission_id' => $perm->id,
                    'granted'              => true,
                ]);
            }
        }

        return $staffUser;
    }

    /* -------------------------------------------------------------------------
     * 1. Stage 1: Preview Tests
     * ---------------------------------------------------------------------- */

    public function test_owner_can_generate_preview_for_explicit_listing_ids(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'target_entity'      => 'listing',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id, $this->listingA2->id],
            'update_payload'     => ['status' => 'Inactive'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.total_matching', 2)
            ->assertJsonPath('data.eligible_count', 2)
            ->assertJsonPath('data.ineligible_count', 0)
            ->assertJsonPath('data.can_confirm', true);

        $this->assertDatabaseHas('seller_catalogue_bulk_operations', [
            'company_id'     => $this->companyA->id,
            'user_id'        => $this->sellerA->id,
            'operation_type' => 'status_update',
            'status'         => 'previewed',
            'total_matching' => 2,
            'eligible_count' => 2,
        ]);
    }

    public function test_filter_based_selection_previews_matching_company_records_only(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'target_entity'      => 'listing',
            'selection_mode'     => 'filter',
            'selection_criteria' => [
                'status'      => 'Active',
                'category_id' => $this->category->id,
            ],
            'update_payload'     => ['status' => 'Inactive'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.total_matching', 2)
            ->assertJsonPath('data.eligible_count', 2);
    }

    public function test_exceeding_max_batch_size_is_rejected(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Generate 501 dummy IDs
        $tooManyIds = range(1, 501);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'target_entity'      => 'listing',
            'selection_mode'     => 'ids',
            'selection_criteria' => $tooManyIds,
            'update_payload'     => ['status' => 'Inactive'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    }

    /* -------------------------------------------------------------------------
     * 2. Tenant Isolation & IDOR Tests
     * ---------------------------------------------------------------------- */

    public function test_cross_seller_tenant_isolation_prevents_previewing_or_modifying_other_sellers_listings(): void
    {
        // Seller B attempts to target Seller A's listing
        Sanctum::actingAs($this->sellerB);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'target_entity'      => 'listing',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['status' => 'Inactive'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['selection']);
    }

    public function test_seller_cannot_confirm_another_sellers_operation(): void
    {
        // Step 1: Seller A creates a preview operation
        Sanctum::actingAs($this->sellerA);
        $previewRes = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'target_entity'      => 'listing',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['status' => 'Inactive'],
        ]);
        $opId = $previewRes->json('data.operation_id');

        // Step 2: Seller B tries to confirm Seller A's operation
        app('auth')->forgetGuards();
        Sanctum::actingAs($this->sellerB);

        $confirmRes = $this->postJson("/api/v2/seller/catalogue/bulk/{$opId}/confirm");
        $confirmRes->assertStatus(404);
    }

    /* -------------------------------------------------------------------------
     * 3. Granular Permission Enforcement Tests (Phase 2.1 integration)
     * ---------------------------------------------------------------------- */

    public function test_staff_without_products_toggle_listing_permission_cannot_change_status(): void
    {
        // Staff has products.view, but NOT products.toggle_listing
        $staff = $this->createStaff(['products.view']);
        Sanctum::actingAs($staff);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['status' => 'Inactive'],
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_with_products_toggle_listing_permission_can_change_status(): void
    {
        $staff = $this->createStaff(['products.view', 'products.toggle_listing']);
        Sanctum::actingAs($staff);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['status' => 'Inactive'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_staff_without_pricing_update_permission_cannot_update_pricing(): void
    {
        $staff = $this->createStaff(['products.view', 'products.edit']);
        Sanctum::actingAs($staff);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'field_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['seller_price' => 350.00],
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_with_pricing_update_permission_can_update_pricing(): void
    {
        $staff = $this->createStaff(['products.view', 'pricing.update']);
        Sanctum::actingAs($staff);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'field_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['seller_price' => 350.00],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_staff_without_inventory_update_permission_cannot_adjust_stock(): void
    {
        $staff = $this->createStaff(['products.view']);
        Sanctum::actingAs($staff);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'field_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['stock_quantity' => 100],
        ]);

        $response->assertStatus(403);
    }

    /* -------------------------------------------------------------------------
     * 4. Platform-Managed Fields Protection
     * ---------------------------------------------------------------------- */

    public function test_attempting_to_bulk_update_platform_managed_fields_is_rejected(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'field_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => [
                'tax'         => 28.00,
                'category_id' => 999,
                'hsncode'     => '9999',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payload']);
    }

    /* -------------------------------------------------------------------------
     * 5. Status Validation & Transitions
     * ---------------------------------------------------------------------- */

    public function test_cannot_activate_listing_with_zero_seller_price(): void
    {
        $this->listingA1->update(['seller_price' => 0.00, 'status' => 'Deactive']);

        Sanctum::actingAs($this->sellerA);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['status' => 'Active'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.eligible_count', 0)
            ->assertJsonPath('data.ineligible_count', 1);

        $ineligible = $response->json('data.ineligible_items');
        $this->assertNotEmpty($ineligible);
        $this->assertStringContainsString('price is ₹0.00', $ineligible[0]['reason']);
    }

    public function test_cannot_activate_listing_when_parent_product_is_inactive(): void
    {
        $this->productA1->update(['status' => 'Deactive']);
        $this->listingA1->update(['status' => 'Deactive']);

        Sanctum::actingAs($this->sellerA);

        $response = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['status' => 'Active'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.eligible_count', 0)
            ->assertJsonPath('data.ineligible_count', 1);

        $ineligible = $response->json('data.ineligible_items');
        $this->assertStringContainsString('parent product', strtolower($ineligible[0]['reason']));
    }

    /* -------------------------------------------------------------------------
     * 6. Stage 2: Confirm & Execution Tests + Audit Logging
     * ---------------------------------------------------------------------- */

    public function test_stage2_confirmation_executes_bulk_operation_and_logs_audit(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Stage 1: Preview status change to Deactive
        $previewRes = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id, $this->listingA2->id],
            'update_payload'     => ['status' => 'Deactive'],
        ]);
        $opId = $previewRes->json('data.operation_id');

        // Stage 2: Confirm
        $confirmRes = $this->postJson("/api/v2/seller/catalogue/bulk/{$opId}/confirm");

        $confirmRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.success_count', 2)
            ->assertJsonPath('data.failed_count', 0);

        // Verify Listings Updated
        $this->assertEquals('Deactive', $this->listingA1->fresh()->status);
        $this->assertEquals('Deactive', $this->listingA2->fresh()->status);

        // Verify Audit Log
        $this->assertDatabaseHas('company_audit_logs', [
            'company_id'  => $this->companyA->id,
            'user_id'     => $this->sellerA->id,
            'action_type' => 'catalogue_bulk_status_update',
        ]);
    }

    public function test_confirming_already_completed_operation_is_rejected(): void
    {
        Sanctum::actingAs($this->sellerA);

        $previewRes = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'status_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => ['status' => 'Inactive'],
        ]);
        $opId = $previewRes->json('data.operation_id');

        // First Confirm -> Succeeds
        $this->postJson("/api/v2/seller/catalogue/bulk/{$opId}/confirm")->assertStatus(200);

        // Second Confirm -> Fails 422
        $res2 = $this->postJson("/api/v2/seller/catalogue/bulk/{$opId}/confirm");
        $res2->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    /* -------------------------------------------------------------------------
     * 7. Pricing & Stock Calculations
     * ---------------------------------------------------------------------- */

    public function test_bulk_pricing_percentage_increase_recalculates_selling_price(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Listing A1: seller_price = 300, commission_rate = 10% -> initial selling_price = 330
        // Increase seller_price by 10% -> new seller_price = 330.00
        // New selling_price = 330 + 10% = 363.00

        $previewRes = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'field_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => [
                'price_adjustment_type'  => 'percentage_increase',
                'price_adjustment_value' => 10.0,
            ],
        ]);
        $opId = $previewRes->json('data.operation_id');

        $this->postJson("/api/v2/seller/catalogue/bulk/{$opId}/confirm")->assertStatus(200);

        $fresh = $this->listingA1->fresh();
        $this->assertEquals(330.00, (float)$fresh->seller_price);
        $this->assertEquals(363.00, (float)$fresh->selling_price);
    }

    public function test_bulk_stock_increase_and_decrease(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Listing A1 has 50 units initially
        // 1. Stock increase (+30)
        $preview1 = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'field_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => [
                'stock_adjustment_type' => 'increase',
                'stock_quantity'        => 30,
            ],
        ]);
        $op1 = $preview1->json('data.operation_id');
        $this->postJson("/api/v2/seller/catalogue/bulk/{$op1}/confirm")->assertStatus(200);
        $this->assertEquals(80, $this->listingA1->fresh()->stock_quantity);

        // 2. Stock decrease (-20)
        $preview2 = $this->postJson('/api/v2/seller/catalogue/bulk/preview', [
            'operation_type'     => 'field_update',
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
            'update_payload'     => [
                'stock_adjustment_type' => 'decrease',
                'stock_quantity'        => 20,
            ],
        ]);
        $op2 = $preview2->json('data.operation_id');
        $this->postJson("/api/v2/seller/catalogue/bulk/{$op2}/confirm")->assertStatus(200);
        $this->assertEquals(60, $this->listingA1->fresh()->stock_quantity);
    }

    /* -------------------------------------------------------------------------
     * 8. Archive and Restore Workflow
     * ---------------------------------------------------------------------- */

    public function test_bulk_archive_and_restore_workflow(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Step 1: Bulk Archive
        $previewArchive = $this->postJson('/api/v2/seller/catalogue/bulk/archive', [
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
        ]);
        $opArchiveId = $previewArchive->json('data.operation_id');
        $this->postJson("/api/v2/seller/catalogue/bulk/{$opArchiveId}/confirm")->assertStatus(200);

        $this->assertEquals('Archived', $this->listingA1->fresh()->status);

        // Step 2: Bulk Restore -> restores to Deactive
        $previewRestore = $this->postJson('/api/v2/seller/catalogue/bulk/restore', [
            'selection_mode'     => 'ids',
            'selection_criteria' => [$this->listingA1->id],
        ]);
        $opRestoreId = $previewRestore->json('data.operation_id');
        $this->postJson("/api/v2/seller/catalogue/bulk/{$opRestoreId}/confirm")->assertStatus(200);

        $this->assertEquals('Deactive', $this->listingA1->fresh()->status);
    }

    /* -------------------------------------------------------------------------
     * 9. Web Route Views & Workflow
     * ---------------------------------------------------------------------- */

    public function test_web_routes_flow(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // 1. Index View
        $indexRes = $this->actingAs($this->sellerA)->get('/seller/catalogue/bulk');
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Bulk Catalogue Manager');
        $indexRes->assertSee('SKU-ALP-001');

        // 2. Web Preview Post
        $previewRes = $this->actingAs($this->sellerA)->post('/seller/catalogue/bulk/preview', [
            'operation_type' => 'status_update',
            'selection_mode' => 'ids',
            'selected_ids'   => [$this->listingA1->id],
            'update_payload' => ['status' => 'Deactive'],
        ]);
        $previewRes->assertStatus(200);
        $previewRes->assertSee('Bulk Operation Preview');

        // Extract operation ID from DB
        $latestOp = SellerCatalogueBulkOperation::where('company_id', $this->companyA->id)->latest('id')->first();
        $this->assertNotNull($latestOp);

        // 3. Web Confirm Post
        $confirmRes = $this->actingAs($this->sellerA)->post("/seller/catalogue/bulk/{$latestOp->id}/confirm");
        $confirmRes->assertRedirect(route('seller.catalogue.bulk.history'));

        // 4. Web History View
        $historyRes = $this->actingAs($this->sellerA)->get('/seller/catalogue/bulk/history');
        $historyRes->assertStatus(200);
        $historyRes->assertSee('Operations History');
    }
}
