<?php

namespace App\Admin\Showcase\Notifications;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Action\DropDown;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Notifications › Toasts and banners: what a screen method returns —
 * `message`, `message_link` and `alerts` — and how each shows up.
 */
final class ToastsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-notifications-toasts';
    }

    public static function group(): string
    {
        return 'notifications';
    }

    public static function icon(): string
    {
        return 'message-square';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Toasts';
    }

    public function description(): ?string
    {
        return 'message, message_link and alerts of a screen method.';
    }

    protected function demo(): array
    {
        return [
            Layout::markdown(__(<<<'MD'
Press the buttons above. A command method answers with an array, and the panel turns some of its keys into toasts:

| Key | Shows |
|---|---|
| `message` | A success banner above the page, until the next action |
| `message_link` | A link inside that banner, `['url' => '/r/orders', 'label' => 'Open the orders']` — to the page of a started job, say |
| `alerts` | A toast per entry: `type` is `info`, `success`, `warning` or `danger`; `title` and `duration_ms` are optional |

An error answer — a validation error, a refusal — shows as an error toast, and field errors under their fields too; see "Error states".

Texts go through the translator of the request's locale, so English source strings with a `lang/ru.json` are enough.
MD))->card(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Message')->method('message')->primary()->icon('check'),
            Button::make('Message with a link')->method('messageWithLink')->icon('link'),
            Button::make('Every alert type')->method('everyType')->icon('bell'),
            DropDown::make('Duration')->icon('clock')->items([
                Button::make('Short, 2 seconds')->method('shortAlert'),
                Button::make('Long, 15 seconds')->method('longAlert'),
            ]),
        ];
    }

    public function message(): array
    {
        return ['message' => __('Saved. This banner is the message key.')];
    }

    public function messageWithLink(): array
    {
        return [
            'message' => __('The report is ready.'),
            // A panel path (without the /admin prefix) opens through the router;
            // an absolute URL opens in a new tab.
            'message_link' => ['url' => '/r/orders', 'label' => __('Open the orders')],
        ];
    }

    public function everyType(): array
    {
        return [
            'alerts' => [
                ['type' => 'info', 'title' => __('Info'), 'message' => __('The import starts in a minute.')],
                ['type' => 'success', 'title' => __('Success'), 'message' => __('42 products published.')],
                ['type' => 'warning', 'title' => __('Warning'), 'message' => __('3 products have no price.')],
                ['type' => 'danger', 'title' => __('Error'), 'message' => __('The payment gateway did not answer.')],
            ],
        ];
    }

    public function shortAlert(): array
    {
        return ['alerts' => [['type' => 'info', 'message' => __('Gone in two seconds.'), 'duration_ms' => 2000]]];
    }

    public function longAlert(): array
    {
        return ['alerts' => [['type' => 'warning', 'message' => __('This one stays for fifteen seconds.'), 'duration_ms' => 15000]]];
    }
}
