<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Collaborative boards (dskripchenko/laravel-admin-board)
|--------------------------------------------------------------------------
| The stand is public and every board is wiped by the hourly reset, so the
| limits are below the package's (10 000 objects and 10 000 batches
| kept; a batch is the same 500 operations): enough for a crowded board, not for
| filling the SQLite file. There is no laravel-realtime here, so the board
| polls; every demo visitor of one account shares that account's API rate
| limit (admin.api.throttle), hence a calmer poll than the default 1.5 s.
*/

return [
    'limits' => [
        'max_ops' => 500,
        'max_objects' => 2_000,
    ],

    'keep' => 2_000,
    'poll_limit' => 500,

    'presence' => [
        'ttl' => 30,
        'store' => null,
    ],

    'poll_interval' => 2_500,
    'height' => 600,

    'realtime' => [
        'enabled' => false,
        'ticket_url' => null,
    ],

    'widget' => [
        'board' => null,
        'title' => 'Board',
    ],
];
