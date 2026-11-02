<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "=== 1. SELLER / USER ACCOUNTS (users table) ===\n";
$users = User::all();
foreach ($users as $u) {
    $company = Company::where('user_id', $u->id)->first();
    $productCount = Product::where('user_id', $u->id)->count();
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Company: " . ($company ? $company->name : 'None') . " | Products: {$productCount}\n";
}

echo "\n=== 2. SUPER ADMIN ACCOUNTS (admins table) ===\n";
if (\Illuminate\Support\Facades\Schema::hasTable('admins')) {
    $admins = DB::table('admins')->get();
    foreach ($admins as $a) {
        $email = $a->email ?? $a->username ?? $a->name ?? 'N/A';
        $role = $a->role ?? $a->service ?? 'Admin';
        echo "ID: {$a->id} | Name: {$a->name} | Identifier: {$email} | Role: {$role}\n";
    }
}

echo "\n=== 3. CUSTOMER / BUYER ACCOUNTS (customers table) ===\n";
if (\Illuminate\Support\Facades\Schema::hasTable('customers')) {
    $customers = Customer::limit(15)->get();
    foreach ($customers as $c) {
        echo "ID: {$c->id} | Name: {$c->name} | Mobile: {$c->mobile} | Email: {$c->email} | Status: {$c->status}\n";
    }
}
