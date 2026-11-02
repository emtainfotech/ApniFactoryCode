<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$seller = User::where('email', 'seller@apnifactory.local')->first();
echo "seller@apnifactory.local exists: " . ($seller ? "YES" : "NO") . "\n";
echo "password123 matches: " . (Hash::check('password123', $seller->password) ? "YES" : "NO") . "\n";
echo "seller@123 matches: " . (Hash::check('seller@123', $seller->password) ? "YES" : "NO") . "\n";
echo "password matches: " . (Hash::check('password', $seller->password) ? "YES" : "NO") . "\n";

$admin = User::where('email', 'admin@apnifactory.local')->first();
echo "admin@apnifactory.local exists: " . ($admin ? "YES" : "NO") . "\n";
echo "admin@123 matches: " . (Hash::check('admin@123', $admin->password) ? "YES" : "NO") . "\n";
echo "password123 matches: " . (Hash::check('password123', $admin->password) ? "YES" : "NO") . "\n";

