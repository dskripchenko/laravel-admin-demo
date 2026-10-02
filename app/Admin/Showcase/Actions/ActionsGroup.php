<?php

namespace App\Admin\Showcase\Actions;

use App\Admin\Showcase\ShowcaseGroup;
use Dskripchenko\LaravelAdmin\DelayedProcess\AllowlistRegistrar;

final class ActionsGroup extends ShowcaseGroup
{
    public static function key(): string
    {
        return 'actions';
    }

    public static function title(): string
    {
        return 'Actions';
    }

    public static function about(): string
    {
        return 'Buttons, confirmations, modal forms, downloads, background jobs.';
    }

    public static function icon(): string
    {
        return 'zap';
    }

    public static function screens(): array
    {
        return [
            ButtonsScreen::class,
            ConfirmationsScreen::class,
            ModalFormsScreen::class,
            ResponsesScreen::class,
            BackgroundJobsScreen::class,
            PermissionsScreen::class,
        ];
    }

    /** The background handler of BackgroundJobsScreen may be started from the panel. */
    public static function boot(): void
    {
        app(AllowlistRegistrar::class)->allow(ReportBuilder::class, 'build');
    }
}
