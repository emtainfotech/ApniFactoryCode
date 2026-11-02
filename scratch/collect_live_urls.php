<?php

$ctx = stream_context_create([
    'http' => [
        'timeout' => 15,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$productUrls = [];

// Check page 1, 2, 3
for ($page = 1; $page <= 3; $page++) {
    $url = "https://apnifactory.co.in/products/?page={$page}";
    echo "Fetching $url...\n";
    $html = @file_get_contents($url, false, $ctx);
    if (!$html) break;

    // Match links ending with a slug and ID like /exterior-wall-primer/general/exterior-interior-wall-primer-75467/
    preg_match_all('/href="(\/[a-z0-9-]+\/[a-z0-9-]+\/[a-z0-9-]+-[0-9]+\/)"/i', $html, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $pUrl) {
            $productUrls[$pUrl] = true;
        }
    }
}

echo "Total unique product URLs found: " . count($productUrls) . "\n";
print_r(array_keys($productUrls));

