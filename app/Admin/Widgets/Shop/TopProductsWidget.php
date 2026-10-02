<?php

namespace App\Admin\Widgets\Shop;

use App\Models\Shop\OrderItem;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/** Best sellers of the selected period, by revenue. */
final class TopProductsWidget extends Widget
{
    public function widgetType(): string
    {
        return 'table';
    }

    public function data(): array
    {
        $rows = $this->dashboardContext()->constrain(OrderItem::query(), 'created_at')
            ->selectRaw('product_name, sum(quantity) as units, sum(total) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get()
            ->map(fn (OrderItem $row) => [
                'product_name' => $row->product_name,
                'units' => (int) $row->getAttribute('units'),
                'revenue' => round((float) $row->getAttribute('revenue'), 2),
            ])
            ->all();

        return [
            'rows' => $rows,
            'columns' => array_map(fn (TableColumn $c) => $c->toArray(), [
                TableColumn::make('product_name')->label('Product'),
                TableColumn::make('units')->label('Units')->align('right'),
                TableColumn::make('revenue')->label('Revenue')->asMoney('USD')->align('right'),
            ]),
        ];
    }
}
