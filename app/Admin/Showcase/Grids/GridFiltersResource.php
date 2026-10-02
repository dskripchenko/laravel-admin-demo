<?php

namespace App\Admin\Showcase\Grids;

use App\Admin\Resources\Shop\OrderResource;
use App\Enums\OrderStatus;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Filter\DateRangeFilter;
use Dskripchenko\LaravelAdmin\Filter\InputFilter;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Filter\QueryFilter;
use Dskripchenko\LaravelAdmin\Filter\SelectFromModelFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Grids › Search, sorting and filters: every filter type over the orders,
 * free-text search, sortable columns, saved views and export.
 */
final class GridFiltersResource extends Resource
{
    public static string $model = Order::class;

    public static string $icon = 'filter';

    public static function slug(): string
    {
        return 'showcase-grid-filters';
    }

    public static function label(): string
    {
        return 'Search, sorting and filters';
    }

    /** One record's name, for titles, confirmations and toasts: "Create order". */
    public static function singularLabel(): ?string
    {
        return 'order';
    }

    public static function permission(): string
    {
        return 'admin.orders';
    }

    public function fields(): array
    {
        return [];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('number')->sort()->search(),
            TableColumn::make('customer_name')->label('Customer'),
            TableColumn::make('status')->asBadge(OrderStatus::colors(), OrderStatus::options()),
            TableColumn::make('payment_method')->label('Payment'),
            TableColumn::make('shipping_city')->label('City')->sort()->search(),
            TableColumn::make('discount')->asMoney('USD')->align('right')->sort(),
            TableColumn::make('total')->asMoney('USD')->align('right')->sort()->summary(['sum', 'count']),
            TableColumn::make('placed_at')->label('Placed')->asDateTime('d.m.Y H:i')->sort(),
        ];
    }

    public function filters(): array
    {
        return [
            // A text box: LIKE %…%.
            InputFilter::for('shipping_city')->label('City'),
            // A fixed list; multiple() picks several values.
            OptionsFilter::for('status')->options(OrderStatus::options())->multiple(),
            OptionsFilter::for('payment_method')->label('Payment')->options(OrderResource::PAYMENT_METHODS),
            // {from, to} over a date column.
            DateRangeFilter::for('placed_at')->label('Placed'),
            // Options read from a model.
            SelectFromModelFilter::for('customer_id')->label('Customer')->fromModel(Customer::class, 'name')->limit(500),
            // Anything else: a closure, drawn as a switcher or an input.
            QueryFilter::for('discounted')->label('With a discount')->as('switcher')
                ->using(fn (Builder $query, mixed $value) => filter_var($value, FILTER_VALIDATE_BOOL) ? $query->where('discount', '>', 0) : $query),
            QueryFilter::for('min_total')->label('Total at least')->as('input')
                ->using(fn (Builder $query, mixed $value) => is_numeric($value) ? $query->where('total', '>=', (float) $value) : $query),
        ];
    }

    /** The free-text search box looks in these columns. */
    public function searchableFields(): array
    {
        return ['number', 'shipping_city'];
    }

    /** Without a sort picked by the user: newest first. */
    public function defaultOrder(): array
    {
        return [['column' => 'placed_at', 'direction' => 'desc']];
    }

    /** Named sets of filters, sorting and columns, per user. */
    public function savedViews(): bool
    {
        return true;
    }

    /** XLSX and PDF need openspout/openspout and a PDF renderer installed. */
    public function exportable(): array
    {
        return ['csv', 'json'];
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with('customer');
    }
}
