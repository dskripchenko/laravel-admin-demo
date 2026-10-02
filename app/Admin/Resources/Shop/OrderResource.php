<?php

namespace App\Admin\Resources\Shop;

use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Action\BulkAction;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\RelationTable;
use Dskripchenko\LaravelAdmin\Field\ResourcePicker;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Filter\DateRangeFilter;
use Dskripchenko\LaravelAdmin\Filter\InputFilter;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Resource\ActionFailedException;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Orders: a status flow (pending → paid → shipped → delivered, with
 * cancellation and refunds), line items and totals.
 */
final class OrderResource extends Resource
{
    public static string $model = Order::class;

    public static string $icon = 'shopping-cart';

    public const PAYMENT_METHODS = ['card' => 'Card', 'paypal' => 'PayPal', 'invoice' => 'Invoice'];

    public static function slug(): string
    {
        return 'orders';
    }

    public static function label(): string
    {
        return 'Orders';
    }

    public function fields(): array
    {
        return [
            Input::make('number')->required()->span(4)->help('ORD-10001'),
            ResourcePicker::make('customer_id')->title('Customer')->resource('customers')->required()->layout('list'),
            Select::make('status')->options(OrderStatus::options())->required()->span(4),
            Select::make('payment_method')->options(self::PAYMENT_METHODS)->span(4),
            DatePicker::make('placed_at')->withTime()->required()->span(4),
            Number::make('shipping')->min(0)->step(0.01)->span(4),
            Number::make('discount')->min(0)->step(0.01)->span(4),
            Number::make('total')->readonly()->span(4)->help('Recomputed from the line items on save'),
            Input::make('shipping_city')->span(4),
            Input::make('shipping_address')->span(8),
            Textarea::make('notes')->rows(2),
            RelationTable::make('items')->title('Line items')->relation('items')->onCreate(false)->columns([
                TableColumn::make('product_name')->label('Product'),
                TableColumn::make('sku')->label('SKU'),
                TableColumn::make('unit_price')->label('Price')->asMoney('USD'),
                TableColumn::make('quantity')->label('Qty'),
                TableColumn::make('total')->asMoney('USD'),
            ]),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('number')->sort()->search()->copyable(),
            TableColumn::make('customer_name')->label('Customer'),
            TableColumn::make('status')->asBadge(OrderStatus::colors()),
            TableColumn::make('items_count')->label('Items')->align('right'),
            TableColumn::make('total')->asMoney('USD')->align('right')->sort()->summary(['sum', 'avg']),
            TableColumn::make('payment_method')->label('Payment')->defaultHidden(),
            TableColumn::make('shipping_city')->label('City')->defaultHidden(),
            TableColumn::make('placed_at')->label('Placed')->asDateTime()->sort(),
        ];
    }

    public function filters(): array
    {
        return [
            OptionsFilter::for('status')->options(OrderStatus::options())->multiple(),
            OptionsFilter::for('payment_method')->label('Payment')->options(self::PAYMENT_METHODS),
            DateRangeFilter::for('placed_at')->label('Placed'),
            InputFilter::for('shipping_city')->label('City'),
        ];
    }

    public function actions(): array
    {
        return [
            Button::make('Advance')->method('advance')->icon('play')->position(['row'])
                ->confirm('Move the order to its next status?'),
            BulkAction::make('Mark as shipped')->withName('ship')->method('ship')->icon('truck'),
            BulkAction::make('Cancel')->withName('cancel')->method('cancel')->icon('x-circle')->destructive()
                ->confirm('Cancel the selected orders?'),
        ];
    }

    /**
     * Moves one order to the first status of its flow.
     *
     * @param  list<int>  $ids
     */
    public function advance(array $ids): string
    {
        $order = Order::query()->findOrFail($ids[0]);
        $next = $order->status->next()[0] ?? null;
        if ($next === null) {
            throw new ActionFailedException('The order is already closed.');
        }
        $order->transitionTo($next);

        return "{$order->number}: ".$next->label();
    }

    /** @param list<int> $ids */
    public function ship(array $ids): int
    {
        $count = 0;
        foreach (Order::query()->whereKey($ids)->where('status', OrderStatus::Paid->value)->get() as $order) {
            $order->transitionTo(OrderStatus::Shipped);
            $count++;
        }

        return $count;
    }

    /** @param list<int> $ids */
    public function cancel(array $ids): int
    {
        return Order::query()->whereKey($ids)->where('status', OrderStatus::Pending->value)
            ->update(['status' => OrderStatus::Cancelled->value]);
    }

    public function fillModel(Model $model, array $data): void
    {
        unset($data['total'], $data['items']);
        parent::fillModel($model, $data);
        $model->setAttribute('total', max(0, (float) $model->getAttribute('subtotal')
            + (float) $model->getAttribute('shipping') - (float) $model->getAttribute('discount')));
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with('customer')->withCount('items');
    }

    public function defaultOrder(): array
    {
        return [['column' => 'placed_at', 'direction' => 'desc']];
    }

    public function transformRecord(Model $record): array
    {
        return parent::transformRecord($record) + [
            'items' => $record->items()->get()->toArray(),
        ];
    }

    public function recordSubtitle(Model $row): ?string
    {
        return $row->getAttribute('customer_name');
    }
}
