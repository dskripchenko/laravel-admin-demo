<?php

namespace App\Admin\Showcase\Actions;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Action\DropDown;
use Dskripchenko\LaravelAdmin\Field\Label;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Actions › Responses: what a screen method may answer. The array it returns
 * is normalized into one payload: message, message_link, alerts, state,
 * refresh, redirect_url and download_url.
 */
final class ResponsesScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-actions-responses';
    }

    public static function group(): string
    {
        return 'actions';
    }

    public static function icon(): string
    {
        return 'send';
    }

    public function name(): string
    {
        return 'Responses';
    }

    public function description(): ?string
    {
        return 'Messages, alerts, new state, refresh, redirect and download — from the return value.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'rendered_at' => now()->format('H:i:s'),
            'dice' => '—',
            'lucky' => '—',
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('State', [
                    Label::make('rendered_at')->title('Rendered by query() at'),
                    Label::make('dice')->title('Dice'),
                    Label::make('lucky')->title('Lucky number'),
                ])->description(__('“Roll the dice” replaces these values; “Refresh” runs query() again.')),
                Layout::markdown(implode("\n", [
                    '| '.__('Key').' | '.__('Effect').' |',
                    '|---|---|',
                    '| `message` | '.__('A success bar above the screen').' |',
                    '| `message_link` | '.__('A link inside that bar').' |',
                    '| `alerts` | '.__('Toasts: info, success, warning, danger').' |',
                    '| `state` | '.__('New values of the form').' |',
                    '| `refresh` | '.__('Reloads the screen through query()').' |',
                    '| `redirect_url` | '.__('Navigates to another page').' |',
                    '| `download_url` | '.__('Downloads a file').' |',
                ]))->card(),
            ]),
        ];
    }

    public function commandBar(): array
    {
        return [
            DropDown::make('Messages')->icon('message-circle')->items([
                Button::make('Message')->method('message'),
                Button::make('Message with a link')->method('messageWithLink'),
                Button::make('Alerts')->method('alerts'),
            ]),
            Button::make('Roll the dice')->method('rollDice')->icon('refresh-cw'),
            Button::make('Refresh')->method('reload')->icon('rotate-ccw'),
            Button::make('Redirect')->method('redirect')->icon('link'),
            Button::make('Download')->method('download')->icon('download')->primary(),
        ];
    }

    public function message(): array
    {
        return ['message' => __('Done. This is the message of the response.')];
    }

    public function messageWithLink(): array
    {
        return [
            'message' => __('The report is ready.'),
            'message_link' => ['url' => '/admin/screens/showcase', 'label' => __('Open the showcase')],
        ];
    }

    public function alerts(): array
    {
        return ['alerts' => [
            ['type' => 'info', 'title' => __('Info'), 'message' => __('A neutral note.')],
            ['type' => 'success', 'title' => __('Success'), 'message' => __('Everything went well.')],
            ['type' => 'warning', 'title' => __('Warning'), 'message' => __('Something needs a look.'), 'duration_ms' => 8000],
            ['type' => 'danger', 'title' => __('Error'), 'message' => __('Something went wrong.')],
        ]];
    }

    /** @param array<string, mixed> $state */
    public function rollDice(array $state): array
    {
        return ['state' => [
            ...$state,
            'dice' => (string) random_int(1, 6),
            'lucky' => (string) random_int(1, 100),
        ]];
    }

    public function reload(): array
    {
        return ['message' => __('Reloaded.'), 'refresh' => true];
    }

    public function redirect(): array
    {
        return ['redirect_url' => '/admin/screens/showcase-actions-buttons'];
    }

    public function download(): array
    {
        return ['download_url' => SampleCsv::url()];
    }
}
