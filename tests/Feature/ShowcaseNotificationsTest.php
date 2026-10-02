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

    public function test_a_notification_reaches_the_signed_in_user(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-notifications-centre/runMethod', [
            'method' => 'send',
            'payload' => ['level' => 'nope', 'title' => ''],
        ])->assertStatus(422);

        $this->postJson('/api/admin/showcase-notifications-centre/runMethod', ['method' => 'sendEachLevel', 'payload' => []])
            ->assertOk();
        $this->assertSame(4, DB::table('notifications')->count());
    }

    public function test_the_error_states_answer_as_documented(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-notifications-states/runMethod', [
            'method' => 'applyCoupon',
            'payload' => ['coupon' => 'SPRING', 'quantity' => 0],
        ])->assertStatus(422)->assertJsonStructure(['payload' => ['messages' => ['coupon', 'quantity']]]);

        $this->postJson('/api/admin/showcase-notifications-states/runMethod', ['method' => 'refuse', 'payload' => []])
            ->assertStatus(422);

        $this->postJson('/api/admin/showcase-notifications-states/runMethod', ['method' => 'adminOnly', 'payload' => []])
            ->assertStatus(403)
            ->assertJsonPath('payload.errorKey', 'action_forbidden');

        $this->loginAs('admin');
        $this->postJson('/api/admin/showcase-notifications-states/runMethod', ['method' => 'adminOnly', 'payload' => []])
            ->assertOk();
    }
}
