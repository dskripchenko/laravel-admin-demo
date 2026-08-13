<?php

declare(strict_types=1);
use App\Admin\DemoPlugin;
use Dskripchenko\LaravelAdmin\Models\AdminUser;

/**
 * The demo config of dskripchenko/laravel-admin.
 *
 * Every plugin is enabled — the stand shows the full set of features. For a real
 * project keep only the packages you need in plugins[].
 *
 * For the full set of settings see the core's config/admin.php (published by
 * `php artisan vendor:publish --tag=admin-config`).
 */

return [
    'path' => env('ADMIN_PATH', 'admin'),
    'api_path' => env('ADMIN_API_PATH', 'api/admin'),

    'auth' => [
        'strategy' => 'dedicated',
        'guard' => 'admin',
        'provider' => 'admin_users',
        'model' => AdminUser::class,
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
     * The sister-pack plugins. Each of them registers its own resources and
     * routes automatically (through Laravel's auto-discovery plus the
     * RegistersAdminPlugin trait in every pack's service provider).
     *
     * This array is kept for compatibility — in fact the plugins are added
     * through `config['admin.plugins'][] = ...` in the register() of the
     * packages' providers.
     */
    'plugins' => [
        // The sister packs register themselves through the RegistersAdminPlugin
        // trait. Only the host project goes here:
        DemoPlugin::class,
    ],

    /**
     * The frontend SPA assets — they are pulled in through the Vite manifest.
     *
     * Vite writes `public/build/manifest.json` on `npm run build`
     * (laravel-vite-plugin puts the manifest at the standard `.vite/manifest.json`
     * path). The core's ShellController parses the manifest and resolves the CSS
     * and JS chunks of the given entry.
     *
     * Substituting through config('admin.assets.css|js') is possible as an
     * override.
     */
    'assets' => [
        'vite_manifest' => public_path('build/manifest.json'),
        'vite_entry' => 'resources/js/admin.js',
        'vite_base_url' => '/build/',
        'css' => [],
        'js' => [],
    ],
];
