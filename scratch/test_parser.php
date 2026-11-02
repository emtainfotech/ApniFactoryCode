<?php

$ctx = stream_context_create([
    'http' => [
        'timeout' => 15,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$testUrls = [
    'https://apnifactory.co.in/metallic-paints/general/metallic-emulsion-paints-533215/',
    'https://apnifactory.co.in/shine-emulsion/general/platinum-high-gloss-emulsion-150098/',
    'https://apnifactory.co.in/interior-wall-primer/general/interior-emulsion-wall-primer-443241/'
];

foreach ($testUrls as $url) {
    echo "========================================\n";
    echo "URL: $url\n";
    $html = file_get_contents($url, false, $ctx);
    if (!$html) continue;

    // 1. Title / Name
    $name = '';
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
        $name = trim(strip_tags($m[1]));
    } elseif (preg_match('/<meta\s+property="og:title"\s+content="([^"]+)"/i', $html, $m)) {
        $parts = explode('|', $m[1]);
        $name = trim($parts[0]);
    }
    echo "Name: $name\n";

    // 2. Brand
    $brand = 'Creation';
    if (preg_match('/by\s+([A-Z0-9\s]+?)\s+at wholesale/i', $html, $m)) {
        $brand = trim($m[1]);
    } elseif (preg_match('/<div[^>]*class="[^"]*brand[^"]*"[^>]*>(.*?)<\/div>/is', $html, $m)) {
        $brand = trim(strip_tags($m[1]));
    }
    echo "Brand: $brand\n";

    // 3. Category
    $category = '';
    if (preg_match('/<li[^>]*class="breadcrumb-item"[^>]*>\s*<a[^>]*>(.*?)<\/a>\s*<\/li>/is', $html, $m)) {
        $category = trim(strip_tags($m[1]));
    }
    if (empty($category) && preg_match('/apnifactory\.co\.in\/([^\/]+)\//', $url, $m)) {
        $category = ucwords(str_replace('-', ' ', $m[1]));
    }
    echo "Category: $category\n";

    // 4. Description
    $desc = '';
    if (preg_match('/<meta\s+name="description"\s+content="([^"]+)"/i', $html, $m)) {
        $desc = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    }
    echo "Description: " . substr($desc, 0, 100) . "...\n";

    // 5. Main Image
    $mainImage = '';
    if (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $m)) {
        $mainImage = trim($m[1]);
    } elseif (preg_match('/<img[^>]*class="[^"]*prod-main-img[^"]*"[^>]*src="([^"]+)"/i', $html, $m)) {
        $mainImage = trim($m[1]);
    }
    echo "Main Image: $mainImage\n";

    // 6. Sizes & Prices
    // selectSizePill(this, '4 Ltr', 114.67, 143.34, 20)
    preg_match_all('/selectSizePill\s*\([^,]+,\s*[\'"]([^\'"]+)[\'"]\s*,\s*([0-9.]+)\s*,\s*([0-9.]+)/i', $html, $sizeMatches, PREG_SET_ORDER);
    echo "Sizes Found (" . count($sizeMatches) . "):\n";
    foreach ($sizeMatches as $sm) {
        echo " - Size: {$sm[1]} | Price: Rs.{$sm[2]} | MRP: Rs.{$sm[3]}\n";
    }

    // 7. Shade Cards
    // selectColorVariant(this, 'White', '#fffff', '...')
    preg_match_all('/selectColorVariant\s*\([^,]+,\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]/i', $html, $shadeMatches, PREG_SET_ORDER);
    echo "Shade Cards Found (" . count($shadeMatches) . "):\n";
    foreach ($shadeMatches as $shm) {
        echo " - Shade: {$shm[1]} | Hex: {$shm[2]} | Swatch Image: {$shm[3]}\n";
    }
}

