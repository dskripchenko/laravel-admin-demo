<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * The default seeder — it delegates to DemoSeeder.
     *
     * Run it with `php artisan db:seed` or `php artisan migrate --seed`.
     */
    public function run(): void
    {
        $this->call(DemoSeeder::class);
    }
}
