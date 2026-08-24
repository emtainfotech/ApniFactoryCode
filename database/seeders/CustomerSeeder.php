<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        // Seed a local test user
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Test Customer',
            'email'      => 'customer@apnifactory.local',
            'password'   => Hash::make('customer@123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed corresponding customer record
        DB::table('customers')->insert([
            'name'       => 'Test Customer',
            'email'      => 'customer@apnifactory.local',
            'mobile'     => '9876543210',
            'password'   => Hash::make('customer@123'),
            'type'       => 'user',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
