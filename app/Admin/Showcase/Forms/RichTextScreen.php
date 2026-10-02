<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Code;
use Dskripchenko\LaravelAdmin\Field\Markdown;
use Dskripchenko\LaravelAdmin\Field\TranslatableInput;
use Dskripchenko\LaravelAdmin\Field\Wysiwyg;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Forms › Rich text and code: Markdown with a preview, the built-in WYSIWYG
 * editor in two presets, a code editor and a field per language.
 */
final class RichTextScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-rich-text';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'file-text';
    }

    public function name(): string
    {
        return 'Rich text and code';
    }

    public function description(): ?string
    {
        return 'Markdown, Wysiwyg, Code and TranslatableInput.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'body' => "## Release notes\n\n- **Faster** dashboards\n- A new `Slug` field\n\n> Markdown is stored as text.",
            'notes' => 'A plain editor: no toolbar, no preview.',
            'summary' => '<p>The <strong>minimal</strong> preset: bold, italic and links.</p>',
            'page' => '<h2>The full preset</h2><p>Headings, lists, quotes, tables and more.</p><ul><li>One</li><li>Two</li></ul>',
            'snippet' => "Route::get('/hello', function () {\n    return 'Hello, world';\n});",
            'title' => ['en' => 'Spring sale', 'ru' => 'Весенняя распродажа'],
            'teaser' => ['en' => "Up to 30% off.\nThis week only.", 'ru' => "Скидки до 30%.\nТолько на этой неделе."],
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::tabs([
                'Markdown' => [
                    Markdown::make('body')->height('260px'),
                    Markdown::make('notes')->preview(false)->toolbar(false)->height('120px'),
                ],
                'WYSIWYG' => [
                    Wysiwyg::make('summary')->preset('minimal')->height(160),
                    Wysiwyg::make('page')->preset('full')->height(280),
                ],
                'Code' => [
                    Code::make('snippet')->language('php')->lineNumbers()->height(220),
                ],
                'Translations' => [
                    TranslatableInput::make('title')->locales(['en', 'ru'])->requireAllLocales(),
                    TranslatableInput::make('teaser')->as('textarea')->locales(['en', 'ru']),
                ],
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
            'body' => 'required|string',
            'title.en' => 'required|string',
            'title.ru' => 'required|string',
        ], [
            'title.*.required' => __('Fill the title in every language.'),
        ])->validate();

        return ['message' => __('Valid! :count characters of Markdown.', ['count' => mb_strlen((string) $state['body'])])];
    }
}
