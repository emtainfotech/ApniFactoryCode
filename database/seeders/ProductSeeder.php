<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $mid = DB::table('main_categories')->value('id') ?? 1;
        $cid = DB::table('categories')->value('id') ?? 1;
        $sid = DB::table('sub_categories')->value('id') ?? 1;

        $products = [
            [
                'product_id'     => 1001,
                'mid'            => $mid,
                'cid'            => $cid,
                'sid'            => $sid,
                'name'           => 'Pure Cotton Fabric',
                'slug'           => 'pure-cotton-fabric',
                'title'          => 'Premium Pure Cotton Fabric - 100% Natural',
                'image'          => 'default.png',
                'price'          => 299.00,
                'oldprice'       => 399.00,
                'capacity'       => '1 Meter',
                'colorcode'      => '#FFFFFF',
                'colorimage'     => 'default.png',
                'material'       => 'Cotton',
                'assurance'      => '100% Pure Cotton',
                'highlights'     => 'Soft texture, Breathable, Machine washable',
                'description'    => 'High quality pure cotton fabric suitable for all types of garments.',
                'multipleimages' => '[]',
                'status'         => 'Active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'product_id'     => 1002,
                'mid'            => $mid,
                'cid'            => $cid,
                'sid'            => $sid,
                'name'           => 'Printed Floral Cotton',
                'slug'           => 'printed-floral-cotton',
                'title'          => 'Printed Floral Cotton Fabric',
                'image'          => 'default.png',
                'price'          => 349.00,
                'oldprice'       => 499.00,
                'capacity'       => '1 Meter',
                'colorcode'      => '#FF6B9D',
                'colorimage'     => 'default.png',
                'material'       => 'Cotton',
                'assurance'      => '100% Cotton with Digital Print',
                'highlights'     => 'Vibrant colors, Floral pattern, Lightweight',
                'description'    => 'Beautiful floral printed cotton fabric for summer wear.',
                'multipleimages' => '[]',
                'status'         => 'Active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'product_id'     => 1003,
                'mid'            => $mid,
                'cid'            => $cid,
                'sid'            => $sid,
                'name'           => 'Raw Silk Fabric',
                'slug'           => 'raw-silk-fabric',
                'title'          => 'Luxurious Raw Silk Fabric',
                'image'          => 'default.png',
                'price'          => 799.00,
                'oldprice'       => 999.00,
                'capacity'       => '1 Meter',
                'colorcode'      => '#C4A35A',
                'colorimage'     => 'default.png',
                'material'       => 'Silk',
                'assurance'      => '100% Natural Silk',
                'highlights'     => 'Luxurious feel, Natural sheen, Hypoallergenic',
                'description'    => 'Premium quality raw silk fabric for formal and ethnic wear.',
                'multipleimages' => '[]',
                'status'         => 'Active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert($product);
        }
    }
}
