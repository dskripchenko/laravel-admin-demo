<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Radio;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Illuminate\Http\Request;

/**
 * Forms › Dependent fields: three ways for one field to follow another.
 * visibleWhen() shows and hides in the browser; a slug follows its source in
 * the browser too; a listener asks the server — for the options of the next
 * select, or for computed values.
 */
final class DependentFieldsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-dependent';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'workflow';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Dependent fields';
    }

    public function description(): ?string
    {
        return 'visibleWhen(), a slug that follows its title, and listeners re-rendered by the server.';
    }

    public function query(mixed ...$params): array
    {
        $product = Product::query()->whereNotNull('category_id')->orderBy('id')->firstOrFail();
        $state = [
            'delivery' => 'courier',
            'address' => '221B Baker Street, London',
            'store' => null,
            'is_gift' => 'no',
            'gift_note' => '',
            'title' => 'Summer collection 2026',
            'slug' => Slug::generate('Summer collection 2026'),
            'category_id' => $product->category_id,
            'product_id' => $product->id,
            'quantity' => 2,
        ];

        return $state + $this->recalculate($state, request());
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('In the browser', [
                    Layout::rows([
                        Radio::make('delivery')->options(['courier' => 'Courier', 'pickup' => 'Pickup'])->inline(),
                        Input::make('address')->title('Delivery address')->visibleWhen('delivery', 'courier'),
                        Select::make('store')->title('Pickup point')->visibleWhen('delivery', 'pickup')
                            ->options(['soho' => 'Soho', 'camden' => 'Camden', 'shoreditch' => 'Shoreditch']),
                        Radio::make('is_gift')->title('A gift?')->options(['no' => 'No', 'yes' => 'Yes'])->inline(),
                        Textarea::make('gift_note')->title('Gift note')->rows(2)->visibleWhen('is_gift', 'yes'),
                        Input::make('title'),
                        Slug::make('slug')->from('title'),
                    ]),
                ])->icon('eye')->description('visibleWhen() and Slug::from(): no request is made.'),
                Layout::block('On the server', [
                    Layout::rows([
                        Select::make('category_id')->title('Category')
                            ->fromModel(Category::query()->has('products')->orderBy('name'))->searchable(),

                        // Children as a closure: re-rendered for the chosen category.
                        Layout::listener(fn (array $state) => [
                            Select::make('product_id')->title('Product')->searchable()->options(
                                Product::query()->where('category_id', $state['category_id'] ?? 0)
                                    ->orderBy('name')->limit(50)->pluck('name', 'id')->all(),
                            )->help(empty($state['category_id']) ? 'Pick a category first' : null),
                        ])->listen('category_id'),

                        // A handler computing values: recalculate() below.
                        Layout::listener([
                            Number::make('unit_price')->title('Unit price')->readonly()->span(4),
                            Number::make('quantity')->integer()->min(1)->max(99)->span(4),
                            Number::make('total')->readonly()->span(4),
                        ])->listen(['product_id', 'quantity'])->handler('recalculate'),
                    ]),
                ])->icon('server')->description('Layout::listener(): the server renders the fields or computes values.'),
            ]),
        ];
    }

    /**
     * The listener's handler: returns the part of the state to change.
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function recalculate(array $state, Request $request): array
    {
        $price = (float) (Product::query()->whereKey($state['product_id'] ?? 0)->value('price') ?? 0);
        $quantity = max(1, (int) ($state['quantity'] ?? 1));

        return ['unit_price' => $price, 'total' => round($price * $quantity, 2)];
    }

    public function commandBar(): array
    {
        return [Button::make('Submit')->method('submit')->primary()->icon('send')];
    }

    /** @param array<string, mixed> $state */
    public function submit(array $state): array
    {
        validator($state, [
            'delivery' => 'required|in:courier,pickup',
            'address' => 'required_if:delivery,courier',
            'store' => 'required_if:delivery,pickup',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
        ])->validate();

        return ['message' => __('Valid! :quantity × :product.', [
            'quantity' => $state['quantity'],
            'product' => Product::query()->whereKey($state['product_id'])->value('name'),
        ])];
    }
}
