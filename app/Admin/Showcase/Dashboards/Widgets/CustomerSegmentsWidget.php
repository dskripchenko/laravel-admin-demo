<?php

namespace App\Admin\Showcase\Dashboards\Widgets;

use App\Enums\CustomerSegment;
use App\Models\Shop\Customer;
use Dskripchenko\LaravelAdmin\Widget\ChartWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/**
 * Customers by segment. The dashboard guards it with
 * ->permission('admin.customers.view'): the Editor demo account lacks it, so
 * for them the widget is dropped before data() runs.
 */
final class CustomerSegmentsWidget extends Widget
{
    public function widgetType(): string
    {
        return 'chart';
    }

    public function data(): array
    {
        $counts = Customer::query()->selectRaw('segment, count(*) as aggregate')->groupBy('segment')->pluck('aggregate', 'segment');

        return ChartWidget::make()
            ->chartType('bar')
            ->labels(array_map(fn (CustomerSegment $s) => $s->label(), CustomerSegment::cases()))
            ->dataset('Customers', array_map(fn (CustomerSegment $s) => (int) ($counts[$s->value] ?? 0), CustomerSegment::cases()), '#8b5cf6')
            ->data();
    }
}
