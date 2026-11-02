<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;

$credentials = [
    'email' => 'seller@apnifactory.local',
    'password' => 'password123'
];

$success = Auth::attempt($credentials);
echo "Auth::attempt result: " . ($success ? "SUCCESS" : "FAILED") . "\n";
if ($success) {
    $u = Auth::user();
    echo "Logged in as ID: {$u->id}, Email: {$u->email}, Name: {$u->name}\n";
}
