<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

foreach (['orders', 'order_status', 'products', 'product_attributes', 'india_pincode'] as $tbl) {
    echo "=== Indexes for {$tbl} ===\n";
    $indexes = DB::select("SHOW INDEX FROM `{$tbl}`");
    $names = array_unique(array_map(fn($i) => $i->Key_name . " (" . $i->Column_name . ")", $indexes));
    foreach ($names as $n) {
        echo " - " . $n . "\n";
    }
}

