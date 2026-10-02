<?php

namespace App\Admin\Screens\Docs\Pages;

use App\Admin\Screens\Docs\DocsPageScreen;

final class ApiAuthPage extends DocsPageScreen
{
    public static function page(): string
    {
        return 'api/auth';
    }
}
