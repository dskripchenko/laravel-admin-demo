<?php

declare(strict_types=1);

namespace App\Admin\Resources;

use App\Models\Order;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\TextArea;
use Dskripchenko\LaravelAdmin\Filter\InputFilter;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Demo Resource — заказы.
 *
 * Показывает: workflow-статус с цветными badge'ами, фильтр по дате,
 * email-search, money-формат для total.
 */
final class OrderResource extends Resource
{
    public static string $model = Order::class;

    public static string $icon = 'shopping-cart';

    public static ?string $group = 'Магазин';

    public static function slug(): string
    {
        return 'orders';
    }

    public static function permission(): string
    {
        return 'admin.shop.orders';
    }

    public static function label(): string
    {
        return 'Заказы';
    }

    public function fields(): array
    {
        return [
            Input::make('number')->required()->title('Номер заказа'),
            Input::make('customer_name')->required()->title('Покупатель'),
            Input::make('customer_email')->type('email')->required()->title('Email'),
            Input::make('total')->type('number')->required()->title('Сумма')->help('USD'),
            Select::make('status')->options([
                'new' => 'Новый',
                'processing' => 'В обработке',
                'shipped' => 'Отправлен',
                'delivered' => 'Доставлен',
                'cancelled' => 'Отменён',
                'refunded' => 'Возврат',
            ])->required()->title('Статус'),
            Input::make('placed_at')->type('datetime-local')->title('Дата заказа'),
            TextArea::make('notes')->title('Заметки')->rows(3),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('id')->sort()->width('60px'),
            TableColumn::make('number')->sort()->search()->copyable(),
            TableColumn::make('customer_name')->sort()->search(),
            TableColumn::make('customer_email')->search()->copyable(),
            TableColumn::make('total')->sort()->asMoney('USD'),
            TableColumn::make('status')->asBadge([
                'new' => 'info',
                'processing' => 'warning',
                'shipped' => 'default',
                'delivered' => 'success',
                'cancelled' => 'danger',
                'refunded' => 'danger',
            ]),
            TableColumn::make('placed_at')->sort()->asDateTime(),
        ];
    }

    public function filters(): array
    {
        return [
            InputFilter::for('customer_email')->label('Email покупателя'),
            OptionsFilter::for('status')->label('Статус')->options([
                'new' => 'Новый',
                'processing' => 'В обработке',
                'shipped' => 'Отправлен',
                'delivered' => 'Доставлен',
                'cancelled' => 'Отменён',
                'refunded' => 'Возврат',
            ]),
        ];
    }
}
