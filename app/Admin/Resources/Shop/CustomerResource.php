<?php

namespace App\Admin\Resources\Shop;

use App\Enums\CustomerSegment;
use App\Enums\OrderStatus;
use App\Models\Shop\Customer;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\RelationTable;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Filter\DateRangeFilter;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Filter\SwitcherFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class CustomerResource extends Resource
{
    public static string $model = Customer::class;

    public static string $icon = 'users';

    public const COUNTRIES = [
        'US' => 'United States', 'GB' => 'United Kingdom', 'DE' => 'Germany', 'FR' => 'France',
        'CA' => 'Canada', 'NL' => 'Netherlands', 'ES' => 'Spain', 'PL' => 'Poland',
    ];

    public static function slug(): string
    {
        return 'customers';
    }

    public static function label(): string
    {
        return 'Customers';
    }

    /** One record's name, for titles, confirmations and toasts: "Create customer". */
    public static function singularLabel(): ?string
    {
        return 'customer';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required()->span(6),
            Input::make('email')->type('email')->required()->span(6),
            Input::make('phone')->type('tel')->span(6),
            Input::make('city')->span(3),
            Select::make('country')->options(self::COUNTRIES)->span(3),
            Select::make('segment')->options(CustomerSegment::options())->required(),
            Switcher::make('accepts_marketing')->title('Accepts marketing'),
            Textarea::make('notes')->rows(3),
            RelationTable::make('orders')->relation('orders')->onCreate(false)->onUpdate(false)->columns([
                TableColumn::make('number'),
                TableColumn::make('status')->asBadge(OrderStatus::colors(), OrderStatus::options()),
                TableColumn::make('total')->asMoney('USD'),
                TableColumn::make('placed_at')->asDateTime(),
            ]),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('name')->sort()->search(),
            TableColumn::make('email')->search()->copyable(),
            TableColumn::make('city')->sort(),
            TableColumn::make('country')->sort(),
            TableColumn::make('segment')->asBadge(CustomerSegment::colors(), CustomerSegment::options()),
            TableColumn::make('orders_count')->label('Orders')->align('right')->sort(),
            TableColumn::make('accepts_marketing')->label('Marketing')->asBoolean()->defaultHidden(),
            TableColumn::make('created_at')->label('Since')->asDate()->sort(),
        ];
    }

    public function filters(): array
    {
        return [
            OptionsFilter::for('segment')->options(CustomerSegment::options()),
            OptionsFilter::for('country')->options(self::COUNTRIES)->multiple(),
            SwitcherFilter::for('accepts_marketing')->label('Accepts marketing'),
            DateRangeFilter::for('created_at')->label('Customer since'),
        ];
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->withCount('orders');
    }

    public function transformRecord(Model $record): array
    {
        return parent::transformRecord($record) + [
            'orders' => $record->orders()->latest('placed_at')->limit(20)->get()->toArray(),
        ];
    }
}
