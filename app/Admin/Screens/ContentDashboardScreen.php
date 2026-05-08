<?php

declare(strict_types=1);

namespace App\Admin\Screens;

use App\Models\Article;
use Dskripchenko\LaravelAdmin\Widget\ChartWidget;
use Dskripchenko\LaravelAdmin\Widget\DashboardScreen;
use Dskripchenko\LaravelAdmin\Widget\GaugeWidget;
use Dskripchenko\LaravelAdmin\Widget\HeatmapWidget;
use Dskripchenko\LaravelAdmin\Widget\MarkdownWidget;
use Dskripchenko\LaravelAdmin\Widget\RecentListWidget;
use Dskripchenko\LaravelAdmin\Widget\StatsOverviewWidget;
use Illuminate\Support\Carbon;

/**
 * ContentDashboard — главный экран demo-админки. Воспроизводит layout
 * эталонного дизайна (см. design_handoff_laravel_admin):
 *
 *   Row 1: 4 stat-карточки (Total / Views / Avg time / In review)
 *   Row 2: bar-chart публикаций по дням + donut статусов
 *   Row 3: recent-list последние публикации + heatmap активности
 *   Row 4: gauge SEO + markdown-заметка команды
 *
 * widgets() возвращает array<Widget>; их порядок задаёт раскладку
 * по grid'у (frontend читает widget.size как span 1..12).
 */
final class ContentDashboardScreen extends DashboardScreen
{
    public static function slug(): string
    {
        return 'content';
    }

    public function name(): string
    {
        return 'Аналитика';
    }

    public function description(): ?string
    {
        return 'Аналитика контента · последние 30 дней';
    }

    public function widgets(): array
    {
        // periodDays() из DashboardScreen (default 30). Frontend меняет period
        // через /dashboard/widgets endpoint → withPeriod() выставляет $period.
        $days = $this->periodDays();
        $since = Carbon::now()->subDays($days);

        $totalArticles = (int) Article::query()->where('created_at', '>=', $since)->count();
        $published = (int) Article::query()->where('created_at', '>=', $since)->where('status', 'published')->count();
        $inReview = (int) Article::query()->where('created_at', '>=', $since)->where('status', 'review')->count();
        $draft = (int) Article::query()->where('created_at', '>=', $since)->where('status', 'draft')->count();
        $archived = (int) Article::query()->where('created_at', '>=', $since)->where('status', 'archived')->count();

        $stat1 = (new class extends StatsOverviewWidget {
            public static function slug(): string { return 'content.total'; }
        })
            ->title('Всего статей')
            ->size(3)
            ->stat('TOTAL ARTICLES', $totalArticles)
            ->trend(12.4, 'up');

        $stat2 = (new class extends StatsOverviewWidget {
            public static function slug(): string { return 'content.views'; }
        })
            ->title('Просмотры')
            ->size(3)
            ->stat('PAGE VIEWS', '428K')
            ->trend(8.1, 'up');

        $stat3 = (new class extends StatsOverviewWidget {
            public static function slug(): string { return 'content.avg-time'; }
        })
            ->title('Среднее время')
            ->size(3)
            ->stat('AVG READ TIME', '3:42')
            ->trend(-0.4, 'down');

        $stat4 = (new class extends StatsOverviewWidget {
            public static function slug(): string { return 'content.in-review'; }
        })
            ->title('В ревью')
            ->size(3)
            ->stat('IN REVIEW', $inReview);

        // Bar chart публикаций по дням за выбранный period.
        $rows = Article::query()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', $since)
            ->groupBy('d')
            ->orderBy('d')
            ->get();
        $labels = [];
        $values = [];
        // Для очень больших периодов (all = 3650 дней) лимитируем чтобы chart
        // не разъезжался — берём максимум 60 точек.
        $chartDays = min($days, 60);
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('d.m');
            $row = $rows->firstWhere('d', $date);
            $values[] = $row ? (int) $row->c : 0;
        }

        $bar = (new class extends ChartWidget {
            public static function slug(): string { return 'content.daily-bar'; }
        })
            ->title('Публикации по дням')
            ->size(8)
            ->rowSpan(2)
            ->chartType('bar')
            ->labels($labels)
            ->dataset('Опубликовано', $values, '#10b981');

        // Donut статусов.
        $donut = (new class extends ChartWidget {
            public static function slug(): string { return 'content.status-donut'; }
        })
            ->title('Распределение статусов')
            ->size(4)
            ->rowSpan(2)
            ->chartType('doughnut')
            ->labels(['Published', 'In review', 'Draft', 'Archived'])
            ->dataset(
                'Articles',
                [$published, $inReview, $draft, $archived],
            );

        // Recent published.
        $recent = (new class extends RecentListWidget {
            public static function slug(): string { return 'content.recent'; }
        })
            ->title('Последние публикации')
            ->size(8)
            ->rowSpan(3)
            ->model(Article::class)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->column('title', 'TITLE')
            ->column('status', 'STATUS')
            ->column('created_at', 'CREATED')
            ->linkTo('articles');

        // Heatmap активности (24 часа × 7 дней).
        $heatmap = (new class extends HeatmapWidget {
            public static function slug(): string { return 'content.activity-heatmap'; }
        })
            ->title('Активность по часам')
            ->size(4)
            ->rowSpan(3)
            ->axes(
                ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
                array_map(static fn ($h) => sprintf('%02dh', $h), range(0, 23)),
            )
            ->matrix($this->buildActivityMatrix());

        // SEO Gauge.
        $gauge = (new class extends GaugeWidget {
            public static function slug(): string { return 'content.seo-score'; }
        })
            ->title('SEO score (avg)')
            ->size(4)
            ->rowSpan(2)
            ->value(78)
            ->range(0, 100)
            ->threshold(0, 40, '#dc2626')
            ->threshold(40, 70, '#f59e0b')
            ->threshold(70, 100, '#10b981');

        // Note.
        $note = (new class extends MarkdownWidget {
            public static function slug(): string { return 'content.note'; }
        })
            ->title('Заметка команды')
            ->size(8)
            ->rowSpan(2)
            ->content(<<<'MD'
**Релиз 1 мая.** Сегодня код-фриз. Все новые статьи попадут в публикацию `v2.5.0`. Любые срочные правки — через сторим.

— @anna
MD);

        return [$stat1, $stat2, $stat3, $stat4, $bar, $donut, $recent, $heatmap, $gauge, $note];
    }

    /**
     * Псевдо-матрица активности 7×24. Реальный data-source — log/audit
     * по hour of day; здесь deterministic-pattern для demo.
     *
     * @return list<list<int>>
     */
    private function buildActivityMatrix(): array
    {
        $matrix = [];
        for ($d = 0; $d < 7; $d++) {
            $row = [];
            for ($h = 0; $h < 24; $h++) {
                // working-hours peak с пятна шумом, weekends — затишье.
                $base = ($d < 5 && $h >= 9 && $h <= 18) ? 70 : 20;
                $row[] = max(0, $base + (int) (sin($d * 24 + $h) * 25));
            }
            $matrix[] = $row;
        }

        return $matrix;
    }
}
