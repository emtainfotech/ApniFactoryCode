<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Seed into users table for Filament web auth and seller portal
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@apnifactory.local'],
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@apnifactory.local',
                'password' => Hash::make('admin@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'seller@apnifactory.local'],
            [
                'name'     => 'Demo Seller',
                'email'    => 'seller@apnifactory.local',
                'password' => Hash::make('seller@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Seed into Admin table
        if (\Illuminate\Support\Facades\Schema::hasTable('Admin')) {
            DB::table('Admin')->updateOrInsert(
                ['email' => 'admin@apnifactory.local'],
                [
                    'name'     => 'Super Admin',
                    'email'    => 'admin@apnifactory.local',
                    'password' => Hash::make('admin@123'),
                    'role'     => 'superadmin',
                    'services' => 'all',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Seed basic india_pincode data for state/city dropdowns
        if (\Illuminate\Support\Facades\Schema::hasTable('india_pincode')) {
            DB::table('india_pincode')->updateOrInsert(
                ['pincode' => '110001'],
                ['state' => 'Delhi', 'city' => 'New Delhi', 'district' => 'Central Delhi', 'country' => 'India', 'created_at' => now(), 'updated_at' => now()]
            );
            DB::table('india_pincode')->updateOrInsert(
                ['pincode' => '400001'],
                ['state' => 'Maharashtra', 'city' => 'Mumbai', 'district' => 'Mumbai', 'country' => 'India', 'created_at' => now(), 'updated_at' => now()]
            );
            DB::table('india_pincode')->updateOrInsert(
                ['pincode' => '560001'],
                ['state' => 'Karnataka', 'city' => 'Bengaluru', 'district' => 'Bangalore', 'country' => 'India', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
