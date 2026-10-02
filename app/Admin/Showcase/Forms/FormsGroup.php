<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseGroup;

final class FormsGroup extends ShowcaseGroup
{
    public static function key(): string
    {
        return 'forms';
    }

    public static function title(): string
    {
        return 'Forms';
    }

    public static function about(): string
    {
        return 'Every field type, validation, dependent fields, read-only views.';
    }

    public static function icon(): string
    {
        return 'clipboard-list';
    }

    public static function screens(): array
    {
        return [
            FormBasicsScreen::class,
            TextInputsScreen::class,
            ChoicesScreen::class,
            DatesScreen::class,
            NumbersScreen::class,
            RichTextScreen::class,
            FilesScreen::class,
            RelationsScreen::class,
            CompositeScreen::class,
            ValidationScreen::class,
            DependentFieldsScreen::class,
            ReadOnlyScreen::class,
        ];
    }
}
