<?php

namespace Tests\Feature;

use App\Admin\Showcase\Grids\GridsGroup;
use App\Enums\OrderStatus;
use App\Enums\PostStatus;
use App\Models\Blog\Author;
use App\Models\Blog\Post;
use App\Models\Shop\Order;

class ShowcaseGridsTest extends DemoTestCase
{
    public function test_every_grid_example_opens_for_a_viewer(): void
    {
        $this->loginAs('viewer');

        foreach (GridsGroup::screens() as $screen) {
            $this->getJson('/api/admin/'.$screen::slug().'/state')->assertOk();
        }
    }

    public function test_every_grid_resource_lists_its_records(): void
    {
        $this->loginAs('admin');

        foreach (GridsGroup::resources() as $resource) {
            $this->getJson('/api/admin/'.$resource::slug().'/meta')->assertOk();
            $this->postJson('/api/admin/'.$resource::slug().'/search', [])->assertOk();
        }
    }

    public function test_a_query_filter_narrows_the_orders(): void
    {
        $this->loginAs('admin');

        $all = $this->postJson('/api/admin/showcase-grid-filters/search', [])->assertOk()->json('payload.meta.total');
        $discounted = $this->postJson('/api/admin/showcase-grid-filters/search', ['filters' => ['discounted' => true]])
            ->assertOk()->json('payload.meta.total');

        $this->assertSame(Order::query()->count(), $all);
        $this->assertSame(Order::query()->where('discount', '>', 0)->count(), $discounted);
        $this->assertLessThan($all, $discounted);
    }

    public function test_the_embedded_table_lists_only_the_authors_posts(): void
    {
        $this->loginAs('admin');
        $author = Author::query()->has('posts')->firstOrFail();

        $ids = collect($this->postJson('/api/admin/showcase-grid-author-posts/search', ['filters' => ['author_id' => $author->id]])
            ->assertOk()->json('payload.data'))->pluck('id')->all();

        $this->assertNotEmpty($ids);
        $this->assertSame([], array_diff($ids, $author->posts()->pluck('id')->all()));
    }

    public function test_a_cell_is_edited_in_place_and_validated(): void
    {
        $this->loginAs('admin');
        $post = Post::query()->where('status', PostStatus::Draft->value)->firstOrFail();

        $this->postJson('/api/admin/showcase-grid-inline/inlineUpdate', ['id' => $post->id, 'column' => 'views', 'value' => -1])
            ->assertStatus(422);
        $this->postJson('/api/admin/showcase-grid-inline/inlineUpdate', ['id' => $post->id, 'column' => 'views', 'value' => 4242])
            ->assertOk();
        $this->assertSame(4242, $post->refresh()->views);
    }

    public function test_viewers_cannot_run_the_row_actions(): void
    {
        $this->loginAs('viewer');
        $order = Order::query()->where('status', OrderStatus::Pending->value)->firstOrFail();

        $this->postJson('/api/admin/showcase-grid-actions/action', ['key' => 'advance', 'ids' => [$order->id]])
            ->assertStatus(403);
        $this->postJson('/api/admin/showcase-grid-actions/action', ['key' => 'overdue', 'ids' => []])
            ->assertOk();
    }
}
