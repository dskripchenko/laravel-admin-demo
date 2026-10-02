<?php

namespace App\Admin\Showcase\Notifications;

use App\Admin\Showcase\ShowcaseGroup;

final class NotificationsGroup extends ShowcaseGroup
{
    public static function key(): string
    {
        return 'notifications';
    }

    public static function title(): string
    {
        return 'Notifications';
    }

    public static function about(): string
    {
        return 'Toasts, the notification centre, callouts, empty and error states.';
    }

    public static function icon(): string
    {
        return 'bell';
    }

    public static function screens(): array
    {
        return [
            ToastsScreen::class,
            NotificationCentreScreen::class,
            CalloutsScreen::class,
            StatesScreen::class,
        ];
    }
}
