<?php

declare(strict_types=1);

/**
 * Demo-конфиг dskripchenko/laravel-admin.
 *
 * Все плагины активированы — стенд показывает полный набор функций.
 * Для реального проекта оставьте только нужные пакеты в plugins[].
 *
 * Полный набор настроек см. в core: config/admin.php (опубликован
 * `php artisan vendor:publish --tag=admin-config`).
 */

return [
    'path' => env('ADMIN_PATH', 'admin'),
    'api_path' => env('ADMIN_API_PATH', 'api/admin'),

    'auth' => [
        'strategy' => 'dedicated',
        'guard' => 'admin',
        'provider' => 'admin_users',
        'model' => Dskripchenko\LaravelAdmin\Models\AdminUser::class,
        'table' => 'admin_users',
        'password_broker' => 'admin_users',
        'login_throttle' => '5,1',
        'two_factor' => [
            'enabled' => true,
            'recovery_codes' => 8,
            'window' => 1,
            'enforce_for' => [],
        ],
        'impersonation' => [
            'enabled' => true,
            'permission' => 'admin.impersonate',
            'block_higher_powered' => true,
        ],
        'api_tokens' => [
            'enabled' => true,
            'rate_limit' => '60,1',
            'default_expiry' => null,
        ],
    ],

    'brand' => [
        'name' => env('ADMIN_BRAND_NAME', 'Laravel-Admin Demo'),
        'logo' => null,
        'favicon' => null,
    ],

    'ui' => [
        'default_theme' => 'light',
        'default_locale' => 'ru',
        'available_locales' => ['ru', 'en'],
    ],

    'audit' => [
        'enabled' => true,
        'retention_days' => 90,
    ],

    /**
     * Sister-pack плагины. Каждый авторегистрирует свои Resource'ы и routes
     * автоматически (через Laravel auto-discovery + RegistersAdminPlugin
     * trait в ServiceProvider'е каждого pack'а).
     *
     * Этот массив поддерживается совместимости ради — фактически плагины
     * добавляются через `config['admin.plugins'][] = ...` в register()
     * пакетных провайдеров.
     */
    'plugins' => [
        // pack'и сами регистрируются — список здесь только для документации.
    ],

    /**
     * Frontend SPA assets — подгружаются через Vite manifest.
     *
     * Vite пишет `public/build/manifest.json` на `npm run build` (laravel-vite-plugin
     * выкладывает manifest по стандартному пути `.vite/manifest.json`). Core ShellController
     * парсит manifest и резолвит chunks с CSS/JS для указанного entry.
     *
     * Подмена через config('admin.assets.css|js') возможна для override.
     */
    'assets' => [
        'vite_manifest' => public_path('build/manifest.json'),
        'vite_entry' => 'resources/js/admin.js',
        'vite_base_url' => '/build/',
        'css' => [],
        'js' => [],
    ],
];
