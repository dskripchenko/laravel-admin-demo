<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use App\Enums\ProductStatus;
use App\Models\Shop\Category;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Cascader;
use Dskripchenko\LaravelAdmin\Field\Checkbox;
use Dskripchenko\LaravelAdmin\Field\Combobox;
use Dskripchenko\LaravelAdmin\Field\Radio;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\TagsInput;
use Dskripchenko\LaravelAdmin\Field\TreeSelect;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Forms › Choices: picking from a list — closed (Select, Radio), several at
 * once (Select::multiple(), a Checkbox group), open (Combobox, TagsInput),
 * yes/no (Checkbox, Switcher) and hierarchical (TreeSelect, Cascader). Options come from an array, an enum or a model.
 */
final class ChoicesScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-choices';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'list-checks';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Choices';
    }

    public function description(): ?string
    {
        return 'Select (one or several), Combobox, Radio, Checkbox (one or a group), Switcher, TagsInput, TreeSelect and Cascader.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'plan' => 'team',
            'status' => 'active',
            'category_id' => null,
            'channels' => ['web', 'marketplace'],
            'model' => 'claude-opus-5',
            'billing' => 'yearly',
            'agree' => true,
            'published' => false,
            'notify' => ['email'],
            'tags' => ['new', 'sale'],
            'regions' => [3],
            'location' => ['eu', 'de', 'berlin'],
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('From a list', [
                    Layout::rows([
                        Select::make('plan')->options([
                            'starter' => 'Starter',
                            'team' => 'Team',
                            'enterprise' => 'Enterprise',
                        ])->required()->span(6),
                        Select::make('status')->title('Status (from an enum)')->fromEnum(ProductStatus::class)->span(6),
                        Select::make('category_id')->title('Category (from a model)')
                            ->fromModel(Category::query()->orderBy('name'))->searchable()->clearable(),
                        Select::make('channels')->title('Sales channels (several)')->options([
                            'web' => 'Web shop',
                            'marketplace' => 'Marketplace',
                            'retail' => 'Retail store',
                            'wholesale' => 'Wholesale',
                        ])->multiple()->help('multiple(): the value is a list'),
                        Combobox::make('model')->title('Model (any value allowed)')->options([
                            'claude-opus-5' => 'Claude Opus 5',
                            'claude-sonnet-5' => 'Claude Sonnet 5',
                        ])->clearable()->help('The options are hints: type a name that is not in the list'),
                        Radio::make('billing')->options(['monthly' => 'Monthly', 'yearly' => 'Yearly'])->inline(),
                    ]),
                ])->icon('list'),
                Layout::block('Yes or no, tags and trees', [
                    Layout::rows([
                        Checkbox::make('agree')->title('I agree to the terms')->span(6),
                        Switcher::make('published')->title('Status')->labels('Published', 'Draft')->span(6),
                        Checkbox::make('notify')->title('Notify me by')->options([
                            'email' => 'Email',
                            'sms' => 'SMS',
                            'push' => 'Push',
                        ])->inline()->help('A Checkbox with options() is a group; its value is a list'),
                        TagsInput::make('tags')->suggestions(['new', 'sale', 'bestseller', 'gift'])->maxItems(5),
                        TreeSelect::make('regions')->title('Regions')->tree([
                            ['value' => 1, 'label' => 'Europe', 'children' => [
                                ['value' => 2, 'label' => 'Germany'],
                                ['value' => 3, 'label' => 'France'],
                            ]],
                            ['value' => 4, 'label' => 'Asia', 'children' => [
                                ['value' => 5, 'label' => 'Japan'],
                            ]],
                        ])->multiple(),
                        Cascader::make('location')->title('Warehouse')->options([
                            ['value' => 'eu', 'label' => 'Europe', 'children' => [
                                ['value' => 'de', 'label' => 'Germany', 'children' => [
                                    ['value' => 'berlin', 'label' => 'Berlin'],
                                    ['value' => 'hamburg', 'label' => 'Hamburg'],
                                ]],
                                ['value' => 'fr', 'label' => 'France', 'children' => [
                                    ['value' => 'paris', 'label' => 'Paris'],
                                ]],
                            ]],
                            ['value' => 'us', 'label' => 'USA', 'children' => [
                                ['value' => 'ny', 'label' => 'New York', 'children' => [
                                    ['value' => 'nyc', 'label' => 'New York City'],
                                ]],
                            ]],
                        ]),
                    ]),
                ])->icon('folder-tree'),
            ]),
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
            'plan' => 'required|in:starter,team,enterprise',
            'agree' => 'accepted',
            'tags' => 'array|max:5',
            'channels' => 'required|array|min:1',
            'channels.*' => 'in:web,marketplace,retail,wholesale',
            'notify' => 'array',
            'notify.*' => 'in:email,sms,push',
        ], [
            'agree.accepted' => __('Please accept the terms.'),
        ])->validate();

        return ['message' => __('Valid! Your choices would be saved.')];
    }
}
