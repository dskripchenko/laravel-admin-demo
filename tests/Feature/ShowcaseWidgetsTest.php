<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PostStatus;
use App\Models\Blog\Post;
use App\Models\Shop\Order;
use App\Support\DemoBoard;
use Dskripchenko\LaravelAdminBoard\Models\Board;
use Illuminate\Validation\ValidationException;

/**
 * The document layouts, the editor fields, the blocks, the kanban and
 * calendar views, the dashboard widgets and the collaborative board.
 */
class ShowcaseWidgetsTest extends DemoTestCase
{
    private function layoutOf(string $slug): string
    {
        return (string) json_encode($this->getJson("/api/admin/{$slug}/state")->assertOk()->json('payload.layout'));
    }

    private function switchTo(string $role): void
    {
        $this->postJson('/api/admin/auth/logout');
        $this->app['auth']->forgetGuards();
        $this->loginAs($role);
    }

    public function test_the_documents_screen_draws_every_notation_and_model(): void
    {
        $this->loginAs('viewer');

        $layout = $this->layoutOf('showcase-layouts-documents');
        foreach (['mermaid', 'plantuml', 'dot', 'd2', 'dbml', 'structurizr', 'wardley'] as $syntax) {
            $this->assertStringContainsString('"syntax":"'.$syntax.'"', $layout, $syntax);
        }
        foreach (['"type":"diagram"', '"type":"bpmn"', '"type":"dmn"', '"type":"drawio"', '"type":"asyncapi"', '"source":"process"', '"highlight":["Task_Pack"]'] as $needle) {
            $this->assertStringContainsString($needle, $layout);
        }

        $state = $this->getJson('/api/admin/showcase-layouts-documents/state')->json('payload.state');
        $this->assertStringContainsString('bpmn:definitions', $state['process']);
        $this->assertStringContainsString('Pay by invoice', $state['rules']);
        $this->assertStringContainsString('<mxfile', $state['architecture']);
        $this->assertStringStartsWith('asyncapi: 3.0.0', $state['events']);
    }

    public function test_the_kanban_and_calendar_are_saved_for_the_session_and_respect_the_lock(): void
    {
        $this->loginAs('editor');
        $slug = 'showcase-layouts-kanban-calendar';

        $layout = $this->layoutOf($slug);
        foreach (['"type":"kanban"', '"type":"calendar"', '"saveMethod":"saveBoard"', '"lockMethod":"lockSchedule"', '"lockedSource":"board_locked"', '"view":"month"'] as $needle) {
            $this->assertStringContainsString($needle, $layout);
        }

        $markup = "## To do\n- Write the release notes\n\n## Done";
        $this->postJson("/api/admin/{$slug}/runMethod", ['method' => 'saveBoard', 'parameters' => [$markup]])->assertOk();
        $this->assertSame($markup, $this->getJson("/api/admin/{$slug}/state")->json('payload.state.board'));

        $this->postJson("/api/admin/{$slug}/runMethod", ['method' => 'lockBoard', 'parameters' => [true]])->assertOk();
        $this->assertTrue($this->getJson("/api/admin/{$slug}/state")->json('payload.state.board_locked'));
        $this->postJson("/api/admin/{$slug}/runMethod", ['method' => 'saveBoard', 'parameters' => ["## Other\n"]])->assertStatus(422);
        $this->assertSame($markup, $this->getJson("/api/admin/{$slug}/state")->json('payload.state.board'));

        $this->postJson("/api/admin/{$slug}/runMethod", ['method' => 'startOver'])->assertOk();
        $this->assertStringContainsString('Black Friday launch', $this->getJson("/api/admin/{$slug}/state")->json('payload.state.board'));

        // A viewer gets a read-only board and may not call the methods.
        $this->switchTo('viewer');
        $this->assertStringContainsString('"editable":false', $this->layoutOf($slug));
        $this->postJson("/api/admin/{$slug}/runMethod", ['method' => 'saveBoard', 'parameters' => [$markup]])->assertForbidden();
    }

    public function test_the_editor_fields_validate_their_values(): void
    {
        $this->loginAs('viewer');
        $slug = 'showcase-forms-editors';

        $layout = $this->layoutOf($slug);
        foreach (['"type":"bpmn"', '"type":"dmn"', '"type":"kanban"', '"type":"calendar"', '"type":"markdown_editor"', '"type":"infolist"', '"type":"markdown"'] as $needle) {
            $this->assertStringContainsString($needle, $layout);
        }

        $state = $this->getJson("/api/admin/{$slug}/state")->json('payload.state');
        $this->postJson("/api/admin/{$slug}/runMethod", ['method' => 'submit', 'payload' => $state])
            ->assertOk()
            ->assertJsonPath('payload.message', fn (string $message) => str_contains($message, 'tasks in the process'));

        $this->postJson("/api/admin/{$slug}/runMethod", ['method' => 'submit', 'payload' => ['process' => '<notbpmn/>'] + $state])
            ->assertStatus(422);
        $this->postJson("/api/admin/{$slug}/runMethod", ['method' => 'submit', 'payload' => ['rules' => $state['process']] + $state])
            ->assertStatus(422);
    }

    public function test_the_new_block_languages_are_in_markdown_and_in_the_editor(): void
    {
        $this->loginAs('viewer');

        $blocks = $this->getJson('/api/admin/showcase-layouts-document-blocks/state')->assertOk();
        $layout = (string) json_encode($blocks->json('payload.layout'));
        foreach (['kanban', 'calendar', 'bpmn', 'dmn', 'drawio'] as $language) {
            $this->assertStringContainsString('```'.$language.'\n', $layout, $language);
            $this->assertStringContainsString('language-'.$language, $blocks->json('payload.state.note'), $language);
        }

        $diagrams = $this->layoutOf('showcase-layouts-diagrams');
        $editor = $this->getJson('/api/admin/showcase-layouts-diagrams-in-editor/state')->json('payload.state.document');
        foreach (['plantuml', 'dot', 'd2', 'dbml', 'structurizr', 'wardley'] as $language) {
            $this->assertStringContainsString('```'.$language.'\n', $diagrams, $language);
            $this->assertStringContainsString('language-'.$language, $editor, $language);
        }

        $this->assertStringContainsString('```asyncapi\n', $this->layoutOf('showcase-layouts-api-blocks'));
        $this->assertStringContainsString('language-asyncapi', $this->getJson('/api/admin/showcase-layouts-api-blocks/state')->json('payload.state.article'));
    }

    public function test_orders_have_kanban_and_calendar_views(): void
    {
        $this->loginAs('viewer');

        $meta = $this->getJson('/api/admin/orders/meta')->assertOk();
        $this->assertSame(['list', 'kanban', 'calendar'], $meta->json('payload.views'));
        $this->assertSame('status', $meta->json('payload.kanban.column'));
        $this->assertSame('placed_at', $meta->json('payload.calendar.start'));

        $this->postJson('/api/admin/orders/board', [])->assertOk();
        $this->postJson('/api/admin/orders/calendar', ['from' => now()->subMonth()->toDateString(), 'to' => now()->toDateString()])->assertOk();

        // A viewer may look, not move.
        $pending = Order::query()->where('status', OrderStatus::Pending->value)->firstOrFail();
        $this->postJson('/api/admin/orders/boardMove', ['id' => $pending->id, 'value' => 'paid'])->assertForbidden();
    }

    public function test_a_kanban_move_follows_the_order_flow(): void
    {
        $this->loginAs('admin');

        $pending = Order::query()->where('status', OrderStatus::Pending->value)->whereNull('paid_at')->firstOrFail();
        $this->postJson('/api/admin/orders/boardMove', ['id' => $pending->id, 'value' => 'delivered'])->assertStatus(422);
        $this->assertSame(OrderStatus::Pending, $pending->refresh()->status);

        $this->postJson('/api/admin/orders/boardMove', ['id' => $pending->id, 'value' => 'paid'])->assertOk();
        $pending->refresh();
        $this->assertSame(OrderStatus::Paid, $pending->status);
        $this->assertNotNull($pending->paid_at);

        $delivered = Order::query()->where('status', OrderStatus::Delivered->value)->firstOrFail();
        $this->postJson('/api/admin/orders/boardMove', ['id' => $delivered->id, 'value' => 'pending'])->assertStatus(422);
        $this->postJson('/api/admin/orders/boardMove', ['id' => $delivered->id, 'value' => 'refunded'])->assertOk();

        // A closed order stays in its column.
        $cancelled = Order::query()->where('status', OrderStatus::Cancelled->value)->firstOrFail();
        $this->postJson('/api/admin/orders/boardMove', ['id' => $cancelled->id, 'value' => 'paid'])->assertForbidden();
    }

    public function test_only_a_pending_order_moves_to_another_day(): void
    {
        $this->loginAs('admin');
        $day = now()->subDays(2)->setTime(12, 0)->format('Y-m-d H:i:s');

        $pending = Order::query()->where('status', OrderStatus::Pending->value)->firstOrFail();
        $this->postJson('/api/admin/orders/calendarMove', ['id' => $pending->id, 'start' => $day])->assertOk();
        $this->assertSame($day, $pending->refresh()->placed_at->format('Y-m-d H:i:s'));

        $paid = Order::query()->where('status', OrderStatus::Delivered->value)->firstOrFail();
        $this->postJson('/api/admin/orders/calendarMove', ['id' => $paid->id, 'start' => $day])->assertForbidden();
    }

    public function test_the_form_keeps_to_the_order_flow_too(): void
    {
        $this->loginAs('admin');
        $order = Order::query()->where('status', OrderStatus::Shipped->value)->firstOrFail();

        $this->assertThrows(fn () => $order->transitionTo(OrderStatus::Pending), ValidationException::class);
        $order->refresh()->transitionTo(OrderStatus::Delivered);
        $this->assertNotNull($order->refresh()->delivered_at);
    }

    public function test_the_grid_views_screen_opens_the_posts_on_their_board(): void
    {
        $this->loginAs('editor');

        $this->assertStringContainsString('"view":"kanban","resource":"showcase-grid-views"', $this->layoutOf('showcase-grids-views'));
        $this->postJson('/api/admin/showcase-grid-views/board', [])->assertOk();

        $draft = Post::query()->where('status', PostStatus::Draft->value)->firstOrFail();
        $this->postJson('/api/admin/showcase-grid-views/boardMove', ['id' => $draft->id, 'value' => 'review'])->assertOk();
        $this->assertSame(PostStatus::Review, $draft->refresh()->status);
    }

    public function test_the_dashboard_widgets_and_who_sees_them(): void
    {
        $this->loginAs('viewer');
        $layout = $this->layoutOf('showcase-dashboards-documents');
        foreach (['"type":"kanban"', '"type":"calendar"', '"type":"diagram"', '"type":"bpmn"', '"mode":"resource"', '"mode":"markup"', '"resource":"orders"'] as $needle) {
            $this->assertStringContainsString($needle, $layout);
        }

        // The editor may not view orders: the resource widgets are left out.
        $this->switchTo('editor');
        $layout = $this->layoutOf('showcase-dashboards-documents');
        $this->assertStringNotContainsString('"resource":"orders"', $layout);
        $this->assertStringContainsString('"type":"diagram"', $layout);
    }

    public function test_the_seeded_board_is_drawn_and_edited_by_those_who_may(): void
    {
        $board = Board::query()->where('title', DemoBoard::TITLE)->firstOrFail();
        $this->assertGreaterThan(5, count(json_decode($board->board, true)['objects']));

        $this->loginAs('editor');
        $layout = $this->layoutOf('showcase-layouts-board');
        $this->assertStringContainsString('"type":"board"', $layout);
        $this->assertStringContainsString('"endpoint":"\/boards\/'.$board->id.'"', $layout);
        $this->assertStringContainsString('"readonly":false', $layout);

        $snapshot = $this->postJson("/api/admin/boards/{$board->id}/snapshot")->assertOk();
        $this->assertArrayHasKey('ideas', (array) $snapshot->json('payload.board.objects'));
        $this->postJson("/api/admin/boards/{$board->id}/batches", ['client' => 'test-editor', 'cseq' => 1, 'ops' => [
            ['k' => 'create', 'id' => 'new1', 'obj' => ['id' => 'new1', 'type' => 'sticky', 'x' => 10, 'y' => 500, 'w' => 160, 'h' => 160, 'z' => 'b0', 'color' => 'yellow', 'text' => 'Hi']],
        ]])->assertOk();

        $this->switchTo('viewer');
        $this->assertStringContainsString('"readonly":true', $this->layoutOf('showcase-layouts-board'));
        $this->postJson("/api/admin/boards/{$board->id}/batches", ['client' => 'test-viewer', 'cseq' => 1, 'ops' => []])->assertForbidden();
        $this->getJson('/api/admin/boards/meta')->assertOk();
    }

    public function test_the_board_page_brings_the_board_back_after_a_delete(): void
    {
        Board::query()->delete();
        $this->loginAs('viewer');

        $this->assertStringContainsString('"type":"board"', $this->layoutOf('showcase-layouts-board'));
        $this->assertSame(1, Board::query()->where('title', DemoBoard::TITLE)->count());
    }
}
