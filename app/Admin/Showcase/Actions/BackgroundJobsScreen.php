<?php

namespace App\Admin\Showcase\Actions;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\AsyncAction;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Actions › Background jobs: an AsyncAction starts a delayed process in the
 * queue and the panel polls it until it finishes. The handler must be
 * allowlisted — see ActionsGroup::boot().
 */
final class BackgroundJobsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-actions-background';
    }

    public static function group(): string
    {
        return 'actions';
    }

    public static function icon(): string
    {
        return 'cpu';
    }

    public function name(): string
    {
        return 'Background jobs';
    }

    public function description(): ?string
    {
        return 'AsyncAction: a long operation in the queue, with a progress dialog.';
    }

    protected function demo(): array
    {
        return [
            Layout::markdown(implode("\n\n", [
                __('**Build a report** starts `ReportBuilder::build()` as a delayed process: the request returns at once with the process id, a queue worker runs the handler, and the panel polls `delayed/status` every two seconds, showing a progress dialog until the handler returns its message.'),
                __('**Big report** passes other parameters through `withParams()` and polls less often.'),
                '> **Note** '.__('Only allowlisted handlers can be started: `AllowlistRegistrar::allow(ReportBuilder::class, \'build\')`. Without it the SPA gets 403, whatever the request says.'),
            ]))->card(),
        ];
    }

    public function commandBar(): array
    {
        return [
            AsyncAction::make('Build a report')->icon('file-text')->primary()
                ->handler(ReportBuilder::class, 'build')
                ->withParams(['rows' => 500, 'format' => 'csv'])
                ->pollInterval(2),
            AsyncAction::make('Big report')->icon('database')
                ->handler(ReportBuilder::class, 'build')
                ->withParams(['rows' => 2000, 'format' => 'xlsx'])
                ->pollInterval(3)
                ->confirm('It takes about six seconds. Start?'),
        ];
    }

    protected function sourceClasses(): array
    {
        return [ReportBuilder::class, ActionsGroup::class];
    }
}
