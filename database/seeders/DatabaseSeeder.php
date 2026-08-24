<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the local development database with dummy data.
     * NOTE: This does NOT touch production data.
     */
    public function run()
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            SampleDataSeeder::class,
            DemoMultiCategoryProductSeeder::class,
        ]);
    }
}
