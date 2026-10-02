<?php

namespace App\Admin\Screens\Docs\Pages;

use App\Admin\Screens\Docs\DocsPageScreen;

final class RecipesImportExportPage extends DocsPageScreen
{
    public static function page(): string
    {
        return 'recipes/import-export';
    }
}
