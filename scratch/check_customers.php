<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;

$customers = Customer::whereIn("email", ["customer@apnifactory.local", "demo@apnifactory.local"])
    ->orWhereIn("mobile", ["9876543210", "9999999999"])
    ->get();

foreach ($customers as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | Email: {$c->email} | Mobile: {$c->mobile} | Status: {$c->status}\n";
}

