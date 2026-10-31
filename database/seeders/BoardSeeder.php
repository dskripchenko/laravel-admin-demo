<?php

namespace Database\Seeders;

use App\Support\DemoBoard;
use Illuminate\Database\Seeder;

/**
 * The demo's collaborative board, with a few frames, stickers and
 * connectors drawn on it (dskripchenko/laravel-admin-board).
 */
class BoardSeeder extends Seeder
{
    public function run(): void
    {
        DemoBoard::create();
    }
}
