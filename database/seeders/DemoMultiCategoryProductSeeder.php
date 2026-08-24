<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoMultiCategoryProductSeeder extends Seeder
{
    public function run()
    {
        $userId = DB::table('users')->value('id') ?? 1;

        // 1. Ensure Main Categories exist
        $mainCategories = [
            ['name' => 'paints',      'title' => 'Paints & Coatings', 'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'fabrics',     'title' => 'Fabrics',           'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'garments',    'title' => 'Garments',          'image' => 'default.png', 'status' => 'Active'],
            ['name' => 'accessories', 'title' => 'Accessories',       'image' => 'default.png', 'status' => 'Active'],
        ];

        foreach ($mainCategories as $mc) {
            DB::table('main_categories')->updateOrInsert(
                ['name' => $mc['name']],
                array_merge($mc, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $midPaints = DB::table('main_categories')->where('name', 'paints')->value('id');
        $midFabrics = DB::table('main_categories')->where('name', 'fabrics')->value('id');
        $midGarments = DB::table('main_categories')->where('name', 'garments')->value('id');
        $midAccessories = DB::table('main_categories')->where('name', 'accessories')->value('id');

        // 2. Ensure Categories exist
        $categories = [
            ['name' => 'Enamel Paints',       'title' => 'Enamel & Synthetic Paints', 'maincategory_id' => $midPaints,      'mid' => $midPaints,      'status' => 'Active'],
            ['name' => 'Emulsion Paints',     'title' => 'Emulsion & Wall Paints',    'maincategory_id' => $midPaints,      'mid' => $midPaints,      'status' => 'Active'],
            ['name' => 'Cotton Fabrics',      'title' => 'Cotton Fabrics',            'maincategory_id' => $midFabrics,     'mid' => $midFabrics,     'status' => 'Active'],
            ['name' => 'Silk Fabrics',        'title' => 'Silk Fabrics',              'maincategory_id' => $midFabrics,     'mid' => $midFabrics,     'status' => 'Active'],
            ['name' => 'Men Wear',            'title' => 'Men Wear',                  'maincategory_id' => $midGarments,    'mid' => $midGarments,    'status' => 'Active'],
            ['name' => 'Women Wear',          'title' => 'Women Wear',                'maincategory_id' => $midGarments,    'mid' => $midGarments,    'status' => 'Active'],
            ['name' => 'Painting Tools',      'title' => 'Hardware & Painting Tools', 'maincategory_id' => $midAccessories, 'mid' => $midAccessories, 'status' => 'Active'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['name' => $cat['name']],
                array_merge($cat, ['image' => 'default.png', 'created_at' => now(), 'updated_at' => now()])
            );
        }

        $cidEnamel = DB::table('categories')->where('name', 'Enamel Paints')->value('id');
        $cidEmulsion = DB::table('categories')->where('name', 'Emulsion Paints')->value('id');
        $cidCotton = DB::table('categories')->where('name', 'Cotton Fabrics')->value('id');
        $cidSilk = DB::table('categories')->where('name', 'Silk Fabrics')->value('id');
        $cidMen = DB::table('categories')->where('name', 'Men Wear')->value('id');
        $cidWomen = DB::table('categories')->where('name', 'Women Wear')->value('id');
        $cidTools = DB::table('categories')->where('name', 'Painting Tools')->value('id');

        // 3. Ensure Brands exist for seller
        $companyId = DB::table('companies')->where('user_id', $userId)->value('id') ?? 1;

        $brands = [
            ['name' => 'Asian Paints Pro',    'category_id' => $cidEnamel,   'mid' => $midPaints,      'user_id' => $userId, 'company_id' => $companyId, 'status' => 'Active'],
            ['name' => 'Berger ColorCraft',   'category_id' => $cidEmulsion, 'mid' => $midPaints,      'user_id' => $userId, 'company_id' => $companyId, 'status' => 'Active'],
            ['name' => 'Vardhman Textiles',   'category_id' => $cidCotton,   'mid' => $midFabrics,     'user_id' => $userId, 'company_id' => $companyId, 'status' => 'Active'],
            ['name' => 'Mysore Silk Works',   'category_id' => $cidSilk,     'mid' => $midFabrics,     'user_id' => $userId, 'company_id' => $companyId, 'status' => 'Active'],
            ['name' => 'Raymond Apparel',     'category_id' => $cidMen,      'mid' => $midGarments,    'user_id' => $userId, 'company_id' => $companyId, 'status' => 'Active'],
            ['name' => 'FabIndia Crafts',     'category_id' => $cidWomen,    'mid' => $midGarments,    'user_id' => $userId, 'company_id' => $companyId, 'status' => 'Active'],
            ['name' => 'MasterCraft Tools',   'category_id' => $cidTools,    'mid' => $midAccessories, 'user_id' => $userId, 'company_id' => $companyId, 'status' => 'Active'],
            ['name' => 'FactoryStandard',     'category_id' => $cidCotton,   'mid' => $midFabrics,     'user_id' => $userId, 'company_id' => $companyId, 'status' => 'Active'],
        ];

        foreach ($brands as $brd) {
            DB::table('brands')->updateOrInsert(
                ['name' => $brd['name'], 'user_id' => $userId],
                array_merge($brd, ['adminresponse' => 'Approved', 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // 4. Ensure Box Packings / Sizes exist
        $packings = [
            // Paints
            ['name' => '1 Litre',    'pcs' => 1,   'maincategory_id' => $midPaints,      'status' => 'Active'],
            ['name' => '4 Litres',   'pcs' => 4,   'maincategory_id' => $midPaints,      'status' => 'Active'],
            ['name' => '10 Litres',  'pcs' => 10,  'maincategory_id' => $midPaints,      'status' => 'Active'],
            ['name' => '20 Litres',  'pcs' => 20,  'maincategory_id' => $midPaints,      'status' => 'Active'],
            // Fabrics
            ['name' => '10 Meters',  'pcs' => 10,  'maincategory_id' => $midFabrics,     'status' => 'Active'],
            ['name' => '25 Meters',  'pcs' => 25,  'maincategory_id' => $midFabrics,     'status' => 'Active'],
            ['name' => '50 Meters',  'pcs' => 50,  'maincategory_id' => $midFabrics,     'status' => 'Active'],
            ['name' => '100 Meters', 'pcs' => 100, 'maincategory_id' => $midFabrics,     'status' => 'Active'],
            // Garments (Sizes)
            ['name' => 'Size S',     'pcs' => 1,   'maincategory_id' => $midGarments,    'status' => 'Active'],
            ['name' => 'Size M',     'pcs' => 1,   'maincategory_id' => $midGarments,    'status' => 'Active'],
            ['name' => 'Size L',     'pcs' => 1,   'maincategory_id' => $midGarments,    'status' => 'Active'],
            ['name' => 'Size XL',    'pcs' => 1,   'maincategory_id' => $midGarments,    'status' => 'Active'],
            ['name' => 'Size XXL',   'pcs' => 1,   'maincategory_id' => $midGarments,    'status' => 'Active'],
            // Tools & Accessories
            ['name' => 'Single Pcs', 'pcs' => 1,   'maincategory_id' => $midAccessories, 'status' => 'Active'],
            ['name' => 'Pack of 3',  'pcs' => 3,   'maincategory_id' => $midAccessories, 'status' => 'Active'],
            ['name' => 'Box of 10',  'pcs' => 10,  'maincategory_id' => $midAccessories, 'status' => 'Active'],
        ];

        foreach ($packings as $pck) {
            DB::table('box_packings')->updateOrInsert(
                ['name' => $pck['name']],
                array_merge($pck, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 5. Ensure Shade Cards exist
        $shades = [
            // Paints - Enamel
            ['name' => 'Super White',    'hexcode' => '#FFFFFF', 'category_id' => $cidEnamel,   'maincategory_id' => $midPaints,      'status' => 'Active'],
            ['name' => 'Royal Blue',     'hexcode' => '#1E3A8A', 'category_id' => $cidEnamel,   'maincategory_id' => $midPaints,      'status' => 'Active'],
            ['name' => 'Sunset Orange',  'hexcode' => '#EA580C', 'category_id' => $cidEnamel,   'maincategory_id' => $midPaints,      'status' => 'Active'],
            ['name' => 'Emerald Green',  'hexcode' => '#059669', 'category_id' => $cidEnamel,   'maincategory_id' => $midPaints,      'status' => 'Active'],
            // Paints - Emulsion
            ['name' => 'Pearl Ivory',    'hexcode' => '#FFFBEB', 'category_id' => $cidEmulsion, 'maincategory_id' => $midPaints,      'status' => 'Active'],
            ['name' => 'Mint Whisper',   'hexcode' => '#ECFDF5', 'category_id' => $cidEmulsion, 'maincategory_id' => $midPaints,      'status' => 'Active'],
            ['name' => 'Coral Glow',     'hexcode' => '#FFE4E6', 'category_id' => $cidEmulsion, 'maincategory_id' => $midPaints,      'status' => 'Active'],
            // Fabrics - Cotton
            ['name' => 'Bleached White', 'hexcode' => '#FAFAFA', 'category_id' => $cidCotton,   'maincategory_id' => $midFabrics,     'status' => 'Active'],
            ['name' => 'Natural Beige',  'hexcode' => '#E5D3B3', 'category_id' => $cidCotton,   'maincategory_id' => $midFabrics,     'status' => 'Active'],
            ['name' => 'Indigo Blue',    'hexcode' => '#1E293B', 'category_id' => $cidCotton,   'maincategory_id' => $midFabrics,     'status' => 'Active'],
            // Fabrics - Silk
            ['name' => 'Royal Gold',     'hexcode' => '#D97706', 'category_id' => $cidSilk,     'maincategory_id' => $midFabrics,     'status' => 'Active'],
            ['name' => 'Ruby Maroon',    'hexcode' => '#881337', 'category_id' => $cidSilk,     'maincategory_id' => $midFabrics,     'status' => 'Active'],
            ['name' => 'Peacock Teal',   'hexcode' => '#0F766E', 'category_id' => $cidSilk,     'maincategory_id' => $midFabrics,     'status' => 'Active'],
            // Garments - Men Wear
            ['name' => 'Crisp White',    'hexcode' => '#FFFFFF', 'category_id' => $cidMen,      'maincategory_id' => $midGarments,    'status' => 'Active'],
            ['name' => 'Sky Blue',       'hexcode' => '#38BDF8', 'category_id' => $cidMen,      'maincategory_id' => $midGarments,    'status' => 'Active'],
            ['name' => 'Charcoal Grey',  'hexcode' => '#334155', 'category_id' => $cidMen,      'maincategory_id' => $midGarments,    'status' => 'Active'],
            // Garments - Women Wear
            ['name' => 'Deep Crimson',   'hexcode' => '#991B1B', 'category_id' => $cidWomen,    'maincategory_id' => $midGarments,    'status' => 'Active'],
            ['name' => 'Mustard Ochre',  'hexcode' => '#CA8A04', 'category_id' => $cidWomen,    'maincategory_id' => $midGarments,    'status' => 'Active'],
            ['name' => 'Emerald Flora',  'hexcode' => '#047857', 'category_id' => $cidWomen,    'maincategory_id' => $midGarments,    'status' => 'Active'],
            // Tools
            ['name' => 'Pro Standard',   'hexcode' => '#475569', 'category_id' => $cidTools,    'maincategory_id' => $midAccessories, 'status' => 'Active'],
        ];

        foreach ($shades as $shd) {
            DB::table('shade_cards')->updateOrInsert(
                ['name' => $shd['name'], 'category_id' => $shd['category_id']],
                array_merge($shd, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Fetch IDs
        $bAsianPaints = DB::table('brands')->where('name', 'Asian Paints Pro')->value('id') ?? 1;
        $bBerger = DB::table('brands')->where('name', 'Berger ColorCraft')->value('id') ?? 1;
        $bVardhman = DB::table('brands')->where('name', 'Vardhman Textiles')->value('id') ?? 1;
        $bMysoreSilk = DB::table('brands')->where('name', 'Mysore Silk Works')->value('id') ?? 1;
        $bRaymond = DB::table('brands')->where('name', 'Raymond Apparel')->value('id') ?? 1;
        $bFabIndia = DB::table('brands')->where('name', 'FabIndia Crafts')->value('id') ?? 1;
        $bMasterCraft = DB::table('brands')->where('name', 'MasterCraft Tools')->value('id') ?? 1;

        // Packings
        $p1L = DB::table('box_packings')->where('name', '1 Litre')->value('id');
        $p4L = DB::table('box_packings')->where('name', '4 Litres')->value('id');
        $p10L = DB::table('box_packings')->where('name', '10 Litres')->value('id');
        $p20L = DB::table('box_packings')->where('name', '20 Litres')->value('id');

        $p10M = DB::table('box_packings')->where('name', '10 Meters')->value('id');
        $p25M = DB::table('box_packings')->where('name', '25 Meters')->value('id');
        $p50M = DB::table('box_packings')->where('name', '50 Meters')->value('id');
        $p100M = DB::table('box_packings')->where('name', '100 Meters')->value('id');

        $pSizeS = DB::table('box_packings')->where('name', 'Size S')->value('id');
        $pSizeM = DB::table('box_packings')->where('name', 'Size M')->value('id');
        $pSizeL = DB::table('box_packings')->where('name', 'Size L')->value('id');
        $pSizeXL = DB::table('box_packings')->where('name', 'Size XL')->value('id');

        $pSingle = DB::table('box_packings')->where('name', 'Single Pcs')->value('id');
        $pPack3 = DB::table('box_packings')->where('name', 'Pack of 3')->value('id');

        // Shades
        $sWhite = DB::table('shade_cards')->where('name', 'Super White')->value('id');
        $sBlue = DB::table('shade_cards')->where('name', 'Royal Blue')->value('id');
        $sOrange = DB::table('shade_cards')->where('name', 'Sunset Orange')->value('id');
        $sGreen = DB::table('shade_cards')->where('name', 'Emerald Green')->value('id');

        $sIvory = DB::table('shade_cards')->where('name', 'Pearl Ivory')->value('id');
        $sMint = DB::table('shade_cards')->where('name', 'Mint Whisper')->value('id');
        $sCoral = DB::table('shade_cards')->where('name', 'Coral Glow')->value('id');

        $sBWhite = DB::table('shade_cards')->where('name', 'Bleached White')->value('id');
        $sBeige = DB::table('shade_cards')->where('name', 'Natural Beige')->value('id');
        $sIndigo = DB::table('shade_cards')->where('name', 'Indigo Blue')->value('id');

        $sGold = DB::table('shade_cards')->where('name', 'Royal Gold')->value('id');
        $sMaroon = DB::table('shade_cards')->where('name', 'Ruby Maroon')->value('id');
        $sTeal = DB::table('shade_cards')->where('name', 'Peacock Teal')->value('id');

        $sCWhite = DB::table('shade_cards')->where('name', 'Crisp White')->value('id');
        $sSBlue = DB::table('shade_cards')->where('name', 'Sky Blue')->value('id');
        $sCharcoal = DB::table('shade_cards')->where('name', 'Charcoal Grey')->value('id');

        $sCrimson = DB::table('shade_cards')->where('name', 'Deep Crimson')->value('id');
        $sMustard = DB::table('shade_cards')->where('name', 'Mustard Ochre')->value('id');
        $sFlora = DB::table('shade_cards')->where('name', 'Emerald Flora')->value('id');

        $sTool = DB::table('shade_cards')->where('name', 'Pro Standard')->value('id');

        // 6. Define Demo Product Catalog
        $demoProducts = [
            // 1. Paints - Enamel
            [
                'name' => 'Apcolite Premium Gloss Enamel',
                'slug' => 'apcolite-premium-gloss-enamel',
                'title' => 'Apcolite Premium High-Gloss Enamel Paint for Wood & Metal',
                'description' => 'Superior mirror-like gloss finish with stain protection and high resistance against corrosion and weathering.',
                'maincategory_id' => $midPaints,
                'category_id' => $cidEnamel,
                'brand_id' => $bAsianPaints,
                'hsncode' => '3208',
                'tax' => 18,
                'skus' => [
                    ['color' => $sWhite, 'pck' => $p1L, 'litres' => 1.0, 'seller' => 420.00, 'cust' => 525.00],
                    ['color' => $sWhite, 'pck' => $p4L, 'litres' => 4.0, 'seller' => 1650.00, 'cust' => 2062.50],
                    ['color' => $sWhite, 'pck' => $p10L, 'litres' => 10.0, 'seller' => 3950.00, 'cust' => 4937.50],
                    ['color' => $sWhite, 'pck' => $p20L, 'litres' => 20.0, 'seller' => 7700.00, 'cust' => 9625.00],

                    ['color' => $sBlue, 'pck' => $p1L, 'litres' => 1.0, 'seller' => 460.00, 'cust' => 575.00],
                    ['color' => $sBlue, 'pck' => $p4L, 'litres' => 4.0, 'seller' => 1820.00, 'cust' => 2275.00],
                    ['color' => $sBlue, 'pck' => $p10L, 'litres' => 10.0, 'seller' => 4400.00, 'cust' => 5500.00],
                    ['color' => $sBlue, 'pck' => $p20L, 'litres' => 20.0, 'seller' => 8600.00, 'cust' => 10750.00],

                    ['color' => $sOrange, 'pck' => $p1L, 'litres' => 1.0, 'seller' => 440.00, 'cust' => 550.00],
                    ['color' => $sOrange, 'pck' => $p4L, 'litres' => 4.0, 'seller' => 1740.00, 'cust' => 2175.00],
                    ['color' => $sOrange, 'pck' => $p10L, 'litres' => 10.0, 'seller' => 4200.00, 'cust' => 5250.00],
                    ['color' => $sOrange, 'pck' => $p20L, 'litres' => 20.0, 'seller' => 8200.00, 'cust' => 10250.00],
                ]
            ],
            // 2. Paints - Emulsion
            [
                'name' => 'Royale Luxury Interior Emulsion',
                'slug' => 'royale-luxury-interior-emulsion',
                'title' => 'Royale Luxury Teflon Surface Protector Emulsion Paint',
                'description' => 'Ultra-smooth velvet finish interior paint formulated with Teflon Surface Protector for ultimate washability.',
                'maincategory_id' => $midPaints,
                'category_id' => $cidEmulsion,
                'brand_id' => $bBerger,
                'hsncode' => '3209',
                'tax' => 18,
                'skus' => [
                    ['color' => $sIvory, 'pck' => $p1L, 'litres' => 1.0, 'seller' => 550.00, 'cust' => 687.50],
                    ['color' => $sIvory, 'pck' => $p4L, 'litres' => 4.0, 'seller' => 2100.00, 'cust' => 2625.00],
                    ['color' => $sIvory, 'pck' => $p10L, 'litres' => 10.0, 'seller' => 5100.00, 'cust' => 6375.00],
                    ['color' => $sIvory, 'pck' => $p20L, 'litres' => 20.0, 'seller' => 9900.00, 'cust' => 12375.00],

                    ['color' => $sMint, 'pck' => $p1L, 'litres' => 1.0, 'seller' => 560.00, 'cust' => 700.00],
                    ['color' => $sMint, 'pck' => $p4L, 'litres' => 4.0, 'seller' => 2150.00, 'cust' => 2687.50],
                    ['color' => $sMint, 'pck' => $p10L, 'litres' => 10.0, 'seller' => 5200.00, 'cust' => 6500.00],
                    ['color' => $sMint, 'pck' => $p20L, 'litres' => 20.0, 'seller' => 10100.00, 'cust' => 12625.00],

                    ['color' => $sCoral, 'pck' => $p1L, 'litres' => 1.0, 'seller' => 580.00, 'cust' => 725.00],
                    ['color' => $sCoral, 'pck' => $p4L, 'litres' => 4.0, 'seller' => 2250.00, 'cust' => 2812.50],
                    ['color' => $sCoral, 'pck' => $p10L, 'litres' => 10.0, 'seller' => 5400.00, 'cust' => 6750.00],
                    ['color' => $sCoral, 'pck' => $p20L, 'litres' => 20.0, 'seller' => 10500.00, 'cust' => 13125.00],
                ]
            ],
            // 3. Fabrics - Cotton
            [
                'name' => '100% Pure Organic Cotton Fabric',
                'slug' => '100-pure-organic-cotton-fabric',
                'title' => 'Wholesale Premium Combed Organic Cotton Fabric Rolls',
                'description' => 'Breathable, skin-friendly, high-density weave pure cotton fabric ideal for premium apparel manufacturing.',
                'maincategory_id' => $midFabrics,
                'category_id' => $cidCotton,
                'brand_id' => $bVardhman,
                'hsncode' => '5208',
                'tax' => 5,
                'skus' => [
                    ['color' => $sBWhite, 'pck' => $p10M, 'litres' => 10.0, 'seller' => 1800.00, 'cust' => 2250.00],
                    ['color' => $sBWhite, 'pck' => $p25M, 'litres' => 25.0, 'seller' => 4250.00, 'cust' => 5312.50],
                    ['color' => $sBWhite, 'pck' => $p50M, 'litres' => 50.0, 'seller' => 8000.00, 'cust' => 10000.00],
                    ['color' => $sBWhite, 'pck' => $p100M, 'litres' => 100.0, 'seller' => 15000.00, 'cust' => 18750.00],

                    ['color' => $sBeige, 'pck' => $p10M, 'litres' => 10.0, 'seller' => 1850.00, 'cust' => 2312.50],
                    ['color' => $sBeige, 'pck' => $p25M, 'litres' => 25.0, 'seller' => 4350.00, 'cust' => 5437.50],
                    ['color' => $sBeige, 'pck' => $p50M, 'litres' => 50.0, 'seller' => 8200.00, 'cust' => 10250.00],
                    ['color' => $sBeige, 'pck' => $p100M, 'litres' => 100.0, 'seller' => 15500.00, 'cust' => 19375.00],

                    ['color' => $sIndigo, 'pck' => $p10M, 'litres' => 10.0, 'seller' => 1950.00, 'cust' => 2437.50],
                    ['color' => $sIndigo, 'pck' => $p25M, 'litres' => 25.0, 'seller' => 4600.00, 'cust' => 5750.00],
                    ['color' => $sIndigo, 'pck' => $p50M, 'litres' => 50.0, 'seller' => 8800.00, 'cust' => 11000.00],
                    ['color' => $sIndigo, 'pck' => $p100M, 'litres' => 100.0, 'seller' => 16500.00, 'cust' => 20625.00],
                ]
            ],
            // 4. Fabrics - Silk
            [
                'name' => 'Banarasi Pure Raw Silk Fabric',
                'slug' => 'banarasi-pure-raw-silk-fabric',
                'title' => 'Authentic Handloom Pure Raw Silk Fabric with Natural Sheen',
                'description' => 'Luxurious, rich texture raw silk with a radiant natural gloss, perfect for bridal wear and royal couture.',
                'maincategory_id' => $midFabrics,
                'category_id' => $cidSilk,
                'brand_id' => $bMysoreSilk,
                'hsncode' => '5007',
                'tax' => 5,
                'skus' => [
                    ['color' => $sGold, 'pck' => $p10M, 'litres' => 10.0, 'seller' => 6500.00, 'cust' => 8125.00],
                    ['color' => $sGold, 'pck' => $p25M, 'litres' => 25.0, 'seller' => 15500.00, 'cust' => 19375.00],
                    ['color' => $sGold, 'pck' => $p50M, 'litres' => 50.0, 'seller' => 29000.00, 'cust' => 36250.00],

                    ['color' => $sMaroon, 'pck' => $p10M, 'litres' => 10.0, 'seller' => 6800.00, 'cust' => 8500.00],
                    ['color' => $sMaroon, 'pck' => $p25M, 'litres' => 25.0, 'seller' => 16000.00, 'cust' => 20000.00],
                    ['color' => $sMaroon, 'pck' => $p50M, 'litres' => 50.0, 'seller' => 30500.00, 'cust' => 38125.00],

                    ['color' => $sTeal, 'pck' => $p10M, 'litres' => 10.0, 'seller' => 6700.00, 'cust' => 8375.00],
                    ['color' => $sTeal, 'pck' => $p25M, 'litres' => 25.0, 'seller' => 15800.00, 'cust' => 19750.00],
                    ['color' => $sTeal, 'pck' => $p50M, 'litres' => 50.0, 'seller' => 30000.00, 'cust' => 37500.00],
                ]
            ],
            // 5. Garments - Men Wear
            [
                'name' => 'Executive Slim-Fit Cotton Formal Shirt',
                'slug' => 'executive-slim-fit-cotton-formal-shirt',
                'title' => 'Tailored Executive Slim-Fit Formal Shirt in Giza Cotton',
                'description' => 'Wrinkle-resistant luxury formal shirt with cutaway collar, mother-of-pearl buttons, and premium stitching.',
                'maincategory_id' => $midGarments,
                'category_id' => $cidMen,
                'brand_id' => $bRaymond,
                'hsncode' => '6205',
                'tax' => 12,
                'skus' => [
                    ['color' => $sCWhite, 'pck' => $pSizeS, 'litres' => 1.0, 'seller' => 950.00, 'cust' => 1187.50],
                    ['color' => $sCWhite, 'pck' => $pSizeM, 'litres' => 1.0, 'seller' => 950.00, 'cust' => 1187.50],
                    ['color' => $sCWhite, 'pck' => $pSizeL, 'litres' => 1.0, 'seller' => 950.00, 'cust' => 1187.50],
                    ['color' => $sCWhite, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 990.00, 'cust' => 1237.50],

                    ['color' => $sSBlue, 'pck' => $pSizeS, 'litres' => 1.0, 'seller' => 980.00, 'cust' => 1225.00],
                    ['color' => $sSBlue, 'pck' => $pSizeM, 'litres' => 1.0, 'seller' => 980.00, 'cust' => 1225.00],
                    ['color' => $sSBlue, 'pck' => $pSizeL, 'litres' => 1.0, 'seller' => 980.00, 'cust' => 1225.00],
                    ['color' => $sSBlue, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 1020.00, 'cust' => 1275.00],

                    ['color' => $sCharcoal, 'pck' => $pSizeS, 'litres' => 1.0, 'seller' => 1050.00, 'cust' => 1312.50],
                    ['color' => $sCharcoal, 'pck' => $pSizeM, 'litres' => 1.0, 'seller' => 1050.00, 'cust' => 1312.50],
                    ['color' => $sCharcoal, 'pck' => $pSizeL, 'litres' => 1.0, 'seller' => 1050.00, 'cust' => 1312.50],
                    ['color' => $sCharcoal, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 1100.00, 'cust' => 1375.00],
                ]
            ],
            // 6. Garments - Women Wear
            [
                'name' => 'Handcrafted Embroidered Anarkali Kurti',
                'slug' => 'handcrafted-embroidered-anarkali-kurti',
                'title' => 'Designer Chanderi Silk Handcrafted Anarkali Kurti Set',
                'description' => 'Graceful floor-length flared silhouette adorned with Zari embroidery and paired with a pure organza dupatta.',
                'maincategory_id' => $midGarments,
                'category_id' => $cidWomen,
                'brand_id' => $bFabIndia,
                'hsncode' => '6204',
                'tax' => 12,
                'skus' => [
                    ['color' => $sCrimson, 'pck' => $pSizeS, 'litres' => 1.0, 'seller' => 2200.00, 'cust' => 2750.00],
                    ['color' => $sCrimson, 'pck' => $pSizeM, 'litres' => 1.0, 'seller' => 2200.00, 'cust' => 2750.00],
                    ['color' => $sCrimson, 'pck' => $pSizeL, 'litres' => 1.0, 'seller' => 2200.00, 'cust' => 2750.00],
                    ['color' => $sCrimson, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 2300.00, 'cust' => 2875.00],

                    ['color' => $sMustard, 'pck' => $pSizeS, 'litres' => 1.0, 'seller' => 2100.00, 'cust' => 2625.00],
                    ['color' => $sMustard, 'pck' => $pSizeM, 'litres' => 1.0, 'seller' => 2100.00, 'cust' => 2625.00],
                    ['color' => $sMustard, 'pck' => $pSizeL, 'litres' => 1.0, 'seller' => 2100.00, 'cust' => 2625.00],
                    ['color' => $sMustard, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 2200.00, 'cust' => 2750.00],

                    ['color' => $sFlora, 'pck' => $pSizeS, 'litres' => 1.0, 'seller' => 2350.00, 'cust' => 2937.50],
                    ['color' => $sFlora, 'pck' => $pSizeM, 'litres' => 1.0, 'seller' => 2350.00, 'cust' => 2937.50],
                    ['color' => $sFlora, 'pck' => $pSizeL, 'litres' => 1.0, 'seller' => 2350.00, 'cust' => 2937.50],
                    ['color' => $sFlora, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 2450.00, 'cust' => 3062.50],
                ]
            ],
            // 7. Accessories - Painting Tools
            [
                'name' => 'Pro Synthetic Paint Roller & Tray Kit',
                'slug' => 'pro-synthetic-paint-roller-and-tray-kit',
                'title' => 'Professional Heavy-Duty Paint Roller & Ergonomic Tray Kit',
                'description' => 'High-absorbency microfiber paint roller sleeve for streak-free coating on smooth and semi-rough surfaces.',
                'maincategory_id' => $midAccessories,
                'category_id' => $cidTools,
                'brand_id' => $bMasterCraft,
                'hsncode' => '9603',
                'tax' => 18,
                'skus' => [
                    ['color' => $sTool, 'pck' => $pSingle, 'litres' => 1.0, 'seller' => 220.00, 'cust' => 275.00],
                    ['color' => $sTool, 'pck' => $pPack3,  'litres' => 3.0, 'seller' => 580.00, 'cust' => 725.00],
                ]
            ],
        ];

        // 7. Insert or update Products and Attributes
        foreach ($demoProducts as $pData) {
            $skus = $pData['skus'];
            unset($pData['skus']);

            $productId = DB::table('products')->where('slug', $pData['slug'])->value('id');

            if (!$productId) {
                $maxPrId = (DB::table('products')->max('id') ?? 0) + 1;
                $productId = DB::table('products')->insertGetId(array_merge($pData, [
                    'subcategory_id' => 0,
                    'user_id'        => $userId,
                    'image'          => 'default.png',
                    'multipleimages' => json_encode(['default.png']),
                    'status'         => '1',
                    'product_id'     => $maxPrId + 100,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]));
            } else {
                DB::table('products')->where('id', $productId)->update(array_merge($pData, [
                    'user_id'    => $userId,
                    'status'     => '1',
                    'updated_at' => now(),
                ]));
            }

            // Remove existing attributes to re-seed cleanly
            DB::table('product_attributes')->where('product_id', $productId)->delete();

            foreach ($skus as $sku) {
                if (!empty($sku['color']) && !empty($sku['pck'])) {
                    DB::table('product_attributes')->insert([
                        'product_id'      => $productId,
                        'color'           => $sku['color'],
                        'quantity'        => $sku['pck'],
                        'pack_litres'     => $sku['litres'] ?? 1.0,
                        'seller_price'    => $sku['seller'],
                        'commission_rate' => 25.00,
                        'oldprice'        => $sku['cust'],
                        'price'           => $sku['cust'],
                        'status'          => 'active',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }
    }
}
