<?php

namespace Tests\Feature;

use App\Admin\Showcase\Forms\FormsGroup;
use App\Models\Shop\Category;
use App\Models\Shop\Product;

class ShowcaseFormsTest extends DemoTestCase
{
    public function test_every_forms_screen_opens_for_each_role(): void
    {
        foreach (['viewer', 'editor', 'admin'] as $role) {
            $this->loginAs($role);
            foreach (FormsGroup::screens() as $screen) {
                $this->getJson('/api/admin/'.$screen::slug().'/state')->assertOk();
            }
            $this->postJson('/api/admin/auth/logout');
        }
    }

    public function test_the_validation_screen_reports_every_field_then_the_server_only_rule(): void
    {
        $this->loginAs('viewer');
        $state = $this->getJson('/api/admin/showcase-forms-validation/state')->json('payload.state');

        $errors = $this->postJson('/api/admin/showcase-forms-validation/runMethod', ['method' => 'check', 'payload' => $state])
            ->assertStatus(422)->json('payload.messages');
        foreach (['email', 'age', 'team_size', 'country', 'website', 'password'] as $field) {
            $this->assertArrayHasKey($field, (array) $errors, $field);
        }

        $valid = [
            'username' => 'admin', 'email' => 'ada@example.com', 'age' => 30, 'team_size' => 3, 'country' => 'gb',
            'website' => 'https://example.com', 'joined_on' => '2024-01-01',
            'password' => 'long-enough', 'password_confirmation' => 'long-enough',
        ];
        $this->postJson('/api/admin/showcase-forms-validation/runMethod', ['method' => 'check', 'payload' => $valid])
            ->assertStatus(422);
        $this->postJson('/api/admin/showcase-forms-validation/runMethod', ['method' => 'check', 'payload' => ['username' => 'ada'] + $valid])
            ->assertOk();
    }

    public function test_the_dependent_listeners_render_products_and_compute_the_total(): void
    {
        $this->loginAs('viewer');
        $layout = $this->getJson('/api/admin/showcase-forms-dependent/state')->json('payload.layout');
        $listeners = [];
        $walk = function (array $node) use (&$walk, &$listeners): void {
            if (($node['type'] ?? null) === 'listener') {
                $listeners[] = $node['id'];
            }
            foreach ($node['items'] ?? [] as $child) {
                if (is_array($child)) {
                    $walk($child);
                }
            }
        };
        foreach ($layout as $node) {
            $walk($node);
        }
        $this->assertCount(2, $listeners);

        $product = Product::query()->whereNotNull('category_id')->firstOrFail();
        $response = $this->postJson('/api/admin/showcase-forms-dependent/listener', [
            'listener' => $listeners[1],
            'state' => ['product_id' => $product->id, 'quantity' => 3],
        ])->assertOk();
        $this->assertEqualsWithDelta(round((float) $product->price * 3, 2), $response->json('payload.state.total'), 0.001);

        $this->postJson('/api/admin/showcase-forms-dependent/listener', [
            'listener' => $listeners[0],
            'state' => ['category_id' => Category::query()->has('products')->value('id')],
        ])->assertOk();
    }

    public function test_the_text_inputs_screen_checks_the_password_confirmation(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-forms-text/runMethod', [
            'method' => 'submit',
            'payload' => ['title' => 'Hello', 'password' => 'long-enough', 'password_confirmation' => 'other'],
        ])->assertStatus(422);

        $this->postJson('/api/admin/showcase-forms-text/runMethod', [
            'method' => 'submit',
            'payload' => ['title' => 'Hello', 'slug' => 'hello', 'password' => 'long-enough', 'password_confirmation' => 'long-enough'],
        ])->assertOk();
    }
}
