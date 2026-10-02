<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use App\Models\Blog\Author;
use App\Models\Blog\Post;
use App\Models\Blog\Tag;
use App\Models\Shop\Category;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\MorphSwitcher;
use Dskripchenko\LaravelAdmin\Field\RelationSelect;
use Dskripchenko\LaravelAdmin\Field\RelationTable;
use Dskripchenko\LaravelAdmin\Field\ResourcePicker;
use Dskripchenko\LaravelAdmin\Field\TreeSelect;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Forms › Relations: records of other tables — a select of a reference
 * table, records of another resource picked in a dialog, a polymorphic
 * subject, a tree of categories and a read-only table of related rows.
 */
final class RelationsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-relations';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'link';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Relations';
    }

    public function description(): ?string
    {
        return 'RelationSelect, ResourcePicker, MorphSwitcher, TreeSelect from a model and RelationTable.';
    }

    public function query(mixed ...$params): array
    {
        $order = Order::query()->with('items')->orderBy('id')->first();

        return [
            'author_id' => Author::query()->value('id'),
            'product_id' => Product::query()->orderBy('id')->value('id'),
            'tag_ids' => Tag::query()->orderBy('id')->limit(2)->pluck('id')->all(),
            'subject' => ['type' => 'post', 'id' => Post::query()->orderBy('id')->value('id')],
            'category_id' => Category::query()->whereDoesntHave('children')->orderBy('id')->value('id'),
            'items' => $order?->items->toArray() ?? [],
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block('Pick related records', [
                Layout::rows([
                    RelationSelect::make('author_id')->title('Author')->relation(Author::class, 'name')->span(6),
                    TreeSelect::make('category_id')->title('Category (leaves only)')
                        ->fromModel(Category::class)->selectableParents(false)->span(6),
                    ResourcePicker::make('product_id')->title('Product')->resource('products')
                        ->help('Opens the products list in a dialog, with its search and filters'),
                    ResourcePicker::make('tag_ids')->title('Tags')->resource('tags')->multiple()->maxItems(5)->layout('list'),
                    MorphSwitcher::make('subject')->title('About')->morph('post', Post::class, 'title')
                        ->morph('product', Product::class, 'name'),
                ]),
            ])->icon('link'),
            Layout::block('Related rows', [
                Layout::rows([
                    RelationTable::make('items')->title('Line items of the first order')->relation('items')->columns([
                        TableColumn::make('product_name')->label('Product'),
                        TableColumn::make('sku')->label('SKU'),
                        TableColumn::make('unit_price')->label('Price')->asMoney('USD'),
                        TableColumn::make('quantity')->label('Qty')->align('right'),
                        TableColumn::make('total')->asMoney('USD')->align('right'),
                    ]),
                ]),
            ])->icon('table')->description('Read-only: the rows come with the state.'),
        ];
    }

    public function commandBar(): array
    {
        return [Button::make('Submit')->method('submit')->primary()->icon('send')];
    }

    /** @param array<string, mixed> $state */
    public function submit(array $state): array
    {
        validator($state, [
            'author_id' => 'required|exists:authors,id',
            'product_id' => 'required|exists:products,id',
            'tag_ids' => 'array|max:5',
            'tag_ids.*' => 'exists:tags,id',
            'subject.type' => 'required|in:post,product',
            'subject.id' => 'required|integer',
        ])->validate();

        return ['message' => __('Valid! :product, :tags tag(s).', [
            'product' => Product::query()->whereKey($state['product_id'])->value('name'),
            'tags' => count($state['tag_ids'] ?? []),
        ])];
    }
}
