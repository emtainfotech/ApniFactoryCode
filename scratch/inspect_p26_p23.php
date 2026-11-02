<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$p26 = Product::find(26);
$p23 = Product::find(23);

if ($p26) echo "ID 26: {$p26->name} | Img: {$p26->image}\n";
if ($p23) echo "ID 23: {$p23->name} | Img: {$p23->image}\n";
