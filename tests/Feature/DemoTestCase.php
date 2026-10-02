<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * A seeded stand: the demo accounts, the shop and the blog. The media seeder
 * writes to a fake public disk.
 */
abstract class DemoTestCase extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function beforeRefreshingDatabase(): void
    {
        Storage::fake('public');
    }

    /** Signs in through the API, the way the demo buttons do. */
    protected function loginAs(string $role): array
    {
        $response = $this->postJson('/api/admin/auth/login', [
            'email' => "{$role}@demo.test",
            'password' => 'demo',
        ])->assertOk();

        return (array) $response->json('payload.permissions');
    }
}
