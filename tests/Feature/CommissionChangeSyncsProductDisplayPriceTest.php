<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Brand;
use App\Models\MainCategory;
use App\Models\Category;
use App\Models\SubCatgory;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use App\Models\ProductReviewHistory;
use App\Services\PaintPricingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CommissionChangeSyncsProductDisplayPriceTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $seller;
    protected $company;
    protected $mainCategory;
    protected $category;
    protected $packing20L;
    protected $packing10L;
    protected $shadeWhite;
    protected $shadeCream;
    protected $product;
    protected $attrGstYes;
    protected $attrGstNo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin_comm_' . uniqid() . '@example.com',
        ]);

        $this->seller = User::factory()->create([
            'email' => 'seller_comm_' . uniqid() . '@example.com',
        ]);

        $this->company = Company::create([
            'user_id' => $this->seller->id,
            'name' => 'Apex Test Paints',
            'status' => 'Active',
            'comission' => 15.00,
        ]);

        $this->mainCategory = MainCategory::create([
            'name' => 'Decorative Paints ' . uniqid(),
            'slug' => 'decorative-paints-' . uniqid(),
            'status' => 'Active',
            'commission_rate' => 10.00, // 10%
        ]);

        $this->category = Category::create([
            'maincategory_id' => $this->mainCategory->id,
            'name' => 'Exterior Emulsion ' . uniqid(),
            'slug' => 'exterior-emulsion-' . uniqid(),
            'status' => 'Active',
            'commission_rate' => 10.00, // 10%
        ]);

        $brand = Brand::create([
            'user_id' => $this->seller->id,
            'mid' => $this->mainCategory->id,
            'category_id' => $this->category->id,
            'name' => 'Brand Test',
            'status' => 'Active',
        ]);

        $this->packing20L = BoxPacking::create(['name' => '20 LTR', 'pcs' => 1, 'status' => '1']);
        $this->packing10L = BoxPacking::create(['name' => '10 LTR', 'pcs' => 1, 'status' => '1']);
        $this->shadeWhite = ShadeCard::create(['name' => 'White ' . uniqid(), 'hexcode' => '#fff', 'status' => '1']);
        $this->shadeCream = ShadeCard::create(['name' => 'Cream ' . uniqid(), 'hexcode' => '#f5f5dc', 'status' => '1']);

        // Product with factory prices
        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'maincategory_id' => $this->mainCategory->id,
            'category_id' => $this->category->id,
            'subcategory_id' => 0,
            'brand_id' => $brand->id,
            'name' => 'Apex Weatherproof Emulsion',
            'slug' => 'apex-weatherproof-' . uniqid(),
            'title' => 'Apex Weatherproof Emulsion',
            'status' => 'Deactive',
            'approval_status' => 'pending_review',
            'has_gst_included' => 1,
            'image' => 'product/apex.jpg',
        ]);

        // Variant 1: Factory price 100, GST Included = Yes, Display Price = 110 (10% comm)
        $this->attrGstYes = ProductAttributes::create([
            'product_id' => $this->product->id,
            'quantity' => $this->packing20L->id,
            'color' => $this->shadeWhite->id,
            'seller_price' => 100.00,
            'price' => 110.00,
            'is_gst_included' => 1,
            'mrp' => 150.00,
        ]);

        // Variant 2: Factory price 50, GST Included = No, Display Price = 55 (10% comm)
        $this->attrGstNo = ProductAttributes::create([
            'product_id' => $this->product->id,
            'quantity' => $this->packing10L->id,
            'color' => $this->shadeCream->id,
            'seller_price' => 50.00,
            'price' => 55.00,
            'is_gst_included' => 0,
            'mrp' => 80.00,
        ]);
    }

    public function test_changing_category_commission_updates_all_product_display_prices()
    {
        $this->actingAs($this->admin);

        // Change category commission from 10% to 20%
        $response = $this->postJson(route('admin.commissions.categories.update'), [
            'level' => 'category',
            'id' => $this->category->id,
            'commission_rate' => 20.00, // 20%
            'cascade' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'commission_rate' => 20.00,
        ]);

        // Verify that product attributes display prices recalculated:
        // Variant 1: Factory 100 * (1 + 20/100) = 120.00
        $this->attrGstYes->refresh();
        $this->assertEquals(120.00, (float)$this->attrGstYes->price);
        $this->assertEquals(100.00, (float)$this->attrGstYes->seller_price);

        // Variant 2: Factory 50 * (1 + 20/100) = 60.00
        $this->attrGstNo->refresh();
        $this->assertEquals(60.00, (float)$this->attrGstNo->price);
        $this->assertEquals(50.00, (float)$this->attrGstNo->seller_price);
    }

    public function test_changing_main_category_commission_with_cascade_updates_display_prices()
    {
        $this->actingAs($this->admin);

        // Change Main Category commission to 25% with cascade
        $response = $this->postJson(route('admin.commissions.categories.update'), [
            'level' => 'main',
            'id' => $this->mainCategory->id,
            'commission_rate' => 25.00, // 25%
            'cascade' => true,
        ]);

        $response->assertStatus(200);

        // Variant 1: Factory 100 * (1 + 25/100) = 125.00
        $this->attrGstYes->refresh();
        $this->assertEquals(125.00, (float)$this->attrGstYes->price);

        // Variant 2: Factory 50 * (1 + 25/100) = 62.50
        $this->attrGstNo->refresh();
        $this->assertEquals(62.50, (float)$this->attrGstNo->price);
    }

    public function test_review_bucket_filters_only_gst_included_variants()
    {
        $reviewBucketPage = new \App\Filament\Pages\ProductReviewBucket();
        $viewData = $reviewBucketPage->getViewData();

        $this->assertArrayHasKey('products', $viewData);
        $this->assertArrayHasKey('historyLogs', $viewData);

        $pendingProducts = $viewData['products'];
        $this->assertTrue($pendingProducts->contains('id', $this->product->id));

        $foundProduct = $pendingProducts->firstWhere('id', $this->product->id);
        
        // Product attributes in the review bucket must ONLY contain the GST Included = Yes variant
        $gstOnlyAttributes = $foundProduct->product_attributes;
        $this->assertCount(1, $gstOnlyAttributes);
        $this->assertEquals($this->attrGstYes->id, $gstOnlyAttributes->first()->id);
        $this->assertEquals(1, (int)$gstOnlyAttributes->first()->is_gst_included);
    }

    public function test_admin_approving_product_creates_history_log_and_moves_product_to_history()
    {
        $this->actingAs($this->admin);

        // Approve product
        $response = $this->post(route('admin.catalogue.reviews.approve', $this->product->id));
        $response->assertStatus(302);

        $this->product->refresh();
        $this->assertEquals('approved', $this->product->approval_status);
        $this->assertEquals('Active', $this->product->status);

        // Check history log created
        $history = ProductReviewHistory::where('product_id', $this->product->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals('approved', $history->action);
        $this->assertEquals('approved', $history->new_status);

        // Product should no longer be in pending bucket
        $reviewBucketPage = new \App\Filament\Pages\ProductReviewBucket();
        $viewData = $reviewBucketPage->getViewData();
        $this->assertFalse($viewData['products']->contains('id', $this->product->id));
    }
}
