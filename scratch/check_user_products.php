<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "--- Products count per user_id in products table ---\n";
$userProducts = DB::table('products')
    ->select('user_id', DB::raw('count(*) as total'))
    ->groupBy('user_id')
    ->get();

foreach ($userProducts as $up) {
    $user = User::find($up->user_id);
    $userName = $user ? $user->name . " ({$user->email})" : "No user";
    echo "user_id: {$up->user_id} | Total Products: {$up->total} | User: {$userName}\n";
}

echo "\n--- Products count per company_id in companies table ---\n";
$companies = Company::all();
foreach ($companies as $c) {
    $prods = Product::where('user_id', $c->user_id)->count();
    echo "Company ID: {$c->id} | Name: {$c->name} | user_id: {$c->user_id} | Email: {$c->email} | Products: {$prods}\n";
}

