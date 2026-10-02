<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Markdown and code: text written in Markdown and rendered safely by
 * the panel (raw HTML is shown as text), and highlighted code blocks with a
 * copy button. The documentation of this stand is made of the same blocks.
 */
final class MarkdownScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-markdown';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'file-text';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Markdown';
    }

    public function description(): ?string
    {
        return 'Layout::markdown() with a table of contents and callouts; Layout::code().';
    }

    protected function demo(): array
    {
        $fence = '```';

        return [
            Layout::markdown(implode("\n\n", [
                '## '.__('Release notes'),
                __('Everything on this card is one `Layout::markdown()` call with `toc()`: the contents on the right are built from the headings.'),
                '### '.__('Text'),
                __('**Bold**, *italic*, ~~struck~~, `inline code` and [a link to the docs](docs-layouts-reference#markdown).'),
                '- '.__('A list item')."\n- ".__('Another one')."\n\n1. ".__('First')."\n2. ".__('Second'),
                '### '.__('Tables'),
                '| '.__('Plan').' | '.__('Seats').' | '.__('Price').' |'."\n|:---|:---:|---:|\n| Starter | 1 | \$0 |\n| Team | 10 | \$49 |\n| Enterprise | ∞ | ".__('Call us').' |',
                '### '.__('Callouts'),
                '> **'.__('Note').'** '.__('Useful information the reader should know.'),
                '> **'.__('Tip').'** '.__('A better way to do something.'),
                '> **'.__('Important').'** '.__('Key information for success.'),
                '> **'.__('Warning').'** '.__('Needs attention to avoid problems.'),
                '> **'.__('Caution').'** '.__('A risk of losing data.'),
                '### '.__('Code fences'),
                "{$fence}php\nTableColumn::make('total')->asMoney('USD')->sort();\n{$fence}",
                "{$fence}bash\ncomposer require dskripchenko/laravel-admin\n{$fence}",
                '<b>'.__('Raw HTML is escaped and shown as text.').'</b>',
            ]))->toc()->tocLabel(__('On this card'))->linkBase('/admin/screens/')->card(),

            Layout::columns([
                Layout::code(<<<'JSON'
                    {
                      "message": "Saved",
                      "alerts": [{ "type": "success", "message": "Thanks!" }],
                      "refresh": true
                    }
                    JSON, 'json')->title('A screen method response'),
                Layout::code('php artisan admin:install && php artisan admin:user "Ada" ada@example.com secret --super && php artisan serve --port=8000', 'bash')
                    ->title('Long lines wrap')
                    ->wrap(),
            ]),

            Layout::code(implode("\n", array_map(
                fn (int $i) => "Route::get('/reports/{$i}', [ReportController::class, 'show'])->name('reports.{$i}');",
                range(1, 30),
            )), 'php')->title('routes/reports.php')->lineNumbers()->maxHeight(240),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}
