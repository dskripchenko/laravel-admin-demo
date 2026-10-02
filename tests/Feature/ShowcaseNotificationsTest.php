<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;

class ShowcaseNotificationsTest extends DemoTestCase
{
    public function test_toasts_come_back_as_message_and_alerts(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-notifications-toasts/runMethod', ['method' => 'everyType', 'payload' => []])
            ->assertOk()
            ->assertJsonCount(4, 'payload.alerts');
        $this->postJson('/api/admin/showcase-notifications-toasts/runMethod', ['method' => 'messageWithLink', 'payload' => []])
            ->assertOk()
            ->assertJsonPath('payload.message_link.url', '/r/orders');
    }

    public function test_every_demo_account_starts_with_notifications(): void
    {
        $this->loginAs('editor');

        $this->getJson('/api/admin/notifications/unread')->assertOk()->assertJsonPath('payload.count', 3);
        $list = $this->getJson('/api/admin/notifications/list')->assertOk();
        $this->assertSame(['info', 'warning', 'success', 'error'], array_column(array_column($list->json('payload.data'), 'data'), 'level'));
    }

    public function test_a_notification_reaches_the_signed_in_user(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-notifications-centre/runMethod', [
            'method' => 'send',
            'payload' => ['level' => 'nope', 'title' => ''],
        ])->assertStatus(422);

        $before = DB::table('notifications')->count();
        $this->postJson('/api/admin/showcase-notifications-centre/runMethod', ['method' => 'sendEachLevel', 'payload' => []])
            ->assertOk();
        $this->assertSame($before + 4, DB::table('notifications')->count());
    }

    public function test_the_error_states_answer_as_documented(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-notifications-states/runMethod', [
            'method' => 'applyCoupon',
            'payload' => ['coupon' => 'SPRING', 'quantity' => 0],
        ])->assertStatus(422)->assertJsonStructure(['payload' => ['messages' => ['coupon', 'quantity']]]);

        $this->postJson('/api/admin/showcase-notifications-states/runMethod', ['method' => 'refuse', 'payload' => []])
            ->assertStatus(422)
            ->assertJsonPath('payload.errorKey', 'action_failed');

        $this->postJson('/api/admin/showcase-notifications-states/runMethod', ['method' => 'adminOnly', 'payload' => []])
            ->assertStatus(403)
            ->assertJsonPath('payload.errorKey', 'action_forbidden');

        $this->loginAs('admin');
        $this->postJson('/api/admin/showcase-notifications-states/runMethod', ['method' => 'adminOnly', 'payload' => []])
            ->assertOk();
    }
}
