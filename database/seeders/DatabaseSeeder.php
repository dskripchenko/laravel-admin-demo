<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * The whole demo stand: accounts and roles, the shop, the blog, a few
 * notifications, and the last week of the back office behind the System
 * pages. Every run produces the same data (a fixed Faker seed); dates are
 * relative to today so the dashboards always look current.
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
            NotificationSeeder::class,
            SystemSeeder::class,
            TelemetrySeeder::class,
        ]);
    }
}
