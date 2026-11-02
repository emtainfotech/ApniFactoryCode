<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

echo "Columns in users:\n";
print_r(Schema::getColumnListing('users'));

$seller = User::where('email', 'seller@apnifactory.local')->first();
if ($seller) {
    echo "Found seller user:\n";
    echo "ID: " . $seller->id . "\n";
    echo "Email: " . $seller->email . "\n";
    echo "Password check 'password123': " . (Hash::check('password123', $seller->password) ? 'TRUE' : 'FALSE') . "\n";
    echo "Password raw: " . $seller->password . "\n";
} else {
    echo "Seller not found by seller@apnifactory.local\n";
}

echo "\nCompanies:\n";
foreach (Company::all() as $c) {
    echo "Company ID: {$c->id} | Name: {$c->name} | User ID: " . ($c->user_id ?? 'N/A') . " | Email: " . ($c->email ?? 'N/A') . "\n";
}

