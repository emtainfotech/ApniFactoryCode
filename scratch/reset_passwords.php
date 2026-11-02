<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$seller = User::where('email', 'seller@apnifactory.local')->first();
if ($seller) {
    $seller->password = Hash::make('password123');
    $seller->save();
    echo "Seller (seller@apnifactory.local) password updated to: password123\n";
    echo "Verify test with password123: " . (Hash::check('password123', $seller->password) ? 'MATCH' : 'FAIL') . "\n";
} else {
    $seller = User::create([
        'name' => 'Demo Seller',
        'email' => 'seller@apnifactory.local',
        'password' => Hash::make('password123'),
        'mobile' => '9876543210',
    ]);
    echo "Seller created with password: password123\n";
}

// Also check Admin user
$adminUser = User::where('email', 'admin@apnifactory.local')->first();
if ($adminUser) {
    $adminUser->password = Hash::make('password123');
    $adminUser->save();
    echo "Admin (admin@apnifactory.local) password updated to: password123\n";
}

// Also ensure Super Admin in Admin model
$filamentAdmin = \App\Models\Admin::where('email', 'admin@admin.com')->first();
if ($filamentAdmin) {
    $filamentAdmin->password = Hash::make('password');
    $filamentAdmin->save();
    echo "Filament Admin (admin@admin.com) password updated to: password\n";
}
