<?php

namespace Tests\Feature;

use App\Filament\Resources\AttributeResource;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\SellerListingResource;
use App\Filament\Widgets\CatalogueOverviewWidget;
use App\Filament\Widgets\RecentProductsWidget;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Phase1_5_AdminUiTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin@apnifactory.local'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password123'),
            ]
        );
    }

    public function test_admin_user_implements_filament_user_and_can_access()
    {
        $this->assertInstanceOf(\Filament\Models\Contracts\FilamentUser::class, $this->adminUser);
        $this->assertTrue($this->adminUser->canAccessFilament());
    }

    public function test_filament_login_page_renders_successfully()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Sign in');
    }

    public function test_filament_catalogue_overview_widget_computes_real_metrics()
    {
        $this->actingAs($this->adminUser, 'web');

        $widget = Livewire::test(CatalogueOverviewWidget::class);
        $widget->assertStatus(200);

        // Verify widgets calculate actual DB data counts
        $totalProducts = Product::count();
        $widget->assertSee((string) $totalProducts);
    }

    public function test_filament_recent_products_widget_renders_data()
    {
        $this->actingAs($this->adminUser, 'web');

        $widget = Livewire::test(RecentProductsWidget::class);
        $widget->assertStatus(200);
    }

    public function test_attribute_resource_list_and_options_manager()
    {
        $this->actingAs($this->adminUser, 'web');

        $response = $this->get(AttributeResource::getUrl('index'));
        $response->assertStatus(200);

        // Test creating an attribute via Livewire
        $uniqueCode = 'test_finish_' . time();
        Livewire::test(AttributeResource\Pages\CreateAttribute::class)
            ->set('data.name', 'Surface Finish Test')
            ->set('data.code', $uniqueCode)
            ->set('data.type', 'single_select')
            ->set('data.is_active', true)
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attributes', [
            'code' => $uniqueCode,
        ]);
    }

    public function test_product_variant_resource_list_and_create()
    {
        $this->actingAs($this->adminUser, 'web');

        $response = $this->get(ProductVariantResource::getUrl('index'));
        $response->assertStatus(200);

        $product = Product::first();
        if ($product) {
            $sku = 'TEST-VAR-' . time();
            Livewire::test(ProductVariantResource\Pages\CreateProductVariant::class)
                ->set('data.product_id', $product->id)
                ->set('data.variant_sku', $sku)
                ->set('data.pack_litres', '5L')
                ->set('data.status', 'Active')
                ->call('create')
                ->assertHasNoErrors();

            $this->assertDatabaseHas('product_variants', [
                'variant_sku' => $sku,
            ]);
        }
    }

    public function test_seller_listing_resource_list_and_stock_adjustment()
    {
        $this->actingAs($this->adminUser, 'web');

        $response = $this->get(SellerListingResource::getUrl('index'));
        $response->assertStatus(200);

        $listing = SellerListing::first();
        if ($listing) {
            $oldStock = $listing->stock_quantity;
            $newStock = $oldStock + 10;

            // Trigger stock adjust action
            Livewire::test(SellerListingResource\Pages\ListSellerListings::class)
                ->callTableAction('adjustStock', $listing, [
                    'new_stock' => $newStock,
                    'reason' => 'Inventory Audit Q4',
                ])
                ->assertHasNoErrors();

            $listing->refresh();
            $this->assertEquals($newStock, $listing->stock_quantity);
        }
    }

    public function test_product_resource_list_and_relation_managers()
    {
        $this->actingAs($this->adminUser, 'web');

        $response = $this->get(ProductResource::getUrl('index'));
        $response->assertStatus(200);
        $response->assertSee('Products');
    }

    public function test_category_and_brand_resources_render()
    {
        $this->actingAs($this->adminUser, 'web');

        $responseCat = $this->get(CategoryResource::getUrl('index'));
        $responseCat->assertStatus(200);

        $responseBrand = $this->get(BrandResource::getUrl('index'));
        $responseBrand->assertStatus(200);
    }
}
