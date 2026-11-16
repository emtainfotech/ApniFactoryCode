<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Brand;
use App\Models\MainCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use App\Helper\Helper;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductGalleryAndVariantPricingEditTest extends TestCase
{
    use DatabaseTransactions;

    protected $seller;
    protected $company;
    protected $brand;
    protected $mainCategory;
    protected $category;
    protected $packing;
    protected $shade;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create([
            'email' => 'seller_gallery_' . uniqid() . '@example.com',
        ]);

        $this->company = Company::create([
            'user_id' => $this->seller->id,
            'name' => 'Gallery Paint Co',
            'status' => 'Active',
            'comission' => 12.00,
        ]);

        $this->mainCategory = MainCategory::create([
            'name' => 'Wall Paints',
            'slug' => 'wall-paints-' . uniqid(),
            'status' => 'Active',
            'commission_rate' => 10.00,
        ]);

        $this->category = Category::create([
            'maincategory_id' => $this->mainCategory->id,
            'name' => 'Interior Acrylic',
            'slug' => 'interior-acrylic-' . uniqid(),
            'status' => 'Active',
            'commission_rate' => 14.00,
        ]);

        $this->brand = Brand::create([
            'user_id' => $this->seller->id,
            'mid' => $this->mainCategory->id,
            'category_id' => $this->category->id,
            'name' => 'Rainbow Colors',
            'status' => 'Active',
        ]);

        $this->packing = BoxPacking::create([
            'name' => '20 LTR',
            'pcs' => 1,
            'status' => '1',
        ]);

        $this->shade = ShadeCard::create([
            'name' => 'Royal White',
            'hexcode' => '#FFFFFF',
            'category_id' => $this->category->id,
            'status' => '1',
        ]);

        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'maincategory_id' => $this->mainCategory->id,
            'category_id' => $this->category->id,
            'subcategory_id' => 0,
            'brand_id' => $this->brand->id,
            'name' => 'Royal Luxury Emulsion',
            'slug' => 'royal-luxury-emulsion-' . uniqid(),
            'title' => 'Royal Luxury Emulsion',
            'description' => 'Super premium wall paint',
            'status' => 'Active',
            'approval_status' => 'approved',
            'has_gst_included' => 0,
            'image' => 'product/royal_main.jpg',
            'multipleimages' => json_encode(['product/gallery1.jpg', 'product/gallery2.jpg']),
        ]);
    }

    public function test_helper_image_url_sanitizes_paths_and_handles_empty()
    {
        $this->assertStringContainsString('img/Apni-Factory-3.png', Helper::imageUrl(null));
        $this->assertStringContainsString('img/Apni-Factory-3.png', Helper::imageUrl(''));
        
        $clean = Helper::imageUrl('storage/app/public/product/abc.jpg');
        $this->assertStringContainsString('/storage/product/abc.jpg', $clean);
        $this->assertStringNotContainsString('storage/app/public/storage', $clean);

        $cleanDirect = Helper::imageUrl('product/abc.jpg');
        $this->assertStringContainsString('/storage/product/abc.jpg', $cleanDirect);
    }

    public function test_storage_fallback_route_serves_valid_response_for_images()
    {
        $response = $this->get('/storage/product/sample-nonexistent-image.jpg');
        // Should return 200 with fallback image or 200 stream, not a crash or 500
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_product_detail_api_returns_clean_multipleimages_array_and_falls_back_to_main_image()
    {
        // 1. With multiple images
        $response = $this->postJson('/api/productdetail', [
            'productid' => $this->product->id,
        ]);
        $response->assertStatus(200);
        $res = $response->json();
        $this->assertTrue($res['status']);
        $data = $res['data'];
        $this->assertIsArray($data['multipleimages']);
        $this->assertCount(2, $data['multipleimages']);
        $this->assertEquals('product/gallery1.jpg', $data['multipleimages'][0]);

        // 2. With empty multipleimages
        $this->product->update(['multipleimages' => null]);
        $response2 = $this->postJson('/api/productdetail', [
            'productid' => $this->product->id,
        ]);
        $response2->assertStatus(200);
        $res2 = $response2->json();
        $data2 = $res2['data'];
        $this->assertIsArray($data2['multipleimages']);
        $this->assertCount(1, $data2['multipleimages']);
        $this->assertEquals('product/royal_main.jpg', $data2['multipleimages'][0]);
    }

    public function test_seller_can_update_attribute_variant_pricing_with_gst_and_routes_to_review()
    {
        $attribute = ProductAttributes::create([
            'product_id' => $this->product->id,
            'quantity' => $this->packing->id,
            'color' => $this->shade->id,
            'seller_price' => 100.00,
            'price' => 114.00,
            'oldprice' => 150.00,
            'mrp' => 150.00,
            'is_gst_included' => 0,
        ]);

        $this->actingAs($this->seller);

        // Update pricing with GST included
        $response = $this->postJson('/seller/updateattributeprice', [
            'aid' => $attribute->id,
            'seller_price' => 120.00,
            'price' => 136.80,
            'mrp' => 180.00,
            'is_gst_included' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'is_gst_included' => 1,
        ]);

        $attribute->refresh();
        $this->assertEquals(120.00, (float)$attribute->seller_price);
        $this->assertEquals(136.80, (float)$attribute->price);
        $this->assertEquals(1, (int)$attribute->is_gst_included);

        // Parent product must be flagged for admin review
        $this->product->refresh();
        $this->assertEquals(1, (int)$this->product->has_gst_included);
        $this->assertEquals('pending_review', $this->product->approval_status);
        $this->assertEquals('Deactive', $this->product->status);
    }

    /** @test */
    public function test_filament_product_resource_form_builds_without_exception()
    {
        $admin = User::factory()->create([
            'id' => 1,
            'email' => 'admin@apnifactory.com',
        ]);

        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Filament\Resources\ProductResource\Pages\EditProduct::class, [
            'record' => $this->product->id,
        ])->assertSuccessful();
    }
}
