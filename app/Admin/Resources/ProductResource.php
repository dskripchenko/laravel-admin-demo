<?php

declare(strict_types=1);

namespace App\Admin\Resources;

use App\Models\Product;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\TextArea;
use Dskripchenko\LaravelAdmin\Filter\InputFilter;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Demo Resource — каталог товаров.
 *
 * Показывает: денежное поле с decimal-precision, switcher для is_active,
 * фильтр-в-категорию, поиск по SKU/name.
 */
final class ProductResource extends Resource
{
    public static string $model = Product::class;

    public static string $icon = 'cube';

    public static ?string $group = 'Магазин';

    public static function slug(): string
    {
        return 'products';
    }

    public static function permission(): string
    {
        return 'admin.shop.products';
    }

    public static function label(): string
    {
        return 'Товары';
    }

    public function fields(): array
    {
        return [
            Input::make('sku')->required()->title('SKU'),
            Input::make('name')->required()->title('Название'),
            TextArea::make('description')->title('Описание')->rows(4),
            Input::make('price')->type('number')->required()->title('Цена')->help('USD'),
            Input::make('stock')->type('number')->required()->title('Остаток на складе'),
            Select::make('category')->options([
                'electronics' => 'Электроника',
                'apparel' => 'Одежда',
                'home' => 'Дом и быт',
                'books' => 'Книги',
            ])->title('Категория'),
            Switcher::make('is_active')->title('В продаже'),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('id')->sort()->width('60px'),
            TableColumn::make('sku')->sort()->search()->copyable(),
            TableColumn::make('name')->sort()->search(),
            TableColumn::make('price')->sort()->asMoney('USD'),
            TableColumn::make('stock')->sort(),
            TableColumn::make('category')->asBadge([
                'electronics' => 'info',
                'apparel' => 'default',
                'home' => 'warning',
                'books' => 'success',
            ]),
            TableColumn::make('is_active')->asBoolean('В продаже', 'Скрыт'),
        ];
    }

    public function filters(): array
    {
        return [
            InputFilter::for('sku')->label('SKU'),
            OptionsFilter::for('category')->label('Категория')->options([
                'electronics' => 'Электроника',
                'apparel' => 'Одежда',
                'home' => 'Дом и быт',
                'books' => 'Книги',
            ]),
            OptionsFilter::for('is_active')->label('Статус')->options([
                '1' => 'В продаже',
                '0' => 'Скрытые',
            ]),
        ];
    }
}
