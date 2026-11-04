<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Company;
use App\Models\CompanyAuditLog;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductReviewHistory;
use App\Models\ProductValidationResult;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\SellerPermission;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SellerStaffPermission;
use App\Models\SubCatgory;
use App\Models\User;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase2_5_SellerCatalogueApprovalWorkflowTest extends TestCase
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
    protected AttributeOption $attrOption2;

    protected Product $validProduct;
    protected ProductVariant $validVariant;
    protected SellerListing $validListing;

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
            'email' => 'alpha@coatings.test',
            'name'  => 'Alpha Paints Owner',
        ]);
        $this->companyA = Company::factory()->create([
            'user_id' => $this->sellerA->id,
            'name'    => 'Alpha Industrial Coatings',
        ]);

        // Seller B
        $this->sellerB = User::factory()->create([
            'email' => 'beta@coatings.test',
            'name'  => 'Beta Paints Owner',
        ]);
        $this->companyB = Company::factory()->create([
            'user_id' => $this->sellerB->id,
            'name'    => 'Beta Solutions Ltd',
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

        // Required Dynamic Attribute
        $this->requiredAttr = Attribute::create([
            'name'      => 'Finish Gloss Level',
            'code'      => 'gloss_level',
            'type'      => 'single_select',
            'status'    => 'Active',
        ]);

        $this->attrOption1 = AttributeOption::create([
            'attribute_id' => $this->requiredAttr->id,
            'label'        => 'High Gloss (90% RA)',
            'value'        => 'High Gloss (90% RA)',
        ]);
        $this->attrOption2 = AttributeOption::create([
            'attribute_id' => $this->requiredAttr->id,
            'label'        => 'Semi Gloss (60% RA)',
            'value'        => 'Semi Gloss (60% RA)',
        ]);

        // Map attribute to category as required
        CategoryAttribute::create([
            'category_id'  => $this->category->id,
            'attribute_id' => $this->requiredAttr->id,
            'is_required'  => true,
            'is_filterable'=> true,
        ]);

        // Fully Compliant Valid Product for Seller A
        $this->validProduct = Product::create([
            'user_id'         => $this->sellerA->id,
            'category_id'     => $this->category->id,
            'subcategory_id'  => $this->subcategory->id,
            'brand_id'        => $this->brand->id,
            'name'            => 'AlphaCoat Epoxy Primer 20L',
            'title'           => 'Two-component zinc phosphate epoxy primer for harsh environments',
            'description'     => 'Heavy-duty industrial anticorrosive coating with superb adhesion.',
            'image'           => 'products/alpha_primer.jpg',
            'status'          => 'Deactive',
            'approval_status' => 'draft',
        ]);

        // Dynamic attribute value
        ProductAttributeValue::create([
            'product_id'          => $this->validProduct->id,
            'attribute_id'        => $this->requiredAttr->id,
            'attribute_option_id' => $this->attrOption1->id,
        ]);

        // Variant
        $this->validVariant = ProductVariant::create([
            'product_id'   => $this->validProduct->id,
            'variant_code' => 'ALP-EP-20L-HG',
            'title'        => '20 Litre Drum - High Gloss',
            'status'       => 'Active',
        ]);

        // Seller Listing
        $this->validListing = SellerListing::create([
            'company_id'         => $this->companyA->id,
            'product_id'         => $this->validProduct->id,
            'product_variant_id' => $this->validVariant->id,
            'seller_sku'         => 'SKU-ALP-001',
            'seller_price'       => 4500.00,
            'selling_price'      => 4500.00,
            'mrp'                => 6000.00,
            'min_order_quantity' => 2,
            'status'             => 'Deactive',
        ]);
    }

    public function test_seller_can_validate_product_successfully_when_valid(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.is_passed', true)
            ->assertJsonPath('data.has_blocking_errors', false)
            ->assertJsonPath('data.error_count', 0)
            ->assertJsonPath('data.can_submit', true);

        $this->assertDatabaseHas('product_validation_results', [
            'product_id' => $this->validProduct->id,
            'status'     => 'passed',
        ]);
    }

    public function test_validation_blocks_product_missing_mandatory_name(): void
    {
        Sanctum::actingAs($this->sellerA);

        $invalidProduct = Product::create([
            'user_id'         => $this->sellerA->id,
            'category_id'     => $this->category->id,
            'brand_id'        => $this->brand->id,
            'name'            => '', // missing
            'slug'            => 'slug-test-' . uniqid(),
            'image'           => 'img.jpg',
            'approval_status' => 'draft',
        ]);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$invalidProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_passed', false)
            ->assertJsonPath('data.has_blocking_errors', true)
            ->assertJsonPath('data.can_submit', false);

        $this->assertTrue(collect($response->json('data.failed_checks'))->contains('rule', 'product.name.required'));
    }

    public function test_validation_blocks_product_missing_media_images(): void
    {
        Sanctum::actingAs($this->sellerA);

        $this->validProduct->update(['image' => null, 'multipleimages' => null]);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_blocking_errors', true);

        $this->assertTrue(collect($response->json('data.failed_checks'))->contains('rule', 'product.media.required'));
    }

    public function test_validation_blocks_product_with_inactive_category_or_brand(): void
    {
        Sanctum::actingAs($this->sellerA);

        $this->category->update(['status' => 'Deactive']);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_blocking_errors', true);

        $this->assertTrue(collect($response->json('data.failed_checks'))->contains('rule', 'product.category.valid'));
    }

    public function test_validation_blocks_product_missing_required_dynamic_attribute(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Delete configured attribute value
        ProductAttributeValue::where('product_id', $this->validProduct->id)->delete();

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_blocking_errors', true);

        $this->assertTrue(collect($response->json('data.failed_checks'))->contains('rule', 'attribute.required.missing'));
    }

    public function test_validation_blocks_product_with_invalid_dynamic_attribute_option(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Point value to a nonexistent option ID
        ProductAttributeValue::where('product_id', $this->validProduct->id)
            ->update(['attribute_option_id' => 999999]);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_blocking_errors', true);

        $this->assertTrue(collect($response->json('data.failed_checks'))->contains('rule', 'attribute.option.invalid'));
    }

    public function test_validation_blocks_listing_with_zero_selling_price(): void
    {
        Sanctum::actingAs($this->sellerA);

        $this->validListing->update(['selling_price' => 0.00, 'seller_price' => 0.00]);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_blocking_errors', true);

        $this->assertTrue(collect($response->json('data.failed_checks'))->contains('rule', 'listing.pricing.zero'));
    }

    public function test_validation_blocks_listing_with_mrp_lower_than_selling_price(): void
    {
        Sanctum::actingAs($this->sellerA);

        $this->validListing->update(['mrp' => 3000.00, 'seller_price' => 4500.00]);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_blocking_errors', true);

        $this->assertTrue(collect($response->json('data.failed_checks'))->contains('rule', 'listing.pricing.mrp_low'));
    }

    public function test_validation_blocks_listing_with_invalid_moq(): void
    {
        Sanctum::actingAs($this->sellerA);

        $this->validListing->update(['min_order_quantity' => 0]);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/validate");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_blocking_errors', true);

        $this->assertTrue(collect($response->json('data.failed_checks'))->contains('rule', 'listing.moq.invalid'));
    }

    public function test_seller_can_submit_valid_product_for_admin_review(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/submit", [
            'notes' => 'New industrial primer ready for market launch.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'pending_review');

        $this->validProduct->refresh();
        $this->assertEquals('pending_review', $this->validProduct->approval_status);
        $this->assertNotNull($this->validProduct->submitted_at);

        // Verify ProductReviewHistory record
        $this->assertDatabaseHas('product_review_histories', [
            'product_id'      => $this->validProduct->id,
            'seller_user_id'  => $this->sellerA->id,
            'action'          => 'submitted',
            'previous_status' => 'draft',
            'new_status'      => 'pending_review',
        ]);

        // Verify in-app Notification was generated
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sellerA->id,
            'type'    => 'submission_confirmed',
        ]);

        // Verify CompanyAuditLog
        $this->assertDatabaseHas('company_audit_logs', [
            'company_id'  => $this->companyA->id,
            'action_type' => 'catalogue_product_submitted',
        ]);
    }

    public function test_submission_is_blocked_when_product_has_validation_errors(): void
    {
        Sanctum::actingAs($this->sellerA);

        // Make product invalid by clearing image
        $this->validProduct->update(['image' => null, 'multipleimages' => null]);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/submit");

        $response->assertStatus(422)
            ->assertJsonPath('status', false);

        $this->validProduct->refresh();
        $this->assertEquals('draft', $this->validProduct->approval_status);
    }

    public function test_seller_cannot_submit_product_belonging_to_another_seller(): void
    {
        Sanctum::actingAs($this->sellerB);

        // Seller B tries to submit Seller A's product
        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/submit");

        $response->assertStatus(404);
    }

    public function test_seller_cannot_approve_their_own_product(): void
    {
        Sanctum::actingAs($this->sellerA);

        $this->validProduct->update(['approval_status' => 'pending_review']);

        $response = $this->postJson("/api/v2/admin/catalogue/review/{$this->validProduct->id}/approve");

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_admin_review_queue(): void
    {
        Sanctum::actingAs($this->sellerA);

        $response = $this->getJson("/api/v2/admin/catalogue/review-queue");

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_product_and_publish_to_marketplace(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->validProduct->update([
            'approval_status' => 'pending_review',
            'submitted_at'    => now(),
        ]);

        $response = $this->postJson("/api/v2/admin/catalogue/review/{$this->validProduct->id}/approve", [
            'notes' => 'Product complies with technical data sheet and pricing guidelines.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'approved');

        $this->validProduct->refresh();
        $this->assertEquals('approved', $this->validProduct->approval_status);
        $this->assertEquals('Active', $this->validProduct->getRawOriginal('status'));
        $this->assertEquals($this->adminUser->id, $this->validProduct->reviewed_by);
        $this->assertNotNull($this->validProduct->reviewed_at);

        // Associated seller listings with valid pricing become Active
        $this->validListing->refresh();
        $this->assertEquals('Active', $this->validListing->status);

        // Audit & Review History
        $this->assertDatabaseHas('product_review_histories', [
            'product_id'  => $this->validProduct->id,
            'reviewer_id' => $this->adminUser->id,
            'action'      => 'approved',
            'new_status'  => 'approved',
        ]);

        // Seller notified
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sellerA->id,
            'type'    => 'approval_granted',
        ]);
    }

    public function test_admin_can_reject_product_with_mandatory_reason(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->validProduct->update(['approval_status' => 'pending_review']);

        // Without reason -> 422
        $resWithoutReason = $this->postJson("/api/v2/admin/catalogue/review/{$this->validProduct->id}/reject", [
            'reason' => '',
        ]);
        $resWithoutReason->assertStatus(422);

        // With reason -> 200
        $response = $this->postJson("/api/v2/admin/catalogue/review/{$this->validProduct->id}/reject", [
            'reason' => 'Brand name conflicts with registered trademark.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'rejected');

        $this->validProduct->refresh();
        $this->assertEquals('rejected', $this->validProduct->approval_status);
        $this->assertEquals('Deactive', $this->validProduct->getRawOriginal('status'));
        $this->assertEquals('Brand name conflicts with registered trademark.', $this->validProduct->rejection_reason);

        // Associated seller listings deactivated
        $this->validListing->refresh();
        $this->assertEquals('Deactive', $this->validListing->status);

        // Seller notified
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sellerA->id,
            'type'    => 'rejection_notified',
        ]);
    }

    public function test_admin_can_request_corrections_with_instructions(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->validProduct->update(['approval_status' => 'pending_review']);

        $response = $this->postJson("/api/v2/admin/catalogue/review/{$this->validProduct->id}/request-corrections", [
            'instructions' => 'Please provide white background image and check MOQ.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'corrections_requested');

        $this->validProduct->refresh();
        $this->assertEquals('corrections_requested', $this->validProduct->approval_status);
        $this->assertEquals('Please provide white background image and check MOQ.', $this->validProduct->correction_notes);

        // Review History
        $this->assertDatabaseHas('product_review_histories', [
            'product_id' => $this->validProduct->id,
            'action'     => 'corrections_requested',
            'new_status' => 'corrections_requested',
        ]);

        // Seller notified
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sellerA->id,
            'type'    => 'corrections_requested',
        ]);
    }

    public function test_seller_can_resubmit_corrected_product(): void
    {
        Sanctum::actingAs($this->sellerA);

        $this->validProduct->update([
            'approval_status'  => 'corrections_requested',
            'correction_notes' => 'Please fix finish dynamic attribute.',
        ]);

        $response = $this->postJson("/api/v2/seller/catalogue/products/{$this->validProduct->id}/resubmit", [
            'notes' => 'Corrected finish dynamic attribute as requested.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'resubmitted');

        $this->validProduct->refresh();
        $this->assertEquals('resubmitted', $this->validProduct->approval_status);
        $this->assertNull($this->validProduct->correction_notes);

        $this->assertDatabaseHas('product_review_histories', [
            'product_id'      => $this->validProduct->id,
            'action'          => 'resubmitted',
            'previous_status' => 'corrections_requested',
            'new_status'      => 'resubmitted',
        ]);
    }

    public function test_unapproved_product_is_not_visible_in_public_catalogue_apis(): void
    {
        // 1. When product is in pending_review, public index does NOT return it
        $this->validProduct->update([
            'status'          => 'Active', // Even if status was active
            'approval_status' => 'pending_review',
        ]);

        $response = $this->getJson('/api/v2/products');
        $response->assertStatus(200);
        $productIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertNotContains($this->validProduct->id, $productIds);

        // Search also hides it
        $searchRes = $this->getJson("/api/v2/catalogue/search?query=AlphaCoat");
        $searchRes->assertStatus(200);
        $searchIds = collect($searchRes->json('data'))->pluck('id')->all();
        $this->assertNotContains($this->validProduct->id, $searchIds);

        // Public Show endpoint returns 404 for unauthenticated guest
        $showRes = $this->getJson("/api/v2/products/{$this->validProduct->id}");
        $showRes->assertStatus(404);

        // 2. Once Approved, product IS returned publicly
        $this->validProduct->update([
            'approval_status' => 'approved',
            'status'          => 'Active',
        ]);

        $publicRes = $this->getJson("/api/v2/products/{$this->validProduct->id}");
        $publicRes->assertStatus(200)
            ->assertJsonPath('data.id', $this->validProduct->id);
    }

    public function test_bulk_operation_cannot_bypass_approval_status(): void
    {
        Sanctum::actingAs($this->sellerA);

        $this->validProduct->update(['approval_status' => 'pending_review']);

        // Seller attempts to bulk activate listing while product is pending review
        $bulkService = app()->make(\App\Services\SellerCatalogueBulkService::class);

        $operation = $bulkService->preview($this->companyA, $this->sellerA, [
            'operation_type' => 'status_update',
            'target_entity'  => 'listing',
            'selection_mode' => 'ids',
            'ids'            => [$this->validListing->id],
            'update_payload' => ['status' => 'Active'],
        ]);

        // Listing should be flagged as ineligible because parent product is not approved
        $this->assertEquals(0, $operation->eligible_count);
        $this->assertEquals(1, $operation->ineligible_count);
        $this->assertFalse($operation->canConfirm());
    }

    public function test_seller_and_admin_web_controllers_render_successfully(): void
    {
        // 1. Seller Web Submissions
        $sellerWebRes = $this->actingAs($this->sellerA, 'web')
            ->get(route('seller.catalogue.submissions'));
        $sellerWebRes->assertStatus(200)
            ->assertSee('Catalogue Submissions');

        $sellerDetailRes = $this->actingAs($this->sellerA, 'web')
            ->get(route('seller.catalogue.submissions.show', $this->validProduct->id));
        $sellerDetailRes->assertStatus(200)
            ->assertSee($this->validProduct->name);

        // 2. Admin Review Queue (Admin access)
        $adminQueueRes = $this->actingAs($this->adminUser, 'web')
            ->get(route('admin.catalogue.reviews'));
        $adminQueueRes->assertStatus(200)
            ->assertSee('Catalogue Review Queue');

        $adminDetailRes = $this->actingAs($this->adminUser, 'web')
            ->get(route('admin.catalogue.reviews.show', $this->validProduct->id));
        $adminDetailRes->assertStatus(200)
            ->assertSee($this->validProduct->name);

        // 3. Admin Review Queue (Non-admin access blocked)
        $unauthorizedRes = $this->actingAs($this->sellerA, 'web')
            ->get(route('admin.catalogue.reviews'));
        $unauthorizedRes->assertStatus(403);
    }
}
