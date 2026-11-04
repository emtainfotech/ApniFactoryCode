<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class Phase2ProductHierarchyAndInventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = new InventoryService();
    }

    /**
     * Test Phase 1.1: Product hierarchy relationships.
     */
    public function test_product_hierarchy_relationships()
    {
        $product = Product::has('variants')->first();
        $this->assertNotNull($product, 'At least one product with variants must exist.');

        $this->assertTrue($product->variants()->count() > 0);
        $variant = $product->variants()->first();
        $this->assertInstanceOf(ProductVariant::class, $variant);
        $this->assertEquals($product->id, $variant->product_id);

        $this->assertTrue($variant->listings()->count() > 0);
        $listing = $variant->listings()->first();
        $this->assertInstanceOf(SellerListing::class, $listing);
        $this->assertEquals($variant->id, $listing->product_variant_id);
    }

    /**
     * Test Phase 1.2: Dynamic Attributes and Category Mapping.
     */
    public function test_dynamic_attributes_and_category_mapping()
    {
        $attribute = Attribute::where('code', 'finish')->first();
        $this->assertNotNull($attribute);
        $this->assertEquals('single_select', $attribute->type);

        $options = $attribute->options;
        $this->assertTrue($options->count() > 0);
        $this->assertTrue($options->contains('label', 'Matt'));

        $catMapping = CategoryAttribute::where('attribute_id', $attribute->id)->first();
        $this->assertNotNull($catMapping);
        $this->assertFalse($catMapping->is_variant_generating);

        $packAttr = Attribute::where('code', 'pack_size')->first();
        $this->assertNotNull($packAttr);
        $packMapping = CategoryAttribute::where('attribute_id', $packAttr->id)->first();
        $this->assertNotNull($packMapping);
        $this->assertTrue($packMapping->is_variant_generating);
    }

    /**
     * Test Phase 1.3: Packaging rules validation (MOQ, Order Multiple, Carton packaging).
     */
    public function test_packaging_rules_enforcement()
    {
        $listing = SellerListing::first();
        $this->assertNotNull($listing);

        // Configure listing packaging rules
        $listing->min_order_quantity = 5;
        $listing->order_multiple = 5;
        $listing->allow_unit_orders = false;
        $listing->allow_carton_orders = true;
        $listing->case_pack_quantity = 10;
        $listing->save();

        // 1. Quantity below MOQ should throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Minimum order quantity');
        $this->inventoryService->validatePackagingRules($listing, 3);
    }

    /**
     * Test Phase 1.3: Order multiple violation throws exception.
     */
    public function test_order_multiple_violation()
    {
        $listing = SellerListing::first();
        $listing->min_order_quantity = 5;
        $listing->order_multiple = 5;
        $listing->allow_unit_orders = true;
        $listing->save();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order quantity must be in multiples');
        $this->inventoryService->validatePackagingRules($listing, 7);
    }

    /**
     * Test Phase 1.3: Inventory Reservation and Deductions with audit trail.
     */
    public function test_inventory_reservation_and_audit_trail()
    {
        $listing = SellerListing::first();
        $listing->min_order_quantity = 1;
        $listing->order_multiple = 1;
        $listing->allow_unit_orders = true;
        $listing->stock_quantity = 50;
        $listing->reserved_stock = 0;
        $listing->save();

        $orderNo = 'TEST-ORD-' . time();

        // Reserve 10 units
        $reserved = $this->inventoryService->reserveStock($listing->id, 10, $orderNo);
        $this->assertEquals(10, $reserved->reserved_stock);
        $this->assertEquals(40, $reserved->available_stock);

        // Check audit movement logged
        $movement = InventoryMovement::where('reference_order_no', $orderNo)
            ->where('movement_type', 'order_reserve')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-10, $movement->quantity_change);

        // Deduct 10 units upon completion
        $deducted = $this->inventoryService->deductStock($listing->id, 10, $orderNo);
        $this->assertEquals(40, $deducted->stock_quantity);
        $this->assertEquals(0, $deducted->reserved_stock);

        // Verify order deduction movement
        $deductMovement = InventoryMovement::where('reference_order_no', $orderNo)
            ->where('movement_type', 'order_deduct')
            ->first();
        $this->assertNotNull($deductMovement);
        $this->assertEquals(40, $deductMovement->new_stock);
    }

    /**
     * Test Backward Compatibility: Legacy API endpoints continue functioning.
     */
    public function test_legacy_api_homescreen_backward_compatibility()
    {
        $response = $this->getJson('/api/homescreen');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'code',
            'msg',
            'data',
        ]);
    }
}
