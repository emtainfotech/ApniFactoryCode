<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartAttribute;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SubCatgory;
use App\Models\User;
use App\Services\BulkVariantMatrixService;
use App\Services\BuyerCartValidationService;
use App\Services\DynamicPriceRevisionService;
use Database\Seeders\SellerRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase4_1_BuyerCartAndMovTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $sellerA;
    protected Company $companyA;
    protected User $sellerB;
    protected Company $companyB;
    protected User $buyerUser;
    protected User $otherBuyerUser;

    protected Category $categoryIndustrial;
    protected SubCatgory $subcatCoating;

    protected Product $productEpoxy;
    protected Product $productPrimer;

    protected ProductVariant $variant1L;
    protected ProductVariant $variant4L;
    protected ProductVariant $variant20L;

    protected SellerListing $listing1L;   // 1L x 6 tins/box, MOQ=12, ₹500
    protected SellerListing $listing4L;   // 4L x 4 cans/box, MOQ=8,  ₹1800
    protected SellerListing $listing20L;  // 20L x 1 drum/box, MOQ=2, ₹8000 (Seller B)

    protected BuyerCartValidationService $cartService;
    protected BulkVariantMatrixService $matrixService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(SellerRolesAndPermissionsSeeder::class);

        // 1. Users
        $this->adminUser = User::factory()->create([
            'email' => 'admin@apnifactory.com',
            'name'  => 'Platform Super Admin',
        ]);

        $this->sellerA = User::factory()->create([
            'email' => 'alpha@seller.test',
            'name'  => 'Alpha Owner',
        ]);
        $this->companyA = Company::factory()->create([
            'user_id'       => $this->sellerA->id,
            'name'          => 'Alpha Industrial Coatings Ltd',
            'status'        => 'Active',
            'minordervalue' => 10000.00, // ₹10,000 MOV
        ]);

        $this->sellerB = User::factory()->create([
            'email' => 'beta@seller.test',
            'name'  => 'Beta Owner',
        ]);
        $this->companyB = Company::factory()->create([
            'user_id'       => $this->sellerB->id,
            'name'          => 'Beta Polymers Pvt Ltd',
            'status'        => 'Active',
            'minordervalue' => 25000.00, // ₹25,000 MOV
        ]);

        $this->buyerUser = User::factory()->create([
            'email' => 'contractor.buyer@test.com',
            'name'  => 'Premier Contractors',
        ]);

        $this->otherBuyerUser = User::factory()->create([
            'email' => 'other.buyer@test.com',
            'name'  => 'Other Contractor',
        ]);

        // 2. Taxonomy
        $this->categoryIndustrial = Category::create([
            'name'   => 'Industrial Protective Coatings',
            'status' => 'Active',
        ]);

        $this->subcatCoating = SubCatgory::create([
            'category_id' => $this->categoryIndustrial->id,
            'name'        => 'Epoxy & Polyurethane Systems',
            'status'      => 'Active',
        ]);

        // 3. Products
        $this->productEpoxy = Product::create([
            'name'           => 'High-Build Epoxy Enamel',
            'slug'           => 'high-build-epoxy-enamel-' . uniqid(),
            'category_id'    => $this->categoryIndustrial->id,
            'subcategory_id' => $this->subcatCoating->id,
            'user_id'        => $this->sellerA->id,
            'status'         => 'Active',
            'tax'            => 18.0,
        ]);

        $this->productPrimer = Product::create([
            'name'           => 'Zinc Phosphate Polyurethane Primer',
            'slug'           => 'zinc-polyurethane-primer-' . uniqid(),
            'category_id'    => $this->categoryIndustrial->id,
            'subcategory_id' => $this->subcatCoating->id,
            'user_id'        => $this->sellerB->id,
            'status'         => 'Active',
            'tax'            => 18.0,
        ]);

        // 4. Variants
        $this->variant1L = ProductVariant::create([
            'product_id'   => $this->productEpoxy->id,
            'variant_sku'  => 'HBE-1L',
            'pack_litres'  => 1.0,
            'variant_name' => '1 Litre Tin',
            'status'       => 'Active',
        ]);

        $this->variant4L = ProductVariant::create([
            'product_id'   => $this->productEpoxy->id,
            'variant_sku'  => 'HBE-4L',
            'pack_litres'  => 4.0,
            'variant_name' => '4 Litre Can',
            'status'       => 'Active',
        ]);

        $this->variant20L = ProductVariant::create([
            'product_id'   => $this->productPrimer->id,
            'variant_sku'  => 'ZPP-20L',
            'pack_litres'  => 20.0,
            'variant_name' => '20 Litre Drum',
            'status'       => 'Active',
        ]);

        // 5. Seller Listings (Authoritative packaging, MOQ, and stock rules)
        // Seller A: 1L tin, 6 tins/box, MOQ = 12 (must buy in multiples of 6 >= 12)
        $this->listing1L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productEpoxy->id,
            'product_variant_id'  => $this->variant1L->id,
            'seller_sku'          => 'ALPH-HBE-1L',
            'seller_price'        => 500.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 550.00,
            'mrp'                 => 650.00,
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 6,
            'order_multiple'      => 6,
            'min_order_quantity'  => 12,
            'allow_unit_orders'   => false,
            'allow_carton_orders' => true,
            'stock_quantity'      => 120,
            'reserved_stock'      => 0,
            'status'              => 'Active',
        ]);

        // Seller A: 4L can, 4 cans/box, MOQ = 8 (must buy in multiples of 4 >= 8)
        $this->listing4L = SellerListing::create([
            'seller_user_id'      => $this->sellerA->id,
            'company_id'          => $this->companyA->id,
            'product_id'          => $this->productEpoxy->id,
            'product_variant_id'  => $this->variant4L->id,
            'seller_sku'          => 'ALPH-HBE-4L',
            'seller_price'        => 1800.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 1980.00,
            'mrp'                 => 2400.00,
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 4,
            'order_multiple'      => 4,
            'min_order_quantity'  => 8,
            'allow_unit_orders'   => false,
            'allow_carton_orders' => true,
            'stock_quantity'      => 60,
            'reserved_stock'      => 0,
            'status'              => 'Active',
        ]);

        // Seller B: 20L drum, 1 drum/box, MOQ = 2 (multiples of 1 >= 2)
        $this->listing20L = SellerListing::create([
            'seller_user_id'      => $this->sellerB->id,
            'company_id'          => $this->companyB->id,
            'product_id'          => $this->productPrimer->id,
            'product_variant_id'  => $this->variant20L->id,
            'seller_sku'          => 'BETA-ZPP-20L',
            'seller_price'        => 8000.00,
            'commission_rate'     => 10.00,
            'selling_price'       => 8800.00,
            'mrp'                 => 10500.00,
            'unit_of_measurement' => 'Litre',
            'case_pack_quantity'  => 1,
            'order_multiple'      => 1,
            'min_order_quantity'  => 2,
            'allow_unit_orders'   => true,
            'allow_carton_orders' => false,
            'stock_quantity'      => 30,
            'reserved_stock'      => 0,
            'status'              => 'Active',
        ]);

        $this->cartService = app(BuyerCartValidationService::class);
        $this->matrixService = app(BulkVariantMatrixService::class);
    }

    /**
     * TEST 1 – Seller-Specific MOV:
     * Configure two sellers with different MOV values (₹10,000 and ₹25,000).
     * Verify that each seller's cart subtotal is evaluated independently and one seller's
     * excess value cannot satisfy another seller's MOV.
     */
    public function test_mandatory_scenario_1_seller_specific_mov_isolation(): void
    {
        $customerId = $this->buyerUser->id;

        // Seller A: Add 24 tins @ ₹500 = ₹12,000.00 (MOV = ₹10,000 -> MET, shortfall = 0)
        $this->cartService->addItemToCart($customerId, $this->listing1L->id, 24);

        // Seller B: Add 2 drums @ ₹8,000 = ₹16,000.00 (MOV = ₹25,000 -> SHORTFALL ₹9,000)
        $this->cartService->addItemToCart($customerId, $this->listing20L->id, 2);

        $cart = $this->cartService->getGroupedCart($customerId);

        $this->assertEquals(2, $cart['total_sellers_count']);
        $this->assertFalse($cart['all_sellers_can_checkout'], 'Checkout must not be globally permitted when one seller has shortfall');

        // Locate Seller A group
        $groupA = collect($cart['seller_groups'])->firstWhere('company_id', $this->companyA->id);
        $this->assertNotNull($groupA);
        $this->assertEquals(12000.00, $groupA['seller_subtotal']);
        $this->assertEquals(10000.00, $groupA['configured_mov']);
        $this->assertEquals(0.00, $groupA['mov_shortfall']);
        $this->assertEquals(100.0, $groupA['mov_progress_percentage']);
        $this->assertTrue($groupA['is_mov_satisfied']);
        $this->assertTrue($groupA['can_checkout']);

        // Locate Seller B group
        $groupB = collect($cart['seller_groups'])->firstWhere('company_id', $this->companyB->id);
        $this->assertNotNull($groupB);
        $this->assertEquals(16000.00, $groupB['seller_subtotal']);
        $this->assertEquals(25000.00, $groupB['configured_mov']);
        $this->assertEquals(9000.00, $groupB['mov_shortfall']);
        $this->assertEquals(64.0, $groupB['mov_progress_percentage']); // 16000 / 25000 = 64%
        $this->assertFalse($groupB['is_mov_satisfied']);
        $this->assertFalse($groupB['can_checkout']);

        // Excess in Seller A (₹12,000 > ₹10,000) MUST NOT bridge Seller B's ₹9,000 shortfall
        $combinedSubtotal = $groupA['seller_subtotal'] + $groupB['seller_subtotal'];
        $this->assertEquals(28000.00, $combinedSubtotal);
        $this->assertFalse($cart['all_sellers_can_checkout']);
    }

    /**
     * TEST 2 – Carton Pack Multiplier (1L x 6 tins per box):
     * Verify that a SKU configured as 1L x 6 tins per box accepts valid multiples of 6
     * and rejects invalid quantities (e.g., 5, 7, 10, 13).
     */
    public function test_mandatory_scenario_2_carton_pack_multiplier_enforcement(): void
    {
        $customerId = $this->buyerUser->id;

        // Try adding 7 tins (MOQ=12, order_multiple=6) -> Must fail
        try {
            $this->cartService->addItemToCart($customerId, $this->listing1L->id, 7);
            $this->fail('Expected ValidationException for non-multiple quantity.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('quantity', $e->errors());
            $errorMsg = implode(' ', $e->errors()['quantity']);
            $this->assertStringContainsString('multiple of 6', $errorMsg);
        }

        // Try adding 14 tins (>= MOQ 12, but not multiple of 6) -> Must fail
        try {
            $this->cartService->addItemToCart($customerId, $this->listing1L->id, 14);
            $this->fail('Expected ValidationException for non-multiple quantity (14).');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('quantity', $e->errors());
        }

        // Add exactly 18 tins (>= MOQ 12, multiple of 6: 6 x 3) -> Must succeed
        $cart = $this->cartService->addItemToCart($customerId, $this->listing1L->id, 18);
        $this->assertTrue($cart['status']);
        $this->assertEquals(18, $cart['seller_groups'][0]['total_quantity']);
    }

    /**
     * TEST 3 – Different Pack Size (4L x 4 cans per box):
     * Verify that a SKU configured as 4L x 4 cans per box accepts valid multiples of 4
     * and rejects invalid quantities.
     */
    public function test_mandatory_scenario_3_different_pack_size_four_can_multiple(): void
    {
        $customerId = $this->buyerUser->id;

        // Try adding 5 cans (MOQ=8, multiple=4) -> Must fail
        try {
            $this->cartService->addItemToCart($customerId, $this->listing4L->id, 5);
            $this->fail('Expected ValidationException for non-multiple quantity (5).');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('quantity', $e->errors());
        }

        // Try adding 10 cans (>= MOQ 8, but 10 % 4 != 0) -> Must fail
        try {
            $this->cartService->addItemToCart($customerId, $this->listing4L->id, 10);
            $this->fail('Expected ValidationException for non-multiple quantity (10).');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('quantity', $e->errors());
        }

        // Add 12 cans (>= MOQ 8, multiple of 4: 4 x 3) -> Must succeed
        $cart = $this->cartService->addItemToCart($customerId, $this->listing4L->id, 12);
        $this->assertTrue($cart['status']);
        $this->assertEquals(12, $cart['seller_groups'][0]['total_quantity']);
    }

    /**
     * TEST 4 – SKU MOQ Validation:
     * Verify that a quantity below the SKU MOQ is rejected and a valid quantity is accepted.
     */
    public function test_mandatory_scenario_4_sku_moq_enforcement(): void
    {
        $customerId = $this->buyerUser->id;

        // Listing 1L has MOQ=12. Try adding 6 tins (which is a valid carton multiple of 6, but < MOQ 12)
        try {
            $this->cartService->addItemToCart($customerId, $this->listing1L->id, 6);
            $this->fail('Expected ValidationException for quantity below MOQ.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('quantity', $e->errors());
            $errorMsg = $e->errors()['quantity'][0];
            $this->assertStringContainsString('Minimum order quantity', $errorMsg);
        }

        // Add 12 tins (MOQ=12 and multiple=6) -> Must succeed
        $cart = $this->cartService->addItemToCart($customerId, $this->listing1L->id, 12);
        $this->assertTrue($cart['status']);
        $this->assertEquals(12, $cart['seller_groups'][0]['items'][0]['quantity']);
    }

    /**
     * TEST 5 – Seller-Wise Grouping:
     * Add products from multiple sellers and verify that cart groups, subtotals, and MOV calculations remain separate.
     */
    public function test_mandatory_scenario_5_seller_wise_grouping(): void
    {
        $customerId = $this->buyerUser->id;

        // Seller A: 12 tins of 1L @ ₹500 = ₹6,000
        $this->cartService->addItemToCart($customerId, $this->listing1L->id, 12);

        // Seller A: 8 cans of 4L @ ₹1800 = ₹14,400
        $this->cartService->addItemToCart($customerId, $this->listing4L->id, 8);

        // Seller B: 4 drums of 20L @ ₹8000 = ₹32,000
        $this->cartService->addItemToCart($customerId, $this->listing20L->id, 4);

        $cart = $this->cartService->getGroupedCart($customerId);

        $this->assertEquals(2, $cart['total_sellers_count']);

        $groupA = collect($cart['seller_groups'])->firstWhere('company_id', $this->companyA->id);
        $this->assertEquals(2, $groupA['items_count']);
        $this->assertEquals(20400.00, $groupA['seller_subtotal']); // 6000 + 14400
        $this->assertTrue($groupA['is_mov_satisfied']);

        $groupB = collect($cart['seller_groups'])->firstWhere('company_id', $this->companyB->id);
        $this->assertEquals(1, $groupB['items_count']);
        $this->assertEquals(32000.00, $groupB['seller_subtotal']); // 4 * 8000
        $this->assertTrue($groupB['is_mov_satisfied']);

        // Both sellers satisfy their respective MOV, all sellers can checkout
        $this->assertTrue($cart['all_sellers_can_checkout']);
    }

    /**
     * TEST 6 – MOV Progress & Shortfall Calculation:
     * Verify buyer sees the correct shortfall and percentage progress when seller subtotal is below MOV.
     */
    public function test_mandatory_scenario_6_mov_progress_and_shortfall_calculation(): void
    {
        $customerId = $this->buyerUser->id;

        // Seller A: MOV is ₹10,000.
        // Add 12 tins of 1L @ ₹500 = ₹6,000 subtotal.
        $cart = $this->cartService->addItemToCart($customerId, $this->listing1L->id, 12);
        $groupA = $cart['seller_groups'][0];

        $this->assertEquals(6000.00, $groupA['seller_subtotal']);
        $this->assertEquals(10000.00, $groupA['configured_mov']);
        $this->assertEquals(4000.00, $groupA['mov_shortfall']); // 10000 - 6000
        $this->assertEquals(60.0, $groupA['mov_progress_percentage']); // 6000 / 10000 = 60.0%
        $this->assertEquals('shortfall', $groupA['mov_status']);
        $this->assertFalse($groupA['is_mov_satisfied']);
        $this->assertFalse($groupA['can_checkout']);

        // Update quantity to 24 tins @ ₹500 = ₹12,000 subtotal.
        $item = $groupA['items'][0];
        $updated = $this->cartService->updateItemQuantity($customerId, $item['cart_attribute_id'], 24);
        $groupAUpdated = $updated['seller_groups'][0];

        $this->assertEquals(12000.00, $groupAUpdated['seller_subtotal']);
        $this->assertEquals(0.00, $groupAUpdated['mov_shortfall']);
        $this->assertEquals(100.0, $groupAUpdated['mov_progress_percentage']);
        $this->assertEquals('met', $groupAUpdated['mov_status']);
        $this->assertTrue($groupAUpdated['is_mov_satisfied']);
        $this->assertTrue($groupAUpdated['can_checkout']);
    }

    /**
     * TEST 7 – Price and Stock Revalidation:
     * Change price or stock after an item is added to the cart.
     * Verify the cart revalidation detects the change without prematurely mutating or locking.
     */
    public function test_mandatory_scenario_7_price_and_stock_revalidation(): void
    {
        $customerId = $this->buyerUser->id;

        // Add 24 tins @ ₹500.00
        $this->cartService->addItemToCart($customerId, $this->listing1L->id, 24);

        // Before change: Revalidation is 100% clean
        $revalInitial = $this->cartService->revalidateCart($customerId);
        $this->assertTrue($revalInitial['is_checkout_ready']);
        $this->assertFalse($revalInitial['has_changes']);
        $this->assertEmpty($revalInitial['price_changes']);
        $this->assertEmpty($revalInitial['stock_issues']);

        // 1. Simulate Price Revision: Seller price increases from ₹500 to ₹550
        $this->listing1L->update([
            'seller_price'  => 550.00,
            'selling_price' => 605.00,
        ]);

        // 2. Simulate Stock Drop: Available stock drops from 120 to 10 (buyer has 24)
        $this->listing1L->update([
            'stock_quantity' => 10,
        ]);

        // Revalidate: Must catch BOTH price drift and stock insufficiency
        $revalAfter = $this->cartService->revalidateCart($customerId);

        $this->assertFalse($revalAfter['is_checkout_ready']);
        $this->assertTrue($revalAfter['has_changes']);

        // Price change detected
        $this->assertCount(1, $revalAfter['price_changes']);
        $this->assertEquals(500.00, $revalAfter['price_changes'][0]['old_unit_price']);
        $this->assertEquals(550.00, $revalAfter['price_changes'][0]['new_unit_price']);

        // Stock issue detected
        $this->assertCount(1, $revalAfter['stock_issues']);
        $this->assertEquals(24, $revalAfter['stock_issues'][0]['requested_quantity']);
        $this->assertEquals(10, $revalAfter['stock_issues'][0]['available_stock']);
    }

    /**
     * TEST: IDOR Security – Buyer Cart Ownership:
     * Buyer A cannot update or delete Buyer B's cart item.
     */
    public function test_security_buyer_cart_ownership_idor_protection(): void
    {
        // Buyer A adds item
        $cartA = $this->cartService->addItemToCart($this->buyerUser->id, $this->listing1L->id, 12);
        $itemId = $cartA['seller_groups'][0]['items'][0]['cart_attribute_id'];

        // Buyer B attempts to modify Buyer A's item via API
        Sanctum::actingAs($this->otherBuyerUser);
        $respUpdate = $this->putJson("/api/v2/cart/items/{$itemId}", [
            'quantity' => 18,
        ]);
        $respUpdate->assertStatus(404);

        // Buyer B attempts to delete Buyer A's item via API
        $respDelete = $this->deleteJson("/api/v2/cart/items/{$itemId}");
        $respDelete->assertStatus(404);
    }

    /**
     * TEST: IDOR Security – Seller MOV Tenant Isolation:
     * Seller B cannot read or update Seller A's MOV.
     */
    public function test_security_seller_mov_tenant_isolation(): void
    {
        // Seller A checks own MOV
        Sanctum::actingAs($this->sellerA);
        $respA = $this->getJson('/api/v2/seller/mov');
        $respA->assertStatus(200);
        $this->assertEquals(10000.00, $respA->json('data.min_order_value'));

        // Seller B updates own MOV
        Sanctum::actingAs($this->sellerB);
        $respBUpdate = $this->putJson('/api/v2/seller/mov', [
            'min_order_value' => 30000.00,
            'reason'          => 'Annual policy revision',
        ]);
        $respBUpdate->assertStatus(200);
        $this->assertEquals(30000.00, $respBUpdate->json('data.min_order_value'));

        // Verify Seller A's MOV was NOT altered
        $this->companyA->refresh();
        $this->assertEquals(10000.00, $this->companyA->getRawMinOrderValue());
    }

    /**
     * TEST: Bulk Variant Matrix & Independent Validation:
     * Variant matrix displays compatible pack sizes, and validates each SKU independently upon submission.
     */
    public function test_bulk_variant_matrix_independent_validation(): void
    {
        // Get matrix for product
        $matrix = $this->matrixService->getVariantMatrixForProduct($this->productEpoxy->id, $this->companyA->id);
        $this->assertCount(2, $matrix['matrix_rows']); // 1L and 4L

        // Submit invalid bulk matrix: 1L with invalid multiple (7), 4L with valid multiple (8)
        $invalidResult = $this->matrixService->submitBulkMatrix($this->buyerUser->id, [
            ['seller_listing_id' => $this->listing1L->id, 'quantity' => 7],
            ['seller_listing_id' => $this->listing4L->id, 'quantity' => 8],
        ]);

        $this->assertFalse($invalidResult['success']);
        $this->assertArrayHasKey(0, $invalidResult['row_errors']);

        // Submit valid bulk matrix: 1L with 12, 4L with 8
        $validResult = $this->matrixService->submitBulkMatrix($this->buyerUser->id, [
            ['seller_listing_id' => $this->listing1L->id, 'quantity' => 12],
            ['seller_listing_id' => $this->listing4L->id, 'quantity' => 8],
        ]);

        $this->assertTrue($validResult['success']);
        $this->assertEquals(2, $validResult['added_count']);
        $this->assertEquals(20400.00, $validResult['cart_summary']['seller_groups'][0]['seller_subtotal']);
    }

    /**
     * TEST: Separate Freight Reporting:
     * Freight is kept strictly distinct from the product subtotal and MOV qualification.
     */
    public function test_freight_reporting_separate_from_product_subtotal_and_mov(): void
    {
        $customerId = $this->buyerUser->id;
        $cart = $this->cartService->addItemToCart($customerId, $this->listing1L->id, 24);

        $group = $cart['seller_groups'][0];
        $this->assertTrue($group['is_freight_separate']);
        $this->assertEquals(12000.00, $group['seller_subtotal']);
        $this->assertEquals(0.00, $group['freight_amount']);

        // Seller grand total does NOT combine estimated freight into the product subtotal
        $this->assertEquals(12000.00 + $group['seller_tax'], $group['seller_grand_total']);
    }

    /**
     * TEST: Legacy Route Compatibility with Multi-Seller Grouping:
     * Existing /api/usercart endpoint supports products from multiple sellers without error.
     */
    public function test_legacy_api_usercart_multi_seller_support(): void
    {
        $customerId = $this->buyerUser->id;

        // Add items from Seller A and Seller B
        $this->cartService->addItemToCart($customerId, $this->listing1L->id, 12);
        $this->cartService->addItemToCart($customerId, $this->listing20L->id, 2);

        // Call legacy endpoint /api/usercart
        $response = $this->postJson('/api/usercart', [
            'userid' => $customerId,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'code'   => 100,
        ]);

        // Verify seller_groups metadata is present
        $this->assertArrayHasKey('seller_groups', $response->json());
        $this->assertCount(2, $response->json('seller_groups'));
    }

    /**
     * TEST: Web Portal Route for Seller MOV:
     * Seller can view and update MOV via the web settings portal.
     */
    public function test_web_seller_mov_settings_route(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs($this->sellerA);

        $viewResp = $this->get('/seller/settings/mov');
        $viewResp->assertStatus(200);
        $viewResp->assertSee('Minimum Order Value (MOV)');
        $viewResp->assertSee('10,000.00');

        $postResp = $this->post('/seller/settings/mov', [
            'min_order_value' => 15000.00,
            'reason'          => 'Updated for Q4 wholesale discount volume',
        ]);
        $postResp->assertRedirect(route('seller.settings.mov.index'));

        $this->companyA->refresh();
        $this->assertEquals(15000.00, $this->companyA->getRawMinOrderValue());
    }
}
