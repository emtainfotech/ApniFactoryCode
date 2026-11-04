<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase2DataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            // 1. Seed Standard Dynamic Attributes
            $shadeAttr = Attribute::firstOrCreate(
                ['code' => 'shade_color'],
                [
                    'name' => 'Shade / Color',
                    'type' => 'single_select',
                    'description' => 'Color shade or swatch code',
                    'sort_order' => 1,
                    'status' => 'Active',
                ]
            );

            $packAttr = Attribute::firstOrCreate(
                ['code' => 'pack_size'],
                [
                    'name' => 'Pack Size / Volume',
                    'type' => 'measurement',
                    'unit' => 'Litre',
                    'description' => 'Packaging volume or weight container',
                    'sort_order' => 2,
                    'status' => 'Active',
                ]
            );

            $finishAttr = Attribute::firstOrCreate(
                ['code' => 'finish'],
                [
                    'name' => 'Finish',
                    'type' => 'single_select',
                    'description' => 'Surface sheen and finish',
                    'sort_order' => 3,
                    'status' => 'Active',
                ]
            );

            $baseAttr = Attribute::firstOrCreate(
                ['code' => 'base_type'],
                [
                    'name' => 'Base Type',
                    'type' => 'single_select',
                    'description' => 'Chemical base formulation',
                    'sort_order' => 4,
                    'status' => 'Active',
                ]
            );

            // Seed options for Finish
            foreach (['Matt', 'Gloss', 'Satin', 'High Gloss', 'Eggshell'] as $idx => $optName) {
                AttributeOption::firstOrCreate(
                    ['attribute_id' => $finishAttr->id, 'value' => Str::slug($optName)],
                    ['label' => $optName, 'sort_order' => $idx + 1, 'status' => 'Active']
                );
            }

            // Seed options for Base Type
            foreach (['Water Based', 'Solvent Based', 'Oil Based', 'Polyurethane'] as $idx => $optName) {
                AttributeOption::firstOrCreate(
                    ['attribute_id' => $baseAttr->id, 'value' => Str::slug($optName)],
                    ['label' => $optName, 'sort_order' => $idx + 1, 'status' => 'Active']
                );
            }

            // 2. Map attributes to categories
            $categories = Category::all();
            foreach ($categories as $cat) {
                CategoryAttribute::firstOrCreate(
                    ['category_id' => $cat->id, 'attribute_id' => $shadeAttr->id],
                    [
                        'is_required' => false,
                        'is_filterable' => true,
                        'is_searchable' => true,
                        'is_variant_generating' => true,
                        'sort_order' => 1,
                        'status' => 'Active',
                    ]
                );

                CategoryAttribute::firstOrCreate(
                    ['category_id' => $cat->id, 'attribute_id' => $packAttr->id],
                    [
                        'is_required' => false,
                        'is_filterable' => true,
                        'is_searchable' => true,
                        'is_variant_generating' => true,
                        'sort_order' => 2,
                        'status' => 'Active',
                    ]
                );

                CategoryAttribute::firstOrCreate(
                    ['category_id' => $cat->id, 'attribute_id' => $finishAttr->id],
                    [
                        'is_required' => false,
                        'is_filterable' => true,
                        'is_searchable' => false,
                        'is_variant_generating' => false,
                        'sort_order' => 3,
                        'status' => 'Active',
                    ]
                );
            }

            // 3. Migrate legacy product_attributes into product_variants & seller_listings
            $defaultSeller = User::where('email', 'seller@apnifactory.local')->first() ?: User::first();
            $legacyAttrs = ProductAttributes::all();

            $migratedVariants = 0;
            $migratedListings = 0;

            foreach ($legacyAttrs as $leg) {
                $product = Product::find($leg->product_id);
                if (!$product) {
                    continue;
                }

                $shadeCardId = is_numeric($leg->color) ? (int) $leg->color : null;
                $boxPackingId = is_numeric($leg->quantity) ? (int) $leg->quantity : null;
                $packLitres = $leg->packing_litres ?: ($leg->boxPacking->name ?? null);

                // Variant SKU format: PROD-{product_id}-V{id}
                $variantSku = "VAR-P{$product->id}-S" . ($shadeCardId ?: '0') . "-B" . ($boxPackingId ?: '0');

                $variant = ProductVariant::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'shade_card_id' => $shadeCardId,
                        'box_packing_id' => $boxPackingId,
                    ],
                    [
                        'variant_sku' => $variantSku,
                        'pack_litres' => $packLitres,
                        'variant_name' => "{$product->name} " . ($packLitres ? "({$packLitres})" : ''),
                        'image' => $leg->image ?: $product->image,
                        'status' => 'Active',
                    ]
                );

                if ($variant->wasRecentlyCreated) {
                    $migratedVariants++;
                }

                // Seller listing: associate with seller
                $sellerUserId = $product->user_id ?: ($defaultSeller ? $defaultSeller->id : 1);
                $sellerSku = "SKU-S{$sellerUserId}-P{$product->id}-V{$variant->id}";

                $sellerPrice = (float) ($leg->selling_price ?: $leg->price ?: 0);
                $mrp = (float) ($leg->price ?: $sellerPrice);

                $listing = SellerListing::firstOrCreate(
                    [
                        'seller_user_id' => $sellerUserId,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'seller_sku' => $sellerSku,
                        'seller_price' => $sellerPrice,
                        'commission_rate' => 0.00,
                        'selling_price' => $sellerPrice,
                        'mrp' => $mrp,
                        'min_order_quantity' => 1,
                        'unit_of_measurement' => 'Unit',
                        'case_pack_quantity' => 1,
                        'order_multiple' => 1,
                        'allow_unit_orders' => true,
                        'allow_carton_orders' => false,
                        'stock_quantity' => (int) ($leg->count ?? 100),
                        'reserved_stock' => 0,
                        'low_stock_threshold' => 5,
                        'dispatch_lead_days' => 2,
                        'status' => 'Active',
                    ]
                );

                if ($listing->wasRecentlyCreated) {
                    $migratedListings++;
                }
            }

            echo "Migration complete: seeded standard attributes, created {$migratedVariants} variants and {$migratedListings} seller listings.\n";
        });
    }
}

