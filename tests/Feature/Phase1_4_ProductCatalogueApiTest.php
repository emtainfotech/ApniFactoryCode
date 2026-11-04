<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Phase1_4_ProductCatalogueApiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $sellerUser;
    protected string $adminToken;
    protected string $sellerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@apnifactory.local')->first() ?: User::first();
        $this->sellerUser = User::where('email', 'seller@apnifactory.local')->first() ?: User::first();

        $this->adminToken = $this->adminUser->createToken('test-admin-token')->plainTextToken;
        $this->sellerToken = $this->sellerUser->createToken('test-seller-token')->plainTextToken;
    }

    /**
     * 1. Category APIs
     */
    public function test_categories_listing_and_subcategories()
    {
        $response = $this->getJson('/api/v2/categories');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'code',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'status']
                ],
                'meta' => ['current_page', 'total', 'per_page']
            ]);

        $category = Category::first();
        $detailResponse = $this->getJson("/api/v2/categories/{$category->id}");
        $detailResponse->assertStatus(200)
            ->assertJsonPath('data.id', $category->id);

        $subResponse = $this->getJson("/api/v2/categories/{$category->id}/subcategories");
        $subResponse->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    /**
     * 2. Brand APIs
     */
    public function test_brands_listing_and_category_filter()
    {
        $response = $this->getJson('/api/v2/brands');
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data', 'meta']);

        $brand = Brand::first();
        $detailResponse = $this->getJson("/api/v2/brands/{$brand->id}");
        $detailResponse->assertStatus(200)
            ->assertJsonPath('data.id', $brand->id);

        if ($brand->category_id) {
            $catBrandResponse = $this->getJson("/api/v2/brands/by-category/{$brand->category_id}");
            $catBrandResponse->assertStatus(200);
        }
    }

    /**
     * 3. Product Listing & Detail APIs
     */
    public function test_products_listing_and_details()
    {
        $response = $this->getJson('/api/v2/products');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'name', 'slug', 'brand', 'category', 'starting_price', 'status']
                ],
                'meta' => ['total', 'current_page']
            ]);

        $product = Product::first();
        $detailResponse = $this->getJson("/api/v2/products/{$product->id}");
        $detailResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'name',
                    'brand',
                    'category',
                    'variants',
                    'buy_box_price',
                    'available_sellers_count',
                ]
            ]);
    }

    /**
     * 4. Unified Catalogue Search
     */
    public function test_unified_catalogue_search()
    {
        $product = Product::first();
        $keyword = substr($product->name, 0, 4);

        $response = $this->getJson("/api/v2/catalogue/search?q={$keyword}&in_stock=1");
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data', 'meta']);
    }

    /**
     * 5. Dynamic Attributes APIs
     */
    public function test_dynamic_attributes_retrieval_and_validation()
    {
        $category = Category::first();
        $response = $this->getJson("/api/v2/categories/{$category->id}/attributes");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'attribute_id', 'attribute_name', 'attribute_code', 'is_variant_generating']
                ]
            ]);

        $packAttr = Attribute::where('code', 'pack_size')->first();
        $validateResponse = $this->postJson('/api/v2/attributes/validate', [
            'category_id' => $category->id,
            'values' => [
                [
                    'attribute_id' => $packAttr ? $packAttr->id : 1,
                    'value' => '4 Litres',
                ]
            ]
        ]);
        $validateResponse->assertStatus(200)
            ->assertJsonPath('data.valid', true);
    }

    /**
     * 6. Product Variant & Public SKU APIs
     */
    public function test_variants_and_public_sku_privacy()
    {
        $variant = ProductVariant::first();
        $this->assertNotNull($variant);

        $response = $this->getJson("/api/v2/variants/{$variant->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $variant->id);

        $listing = SellerListing::where('status', 'Active')->first();
        $publicResponse = $this->getJson("/api/v2/public/listings/{$listing->id}");
        $publicResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'listing_id',
                    'seller_sku',
                    'selling_price',
                    'mrp',
                    'unit_of_measurement',
                    'min_order_quantity',
                    'in_stock',
                ]
            ]);

        // CRITICAL: Public SKU response MUST NOT expose confidential seller cost or commission rate
        $this->assertArrayNotHasKey('seller_price', $publicResponse->json('data'));
        $this->assertArrayNotHasKey('commission_rate', $publicResponse->json('data'));
    }

    /**
     * 7. Seller SKU Security & Cross-Seller Isolation
     */
    public function test_seller_isolation_and_cross_seller_access_denial()
    {
        // Authenticated seller accessing their listings
        $response = $this->withHeader('Authorization', "Bearer {$this->sellerToken}")
            ->getJson('/api/v2/seller/listings');
        $response->assertStatus(200);

        // Create a listing belonging to another seller (Seller ID 10)
        $foreignListing = SellerListing::where('seller_user_id', '!=', $this->sellerUser->id)->first();
        if ($foreignListing) {
            // Attempt to update another seller's listing with seller token must return 403 Forbidden
            $tamperResponse = $this->withHeader('Authorization', "Bearer {$this->sellerToken}")
                ->putJson("/api/v2/seller/listings/{$foreignListing->id}", [
                    'mrp' => 999.00
                ]);
            $tamperResponse->assertStatus(403)
                ->assertJsonPath('status', false);
        }
    }

    /**
     * 8. Admin Authorization
     */
    public function test_admin_endpoints_reject_unauthorized_users()
    {
        // Unauthenticated request to admin route
        $unauth = $this->postJson('/api/v2/admin/categories', ['name' => 'Hacker Cat']);
        $unauth->assertStatus(401);

        // Non-admin seller attempting admin route must return 403 Forbidden
        $forbidden = $this->withHeader('Authorization', "Bearer {$this->sellerToken}")
            ->postJson('/api/v2/admin/categories', ['name' => 'Disallowed Cat']);
        $forbidden->assertStatus(403);
    }

    /**
     * 9. Backward Compatibility: Existing Mobile App APIs
     */
    public function test_legacy_api_contract_preserved()
    {
        $response = $this->getJson('/api/homescreen');
        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $categoryResponse = $this->getJson('/api/categoryscreen');
        $categoryResponse->assertStatus(200)
            ->assertJsonPath('status', true);
    }
}
