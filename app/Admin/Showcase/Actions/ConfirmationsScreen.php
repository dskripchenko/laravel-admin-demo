<?php

namespace App\Admin\Showcase\Actions;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Actions › Confirmations: a question before the request. The server is not
 * called until the user agrees.
 */
final class ConfirmationsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-actions-confirm';
    }

    public static function group(): string
    {
        return 'actions';
    }

    public static function icon(): string
    {
        return 'help-circle';
    }

    public function name(): string
    {
        return 'Confirmations';
    }

    public function description(): ?string
    {
        return '->confirm() with a message, or with a title and button captions of its own.';
    }

    protected function demo(): array
    {
        return [
            Layout::markdown(implode("\n\n", [
                __('Press a button in the top right corner. Each one asks first; cancelling sends nothing to the server.'),
                '- **'.__('Publish').'** — '.__('`confirm(\'…\')`: a message under the default title.'),
                '- **'.__('Reset the counters').'** — '.__('`confirm([...])`: its own title and captions of both buttons.'),
                '- **'.__('Delete everything').'** — '.__('`destructive()` plus a confirmation: the dialog\'s button turns red too.'),
            ]))->card(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Publish')->method('publish')->icon('send')
                ->confirm('Publish the article now?'),
            Button::make('Reset the counters')->method('resetCounters')->icon('rotate-ccw')
                ->confirm([
                    'title' => 'Reset the counters?',
                    'message' => 'Views and downloads start from zero again.',
                    'confirmLabel' => 'Reset',
                    'cancelLabel' => 'Keep them',
                ]),
            Button::make('Delete everything')->method('deleteEverything')->icon('trash-2')->destructive()
                ->confirm('This cannot be undone. Delete everything?'),
        ];
    }

    public function publish(): array
    {
        return ['message' => __('Published.')];
    }

    public function resetCounters(): array
    {
        return ['message' => __('The counters are back to zero.')];
    }

    public function deleteEverything(): array
    {
        return ['message' => __('Nothing was deleted: this is the showcase.')];
    }
}
