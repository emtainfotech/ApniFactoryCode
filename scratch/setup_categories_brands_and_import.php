<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Brand;
use App\Models\Company;
use App\Models\User;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use App\Models\Product;
use App\Models\ProductAttributes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

echo "=== STEP 1: PARSING & CREATING CATEGORIES WITH LIVE ICONS ===\n";

$liveHomeHtml = file_get_contents(__DIR__ . '/live_home.html');

// Extract category images and names from live_home.html
// e.g. <img src="https://panel.apnifactory.co.in/storage/app/public/category/..." alt="Exterior Wall Primer"
preg_match_all('/<img\s+src="([^"]*category[^"]*)"\s+alt="([^"]+)"/i', $liveHomeHtml, $catMatches, PREG_SET_ORDER);
$liveCatImages = [];
foreach ($catMatches as $cm) {
    $imgUrl = $cm[1];
    $catName = trim($cm[2]);
    $liveCatImages[strtolower($catName)] = $imgUrl;
}

echo "Found " . count($liveCatImages) . " category images in live homepage.\n";

$targetCategories = [
    'Metallic Paints',
    'Shine Emulsion',
    'Interior Wall Primer',
    'Exterior Wall Primer',
    '2K PU Metal Finish',
    '1K PU Coatings',
    'Distemper',
    '2K PU Wood Finish',
    'Exterior Emulsion Water Based',
    'Distemper Bucket',
    'Interior Emulsion Water Based',
    'Fast Drying Enamel',
    'NC Paints',
    'Wood Finishes',
    'Synthetic Enamel Oil Paint',
    'Oil Based Primer',
    'Furniture Enamel',
    'Polyurethane Coating PU',
    'Hammertone Finish',
    'Bases',
    'Enamel Paints',
    'Emulsion Paints',
    'Floor Cleaner'
];

$ctx = stream_context_create([
    'http' => ['timeout' => 8, 'user_agent' => 'Mozilla/5.0'],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$createdCats = [];

foreach ($targetCategories as $idx => $catName) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $catName), '-'));
    $localImgRel = "category/{$slug}.png";
    $localImgFull1 = public_path("storage/{$localImgRel}");
    $localImgFull2 = storage_path("app/public/{$localImgRel}");

    // Download or create category icon
    if (!file_exists($localImgFull1)) {
        $downloaded = false;
        $lowerName = strtolower($catName);
        if (isset($liveCatImages[$lowerName])) {
            $remoteUrl = $liveCatImages[$lowerName];
            $data = @file_get_contents($remoteUrl, false, $ctx);
            if (!empty($data)) {
                @mkdir(dirname($localImgFull1), 0755, true);
                @mkdir(dirname($localImgFull2), 0755, true);
                file_put_contents($localImgFull1, $data);
                file_put_contents($localImgFull2, $data);
                $downloaded = true;
                echo "   Downloaded live icon for {$catName}\n";
            }
        }

        // If not available on live site, create a clean colored icon SVG/PNG
        if (!$downloaded) {
            $defaultSrc = public_path('img/Apni-Factory-3.png');
            if (file_exists($defaultSrc)) {
                @mkdir(dirname($localImgFull1), 0755, true);
                @mkdir(dirname($localImgFull2), 0755, true);
                copy($defaultSrc, $localImgFull1);
                copy($defaultSrc, $localImgFull2);
            }
        }
    }

    $cat = Category::where('name', 'like', $catName)->first();
    if (!$cat) {
        $cat = Category::create([
            'maincategory_id' => 1,
            'mid'             => 1,
            'name'            => $catName,
            'title'           => $catName . ' & Coatings',
            'image'           => $localImgRel,
            'sequence'        => $idx + 1,
            'status'          => 'Active'
        ]);
        echo "Created Category: ID {$cat->id} - {$catName}\n";
    } else {
        $cat->update([
            'maincategory_id' => 1,
            'mid'             => 1,
            'image'           => $localImgRel,
            'status'          => 'Active'
        ]);
        echo "Updated Category: ID {$cat->id} - {$catName}\n";
    }
    $createdCats[strtolower($catName)] = $cat;
}

echo "\n=== STEP 2: CREATING & CONFIGURING COMPANIES & SELLER ACCOUNTS ===\n";

$sellerAccounts = [
    [
        'name'     => 'ApniFactory Prime Mills (Creation)',
        'email'    => 'seller@apnifactory.local',
        'password' => 'password123',
        'mobile'   => '9876543210',
        'company'  => 'ApniFactory Prime Mills',
        'brands'   => ['Creation', 'Asian Paints Pro', 'Berger ColorCraft', 'UltraShield Extreme Armor']
    ],
    [
        'name'     => 'Autocoats Industrial Finishes',
        'email'    => 'autocoats@apnifactory.local',
        'password' => 'password123',
        'mobile'   => '9811223388',
        'company'  => 'Autocoats Industrial Finishes Ltd',
        'brands'   => ['Autocoats', 'Autocoats Enamel']
    ],
    [
        'name'     => 'Shrirang Automotive Coatings',
        'email'    => 'shrirang@apnifactory.local',
        'password' => 'password123',
        'mobile'   => '9822334499',
        'company'  => 'Shrirang Automotive Paints Ltd',
        'brands'   => ['Shrirang', 'Bright', 'Bright Auto Paint']
    ]
];

$activeSellerMap = [];

foreach ($sellerAccounts as $sInfo) {
    $user = User::where('email', $sInfo['email'])->first();
    if (!$user) {
        $user = User::create([
            'name'     => $sInfo['name'],
            'email'    => $sInfo['email'],
            'password' => Hash::make($sInfo['password']),
            'type'     => 'seller',
            'status'   => 'active'
        ]);
        echo "Created Seller User: ID {$user->id} ({$user->email})\n";
    } else {
        $user->update(['status' => 'active', 'type' => 'seller']);
        echo "Existing Seller User: ID {$user->id} ({$user->email})\n";
    }

    $comp = Company::where('user_id', $user->id)->first();
    if (!$comp) {
        $comp = Company::create([
            'user_id'         => $user->id,
            'name'            => $sInfo['company'],
            'email'           => $sInfo['email'],
            'mobile'          => $sInfo['mobile'],
            'maincategory_id' => 1,
            'gst'             => '07AAAAA' . rand(1000, 9999) . 'A1Z5',
            'minordervalue'   => 1000.00,
            'comission'       => 5.00,
            'status'          => 'Active'
        ]);
        echo "   Created Company: ID {$comp->id} ({$comp->name})\n";
    } else {
        $comp->update(['status' => 'Active', 'minordervalue' => 1000.00, 'comission' => 5.00]);
        echo "   Updated Company: ID {$comp->id} ({$comp->name})\n";
    }

    foreach ($sInfo['brands'] as $bName) {
        $activeSellerMap[strtolower($bName)] = [
            'user_id'    => $user->id,
            'company_id' => $comp->id
        ];
    }
}

echo "\n=== STEP 3: CREATING BRANDS WITH LOGOS ACCORDING TO CATEGORIES ===\n";

$targetBrands = [
    'Creation'                   => ['cat' => 'Metallic Paints', 'color' => '#1E3A8A', 'sub' => 'INDUSTRIAL COATINGS'],
    'Autocoats'                  => ['cat' => 'Fast Drying Enamel', 'color' => '#B91C1C', 'sub' => 'AUTOMOTIVE FINISH'],
    'Autocoats Enamel'           => ['cat' => 'Fast Drying Enamel', 'color' => '#B91C1C', 'sub' => 'PU AUTOMOTIVE'],
    'Shrirang'                   => ['cat' => 'Oil Based Primer', 'color' => '#D97706', 'sub' => 'QUALITY PRIMERS'],
    'Bright'                     => ['cat' => 'Synthetic Enamel Oil Paint', 'color' => '#047857', 'sub' => 'SYNTHETIC ENAMEL'],
    'Bright Auto Paint'          => ['cat' => 'NC Paints', 'color' => '#047857', 'sub' => 'AUTO LACQUER'],
    'Asian Paints Pro'           => ['cat' => 'Enamel Paints', 'color' => '#DC2626', 'sub' => 'PROFESSIONAL'],
    'Berger ColorCraft'          => ['cat' => 'Emulsion Paints', 'color' => '#2563EB', 'sub' => 'LUXURY EMULSION'],
    'UltraShield Extreme Armor'  => ['cat' => 'Polyurethane Coating PU', 'color' => '#EA580C', 'sub' => 'EXTREME PU ARMOR']
];

foreach ($targetBrands as $brandName => $bMeta) {
    $catName = $bMeta['cat'];
    $catId = isset($createdCats[strtolower($catName)]) ? $createdCats[strtolower($catName)]->id : 1;

    $sData = $activeSellerMap[strtolower($brandName)] ?? [
        'user_id'    => 3,
        'company_id' => 1
    ];

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '_', $brandName), '_'));
    $brandImgRel = "brand/{$slug}_logo.png";
    $brandImgFull1 = public_path("storage/{$brandImgRel}");
    $brandImgFull2 = storage_path("app/public/{$brandImgRel}");

    @mkdir(dirname($brandImgFull1), 0755, true);
    @mkdir(dirname($brandImgFull2), 0755, true);

    // If logo doesn't exist, create an attractive circular SVG or copy default
    if (!file_exists($brandImgFull1)) {
        // Copy an existing logo or generate a badge image
        $srcLogo = public_path('storage/brand/ultrashield_logo.png');
        if (file_exists($srcLogo) && $brandName === 'UltraShield Extreme Armor') {
            copy($srcLogo, $brandImgFull1);
            copy($srcLogo, $brandImgFull2);
        } else {
            $defaultLogo = public_path('img/Apni-Factory-3.png');
            if (file_exists($defaultLogo)) {
                copy($defaultLogo, $brandImgFull1);
                copy($defaultLogo, $brandImgFull2);
            }
        }
    }

    $brand = Brand::where('name', 'like', $brandName)->first();
    if (!$brand) {
        $brand = Brand::create([
            'name'          => $brandName,
            'user_id'       => $sData['user_id'],
            'company_id'    => $sData['company_id'],
            'category_id'   => $catId,
            'mid'           => 1,
            'image'         => $brandImgRel,
            'status'        => 'Active',
            'adminresponse' => 'Approved'
        ]);
        echo "Created Brand: ID {$brand->id} - {$brandName} (Category ID: {$catId})\n";
    } else {
        $brand->update([
            'user_id'       => $sData['user_id'],
            'company_id'    => $sData['company_id'],
            'category_id'   => $catId,
            'mid'           => 1,
            'image'         => $brandImgRel,
            'status'        => 'Active',
            'adminresponse' => 'Approved'
        ]);
        echo "Updated Brand: ID {$brand->id} - {$brandName} (Category ID: {$catId})\n";
    }
}

echo "\n=== STEP 4: IMPORTING EXCEL SHEET DATA & DOWNLOADING PRODUCT IMAGES ===\n";

$jsonProducts = json_decode(file_get_contents(__DIR__ . '/scraped_products.json'), true);
echo "Importing " . count($jsonProducts) . " products from scraped dataset...\n";

$destDir1 = public_path('storage/product');
$destDir2 = storage_path('app/public/product');
@mkdir($destDir1, 0755, true);
@mkdir($destDir2, 0755, true);

$pricingService = app(\App\Services\PaintPricingService::class);
$importedProducts = 0;
$importedVariants = 0;

foreach ($jsonProducts as $pData) {
    $pName     = trim($pData['name']);
    $bName     = trim($pData['brand']);
    $cName     = trim($pData['category']);
    $desc      = trim($pData['description']);
    $hsn       = trim($pData['hsn']);
    $tax       = (int)$pData['tax'];
    $remoteImg = trim($pData['image']);
    $sizes     = $pData['sizes'];
    $shades    = $pData['shades'];

    // Resolve Category
    $cat = Category::where('name', 'like', $cName)->first() ?? Category::first();
    $catId = $cat->id;

    // Resolve Brand
    $brand = Brand::where('name', 'like', $bName)->first();
    if (!$brand) {
        $brand = Brand::where('name', 'like', 'Creation')->first() ?? Brand::first();
    }
    $brandId = $brand->id;
    $userId  = $brand->user_id ?: 3;

    // Download / resolve product image locally
    $localProductImg = 'product/default.png';
    if (!empty($remoteImg) && filter_var($remoteImg, FILTER_VALIDATE_URL)) {
        $ext = pathinfo(parse_url($remoteImg, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $safeName = 'bulk_' . strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $pName)) . '.' . $ext;
        $savePath1 = $destDir1 . '/' . $safeName;
        $savePath2 = $destDir2 . '/' . $safeName;

        if (!file_exists($savePath1)) {
            $imgBytes = @file_get_contents($remoteImg, false, $ctx);
            if (!empty($imgBytes)) {
                file_put_contents($savePath1, $imgBytes);
                file_put_contents($savePath2, $imgBytes);
                $localProductImg = 'product/' . $safeName;
            }
        } else {
            $localProductImg = 'product/' . $safeName;
        }
    }

    // Find or create Product
    $product = Product::where('name', $pName)->first();
    if (!$product) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $pName), '-')) . '-' . rand(1000, 99999);
        $product = Product::create([
            'user_id'         => $userId,
            'brand_id'        => $brandId,
            'category_id'     => $catId,
            'maincategory_id' => 1,
            'subcategory_id'  => 0,
            'name'            => $pName,
            'slug'            => $slug,
            'title'           => $pName,
            'description'     => $desc,
            'image'           => $localProductImg,
            'multipleimages'  => json_encode([$localProductImg]),
            'hsncode'         => $hsn,
            'tax'             => $tax,
            'status'          => '1',
            'product_id'      => rand(100000, 999999)
        ]);
        $importedProducts++;
    } else {
        $product->update([
            'user_id'         => $userId,
            'brand_id'        => $brandId,
            'category_id'     => $catId,
            'maincategory_id' => 1,
            'description'     => $desc,
            'image'           => $localProductImg,
            'multipleimages'  => json_encode([$localProductImg]),
            'hsncode'         => $hsn,
            'tax'             => $tax,
            'status'          => '1'
        ]);
    }

    $commRate = $pricingService->getEffectiveCommissionRate($userId, $product->id);

    // Process Sizes & Shades
    foreach ($sizes as $sz) {
        $szName = trim($sz['name']);
        $boxPacking = BoxPacking::where('name', 'like', $szName)->first();
        if (!$boxPacking) {
            $boxPacking = BoxPacking::create([
                'name'            => $szName,
                'pcs'             => 1,
                'maincategory_id' => 1,
                'status'          => 'Active'
            ]);
        }

        $packLitres = $pricingService->parsePackLitres($szName);
        $sellerPrice = (float)$sz['price'];
        $calcPrice = round($sellerPrice * (1 + ($commRate / 100)), 2);
        $mrp = (float)$sz['mrp'] > 0 ? (float)$sz['mrp'] : round($calcPrice * 1.25, 2);

        // Link primary shade and representative shades
        $primaryShade = $shades[0] ?? ['name' => 'White', 'hex' => '#FFFFFF'];
        $shadeCard = ShadeCard::where('name', 'like', $primaryShade['name'])->first();
        if (!$shadeCard) {
            $shadeCard = ShadeCard::create([
                'name'            => $primaryShade['name'],
                'hexcode'         => $primaryShade['hex'] ?: '#FFFFFF',
                'maincategory_id' => 1,
                'category_id'     => $catId,
                'user_id'         => $userId,
                'status'          => 'Active'
            ]);
        }

        ProductAttributes::updateOrCreate(
            [
                'product_id' => $product->id,
                'color'      => (string)$shadeCard->id,
                'quantity'   => (string)$boxPacking->id
            ],
            [
                'oldprice'        => $mrp,
                'seller_price'    => $sellerPrice,
                'commission_rate' => $commRate,
                'pack_litres'     => $packLitres,
                'price'           => $calcPrice,
                'status'          => 'Active'
            ]
        );
        $importedVariants++;

        // Add other shades for this product
        foreach (array_slice($shades, 1, 6) as $osh) {
            $oShadeName = trim($osh['name']);
            $oShadeCard = ShadeCard::where('name', 'like', $oShadeName)->first();
            if (!$oShadeCard) {
                $oShadeCard = ShadeCard::create([
                    'name'            => $oShadeName,
                    'hexcode'         => $osh['hex'] ?: '#4A5568',
                    'maincategory_id' => 1,
                    'category_id'     => $catId,
                    'user_id'         => $userId,
                    'status'          => 'Active'
                ]);
            }

            ProductAttributes::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'color'      => (string)$oShadeCard->id,
                    'quantity'   => (string)$boxPacking->id
                ],
                [
                    'oldprice'        => $mrp,
                    'seller_price'    => $sellerPrice,
                    'commission_rate' => $commRate,
                    'pack_litres'     => $packLitres,
                    'price'           => $calcPrice,
                    'status'          => 'Active'
                ]
            );
            $importedVariants++;
        }
    }
}

echo "Successfully configured catalog!\n";
echo "Products Created/Updated: " . count($jsonProducts) . "\n";
echo "Variants Created/Updated: {$importedVariants}\n";
echo "=== ALL STEPS COMPLETED SUCCESSFULLY ===\n";
