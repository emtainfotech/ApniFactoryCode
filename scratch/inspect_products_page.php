<?php

$ctx = stream_context_create([
    'http' => [
        'timeout' => 15,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$html = file_get_contents('https://apnifactory.co.in/products/', false, $ctx);
echo "Products page length: " . strlen($html) . "\n";
file_put_contents('scratch/live_products.html', $html);

// Look for product card patterns or product detail URLs
preg_match_all('/href="(\/[^"]*\/[0-9]+\/|\/[^"]*product[^"]*|\/[^"]+-[0-9]+\/)"/i', $html, $matches);
print_r(array_unique($matches[1]));

// Also check all links in live_products.html
preg_match_all('/<a\s+[^>]*href="([^"]+)"[^>]*>/i', $html, $allA);
$sampleA = array_unique(array_filter($allA[1], function($l) {
    return !str_contains($l, '.css') && !str_contains($l, '.js') && !str_contains($l, '.png') && !str_contains($l, '.jpg') && $l !== '/' && !str_starts_with($l, 'http');
}));
print_r(array_slice($sampleA, 0, 30));

