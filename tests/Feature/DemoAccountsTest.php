<?php

namespace Tests\Feature;

use App\Models\Shop\Product;

class DemoAccountsTest extends DemoTestCase
{
    public function test_the_login_page_offers_the_three_demo_accounts(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('admin@demo.test')
            ->assertSee('editor@demo.test')
            ->assertSee('viewer@demo.test')
            ->assertSee('Demo data resets every hour');
    }

    public function test_every_demo_account_signs_in(): void
    {
        foreach (['admin', 'editor', 'viewer'] as $role) {
            $this->loginAs($role);
            $this->getJson('/api/admin/system/me')->assertOk()->assertJsonPath('payload.email', "{$role}@demo.test");
            $this->postJson('/api/admin/auth/logout')->assertOk();
        }
    }

    public function test_the_editor_manages_content_but_not_orders_or_users(): void
    {
        $this->loginAs('editor');

        $this->postJson('/api/admin/products/search', [])->assertOk();
        $this->postJson('/api/admin/posts/search', [])->assertOk();
        $this->postJson('/api/admin/orders/search', [])->assertForbidden();
        $this->postJson('/api/admin/customers/search', [])->assertForbidden();
        $this->postJson('/api/admin/system-users/search', [])->assertForbidden();
    }

    public function test_the_viewer_reads_everything_and_changes_nothing(): void
    {
        $this->loginAs('viewer');
        $product = Product::query()->firstOrFail();

        $this->postJson('/api/admin/orders/search', [])->assertOk();
        $this->postJson('/api/admin/system-users/search', [])->assertOk();
        $this->postJson('/api/admin/products/update', ['id' => $product->id, 'name' => 'Renamed'])->assertForbidden();
        $this->assertNotSame('Renamed', $product->fresh()->name);
    }

    public function test_the_readonly_guard_protects_users_even_from_the_administrator(): void
    {
        $this->loginAs('admin');
        $user = config('admin.auth.model')::query()->where('email', 'viewer@demo.test')->firstOrFail();

        $this->postJson('/api/admin/system-users/delete', ['id' => $user->id])
            ->assertForbidden()
            ->assertJsonPath('payload.errorKey', 'demo_readonly');
        $this->postJson('/api/admin/profile/changePassword', ['current_password' => 'demo', 'password' => 'x12345678', 'password_confirmation' => 'x12345678'])
            ->assertForbidden();
        $this->assertNotNull($user->fresh());
    }

    public function test_the_administrator_still_edits_demo_records(): void
    {
        $this->loginAs('admin');
        $product = Product::query()->firstOrFail();

        $this->postJson('/api/admin/products/inlineUpdate', ['id' => $product->id, 'column' => 'stock', 'value' => '7'])->assertOk();
        $this->assertSame(7, $product->fresh()->stock);
    }
}
