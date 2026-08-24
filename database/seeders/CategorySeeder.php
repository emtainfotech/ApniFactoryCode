<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Main Categories
        $mainCats = [
            ['name' => 'paints',      'title' => 'Paints & Coatings', 'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'fabrics',     'title' => 'Fabrics',     'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'garments',    'title' => 'Garments',    'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'accessories', 'title' => 'Accessories', 'image' => 'default.png', 'status' => 'Active'],
        ];

        foreach ($mainCats as $mc) {
            DB::table('main_categories')->updateOrInsert(['name' => $mc['name']], array_merge($mc, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $midPaint = DB::table('main_categories')->where('name', 'paints')->value('id');
        $mid1 = DB::table('main_categories')->where('name', 'fabrics')->value('id');
        $mid2 = DB::table('main_categories')->where('name', 'garments')->value('id');

        // Categories
        $categories = [
            ['name' => 'Enamel Paints',  'title' => 'Enamel & Synthetic Paints', 'maincategory_id' => $midPaint, 'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'Cotton Fabrics', 'title' => 'Cotton Fabrics',            'maincategory_id' => $mid1,     'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'Silk Fabrics',   'title' => 'Silk Fabrics',              'maincategory_id' => $mid1,     'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'Men Wear',       'title' => 'Men Wear',                  'maincategory_id' => $mid2,     'image' => 'default.png', 'status' => 'Active'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(['name' => $cat['name']], array_merge($cat, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $cidPaint = DB::table('categories')->where('name', 'Enamel Paints')->value('id');
        $cid1 = DB::table('categories')->where('name', 'Cotton Fabrics')->value('id');

        // Sub Categories
        $subCats = [
            ['name' => 'Premium Enamel', 'title' => 'Premium Enamel Paints', 'mid' => $midPaint, 'cid' => $cidPaint, 'maincategory_id' => $midPaint, 'category_id' => $cidPaint, 'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'Plain Cotton',   'title' => 'Plain Cotton',          'mid' => $mid1,     'cid' => $cid1,     'maincategory_id' => $mid1,     'category_id' => $cid1,     'image' => 'default.png', 'status' => 'Active'],
        ];

        foreach ($subCats as $sc) {
            DB::table('sub_categories')->updateOrInsert(['name' => $sc['name']], array_merge($sc, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Paint Packings (BoxPacking)
        $packings = [
            ['name' => '1 Litre',   'pcs' => 1,  'maincategory_id' => $midPaint, 'status' => 'Active'],
            ['name' => '4 Litres',  'pcs' => 4,  'maincategory_id' => $midPaint, 'status' => 'Active'],
            ['name' => '10 Litres', 'pcs' => 10, 'maincategory_id' => $midPaint, 'status' => 'Active'],
            ['name' => '20 Litres', 'pcs' => 20, 'maincategory_id' => $midPaint, 'status' => 'Active'],
        ];

        foreach ($packings as $pck) {
            DB::table('box_packings')->updateOrInsert(['name' => $pck['name']], array_merge($pck, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Paint Shades (ShadeCard)
        $shades = [
            ['name' => 'Super White',    'hexcode' => '#FFFFFF', 'category_id' => $cidPaint, 'maincategory_id' => $midPaint, 'status' => 'Active'],
            ['name' => 'Royal Blue',     'hexcode' => '#1E3A8A', 'category_id' => $cidPaint, 'maincategory_id' => $midPaint, 'status' => 'Active'],
            ['name' => 'Sunset Orange',  'hexcode' => '#EA580C', 'category_id' => $cidPaint, 'maincategory_id' => $midPaint, 'status' => 'Active'],
            ['name' => 'Emerald Green',  'hexcode' => '#059669', 'category_id' => $cidPaint, 'maincategory_id' => $midPaint, 'status' => 'Active'],
        ];

        foreach ($shades as $shd) {
            DB::table('shade_cards')->updateOrInsert(['name' => $shd['name']], array_merge($shd, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
