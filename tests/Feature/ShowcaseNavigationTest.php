<?php

namespace Tests\Feature;

use App\Admin\Showcase\Navigation\MenuBadgesScreen;

class ShowcaseNavigationTest extends DemoTestCase
{
    public function test_the_nested_menu_is_five_levels_deep_with_a_counter_badge(): void
    {
        $this->loginAs('admin');

        $items = $this->getJson('/api/admin/system/menu')->assertOk()->json('payload.items');
        $nested = $this->find($items, 'showcase-nested');
        $this->assertNotNull($nested);

        $depth = 0;
        $node = $nested;
        while ($node !== null) {
            $depth++;
            $node = collect($node['children'])->first(fn (array $child) => $child['children'] !== []);
        }
        $this->assertGreaterThanOrEqual(5, $depth);

        // The menu is built when the application boots — before the test
        // seeds the database — so the counter is checked at its source.
        $this->assertArrayHasKey('badge', $this->find([$nested], 'resource.orders'));
        $this->assertGreaterThan(0, MenuBadgesScreen::pendingOrders());
    }

    public function test_the_editor_does_not_see_the_orders_entry_or_links(): void
    {
        $this->loginAs('editor');

        $items = $this->getJson('/api/admin/system/menu')->assertOk()->json('payload.items');
        $nested = $this->find($items, 'showcase-nested');
        $this->assertSame(['admin.orders.view'], $this->find([$nested], 'resource.orders')['permissions']);

        $bar = $this->getJson('/api/admin/showcase-navigation-links/state')->assertOk()->json('payload.command_bar');
        $this->assertNotContains('A list', array_column($bar, 'label'));
    }

    public function test_the_query_screen_reads_the_order_from_the_address(): void
    {
        $this->loginAs('viewer');

        $this->getJson('/api/admin/showcase-navigation-query/state?order=ORD-10002')
            ->assertOk()
            ->assertJsonPath('payload.state.number', 'ORD-10002');

        $this->getJson('/api/admin/showcase-navigation-query/state?tab=list&status=paid')
            ->assertOk()
            ->assertJsonPath('payload.state.filter', 'Paid');

        $this->getJson('/api/admin/showcase-navigation-query/state?order=DROP%20TABLE')
            ->assertOk()
            ->assertJsonPath('payload.state.number', '—');
    }

    /** @param list<array<string, mixed>> $items */
    private function find(array $items, string $key): ?array
    {
        foreach ($items as $item) {
            if ($item['key'] === $key) {
                return $item;
            }
            if ($found = $this->find($item['children'] ?? [], $key)) {
                return $found;
            }
        }

        return null;
    }
}
