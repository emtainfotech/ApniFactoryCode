<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;

$prods = Product::all();
$byCat = [];
foreach ($prods as $p) {
    $catName = $p->category ? $p->category->name : 'Unknown';
    $byCat[$catName][] = $p->name;
}

foreach ($byCat as $catName => $names) {
    echo "Category: {$catName} (" . count($names) . " products)\n";
    foreach ($names as $n) {
        echo "   - {$n}\n";
    }
}

