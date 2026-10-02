<?php

namespace Tests\Feature;

use App\Models\Blog\Post;
use App\Models\Shop\Order;
use App\Models\Shop\Product;

class StandTest extends DemoTestCase
{
    public function test_the_landing_page_speaks_english_and_russian(): void
    {
        $this->get('/?lang=en')->assertOk()->assertSee('Open live demo')->assertSee('/admin/login', false);
        $this->get('/?lang=ru')->assertOk()->assertSee('Открыть демо');
        $this->get('/', ['Accept-Language' => 'ru-RU,ru;q=0.9'])->assertOk()->assertSee('lang="ru"', false);
    }

    public function test_the_seed_is_large_enough_to_make_the_grids_interesting(): void
    {
        $this->assertGreaterThan(200, Product::query()->count());
        $this->assertGreaterThan(1000, Order::query()->count());
        $this->assertGreaterThan(100, Post::query()->count());
    }

    public function test_the_shop_dashboard_loads_for_every_period(): void
    {
        $this->loginAs('viewer');

        $this->getJson('/api/admin/dashboard/get?key=main')->assertOk();
        foreach (['7d', '30d', '90d', 'all'] as $period) {
            $this->getJson("/api/admin/dashboard/widgets?key=main&period={$period}")->assertOk();
        }
    }

    public function test_the_reset_is_scheduled(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('demo:reset')->assertSuccessful();
    }
}
