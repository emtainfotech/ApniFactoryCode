<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoMultiCategoryProductSeeder extends Seeder
{
    public function run()
    {
        // 0. Ensure Demo Seller user exists and get their user ID
        $sellerUser = DB::table('users')->where('email', 'seller@apnifactory.local')->first();
        if (!$sellerUser) {
            $sellerId = DB::table('users')->insertGetId([
                'name'       => 'Demo Seller',
                'email'      => 'seller@apnifactory.local',
                'password'   => Hash::make('seller@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $userId = $sellerId;
        } else {
            $userId = $sellerUser->id;
        }

        // Ensure Company for Demo Seller exists
        DB::table('companies')->updateOrInsert(
            ['email' => 'seller@apnifactory.local'],
            [
                'name'             => 'ApniFactory Prime Mills',
                'user_id'          => $userId,
                'email'            => 'seller@apnifactory.local',
                'mobile'           => '9876543210',
                'maincategory_id'  => 1,
                'gst'              => '07AAAAA0000A1Z5',
                'crn'              => 'CRN123456',
                'minordervalue'    => '1000.00',
                'city'             => 'New Delhi',
                'state'            => 'Delhi',
                'pincode'          => '110001',
                'comission'        => '5.00',
                'status'           => 'Active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );

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
        $bAsianPaints = DB::table('brands')->where('name', 'Asian Paints Pro')->where('user_id', $userId)->value('id') ?? 1;
        $bBerger = DB::table('brands')->where('name', 'Berger ColorCraft')->where('user_id', $userId)->value('id') ?? 1;
        $bVardhman = DB::table('brands')->where('name', 'Vardhman Textiles')->where('user_id', $userId)->value('id') ?? 1;
        $bMysoreSilk = DB::table('brands')->where('name', 'Mysore Silk Works')->where('user_id', $userId)->value('id') ?? 1;
        $bRaymond = DB::table('brands')->where('name', 'Raymond Apparel')->where('user_id', $userId)->value('id') ?? 1;
        $bFabIndia = DB::table('brands')->where('name', 'FabIndia Crafts')->where('user_id', $userId)->value('id') ?? 1;
        $bMasterCraft = DB::table('brands')->where('name', 'MasterCraft Tools')->where('user_id', $userId)->value('id') ?? 1;

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
        $pSizeXXL = DB::table('box_packings')->where('name', 'Size XXL')->value('id');

        $pSingle = DB::table('box_packings')->where('name', 'Single Pcs')->value('id');
        $pPack3 = DB::table('box_packings')->where('name', 'Pack of 3')->value('id');
        $pBox10 = DB::table('box_packings')->where('name', 'Box of 10')->value('id');

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

        // 6. Define 12 Multi-Category Demo Products
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
                'price' => 525.00,
                'oldprice' => 600.00,
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
                'price' => 687.50,
                'oldprice' => 750.00,
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
            // 3. Paints - Tractor Emulsion
            [
                'name' => 'Tractor Emulsion Economy Paint',
                'slug' => 'tractor-emulsion-economy-paint',
                'title' => 'Tractor Emulsion High Coverage Interior Wall Paint',
                'description' => 'Affordable and long-lasting matt finish interior emulsion offering twice the coverage of ordinary distempers.',
                'maincategory_id' => $midPaints,
                'category_id' => $cidEmulsion,
                'brand_id' => $bAsianPaints,
                'hsncode' => '3209',
                'tax' => 18,
                'price' => 320.00,
                'oldprice' => 360.00,
                'skus' => [
                    ['color' => $sWhite, 'pck' => $p1L, 'litres' => 1.0, 'seller' => 250.00, 'cust' => 320.00],
                    ['color' => $sWhite, 'pck' => $p4L, 'litres' => 4.0, 'seller' => 950.00, 'cust' => 1200.00],
                    ['color' => $sWhite, 'pck' => $p10L, 'litres' => 10.0, 'seller' => 2300.00, 'cust' => 2850.00],
                    ['color' => $sWhite, 'pck' => $p20L, 'litres' => 20.0, 'seller' => 4400.00, 'cust' => 5400.00],
                ]
            ],
            // 4. Fabrics - Cotton
            [
                'name' => 'Premium Chanderi Pure Cotton Fabric',
                'slug' => 'premium-chanderi-pure-cotton-fabric',
                'title' => 'Handcrafted Chanderi 100% Pure Cotton Fabric (60s Count)',
                'description' => 'Breathable, lightweight 60s combed cotton fabric woven with gold zari border, ideal for ethnic suits and kurtas.',
                'maincategory_id' => $midFabrics,
                'category_id' => $cidCotton,
                'brand_id' => $bVardhman,
                'hsncode' => '5208',
                'tax' => 5,
                'price' => 1875.00,
                'oldprice' => 2100.00,
                'skus' => [
                    ['color' => $sBWhite, 'pck' => $p10M,  'litres' => 10.0, 'seller' => 1500.00, 'cust' => 1875.00],
                    ['color' => $sBWhite, 'pck' => $p25M,  'litres' => 25.0, 'seller' => 3600.00, 'cust' => 4500.00],
                    ['color' => $sBWhite, 'pck' => $p50M,  'litres' => 50.0, 'seller' => 7000.00, 'cust' => 8750.00],
                    ['color' => $sBWhite, 'pck' => $p100M, 'litres' => 100.0, 'seller' => 13500.00, 'cust' => 16875.00],

                    ['color' => $sIndigo, 'pck' => $p10M,  'litres' => 10.0, 'seller' => 1650.00, 'cust' => 2062.50],
                    ['color' => $sIndigo, 'pck' => $p25M,  'litres' => 25.0, 'seller' => 3950.00, 'cust' => 4937.50],
                    ['color' => $sIndigo, 'pck' => $p50M,  'litres' => 50.0, 'seller' => 7700.00, 'cust' => 9625.00],
                    ['color' => $sIndigo, 'pck' => $p100M, 'litres' => 100.0, 'seller' => 14800.00, 'cust' => 18500.00],
                ]
            ],
            // 5. Fabrics - Silk
            [
                'name' => 'Handloom Kanchipuram Silk Fabric',
                'slug' => 'handloom-kanchipuram-silk-fabric',
                'title' => 'Authentic Mulberry Raw Silk Fabric with Zari Finish',
                'description' => 'Rich heavy-weight raw mulberry silk fabric woven with pure silver electroplated zari yarn for bridal ensembles.',
                'maincategory_id' => $midFabrics,
                'category_id' => $cidSilk,
                'brand_id' => $bMysoreSilk,
                'hsncode' => '5007',
                'tax' => 5,
                'price' => 4750.00,
                'oldprice' => 5200.00,
                'skus' => [
                    ['color' => $sGold, 'pck' => $p10M, 'litres' => 10.0, 'seller' => 3800.00, 'cust' => 4750.00],
                    ['color' => $sGold, 'pck' => $p25M, 'litres' => 25.0, 'seller' => 9200.00, 'cust' => 11500.00],
                    ['color' => $sGold, 'pck' => $p50M, 'litres' => 50.0, 'seller' => 18000.00, 'cust' => 22500.00],

                    ['color' => $sMaroon, 'pck' => $p10M, 'litres' => 10.0, 'seller' => 4000.00, 'cust' => 5000.00],
                    ['color' => $sMaroon, 'pck' => $p25M, 'litres' => 25.0, 'seller' => 9700.00, 'cust' => 12125.00],
                    ['color' => $sMaroon, 'pck' => $p50M, 'litres' => 50.0, 'seller' => 19000.00, 'cust' => 23750.00],
                ]
            ],
            // 6. Fabrics - Organic Khadi
            [
                'name' => 'Organic Khadi Linen Weave Fabric',
                'slug' => 'organic-khadi-linen-weave-fabric',
                'title' => 'Eco-Friendly Handspun Khadi Linen Breathable Fabric',
                'description' => 'Unprocessed organic handspun cotton-linen blend fabric with rustic natural texture, perfect for summer apparel.',
                'maincategory_id' => $midFabrics,
                'category_id' => $cidCotton,
                'brand_id' => $bVardhman,
                'hsncode' => '5208',
                'tax' => 5,
                'price' => 1250.00,
                'oldprice' => 1400.00,
                'skus' => [
                    ['color' => $sBeige, 'pck' => $p10M,  'litres' => 10.0, 'seller' => 1000.00, 'cust' => 1250.00],
                    ['color' => $sBeige, 'pck' => $p25M,  'litres' => 25.0, 'seller' => 2400.00, 'cust' => 3000.00],
                    ['color' => $sBeige, 'pck' => $p50M,  'litres' => 50.0, 'seller' => 4600.00, 'cust' => 5750.00],
                ]
            ],
            // 7. Garments - Men Shirt
            [
                'name' => 'Slim-Fit Classic Cotton Oxford Shirt',
                'slug' => 'slim-fit-classic-cotton-oxford-shirt',
                'title' => 'Premium 100% Giza Cotton Formal Slim-Fit Shirt',
                'description' => 'Tailored cut formal long-sleeve oxford shirt crafted from Egyptian Giza cotton with reinforced collar and pearl buttons.',
                'maincategory_id' => $midGarments,
                'category_id' => $cidMen,
                'brand_id' => $bRaymond,
                'hsncode' => '6205',
                'tax' => 12,
                'price' => 1625.00,
                'oldprice' => 1899.00,
                'skus' => [
                    ['color' => $sCWhite, 'pck' => $pSizeS,  'litres' => 1.0, 'seller' => 1300.00, 'cust' => 1625.00],
                    ['color' => $sCWhite, 'pck' => $pSizeM,  'litres' => 1.0, 'seller' => 1300.00, 'cust' => 1625.00],
                    ['color' => $sCWhite, 'pck' => $pSizeL,  'litres' => 1.0, 'seller' => 1300.00, 'cust' => 1625.00],
                    ['color' => $sCWhite, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 1350.00, 'cust' => 1687.50],

                    ['color' => $sSBlue, 'pck' => $pSizeS,  'litres' => 1.0, 'seller' => 1350.00, 'cust' => 1687.50],
                    ['color' => $sSBlue, 'pck' => $pSizeM,  'litres' => 1.0, 'seller' => 1350.00, 'cust' => 1687.50],
                    ['color' => $sSBlue, 'pck' => $pSizeL,  'litres' => 1.0, 'seller' => 1350.00, 'cust' => 1687.50],
                    ['color' => $sSBlue, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 1400.00, 'cust' => 1750.00],
                ]
            ],
            // 8. Garments - Women Kurti
            [
                'name' => 'Pure Mulmul Embroidered Anarkali Kurti',
                'slug' => 'pure-mulmul-embroidered-anarkali-kurti',
                'title' => 'Handcrafted Chikankari Embroidered Mulmul Cotton Kurti',
                'description' => 'Flared Anarkali silhouette with intricate Lucknowi Chikankari embroidery and delicate lace border work.',
                'maincategory_id' => $midGarments,
                'category_id' => $cidWomen,
                'brand_id' => $bFabIndia,
                'hsncode' => '6204',
                'tax' => 12,
                'price' => 2437.50,
                'oldprice' => 2800.00,
                'skus' => [
                    ['color' => $sCrimson, 'pck' => $pSizeS,  'litres' => 1.0, 'seller' => 1950.00, 'cust' => 2437.50],
                    ['color' => $sCrimson, 'pck' => $pSizeM,  'litres' => 1.0, 'seller' => 1950.00, 'cust' => 2437.50],
                    ['color' => $sCrimson, 'pck' => $pSizeL,  'litres' => 1.0, 'seller' => 1950.00, 'cust' => 2437.50],
                    ['color' => $sCrimson, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 2050.00, 'cust' => 2562.50],

                    ['color' => $sFlora, 'pck' => $pSizeS,  'litres' => 1.0, 'seller' => 2350.00, 'cust' => 2937.50],
                    ['color' => $sFlora, 'pck' => $pSizeM,  'litres' => 1.0, 'seller' => 2350.00, 'cust' => 2937.50],
                    ['color' => $sFlora, 'pck' => $pSizeL,  'litres' => 1.0, 'seller' => 2350.00, 'cust' => 2937.50],
                    ['color' => $sFlora, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 2450.00, 'cust' => 3062.50],
                ]
            ],
            // 9. Garments - Men Chinos
            [
                'name' => 'Men Casual Tailored Chinos',
                'slug' => 'men-casual-tailored-chinos',
                'title' => 'Stretch Twill Cotton Slim-Fit Chinos Trousers',
                'description' => 'Comfortable 98% cotton 2% elastane stretch twill trousers with slant pockets and brass zip fly.',
                'maincategory_id' => $midGarments,
                'category_id' => $cidMen,
                'brand_id' => $bRaymond,
                'hsncode' => '6203',
                'tax' => 12,
                'price' => 1750.00,
                'oldprice' => 2100.00,
                'skus' => [
                    ['color' => $sCharcoal, 'pck' => $pSizeM,  'litres' => 1.0, 'seller' => 1400.00, 'cust' => 1750.00],
                    ['color' => $sCharcoal, 'pck' => $pSizeL,  'litres' => 1.0, 'seller' => 1400.00, 'cust' => 1750.00],
                    ['color' => $sCharcoal, 'pck' => $pSizeXL, 'litres' => 1.0, 'seller' => 1450.00, 'cust' => 1812.50],
                ]
            ],
            // 10. Accessories - Paint Roller
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
                'price' => 275.00,
                'oldprice' => 350.00,
                'skus' => [
                    ['color' => $sTool, 'pck' => $pSingle, 'litres' => 1.0, 'seller' => 220.00, 'cust' => 275.00],
                    ['color' => $sTool, 'pck' => $pPack3,  'litres' => 3.0, 'seller' => 580.00, 'cust' => 725.00],
                    ['color' => $sTool, 'pck' => $pBox10,  'litres' => 10.0, 'seller' => 1800.00, 'cust' => 2250.00],
                ]
            ],
            // 11. Accessories - Paint Brushes
            [
                'name' => 'Professional Angled Bristle Paint Brush Set',
                'slug' => 'professional-angled-bristle-paint-brush-set',
                'title' => 'MasterCraft Professional 3-Piece Angled Trim Paint Brush Set',
                'description' => 'Premium synthetic filament brushes with rust-proof stainless steel ferrules and hardwood ergonomic handles.',
                'maincategory_id' => $midAccessories,
                'category_id' => $cidTools,
                'brand_id' => $bMasterCraft,
                'hsncode' => '9603',
                'tax' => 18,
                'price' => 450.00,
                'oldprice' => 550.00,
                'skus' => [
                    ['color' => $sTool, 'pck' => $pSingle, 'litres' => 1.0, 'seller' => 360.00, 'cust' => 450.00],
                    ['color' => $sTool, 'pck' => $pPack3,  'litres' => 3.0, 'seller' => 980.00, 'cust' => 1225.00],
                ]
            ],
            // 12. Accessories - Masking Tape
            [
                'name' => 'Heavy Duty Painter Masking Tape (50m)',
                'slug' => 'heavy-duty-painter-masking-tape-50m',
                'title' => 'MasterCraft Precision Edge No-Residue Painter Masking Tape',
                'description' => 'UV-resistant crepe paper masking tape for sharp paint lines without surface damage or sticky residue.',
                'maincategory_id' => $midAccessories,
                'category_id' => $cidTools,
                'brand_id' => $bMasterCraft,
                'hsncode' => '4811',
                'tax' => 18,
                'price' => 180.00,
                'oldprice' => 220.00,
                'skus' => [
                    ['color' => $sTool, 'pck' => $pSingle, 'litres' => 1.0, 'seller' => 140.00, 'cust' => 180.00],
                    ['color' => $sTool, 'pck' => $pPack3,  'litres' => 3.0, 'seller' => 380.00, 'cust' => 475.00],
                    ['color' => $sTool, 'pck' => $pBox10,  'litres' => 10.0, 'seller' => 1150.00, 'cust' => 1437.50],
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
                    'mid'            => $pData['maincategory_id'],
                    'cid'            => $pData['category_id'],
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
                    'mid'        => $pData['maincategory_id'],
                    'cid'        => $pData['category_id'],
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

        // 8. Ensure Alternative Vendor Companies exist for rejection alternatives & multi-vendor catalog
        $alternativeVendors = [
            [
                'email'       => 'apex.coatings@apnifactory.local',
                'name'        => 'Apex Coatings & Paints Ltd',
                'mobile'      => '9811223344',
                'gst'         => '07BBPPA1234F1Z1',
                'city'        => 'New Delhi',
                'state'       => 'Delhi',
                'pincode'     => '110001',
                'minorder'    => '2000.00',
                'category_id' => $midPaints,
            ],
            [
                'email'       => 'surat.textiles@apnifactory.local',
                'name'        => 'Surat Silk & Cotton Mills',
                'mobile'      => '9822334455',
                'gst'         => '24CCPPB5678G1Z2',
                'city'        => 'Surat',
                'state'       => 'Gujarat',
                'pincode'     => '395001',
                'minorder'    => '1500.00',
                'category_id' => $midFabrics,
            ],
            [
                'email'       => 'mastercraft.tools@apnifactory.local',
                'name'        => 'MasterCraft Hardware Mart',
                'mobile'      => '9833445566',
                'gst'         => '27DDPPC9012H1Z3',
                'city'        => 'Mumbai',
                'state'       => 'Maharashtra',
                'pincode'     => '400001',
                'minorder'    => '800.00',
                'category_id' => $midAccessories,
            ],
            [
                'email'       => 'premier.garments@apnifactory.local',
                'name'        => 'Premier Garments & Apparels',
                'mobile'      => '9844556677',
                'gst'         => '29EEPPD3456I1Z4',
                'city'        => 'Bengaluru',
                'state'       => 'Karnataka',
                'pincode'     => '560001',
                'minorder'    => '1200.00',
                'category_id' => $midGarments,
            ]
        ];

        foreach ($alternativeVendors as $idx => $v) {
            $altUserId = $idx + 10;
            
            DB::table('users')->updateOrInsert(
                ['email' => $v['email']],
                [
                    'name'       => $v['name'],
                    'password'   => Hash::make('vendor@123'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $resolvedUserId = DB::table('users')->where('email', $v['email'])->value('id') ?? $altUserId;

            DB::table('companies')->updateOrInsert(
                ['email' => $v['email']],
                [
                    'name'             => $v['name'],
                    'user_id'          => $resolvedUserId,
                    'email'            => $v['email'],
                    'mobile'           => $v['mobile'],
                    'maincategory_id'  => $v['category_id'],
                    'gst'              => $v['gst'],
                    'crn'              => 'CRN' . (100000 + $idx),
                    'minordervalue'    => $v['minorder'],
                    'city'             => $v['city'],
                    'state'            => $v['state'],
                    'pincode'          => $v['pincode'],
                    'comission'        => '5.00',
                    'status'           => 'Active',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }
    }
}
