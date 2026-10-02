<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * The whole demo stand: accounts and roles, the shop, the blog. Every run
 * produces the same data (a fixed Faker seed); dates are relative to today so
 * the dashboards always look current.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AccessSeeder::class,
            MediaSeeder::class,
            ShopSeeder::class,
            BlogSeeder::class,
            ActivitySeeder::class,
        ]);
    }
}
