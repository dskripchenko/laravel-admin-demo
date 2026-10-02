<?php

namespace Database\Seeders;

use Dskripchenko\LaravelAdmin\Notifications\AdminNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * A few notifications for every demo account, so the bell in the top bar has
 * something to show from the first visit: one per level, the oldest already
 * read. The links lead to pages every demo role can open.
 */
class NotificationSeeder extends Seeder
{
    /** @var list<array{level: string, title: string, body: string, url: string, icon: string, minutes: int, read: bool}> */
    public const NOTES = [
        ['level' => 'info', 'title' => 'Welcome to the demo', 'body' => 'Everything you change here is reset every hour.', 'url' => '/screens/showcase', 'icon' => 'sparkles', 'minutes' => 4, 'read' => false],
        ['level' => 'warning', 'title' => 'Low stock', 'body' => '5 products are running out.', 'url' => '/r/products', 'icon' => 'package', 'minutes' => 35, 'read' => false],
        ['level' => 'success', 'title' => 'Post published', 'body' => '"Spring collection" is live on the blog.', 'url' => '/r/posts', 'icon' => 'check-circle', 'minutes' => 90, 'read' => false],
        ['level' => 'error', 'title' => 'Payment failed', 'body' => 'ORD-10042: the card was declined.', 'url' => '/r/products', 'icon' => 'credit-card', 'minutes' => 300, 'read' => true],
    ];

    public function run(): void
    {
        /** @var class-string<Model> $model */
        $model = config('admin.auth.model');
        $emails = array_column((array) config('demo.accounts'), 'email');

        foreach ($model::query()->whereIn('email', $emails)->get() as $user) {
            foreach (self::NOTES as $note) {
                $notification = new AdminNotification($note['title'], $note['body'], $note['level'], $note['url'], $note['icon']);
                $at = now()->subMinutes($note['minutes']);

                // Written straight to the database channel's table, to give
                // each one its own age; a real app calls $user->notify().
                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => AdminNotification::class,
                    'data' => $notification->toArray($user),
                    'read_at' => $note['read'] ? $at : null,
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);
            }
        }
    }
}
