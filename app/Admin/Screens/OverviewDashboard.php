<?php

namespace App\Admin\Screens;

use App\Models\Post;
use Dskripchenko\LaravelAdmin\Widget\ChartWidget;
use Dskripchenko\LaravelAdmin\Widget\DashboardScreen;
use Dskripchenko\LaravelAdmin\Widget\MarkdownWidget;
use Dskripchenko\LaravelAdmin\Widget\RecentListWidget;
use Dskripchenko\LaravelAdmin\Widget\StatsOverviewWidget;
use Illuminate\Support\Carbon;

/**
 * The home dashboard. Widgets are plain PHP; administrators can rearrange,
 * resize and hide them, and the layout is saved per user.
 */
final class OverviewDashboard extends DashboardScreen
{
    public static function slug(): string
    {
        return 'main';
    }

    public function name(): string
    {
        return 'Overview';
    }

    public function widgets(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->startOfMonth()->subMonths($i));

        return [
            StatsOverviewWidget::make()->title('Posts')->size(12)
                ->stat('Total', Post::count(), 'blue', 'file-text')
                ->stat('Published', Post::where('status', 'published')->count(), 'green', 'check')
                ->stat('In review', Post::where('status', 'review')->count(), 'amber', 'eye')
                ->stat('Drafts', Post::where('status', 'draft')->count(), 'gray', 'edit'),

            ChartWidget::make()->title('Published per month')->size(8)->chartType('area')
                ->labels($months->map->translatedFormat('M')->all())
                ->dataset('Posts', $months->map(fn (Carbon $m) => Post::where('status', 'published')
                    ->whereBetween('published_at', [$m, $m->copy()->endOfMonth()])->count())->all()),

            RecentListWidget::make()->title('Latest posts')->size(4)
                ->model(Post::class)->orderBy('created_at', 'desc')->limit(6)
                ->column('title', 'Title')->linkTo('posts'),

            MarkdownWidget::make()->title('Next steps')->size(12)->content(<<<'MD'
                - Your admin code lives in `app/Admin` and is registered in `app/Providers/AdminServiceProvider.php`.
                - Create a resource: `php artisan admin:make-resource` (an interactive wizard).
                - Users, roles and the audit log come from `dskripchenko/laravel-admin-starter`.
                - Docs: https://github.com/dskripchenko/laravel-admin
                MD),
        ];
    }
}
