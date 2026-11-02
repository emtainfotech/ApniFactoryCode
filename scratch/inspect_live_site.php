<?php

$ctx = stream_context_create([
    'http' => [
        'timeout' => 15,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

$html = file_get_contents('https://apnifactory.co.in/', false, $ctx);
echo "Fetched bytes: " . strlen($html) . "\n";
file_put_contents('scratch/live_home.html', $html);

// Find links
preg_match_all('/href="([^"]*product[^"]*)"/i', $html, $matches);
echo "Product link matches found: " . count($matches[1]) . "\n";
print_r(array_unique(array_slice($matches[1], 0, 15)));

// Find all a hrefs to understand routing
preg_match_all('/href="([^"#][^"]*)"/i', $html, $allLinks);
$cleanLinks = array_unique($allLinks[1]);
echo "Total unique links: " . count($cleanLinks) . "\n";
$sampleLinks = array_filter($cleanLinks, function($l) {
    return !str_contains($l, '.css') && !str_contains($l, '.js') && !str_contains($l, '.png') && !str_contains($l, '.jpg');
});
print_r(array_slice($sampleLinks, 0, 30));
