<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Main Category if not present
        if (\Illuminate\Support\Facades\Schema::hasTable('main_categories')) {
            DB::table('main_categories')->updateOrInsert(
                ['id' => 1],
                [
                    'name'       => 'Paints & Coatings',
                    'status'     => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Seed into users table for Filament web auth and seller portal
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@apnifactory.com'],
            [
                'name'       => 'Super Admin',
                'email'      => 'admin@apnifactory.com',
                'password'   => Hash::make('admin@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@apnifactory.local'],
            [
                'name'       => 'Super Admin',
                'email'      => 'admin@apnifactory.local',
                'password'   => Hash::make('admin@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'seller@apnifactory.local'],
            [
                'name'       => 'Demo Seller',
                'email'      => 'seller@apnifactory.local',
                'password'   => Hash::make('seller@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $adminUser = DB::table('users')->where('email', 'admin@apnifactory.com')->first();
        $sellerUser = DB::table('users')->where('email', 'seller@apnifactory.local')->first();

        // 3. Seed into Admin table
        if (\Illuminate\Support\Facades\Schema::hasTable('Admin')) {
            DB::table('Admin')->updateOrInsert(
                ['email' => 'admin@apnifactory.com'],
                [
                    'name'       => 'Super Admin',
                    'email'      => 'admin@apnifactory.com',
                    'password'   => Hash::make('admin@123'),
                    'role'       => 'superadmin',
                    'services'   => 'all',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            DB::table('Admin')->updateOrInsert(
                ['email' => 'admin@apnifactory.local'],
                [
                    'name'       => 'Super Admin',
                    'email'      => 'admin@apnifactory.local',
                    'password'   => Hash::make('admin@123'),
                    'role'       => 'superadmin',
                    'services'   => 'all',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 4. Seed Companies for Admin and Seller so /seller/profile and /seller/brand never crash
        if (\Illuminate\Support\Facades\Schema::hasTable('companies')) {
            if ($adminUser) {
                DB::table('companies')->updateOrInsert(
                    ['user_id' => $adminUser->id],
                    [
                        'name'            => 'ApniFactory HQ',
                        'maincategory_id' => 1,
                        'email'           => 'admin@apnifactory.com',
                        'mobile'          => '9999999999',
                        'city'            => 'Jaipur',
                        'state'           => 'Rajasthan',
                        'pincode'         => '302001',
                        'status'          => '1',
                        'minordervalue'   => 5000.00,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]
                );
            }

            if ($sellerUser) {
                DB::table('companies')->updateOrInsert(
                    ['user_id' => $sellerUser->id],
                    [
                        'name'            => 'Demo Seller Enterprise',
                        'maincategory_id' => 1,
                        'email'           => 'seller@apnifactory.local',
                        'mobile'          => '9876543210',
                        'city'            => 'Jaipur',
                        'state'           => 'Rajasthan',
                        'pincode'         => '302022',
                        'status'          => '1',
                        'minordervalue'   => 10000.00,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]
                );
            }
        }

        // 5. Seed basic categories if empty
        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            DB::table('categories')->updateOrInsert(
                ['id' => 1],
                [
                    'name'            => 'Wall Paints',
                    'maincategory_id' => 1,
                    'status'          => 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }

        // 6. Seed basic india_pincode data for state/city dropdowns
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
                ['pincode' => '302022'],
                ['state' => 'Rajasthan', 'city' => 'Jaipur', 'district' => 'Jaipur', 'country' => 'India', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
