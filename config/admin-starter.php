<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Which resources to register
    |--------------------------------------------------------------------------
    | Every key is a toggle for one of the resources the package ships. All of
    | them are on by default; set a key to false to keep that resource out of
    | the panel. The permissions are registered regardless, so roles that
    | already reference them stay valid.
    */

    'resources' => [
        'users' => true,
        'roles' => true,
        'audit_log' => true,
    ],
];
