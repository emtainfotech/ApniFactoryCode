<?php

$ctx = stream_context_create([
    'http' => [
        'timeout' => 20,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$urls = [
    '/metallic-paints/general/metallic-emulsion-paints-533215/',
    '/shine-emulsion/general/platinum-high-gloss-emulsion-150098/',
    '/interior-wall-primer/general/interior-emulsion-wall-primer-443241/',
    '/exterior-wall-primer/general/exterior-interior-wall-primer-75467/',
    '/exterior-wall-primer/general/exterior-wall-primer-643970/',
    '/bases/general/platinum-high-gloss-base-cph-21-851228/',
    '/bases/general/gold-luxury-silk-emulsion-base-11-195217/',
    '/bases/general/platinum-high-gloss-base-cph-10-958868/',
    '/bases/general/creation-gold-luxery-silk-eml-84119/',
    '/2k-pu-metal-finish/general/2k-pu-metal-finish-268361/',
    '/interior-emulsion-water-based/general/creation-interior-emulsion-378916/',
    '/1k-pu-coatings-single-pack/general/1k-pu-coatings-969900/',
    '/distemper/general/distemper-pouch-639076/',
    '/2k-pu-wood-finish/general/pu-wood-815418/',
    '/shine-emulsion/general/shine-emulsion-738130/',
    '/exterior-emulsion-water-based/general/exterior-emulaion-water-based-549518/',
    '/distemper-bucket/general/creation-acrylic-distemper-227968/',
    '/interior-emulsion-water-based/general/cp-eco-interior-emlusion-363228/',
    '/2k-pu-interior-finish/general/2k-pu-interior-finishk-569356/',
    '/interior-emulsion-water-based/general/creation-interior-sheen-emulsion-786522/',
    '/synthetic-and-fast-drying-enamel/general/autocoats-enamel-844282/',
    '/synthetic-and-fast-drying-enamel/general/bright-synthetic-enamel-395217/',
    '/n-c-paints/general/shrirang-n-c-paint-328339/',
    '/wood-finishes/general/melamine-wood-finish-933553/',
    '/synthetic-enamel-oil-paint/general/synthetic-enamel-oil-paint-658046/',
    '/oil-based-primer/general/shrirang-115858/',
    '/furniture-enamel/general/furniture-enamel-304157/',
    '/polyurethane-coating-pu/general/2k-pu-paints-524135/',
    '/hammertone-finish/general/hammertone-finish-574388/',
    '/n-c-paints/general/bright-auto-paint-616193/'
];

echo "Total target URLs to scrape: " . count($urls) . "\n";

$scrapedProducts = [];

foreach ($urls as $idx => $relUrl) {
    $fullUrl = 'https://apnifactory.co.in' . $relUrl;
    echo "[" . ($idx + 1) . "/" . count($urls) . "] Fetching $fullUrl...\n";

    $html = @file_get_contents($fullUrl, false, $ctx);
    if (!$html) {
        echo "   FAILED to fetch\n";
        continue;
    }

    // 1. Name
    $name = '';
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
        $name = trim(strip_tags($m[1]));
    } elseif (preg_match('/<meta\s+property="og:title"\s+content="([^"]+)"/i', $html, $m)) {
        $parts = explode('|', $m[1]);
        $name = trim($parts[0]);
    }
    if (empty($name)) {
        $name = ucwords(str_replace('-', ' ', basename(rtrim($relUrl, '/'))));
    }

    // 2. Brand
    $brand = 'Creation';
    if (preg_match('/by\s+([A-Z0-9\s]+?)\s+at wholesale/i', $html, $m)) {
        $brand = ucwords(strtolower(trim($m[1])));
    } elseif (preg_match('/brand[s]?=([^"&]+)/i', $html, $m)) {
        $brand = urldecode($m[1]);
    }

    // 3. Category
    $category = '';
    if (preg_match('/apnifactory\.co\.in\/([^\/]+)\//', $fullUrl, $m)) {
        $category = ucwords(str_replace('-', ' ', $m[1]));
    }
    if (empty($category)) $category = 'Paints & Coatings';

    // 4. Description
    $desc = '';
    if (preg_match('/<meta\s+name="description"\s+content="([^"]+)"/i', $html, $m)) {
        $desc = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    }
    if (empty($desc)) {
        $desc = "Genuine wholesale {$name} by {$brand}. Direct factory supply with GST invoice and pan-India dispatch.";
    }

    // 5. Sizes & Prices
    preg_match_all('/selectSizePill\s*\([^,]+,\s*[\'"]([^\'"]+)[\'"]\s*,\s*([0-9.]+)\s*,\s*([0-9.]+)/i', $html, $sizeMatches, PREG_SET_ORDER);
    $sizes = [];
    if (!empty($sizeMatches)) {
        foreach ($sizeMatches as $sm) {
            $sizes[] = [
                'name'  => trim($sm[1]),
                'price' => (float)$sm[2],
                'mrp'   => (float)$sm[3]
            ];
        }
    } else {
        // Fallback default packing
        $sizes[] = ['name' => '1 Litre', 'price' => 350.0, 'mrp' => 450.0];
    }

    // 6. Shade Cards
    preg_match_all('/selectColorVariant\s*\([^,]+,\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]/i', $html, $shadeMatches, PREG_SET_ORDER);
    $shades = [];
    $productImage = '';

    if (!empty($shadeMatches)) {
        foreach ($shadeMatches as $shm) {
            $shadeName = trim($shm[1]);
            $shadeHex  = trim($shm[2]);
            $swatchImg = trim($shm[3]);
            if (empty($productImage) && !empty($swatchImg)) {
                $productImage = $swatchImg;
            }
            $shades[] = [
                'name'  => $shadeName,
                'hex'   => $shadeHex,
                'image' => $swatchImg
            ];
        }
    }

    // If no shades found, look for general product image
    if (empty($productImage)) {
        if (preg_match('/<img[^>]*class="[^"]*prod-main-img[^"]*"[^>]*src="([^"]+)"/i', $html, $m)) {
            $productImage = $m[1];
        } elseif (preg_match('/https:\/\/panel\.apnifactory\.co\.in\/storage\/app\/public\/product\/[^"\'\s>]+/i', $html, $m)) {
            $productImage = $m[0];
        }
    }

    // Fallback shade if none explicitly found
    if (empty($shades)) {
        $shades[] = [
            'name'  => 'Standard White',
            'hex'   => '#FFFFFF',
            'image' => $productImage
        ];
    }

    // Determine HSN & Tax
    $hsn = '3208';
    $tax = 18;
    if (stripos($category, 'distemper') !== false) {
        $hsn = '3214';
    } elseif (stripos($category, 'primer') !== false) {
        $hsn = '3209';
    } elseif (stripos($category, 'cleaner') !== false || stripos($category, 'wash') !== false) {
        $hsn = '3402';
    }

    $scrapedProducts[] = [
        'name'          => $name,
        'brand'         => $brand,
        'category'      => $category,
        'title'         => $name . ' (' . $brand . ')',
        'description'   => $desc,
        'hsn'           => $hsn,
        'tax'           => $tax,
        'image'         => $productImage,
        'sizes'         => $sizes,
        'shades'        => $shades
    ];

    echo "   -> Name: {$name} | Sizes: " . count($sizes) . " | Shades: " . count($shades) . " | Image: {$productImage}\n";
}

file_put_contents('scratch/scraped_products.json', json_encode($scrapedProducts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\nTotal scraped products: " . count($scrapedProducts) . "\n";

