<?php

namespace App\Admin\Screens\Docs\Pages;

use App\Admin\Screens\Docs\DocsPageScreen;

final class DemoModePage extends DocsPageScreen
{
    public static function page(): string
    {
        return 'demo-mode';
    }
}
