<?php

namespace Tests\Feature;

use App\Models\Shop\Product;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * demo:reset rebuilds the database itself (migrate:fresh), so this test runs
 * outside the transaction RefreshDatabase would wrap it in.
 */
class DemoResetTest extends TestCase
{
    public function test_demo_reset_builds_the_stand_from_scratch(): void
    {
        Storage::fake('public');

        $this->artisan('demo:reset', ['--force' => true])->assertSuccessful();
        Product::query()->limit(5)->delete();
        $this->artisan('demo:reset', ['--force' => true])->assertSuccessful();

        $this->assertGreaterThan(200, Product::query()->count());
        $this->assertTrue(config('admin.auth.model')::query()->where('email', 'admin@demo.test')->exists());
        $this->assertNotEmpty(Storage::disk('public')->allFiles('media'));
    }
}
