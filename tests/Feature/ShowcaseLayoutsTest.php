<?php

namespace Tests\Feature;

use App\Admin\Showcase\Layouts\LayoutsGroup;

class ShowcaseLayoutsTest extends DemoTestCase
{
    public function test_every_layouts_screen_renders_for_the_viewer(): void
    {
        $this->loginAs('viewer');

        foreach (LayoutsGroup::screens() as $screen) {
            $this->getJson('/api/admin/'.$screen::slug().'/state')->assertOk();
        }
    }

    public function test_the_modals_screen_serializes_its_overlays_and_validates(): void
    {
        $this->loginAs('viewer');

        $layout = json_encode($this->getJson('/api/admin/showcase-layouts-modals/state')->json('payload.layout'));
        foreach (['"type":"modal"', '"type":"drawer"', '"id":"profile-modal"', '"id":"log-drawer"', '"dismissable":false'] as $needle) {
            $this->assertStringContainsString($needle, $layout);
        }

        $this->postJson('/api/admin/showcase-layouts-modals/runMethod', ['method' => 'acceptTerms', 'payload' => ['terms' => false]])
            ->assertStatus(422);
        $this->postJson('/api/admin/showcase-layouts-modals/runMethod', ['method' => 'acceptTerms', 'payload' => ['terms' => true]])
            ->assertOk();
    }

    public function test_the_wizard_submits_to_its_method(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-layouts-wizard/runMethod', ['method' => 'finishSignup', 'payload' => ['email' => 'nope']])
            ->assertStatus(422);
        $this->postJson('/api/admin/showcase-layouts-wizard/runMethod', ['method' => 'finishSignup', 'payload' => [
            'email' => 'ada@example.com', 'password' => 'correct-horse', 'company' => 'Acme', 'plan' => 'team', 'starts_on' => '2030-01-01',
        ]])->assertOk()->assertJsonPath('payload.message', fn (string $m) => str_contains($m, 'Acme'));
    }

    public function test_the_order_managers_block_is_left_out_for_the_editor(): void
    {
        $this->loginAs('editor');
        $this->assertStringNotContainsString('"name":"cost"', json_encode($this->getJson('/api/admin/showcase-layouts-rows/state')->json('payload.layout')));
    }
}
