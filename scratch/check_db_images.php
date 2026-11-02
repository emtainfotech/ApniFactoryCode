<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$prods = Product::where('id', '>=', 28)->get(['id', 'name', 'image']);
foreach ($prods as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Image: {$p->image} | Exists: " . (file_exists(public_path('storage/' . $p->image)) ? 'YES' : 'NO') . "\n";
}
