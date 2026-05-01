<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Default seeder — делегирует в DemoSeeder.
     *
     * Запуск: `php artisan db:seed` или `php artisan migrate --seed`.
     */
    public function run(): void
    {
        $this->call(DemoSeeder::class);
    }
}
