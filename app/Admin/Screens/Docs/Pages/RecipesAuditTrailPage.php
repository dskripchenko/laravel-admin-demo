<?php

namespace App\Admin\Screens\Docs\Pages;

use App\Admin\Screens\Docs\DocsPageScreen;

final class RecipesAuditTrailPage extends DocsPageScreen
{
    public static function page(): string
    {
        return 'recipes/audit-trail';
    }
}
