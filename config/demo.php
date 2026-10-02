<?php

/*
|--------------------------------------------------------------------------
| The demo stand
|--------------------------------------------------------------------------
| The accounts the seeder creates and the login page offers as one-click
| buttons (when ADMIN_DEMO=true), and the reset schedule. The passwords are
| shown to every visitor of the login page: these accounts exist for the demo
| only.
*/

$password = env('DEMO_PASSWORD', 'demo');

return [
    'accounts' => [
        [
            'role' => 'super-admin',
            'name' => 'Ada Admin',
            'email' => 'admin@demo.test',
            'password' => $password,
            'label' => 'Administrator',
            'description' => 'Everything, including users and roles',
        ],
        [
            'role' => 'editor',
            'name' => 'Eddie Editor',
            'email' => 'editor@demo.test',
            'password' => $password,
            'label' => 'Editor',
            'description' => 'Catalog and blog only',
        ],
        [
            'role' => 'viewer',
            'name' => 'Vera Viewer',
            'email' => 'viewer@demo.test',
            'password' => $password,
            'label' => 'Viewer',
            'description' => 'Read-only access to everything',
        ],
    ],

    // Cron expression of the `demo:reset` schedule; empty switches it off.
    'reset_cron' => env('DEMO_RESET_CRON', '0 * * * *'),

    // Where "Edit on GitHub" links of the documentation pages point.
    'docs_repository' => env('DEMO_DOCS_REPOSITORY', 'https://github.com/dskripchenko/laravel-admin'),
    'docs_branch' => env('DEMO_DOCS_BRANCH', 'main'),
];
