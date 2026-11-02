<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Brand;
use App\Models\Company;

echo "=== CURRENT CATEGORIES ===\n";
$cats = Category::all();
foreach ($cats as $c) {
    echo "ID: {$c->id} | Mid: {$c->maincategory_id} | Name: {$c->name} | Image: {$c->image} | Status: {$c->status}\n";
}

echo "\n=== CURRENT BRANDS ===\n";
$brands = Brand::all();
foreach ($brands as $b) {
    echo "ID: {$b->id} | User: {$b->user_id} | CatID: {$b->category_id} | Name: {$b->name} | Image: {$b->image} | Status: {$b->status}\n";
}
