<?php

namespace Tests\Feature;

use App\Admin\Showcase\Actions\ActionsGroup;
use App\Admin\Showcase\Actions\ReportBuilder;
use App\Admin\Showcase\Actions\SampleCsv;
use Dskripchenko\DelayedProcess\Contracts\ProcessRunnerInterface;
use Dskripchenko\DelayedProcess\Models\DelayedProcess;

class ShowcaseActionsTest extends DemoTestCase
{
    public function test_every_actions_screen_renders_for_the_viewer(): void
    {
        $this->loginAs('viewer');

        foreach (ActionsGroup::screens() as $screen) {
            $this->getJson('/api/admin/'.$screen::slug().'/state')->assertOk();
        }
    }

    public function test_the_responses_screen_answers_with_every_key(): void
    {
        $this->loginAs('viewer');
        $run = fn (string $method) => $this->postJson('/api/admin/showcase-actions-responses/runMethod', ['method' => $method, 'payload' => []])->assertOk();

        $run('messageWithLink')->assertJsonPath('payload.message_link.url', '/screens/showcase');
        $run('messageWithListLink')->assertJsonPath('payload.message_link.url', '/r/orders');
        $run('messageWithBareLink')->assertJsonPath('payload.message_link', ['url' => '/r/products', 'label' => 'Open']);
        $this->assertCount(4, $run('alerts')->json('payload.alerts'));
        $this->assertMatchesRegularExpression('/^[1-6]$/', $run('rollDice')->json('payload.state.dice'));
        $run('reload')->assertJsonPath('payload.refresh', true);
        $run('redirect')->assertJsonPath('payload.redirect_url', '/admin/screens/showcase-actions-buttons');

        $this->postJson('/api/admin/showcase-actions-responses/runMethod', ['method' => 'refuse', 'payload' => []])
            ->assertStatus(422)->assertJsonPath('payload.errorKey', 'action_failed');

        $url = $run('download')->json('payload.download_url');
        $this->get($url)->assertOk()->assertHeader('content-type', 'text/csv; charset=utf-8');
        $this->get(route('showcase.sample-csv'))->assertForbidden();
        $this->assertStringContainsString('signature=', SampleCsv::url());
    }

    public function test_the_modal_form_is_validated_on_the_server(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-actions-modal-forms/runMethod', ['method' => 'invite', 'payload' => ['invite_email' => 'nope', 'invite_role' => 'viewer']])
            ->assertStatus(422)->assertJsonStructure(['payload' => ['messages' => ['invite_email']]]);
        $this->postJson('/api/admin/showcase-actions-modal-forms/runMethod', ['method' => 'invite', 'payload' => ['invite_email' => 'ada@example.com', 'invite_role' => 'viewer']])
            ->assertOk();
    }

    public function test_gated_buttons_are_hidden_and_refused(): void
    {
        $this->loginAs('viewer');
        $bar = collect($this->getJson('/api/admin/showcase-actions-permissions/state')->json('payload.command_bar'))->pluck('attributes.method');
        $this->assertContains('viewProducts', $bar);
        $this->assertNotContains('publishProducts', $bar);

        $this->postJson('/api/admin/showcase-actions-permissions/runMethod', ['method' => 'publishProducts', 'payload' => []])
            ->assertForbidden()->assertJsonPath('payload.errorKey', 'action_forbidden');
        $this->postJson('/api/admin/showcase-actions-permissions/runMethod', ['method' => 'viewProducts', 'payload' => []])->assertOk();

        $this->postJson('/api/admin/auth/logout');
        $this->loginAs('admin');
        $this->postJson('/api/admin/showcase-actions-permissions/runMethod', ['method' => 'publishProducts', 'payload' => []])->assertOk();
        $this->postJson('/api/admin/showcase-actions-permissions/runMethod', ['method' => 'editRoles', 'payload' => []])->assertOk();
    }

    public function test_the_background_handler_is_allowlisted(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/delayed/run', ['entity' => SampleCsv::class, 'method' => '__invoke'])->assertForbidden();

        $uuid = $this->postJson('/api/admin/delayed/run', [
            'entity' => ReportBuilder::class,
            'method' => 'build',
            'params' => [150, 'csv'],
        ])->assertOk()->json('payload.uuid');

        // Run the process here, the way the queue worker would.
        app(ProcessRunnerInterface::class)->run(DelayedProcess::query()->where('uuid', $uuid)->firstOrFail());

        $status = $this->getJson('/api/admin/delayed/status?uuid='.$uuid)->assertOk();
        $status->assertJsonPath('payload.status', 'done')->assertJsonPath('payload.progress', 100);
        $this->assertSame(150, $status->json('payload.data.rows'));
        $this->assertSame(50, app(ReportBuilder::class)->build(50)['rows']);
    }
}
