<?php

namespace App\Admin\Widgets\Shop;

use App\Enums\ProductStatus;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Dskripchenko\LaravelAdmin\Widget\TableWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/** Active products that are about to run out. */
final class LowStockWidget extends Widget
{
    public function widgetType(): string
    {
        return 'table';
    }

    public function data(): array
    {
        return TableWidget::make()
            ->model(Product::class)
            ->query(fn ($query) => $query->where('status', ProductStatus::Active->value)->where('stock', '<', 10))
            ->orderBy('stock', 'asc')
            ->limit(8)
            ->columns([
                TableColumn::make('name')->label('Product'),
                TableColumn::make('sku')->label('SKU'),
                TableColumn::make('stock')->label('Stock')->align('right'),
            ])
            ->data();
    }
}
