<?php

namespace App\Admin\Screens\Docs\Pages;

use App\Admin\Screens\Docs\DocsPageScreen;

final class RecipesSoftDeletesPage extends DocsPageScreen
{
    public static function page(): string
    {
        return 'recipes/soft-deletes';
    }
}
