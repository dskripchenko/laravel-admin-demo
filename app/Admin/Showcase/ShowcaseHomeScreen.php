<?php

namespace App\Admin\Showcase;

use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Screen\Screen;

/**
 * The showcase's front page: every group and every example in it, linked.
 */
final class ShowcaseHomeScreen extends Screen
{
    public static function slug(): string
    {
        return 'showcase';
    }

    public function name(): string
    {
        return __('Showcase');
    }

    public function description(): ?string
    {
        return __('Every page shows a working example and the PHP that built it.');
    }

    public function query(mixed ...$params): array
    {
        return [];
    }

    public function layout(): array
    {
        $intro = __('Each example is an ordinary screen class of this application. Under the example you will find its source, read through reflection from the very class that rendered the page.');

        $groups = [];
        foreach (Showcase::GROUPS as $group) {
            $lines = [];
            foreach ($group::screens() as $screen) {
                $page = app($screen);
                $lines[] = '- ['.__($page->name()).']('.$screen::slug().') — '.__((string) $page->description());
            }
            $groups[] = Layout::block(__($group::title()), [
                Layout::markdown(implode("\n", $lines))->linkBase($this->screensBase()),
            ])->icon($group::icon())->description(__($group::about()));
        }

        return [
            Layout::markdown($intro)->card(),
            ...array_map(fn (array $pair) => Layout::columns($pair), array_chunk($groups, 2)),
        ];
    }

    private function screensBase(): string
    {
        return '/'.trim((string) config('admin.path', 'admin'), '/').'/screens/';
    }
}
