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
     * Sister-pack плагины. Каждый авторегистрирует свои Resource'ы и routes.
     */
    'plugins' => [
        Dskripchenko\LaravelAdminStarter\AdminStarterPlugin::class,
        Dskripchenko\LaravelAdminHealth\AdminHealthPlugin::class,
        Dskripchenko\LaravelAdminJobs\AdminJobsPlugin::class,
        Dskripchenko\LaravelAdminMedia\AdminMediaPlugin::class,
        Dskripchenko\LaravelAdminPulse\AdminPulsePlugin::class,
        Dskripchenko\LaravelAdminSearch\AdminSearchPlugin::class,
        Dskripchenko\LaravelAdminQuill\AdminQuillPlugin::class,
        Dskripchenko\LaravelAdminTinymce\AdminTinymcePlugin::class,
    ],

    /**
     * Demo-Resource'ы host-проекта. Регистрируются вручную (плагины
     * регистрируют свои Resource'ы автоматически).
     */
    'resources' => [
        App\Admin\Resources\ArticleResource::class,
        App\Admin\Resources\ProductResource::class,
        App\Admin\Resources\OrderResource::class,
    ],
];
