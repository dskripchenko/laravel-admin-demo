<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Builder;
use Dskripchenko\LaravelAdmin\Field\Group;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\KeyValue;
use Dskripchenko\LaravelAdmin\Field\Markdown;
use Dskripchenko\LaravelAdmin\Field\Repeater;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Forms › Composite fields: values that are lists or objects — a repeater of
 * sub-forms, a nested group, free key/value pairs and a page builder of
 * typed blocks.
 */
final class CompositeScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-composite';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'blocks';
    }

    public function name(): string
    {
        return 'Composite fields';
    }

    public function description(): ?string
    {
        return 'Repeater, Group, KeyValue and Builder.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'links' => [
                ['label' => 'Documentation', 'url' => 'https://github.com/dskripchenko/laravel-admin', 'kind' => 'docs'],
                ['label' => 'Changelog', 'url' => 'https://github.com/dskripchenko/laravel-admin/releases', 'kind' => 'news'],
            ],
            'contact' => ['email' => 'support@example.com', 'phone' => '+44 20 7946 0000'],
            'headers' => ['Accept' => 'application/json', 'X-Request-Source' => 'admin'],
            'blocks' => [
                ['type' => 'hero', 'data' => ['title' => 'Spring sale', 'subtitle' => 'Up to **30%** off']],
                ['type' => 'quote', 'data' => ['text' => 'The best admin I have used.', 'author' => 'A happy customer']],
            ],
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('Lists and objects', [
                    Layout::rows([
                        Repeater::make('links')->fields([
                            Input::make('label')->required()->span(4),
                            Input::make('url')->type('url')->required()->span(5),
                            Select::make('kind')->options(['docs' => 'Docs', 'news' => 'News', 'other' => 'Other'])->span(3),
                        ])->minItems(1)->maxItems(5)->defaultItem(['kind' => 'other']),
                        Group::make('contact')->title('Contact')->fields([
                            Input::make('email')->type('email'),
                            Input::make('phone')->type('tel'),
                        ])->layout('columns'),
                        KeyValue::make('headers')->title('HTTP headers')->keyLabel('Header')->valueLabel('Value'),
                    ]),
                ])->icon('list'),
                Layout::block('Page builder', [
                    Layout::rows([
                        Builder::make('blocks')->title('Page blocks')
                            ->block('hero', [
                                Input::make('title')->required(),
                                Markdown::make('subtitle')->height('120px'),
                            ], label: 'Hero', icon: 'image')
                            ->block('quote', [
                                Textarea::make('text')->rows(2)->required(),
                                Input::make('author'),
                            ], label: 'Quote', icon: 'message-square')
                            ->block('cta', [
                                Input::make('label')->required(),
                                Input::make('url')->type('url'),
                            ], label: 'Call to action', icon: 'target')
                            ->maxBlocks(8),
                    ]),
                ])->icon('blocks'),
            ])->ratios([1, 1]),
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
            'links' => 'array|min:1|max:5',
            'links.*.label' => 'required|string',
            'links.*.url' => 'required|url',
            'contact.email' => 'nullable|email',
            'headers' => 'array',
            'blocks' => 'array|max:8',
            'blocks.*.type' => 'required|in:hero,quote,cta',
        ])->validate();

        return ['message' => __('Valid! :links link(s) and :blocks block(s).', [
            'links' => count($state['links']),
            'blocks' => count($state['blocks'] ?? []),
        ])];
    }
}
