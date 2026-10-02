<?php

namespace App\Admin\Screens\Docs\Pages;

use App\Admin\Screens\Docs\DocsPageScreen;

final class ApiHealthPage extends DocsPageScreen
{
    public static function page(): string
    {
        return 'api/health';
    }
}
