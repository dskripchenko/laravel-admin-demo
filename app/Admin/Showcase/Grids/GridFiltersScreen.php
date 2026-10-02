<?php

namespace App\Admin\Showcase\Grids;

use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Grids › Search, sorting and filters.
 */
final class GridFiltersScreen extends GridShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-grids-filters';
    }

    public static function icon(): string
    {
        return 'filter';
    }

    protected static function resource(): string
    {
        return GridFiltersResource::class;
    }

    public function name(): string
    {
        return 'Search and filters';
    }

    public function description(): ?string
    {
        return 'Every filter type, free-text search, sorting, saved views, export and pagination.';
    }

    protected function demo(): array
    {
        $rows = [
            ['InputFilter', __('Text, matched with LIKE')],
            ['OptionsFilter', __('A fixed list, one or several values')],
            ['DateRangeFilter', __('From and to over a date column')],
            ['SelectFromModelFilter', __('A list read from a model')],
            ['QueryFilter', __('Your own closure, drawn as a switcher or an input')],
            ['TrashedFilter', __('Added by itself for models with SoftDeletes')],
        ];
        $table = '| '.__('Filter').' | '.__('What it does').' |'."\n|---|---|\n";
        foreach ($rows as [$class, $about]) {
            $table .= "| `{$class}` | {$about} |\n";
        }

        return [
            Layout::block('What to try', [
                Layout::markdown(__('Open the table, search for a city or an order number, sort by any underlined header and add filters from the toolbar: several statuses at once, a period, a customer, "with a discount". The summary under the table follows the filters. Save the combination as a view, export what you see to CSV or JSON, and change the page size at the bottom.')),
            ]),
            Layout::block('Filter types', [Layout::markdown($table)]),
        ];
    }
}
