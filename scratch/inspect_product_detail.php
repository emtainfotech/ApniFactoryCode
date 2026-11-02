<?php

$url = 'https://apnifactory.co.in/interior-wall-primer/general/interior-emulsion-wall-primer-443241/';
$ctx = stream_context_create([
    'http' => [
        'timeout' => 15,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$html = file_get_contents($url, false, $ctx);
echo "Product detail page length: " . strlen($html) . "\n";
file_put_contents('scratch/sample_product.html', $html);

// Search for shade card / color elements
preg_match_all('/<[^>]+(shade|color|pack|size|price|gallery|carousel)[^>]*>/i', $html, $matches);
echo "Shade/color/pack tags count: " . count($matches[0]) . "\n";
print_r(array_slice($matches[0], 0, 20));

// Search for images in page
preg_match_all('/<img\s+[^>]*src="([^"]+)"[^>]*>/i', $html, $imgMatches);
echo "Images found: " . count($imgMatches[1]) . "\n";
print_r(array_unique(array_slice($imgMatches[1], 0, 15)));

