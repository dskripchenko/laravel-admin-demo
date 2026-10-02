<?php

namespace App\Admin\Showcase\Actions;

use App\Admin\Showcase\ShowcaseGroup;

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
        return [];
    }
}
