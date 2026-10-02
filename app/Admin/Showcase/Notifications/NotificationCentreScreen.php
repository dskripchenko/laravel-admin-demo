<?php

namespace App\Admin\Showcase\Notifications;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Radio;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Notifications\AdminNotification;

/**
 * Notifications › Notification centre: an AdminNotification sent to the
 * signed-in user lands under the bell in the top bar and on the
 * notifications page.
 */
final class NotificationCentreScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-notifications-centre';
    }

    public static function group(): string
    {
        return 'notifications';
    }

    public static function icon(): string
    {
        return 'inbox';
    }

    public function name(): string
    {
        return 'Notification centre';
    }

    public function description(): ?string
    {
        return 'AdminNotification through Laravel notifications: the bell and the notifications page.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'level' => 'success',
            'title' => 'The import has finished',
            'body' => '1,234 products imported, 3 skipped.',
            'url' => '/r/products',
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('Compose', [
                    Layout::rows([
                        Radio::make('level')->options([
                            'info' => 'Info',
                            'success' => 'Success',
                            'warning' => 'Warning',
                            'error' => 'Error',
                        ])->inline(),
                        Input::make('title')->required()->rules(['max:120']),
                        Textarea::make('body')->rows(2),
                        Input::make('url')->title('Opens')->help('A panel path, e.g. /r/orders'),
                    ]),
                ])->icon('send')->description('Press "Send to me", then open the bell in the top bar.'),
                Layout::markdown(__(<<<'MD'
`AdminNotification` is an ordinary Laravel notification on the `database` channel:

- `title`, `body`, a `level` of `info`, `success`, `warning` or `error`, an optional `url` and `icon`;
- send it with `notify()` from anywhere — a job, an event listener, a command;
- the bell polls the unread count; the drawer and the notifications page list them, mark them read and delete them.

The class implements `ShouldQueue`, so `notify()` goes through the queue; this page uses `notifyNow()` to deliver at once. The notifications are yours only — other visitors of the demo do not see them.
MD))->card(),
            ])->ratios([1, 1]),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Send to me')->method('send')->primary()->icon('send'),
            Button::make('One of each level')->method('sendEachLevel')->icon('bell'),
        ];
    }

    /** @param array<string, mixed> $state */
    public function send(array $state): array
    {
        $data = validator($state, [
            'level' => 'required|in:'.implode(',', AdminNotification::LEVELS),
            'title' => 'required|string|max:120',
            'body' => 'nullable|string|max:500',
            'url' => ['nullable', 'string', 'max:200', 'regex:#^/[^/]#'],
        ])->validate();

        $this->user()->notifyNow(new AdminNotification(
            title: $data['title'],
            body: (string) ($data['body'] ?? ''),
            level: $data['level'],
            url: $data['url'] ?? null,
        ));

        return ['message' => __('Sent. Look at the bell in the top bar.')];
    }

    public function sendEachLevel(): array
    {
        $notes = [
            ['info', 'A new comment', 'Ada left a comment on "Spring collection".', '/r/posts', 'message-circle'],
            ['success', 'Export ready', 'orders.csv — 1,700 rows.', '/r/orders', 'download'],
            ['warning', 'Low stock', '5 products are running out.', '/r/products', 'package'],
            ['error', 'Payment failed', 'ORD-10042: the card was declined.', '/r/orders', 'credit-card'],
        ];
        foreach ($notes as [$level, $title, $body, $url, $icon]) {
            $this->user()->notifyNow(new AdminNotification(__($title), __($body), $level, $url, $icon));
        }

        return ['message' => __('Four notifications sent. Look at the bell in the top bar.')];
    }

    private function user(): mixed
    {
        return request()->user((string) config('admin.auth.guard', 'admin'));
    }
}
