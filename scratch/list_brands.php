<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Brand;

foreach (Brand::all(['id', 'name', 'image']) as $b) {
    echo "{$b->id} | {$b->name} | {$b->image}\n";
}

