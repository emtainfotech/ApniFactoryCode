<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductReviewHistory;
use App\Models\ShadeCard;
use App\Models\User;
use App\Services\PaintPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShadeGroupingAndGstRatePricingTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private User $admin;
    private Company $company;
    private MainCategory $mainCategory;
    private Category $category;
    private Brand $brand;
    private ShadeCard $shade1;
    private ShadeCard $shade2;
    private ShadeCard $shade3;
    private ShadeCard $shade4;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seller & Company
        $this->seller = User::factory()->create([
            'email' => 'paint_seller_' . uniqid() . '@example.com',
        ]);
        $this->company = Company::create([
            'user_id'   => $this->seller->id,
            'name'      => 'Asian Paints Seller Ltd',
            'comission' => 20.00,
        ]);

        // 2. Admin
        $this->admin = User::factory()->create([
            'email' => 'admin_reviewer_' . uniqid() . '@example.com',
        ]);

        // 3. Category Tree with 25% commission
        $this->mainCategory = MainCategory::create([
            'name'            => 'Interior & Exterior Paints',
            'title'           => 'interior-exterior-paints',
            'commission_rate' => 25.00,
        ]);

        $this->category = Category::create([
            'maincategory_id' => $this->mainCategory->id,
            'name'            => 'Luxury Emulsions',
            'title'           => 'luxury-emulsions',
            'commission_rate' => 25.00,
        ]);

        $this->brand = Brand::create([
            'user_id'     => $this->seller->id,
            'category_id' => $this->category->id,
            'name'        => 'Royale Luxury',
        ]);

        // 4. Color Shades
        $this->shade1 = ShadeCard::create([
            'category_id' => $this->category->id,
            'name'        => 'Brilliant White',
            'hexcode'     => '#FFFFFF',
        ]);
        $this->shade2 = ShadeCard::create([
            'category_id' => $this->category->id,
            'name'        => 'Blazing White',
            'hexcode'     => '#F8F8F8',
        ]);
        $this->shade3 = ShadeCard::create([
            'category_id' => $this->category->id,
            'name'        => 'Oxford Blue',
            'hexcode'     => '#002147',
        ]);
        $this->shade4 = ShadeCard::create([
            'category_id' => $this->category->id,
            'name'        => 'Mercedes Red',
            'hexcode'     => '#C00000',
        ]);
    }

    public function test_seller_can_create_product_with_shade_groups_and_gst_rate()
    {
        $this->actingAs($this->seller);

        $groupsData = [
            [
                'group_id'        => 'group_1',
                'name'            => 'Gr-0 Whites',
                'shades'          => [$this->shade1->id, $this->shade2->id],
                'is_gst_included' => 1,
                'gst_rate'        => 18.00,
                'packs'           => [
                    '1' => ['seller_price' => 4000.00, 'price' => 5000.00, 'mrp' => 5500.00], // 25% comm: 4000 * 1.25 = 5000
                    '2' => ['seller_price' => 2000.00, 'price' => 2500.00, 'mrp' => 2800.00],
                ]
            ],
            [
                'group_id'        => 'group_2',
                'name'            => 'Gr-1 Deep Colors',
                'shades'          => [$this->shade3->id, $this->shade4->id],
                'is_gst_included' => 0,
                'gst_rate'        => 18.00,
                'packs'           => [
                    '1' => ['seller_price' => 4500.00, 'price' => 5625.00, 'mrp' => 6000.00], // 4500 * 1.25 = 5625
                    '2' => ['seller_price' => 2300.00, 'price' => 2875.00, 'mrp' => 3100.00],
                ]
            ]
        ];

        $response = $this->post(route('product.store'), [
            'previousdata' => json_encode([
                'name'         => 'Royale Touch Silk Emulsion',
                'maincategory' => $this->mainCategory->id,
                'category'     => $this->category->id,
                'brand'        => $this->brand->id,
                'tax'          => 18,
                'hsncode'      => '32091000',
                'details'      => 'High sheen luxury emulsion',
            ]),
            'shade_groups_data' => json_encode($groupsData),
        ]);

        $response->assertRedirect('/seller/product');

        $product = Product::where('name', 'Royale Touch Silk Emulsion')->first();
        $this->assertNotNull($product);

        // Product has GST included (from Group 1), so routed to review bucket
        $this->assertEquals(1, (int)$product->has_gst_included);
        $this->assertEquals('pending_review', $product->approval_status);
        $this->assertEquals('Deactive', $product->status);

        // Attributes created: 2 groups * 2 shades * 2 packs = 8 SKUs
        $this->assertEquals(8, $product->product_attributes()->count());

        // Check Group 1 attributes (Gr-0 Whites)
        $whiteSku = ProductAttributes::where('product_id', $product->id)
            ->where('color', $this->shade1->id)
            ->where('quantity', '1')
            ->first();

        $this->assertNotNull($whiteSku);
        $this->assertEquals('Gr-0 Whites', $whiteSku->group_name);
        $this->assertEquals(4000.00, (float)$whiteSku->seller_price);
        $this->assertEquals(5000.00, (float)$whiteSku->price);
        $this->assertEquals(1, (int)$whiteSku->is_gst_included);
        $this->assertEquals(18.00, (float)$whiteSku->gst_rate);
        $this->assertEquals(5500.00, (float)$whiteSku->mrp);

        // Check Group 2 attributes (Gr-1 Deep Colors)
        $blueSku = ProductAttributes::where('product_id', $product->id)
            ->where('color', $this->shade3->id)
            ->where('quantity', '1')
            ->first();

        $this->assertNotNull($blueSku);
        $this->assertEquals('Gr-1 Deep Colors', $blueSku->group_name);
        $this->assertEquals(4500.00, (float)$blueSku->seller_price);
        $this->assertEquals(5625.00, (float)$blueSku->price);
        $this->assertEquals(0, (int)$blueSku->is_gst_included);

        // Verify history log was generated
        $log = ProductReviewHistory::where('product_id', $product->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('submitted_for_gst_review', $log->action);
        $this->assertEquals($this->seller->id, $log->seller_user_id);
    }

    public function test_seller_can_update_shade_group_pricing_and_propagate_to_all_group_shades()
    {
        $this->actingAs($this->seller);

        // Create initial product with Group 1 having 2 shades
        $product = Product::create([
            'name'            => 'Apex Ultima Exterior',
            'maincategory_id' => $this->mainCategory->id,
            'category_id'     => $this->category->id,
            'brand_id'        => $this->brand->id,
            'user_id'         => $this->seller->id,
            'tax'             => 18,
            'approval_status' => 'approved',
            'status'          => 'Active',
            'shade_groups'    => [
                [
                    'group_id'        => 'group_1',
                    'name'            => 'Standard Whites',
                    'shades'          => [$this->shade1->id, $this->shade2->id],
                    'is_gst_included' => 0,
                    'gst_rate'        => 18.00,
                    'packs'           => [
                        '1' => ['seller_price' => 3000.00, 'price' => 3750.00, 'mrp' => 4000.00],
                    ]
                ]
            ]
        ]);

        // Insert initial attributes
        foreach ([$this->shade1->id, $this->shade2->id] as $sId) {
            ProductAttributes::create([
                'product_id'      => $product->id,
                'color'           => $sId,
                'quantity'        => 1,
                'group_name'      => 'Standard Whites',
                'seller_price'    => 3000.00,
                'price'           => 3750.00,
                'commission_rate' => 25.00,
                'is_gst_included' => 0,
                'gst_rate'        => 18.00,
                'status'          => '1',
            ]);
        }

        // Now seller updates Group 1 pricing to 3200 with GST Included = 1 (18%)
        $response = $this->post(route('seller.product.update-group-pricing', $product->id), [
            'group_id'        => 'group_1',
            'group_name'      => 'Standard Whites',
            'is_gst_included' => 1,
            'gst_rate'        => 18.00,
            'packs'           => [
                '1' => ['seller_price' => 3200.00, 'price' => 4000.00, 'mrp' => 4500.00], // 3200 * 1.25 = 4000
            ]
        ]);

        $response->assertSessionHas('success');

        // Both shade 1 and shade 2 in this group MUST be updated to 3200 / 4000!
        $attr1 = ProductAttributes::where('product_id', $product->id)->where('color', $this->shade1->id)->first();
        $attr2 = ProductAttributes::where('product_id', $product->id)->where('color', $this->shade2->id)->first();

        $this->assertEquals(3200.00, (float)$attr1->seller_price);
        $this->assertEquals(4000.00, (float)$attr1->price);
        $this->assertEquals(1, (int)$attr1->is_gst_included);

        $this->assertEquals(3200.00, (float)$attr2->seller_price);
        $this->assertEquals(4000.00, (float)$attr2->price);
        $this->assertEquals(1, (int)$attr2->is_gst_included);

        // Product routed to review bucket because GST was included
        $product->refresh();
        $this->assertEquals(1, (int)$product->has_gst_included);
        $this->assertEquals('pending_review', $product->approval_status);

        // Verify history log was created for seller group pricing update
        $log = ProductReviewHistory::where('product_id', $product->id)
            ->where('action', 'seller_updated_group_pricing')
            ->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Standard Whites', $log->notes);
    }

    public function test_shades_cannot_repeat_across_multiple_groups()
    {
        $this->actingAs($this->seller);

        $product = Product::create([
            'name'            => 'Tractor Emulsion',
            'maincategory_id' => $this->mainCategory->id,
            'category_id'     => $this->category->id,
            'brand_id'        => $this->brand->id,
            'user_id'         => $this->seller->id,
            'approval_status' => 'approved',
            'status'          => 'Active',
            'shade_groups'    => [
                [
                    'group_id' => 'group_1',
                    'name'     => 'Group 1',
                    'shades'   => [$this->shade1->id],
                    'packs'    => ['1' => ['seller_price' => 1000, 'price' => 1250]]
                ]
            ]
        ]);

        ProductAttributes::create([
            'product_id'   => $product->id,
            'color'        => $this->shade1->id,
            'quantity'     => 1,
            'group_name'   => 'Group 1',
            'seller_price' => 1000,
            'price'        => 1250,
            'status'       => '1',
        ]);

        // Attempt to create Group 2 with the same shade1 (must be rejected!)
        $response = $this->post(route('seller.product.create-shade-group', $product->id), [
            'group_name' => 'Group 2',
            'shades'     => [$this->shade1->id], // Duplicate shade!
            'packs'      => ['1' => ['seller_price' => 1500, 'price' => 1875]],
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('already assigned to another group', session('error'));
    }

    public function test_seller_can_add_and_remove_shades_in_existing_group()
    {
        $this->actingAs($this->seller);

        $product = Product::create([
            'name'            => 'Apex Floor Guard',
            'maincategory_id' => $this->mainCategory->id,
            'category_id'     => $this->category->id,
            'brand_id'        => $this->brand->id,
            'user_id'         => $this->seller->id,
            'approval_status' => 'approved',
            'status'          => 'Active',
            'shade_groups'    => [
                [
                    'group_id' => 'group_1',
                    'name'     => 'Floor Base Group',
                    'shades'   => [$this->shade1->id],
                    'packs'    => ['1' => ['seller_price' => 2500, 'price' => 3125]]
                ]
            ]
        ]);

        ProductAttributes::create([
            'product_id'      => $product->id,
            'color'           => $this->shade1->id,
            'quantity'        => 1,
            'group_name'      => 'Floor Base Group',
            'seller_price'    => 2500,
            'price'           => 3125,
            'commission_rate' => 25.00,
            'status'          => '1',
        ]);

        // 1. Add shade 2 to Floor Base Group
        $addResponse = $this->post(route('seller.product.add-shade-to-group', $product->id), [
            'group_name' => 'Floor Base Group',
            'color_id'   => $this->shade2->id,
        ]);

        $addResponse->assertSessionHas('success');

        // Check shade 2 now has attributes inheriting group 1 pricing!
        $newAttr = ProductAttributes::where('product_id', $product->id)
            ->where('color', $this->shade2->id)
            ->first();
        $this->assertNotNull($newAttr);
        $this->assertEquals('Floor Base Group', $newAttr->group_name);
        $this->assertEquals(2500.00, (float)$newAttr->seller_price);
        $this->assertEquals(3125.00, (float)$newAttr->price);

        // 2. Remove shade 1 from group
        $removeResponse = $this->post(route('seller.product.remove-shade-from-group', $product->id), [
            'group_name' => 'Floor Base Group',
            'color_id'   => $this->shade1->id,
        ]);

        $removeResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('product_attributes', [
            'product_id' => $product->id,
            'color'      => $this->shade1->id,
            'group_name' => 'Floor Base Group',
        ]);
    }

    public function test_admin_review_bucket_supports_group_pricing_updates_and_generates_history()
    {
        $this->actingAs($this->admin);

        // Create product with GST included in Group 1
        $product = Product::create([
            'name'             => 'Royale Glitz',
            'slug'             => 'royale-glitz-' . uniqid(),
            'description'      => 'Ultra luxury interior finish with Teflon surface protector.',
            'image'            => 'product/default.jpg',
            'hsncode'          => '32091000',
            'maincategory_id'  => $this->mainCategory->id,
            'category_id'      => $this->category->id,
            'brand_id'         => $this->brand->id,
            'user_id'          => $this->seller->id,
            'tax'              => 18,
            'approval_status'  => 'pending_review',
            'has_gst_included' => 1,
            'status'           => 'Deactive',
            'shade_groups'     => [
                [
                    'group_id'        => 'group_1',
                    'name'            => 'Group 1',
                    'shades'          => [$this->shade1->id, $this->shade2->id],
                    'is_gst_included' => 1,
                    'gst_rate'        => 18.00,
                    'packs'           => [
                        '1' => ['seller_price' => 2000.00, 'price' => 2500.00],
                    ]
                ]
            ]
        ]);

        foreach ([$this->shade1->id, $this->shade2->id] as $sId) {
            ProductAttributes::create([
                'product_id'      => $product->id,
                'color'           => $sId,
                'quantity'        => 1,
                'group_name'      => 'Group 1',
                'seller_price'    => 2000.00,
                'price'           => 2500.00,
                'commission_rate' => 25.00,
                'is_gst_included' => 1,
                'gst_rate'        => 18.00,
                'status'          => '1',
            ]);
        }

        // Admin adjusts Group 1 display price from 2500 to 2400 in review bucket
        $updateResp = $this->post(route('admin.catalogue.reviews.update-pricing', $product->id), [
            'groups' => [
                'group_1' => [
                    'name'  => 'Group 1',
                    'packs' => [
                        '1' => ['seller_price' => 2000.00, 'price' => 2400.00],
                    ]
                ]
            ]
        ]);

        $updateResp->assertSessionHas('success');

        // Both shades in Group 1 have new display price 2400
        $attr1 = ProductAttributes::where('product_id', $product->id)->where('color', $this->shade1->id)->first();
        $attr2 = ProductAttributes::where('product_id', $product->id)->where('color', $this->shade2->id)->first();
        $this->assertEquals(2400.00, (float)$attr1->price);
        $this->assertEquals(2400.00, (float)$attr2->price);

        // Verify history log was created with reviewer_id = admin
        $histLog = ProductReviewHistory::where('product_id', $product->id)
            ->where('action', 'pricing_adjusted_by_admin')
            ->first();
        $this->assertNotNull($histLog);
        $this->assertEquals($this->admin->id, $histLog->reviewer_id);

        // Now Admin approves product live
        $approveResp = $this->post(route('admin.catalogue.reviews.approve', $product->id));
        $approveResp->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('approved', $product->approval_status);
        $this->assertEquals('Active', $product->status);

        // Approved action logged in history
        $approveLog = ProductReviewHistory::where('product_id', $product->id)
            ->where('action', 'approved')
            ->first();
        $this->assertNotNull($approveLog);
    }
}

