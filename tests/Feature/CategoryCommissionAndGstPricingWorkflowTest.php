<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\SubCatgory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryCommissionAndGstPricingWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $sellerUser;
    protected User $otherSellerUser;
    protected Company $sellerCompany;
    protected Company $otherSellerCompany;
    protected MainCategory $mainCategory;
    protected Category $category;
    protected SubCatgory $subCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Admin User
        $this->adminUser = User::factory()->create([
            'email' => 'admin_test_' . uniqid() . '@example.com',
        ]);

        // 2. Setup Seller User 1
        $this->sellerUser = User::factory()->create([
            'email' => 'seller_test_' . uniqid() . '@example.com',
        ]);
        $this->sellerCompany = Company::create([
            'user_id'   => $this->sellerUser->id,
            'name'      => 'Apex Paints Ltd',
            'comission' => 20.00,
        ]);

        // 3. Setup Seller User 2 (Other Company)
        $this->otherSellerUser = User::factory()->create([
            'email' => 'other_seller_' . uniqid() . '@example.com',
        ]);
        $this->otherSellerCompany = Company::create([
            'user_id'   => $this->otherSellerUser->id,
            'name'      => 'Beta Coatings Corp',
            'comission' => 15.00,
        ]);

        // 4. Setup 3-Tier Category Tree
        $this->mainCategory = MainCategory::create([
            'name'            => 'Paints & Finishes ' . uniqid(),
            'title'           => 'paints-finishes',
            'image'           => 'paints.jpg',
            'sequence'        => 1,
            'status'          => '1',
            'commission_rate' => 18.00,
        ]);

        $this->category = Category::create([
            'name'              => 'Interior Emulsion ' . uniqid(),
            'title'             => 'interior-emulsion',
            'maincategory_id'   => $this->mainCategory->id,
            'mid'               => $this->mainCategory->id,
            'commission_rate'   => 15.00,
            'status'            => '1',
        ]);

        $this->subCategory = SubCatgory::create([
            'name'              => 'Luxury Silk Emulsion ' . uniqid(),
            'title'             => 'luxury-silk',
            'maincategory_id'   => $this->mainCategory->id,
            'category_id'       => $this->category->id,
            'cid'               => $this->category->id,
            'commission_rate'   => null, // Will inherit 15% from Category
            'status'            => '1',
        ]);
    }

    /**
     * Test 1: Admin Category Commission Tree view and hierarchical cascading update.
     */
    public function test_admin_category_commission_tree_and_cascading_update()
    {
        $this->actingAs($this->adminUser);

        // 1. View category commission tree page
        $response = $this->get(route('admin.commissions.categories'));
        $response->assertStatus(200);
        $response->assertSee('Category Commission Hierarchy');
        $response->assertSee($this->mainCategory->name);

        // 2. Update Main Category commission with cascade = true
        $updateResponse = $this->postJson(route('admin.commissions.categories.update'), [
            'level'           => 'main',
            'id'              => $this->mainCategory->id,
            'commission_rate' => 22.50,
            'cascade'         => 1,
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['status' => true]);

        // Verify database updates
        $this->mainCategory->refresh();
        $this->category->refresh();
        $this->subCategory->refresh();

        $this->assertEquals(22.50, (float)$this->mainCategory->commission_rate);
        $this->assertEquals(22.50, (float)$this->category->commission_rate);
        $this->assertEquals(22.50, (float)$this->subCategory->commission_rate);

        // 3. Override specific subcategory without affecting main or parent
        $subcatUpdate = $this->postJson(route('admin.commissions.categories.update'), [
            'level'           => 'subcategory',
            'id'              => $this->subCategory->id,
            'commission_rate' => 12.00,
            'cascade'         => 0,
        ]);

        $subcatUpdate->assertStatus(200);
        $this->subCategory->refresh();
        $this->category->refresh();

        $this->assertEquals(12.00, (float)$this->subCategory->commission_rate);
        $this->assertEquals(22.50, (float)$this->category->commission_rate);
    }

    /**
     * Test 2: Seller product creation with GST Included routes to Admin Review Bucket for display price approval.
     */
    public function test_seller_product_creation_with_gst_included_routes_to_review_bucket()
    {
        $this->actingAs($this->sellerUser);

        $postData = [
            'previousdata' => json_encode([
                'name'         => 'Royale Luxury White Paint',
                'maincategory' => $this->mainCategory->id,
                'category'     => $this->category->id,
                'brand'        => 1,
                'tax'          => 18,
                'hsncode'      => '32081090',
                'details'      => 'High sheen washable paint.',
            ]),
            'boxpacking'      => [1, 2],
            'color'           => [10, 10],
            'seller_price'    => [1000.00, 500.00], // Factory prices
            'is_gst_included' => [0 => '1', 1 => '0'], // First variant has GST included
            'price'           => [1225.00, 612.50], // Display prices
            'mrp'             => [1500.00, 800.00], // Optional MRP
        ];

        $response = $this->post(route('product.store'), $postData);
        $response->assertRedirect('/seller/product');
        $response->assertSessionHas('success');

        // Check created product
        $product = Product::where('name', 'Royale Luxury White Paint')->first();
        $this->assertNotNull($product);
        $this->assertEquals(1, $product->has_gst_included);
        $this->assertEquals('pending_review', $product->approval_status);
        $this->assertEquals('Deactive', $product->status); // Inactive pending approval

        // Verify SKUs attributes
        $attributes = ProductAttributes::where('product_id', $product->id)->get();
        $this->assertCount(2, $attributes);

        $gstSku = $attributes->first();
        $this->assertEquals(1000.00, (float)$gstSku->seller_price);
        $this->assertTrue((bool)$gstSku->is_gst_included);
        $this->assertEquals(1225.00, (float)$gstSku->price);
        $this->assertEquals(1500.00, (float)$gstSku->mrp);
    }

    /**
     * Test 3: Admin Review Bucket can modify Display Price and approve product live.
     */
    public function test_admin_can_adjust_display_price_and_approve_product()
    {
        $brand = \App\Models\Brand::create([
            'name'    => 'Asian Royal Paints',
            'user_id' => $this->sellerUser->id,
            'status'  => '1',
        ]);

        // 1. Create a pending product with GST included
        $product = Product::create([
            'name'             => 'Silk Glow Wall Paint',
            'slug'             => 'silk-glow-' . uniqid(),
            'title'            => 'silk-glow',
            'image'            => 'product/sample_paint.jpg',
            'maincategory_id'  => $this->mainCategory->id,
            'category_id'      => $this->category->id,
            'subcategory_id'   => $this->subCategory->id,
            'brand_id'         => $brand->id,
            'user_id'          => $this->sellerUser->id,
            'status'           => 'Deactive',
            'approval_status'  => 'pending_review',
            'has_gst_included' => 1,
            'submitted_at'     => now(),
        ]);

        $sku = ProductAttributes::create([
            'product_id'      => $product->id,
            'color'           => 1,
            'quantity'        => 1,
            'seller_price'    => 1000.00,
            'is_gst_included' => 1,
            'commission_rate' => 20.00,
            'price'           => 1200.00,
            'mrp'             => 1400.00,
            'status'          => '1',
        ]);

        $this->actingAs($this->adminUser);

        // 2. Admin adjusts Display Price to 1250.00 and Factory Price to 1020.00
        $updatePriceResponse = $this->post(route('admin.catalogue.reviews.update-pricing', $product->id), [
            'attributes' => [
                $sku->id => [
                    'seller_price'    => 1020.00,
                    'price'           => 1250.00,
                    'mrp'             => 1450.00,
                    'is_gst_included' => 1,
                ]
            ]
        ]);

        $updatePriceResponse->assertSessionHas('success');

        $sku->refresh();
        $this->assertEquals(1020.00, (float)$sku->seller_price);
        $this->assertEquals(1250.00, (float)$sku->price);
        $this->assertEquals(1450.00, (float)$sku->mrp);

        // 3. Admin Approves Product
        $approveResponse = $this->post(route('admin.catalogue.reviews.approve', $product->id));
        $approveResponse->assertRedirect(route('admin.catalogue.reviews'));

        $product->refresh();
        $this->assertEquals('approved', $product->approval_status);
        $this->assertEquals('Active', $product->status);
    }

    /**
     * Test 4: Seller Category Pricing & Offer Manager strictly affects own company products.
     */
    public function test_seller_category_pricing_strictly_affects_own_company_products()
    {
        // 1. Create product for Seller 1 (Apex Paints)
        $prodSeller1 = Product::create([
            'name'            => 'Apex Acrylic Primer',
            'slug'            => 'apex-primer-' . uniqid(),
            'title'           => 'apex-primer',
            'category_id'     => $this->category->id,
            'user_id'         => $this->sellerUser->id,
            'status'          => 'Active',
            'approval_status' => 'approved',
        ]);
        $sku1 = ProductAttributes::create([
            'product_id'      => $prodSeller1->id,
            'color'           => 1,
            'quantity'        => 1,
            'seller_price'    => 200.00,
            'commission_rate' => 20.00,
            'price'           => 240.00,
            'status'          => '1',
        ]);

        // 2. Create product for Seller 2 (Beta Coatings) in same category
        $prodSeller2 = Product::create([
            'name'            => 'Beta Exterior Sealer',
            'slug'            => 'beta-sealer-' . uniqid(),
            'title'           => 'beta-sealer',
            'category_id'     => $this->category->id,
            'user_id'         => $this->otherSellerUser->id,
            'status'          => 'Active',
            'approval_status' => 'approved',
        ]);
        $sku2 = ProductAttributes::create([
            'product_id'      => $prodSeller2->id,
            'color'           => 1,
            'quantity'        => 1,
            'seller_price'    => 300.00,
            'commission_rate' => 20.00,
            'price'           => 360.00,
            'status'          => '1',
        ]);

        // 3. Login as Seller 1
        $this->actingAs($this->sellerUser);

        // Update SKU 1 factory price to 250.00
        $updateSkuResponse = $this->postJson(route('seller.category-pricing.update-sku'), [
            'sku_id'       => $sku1->id,
            'seller_price' => 250.00,
        ]);
        $updateSkuResponse->assertStatus(200);
        $updateSkuResponse->assertJson([
            'status'        => true,
            'seller_price'  => 250.00,
        ]);

        $sku1->refresh();
        $this->assertEquals(250.00, (float)$sku1->seller_price);
        // Display price calculated: 250 * (1 + 15/100) = 287.50
        $this->assertEquals(287.50, (float)$sku1->price);

        // 4. Security Check: Seller 1 cannot modify Seller 2's SKU
        $forbiddenResponse = $this->postJson(route('seller.category-pricing.update-sku'), [
            'sku_id'       => $sku2->id,
            'seller_price' => 999.00,
        ]);
        $forbiddenResponse->assertStatus(403);

        $sku2->refresh();
        $this->assertEquals(300.00, (float)$sku2->seller_price);
        $this->assertEquals(360.00, (float)$sku2->price); // Unchanged!

        // 5. Bulk Category Offer for Seller 1 (e.g. -10% discount on factory price)
        $bulkResponse = $this->postJson(route('seller.category-pricing.bulk-apply'), [
            'category_id'      => $this->category->id,
            'adjustment_type'  => 'percentage',
            'adjustment_value' => -10, // -10% discount
        ]);
        $bulkResponse->assertStatus(200);

        $sku1->refresh();
        // 250 - 10% = 225.00
        $this->assertEquals(225.00, (float)$sku1->seller_price);
        // 225 * 1.15 = 258.75
        $this->assertEquals(258.75, (float)$sku1->price);

        // Verify Seller 2 was completely untouched
        $sku2->refresh();
        $this->assertEquals(300.00, (float)$sku2->seller_price);
        $this->assertEquals(360.00, (float)$sku2->price);
    }
}
