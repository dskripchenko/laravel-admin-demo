<?php

namespace App\Admin\Screens\Docs\Pages;

use App\Admin\Screens\Docs\DocsPageScreen;

final class PacksOverviewPage extends DocsPageScreen
{
    public static function page(): string
    {
        return 'sister-packs/README';
    }
}
