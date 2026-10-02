<?php

namespace Tests\Feature;

use App\Admin\Showcase\Dashboards\DashboardsGroup;
use App\Admin\Showcase\Dashboards\ShowcaseDashboard;

class ShowcaseDashboardsTest extends DemoTestCase
{
    public function test_every_dashboards_screen_renders_its_widgets(): void
    {
        $this->loginAs('viewer');

        foreach (DashboardsGroup::screens() as $screen) {
            $layout = $this->getJson('/api/admin/'.$screen::slug().'/state')->assertOk()->json('payload.layout');
            $this->assertNotEmpty($layout, $screen);
        }
    }

    public function test_the_showcase_dashboard_serves_every_period(): void
    {
        $this->loginAs('viewer');
        $key = ShowcaseDashboard::slug();

        $this->getJson("/api/admin/dashboard/get?key={$key}")->assertOk();
        foreach (['7d', '30d', '90d', 'all'] as $period) {
            $this->getJson("/api/admin/dashboard/widgets?key={$key}&period={$period}")->assertOk();
        }
    }

    public function test_the_permission_gated_widget_is_hidden_from_the_editor(): void
    {
        $key = ShowcaseDashboard::slug();

        $this->loginAs('viewer');
        $this->assertStringContainsString('customer-segments', $this->getJson("/api/admin/dashboard/widgets?key={$key}&period=30d")->assertOk()->getContent());

        $this->postJson('/api/admin/auth/logout');
        $this->app['auth']->forgetGuards();
        $this->loginAs('editor');
        $this->assertStringNotContainsString('customer-segments', $this->getJson("/api/admin/dashboard/widgets?key={$key}&period=30d")->assertOk()->getContent());
    }
}
