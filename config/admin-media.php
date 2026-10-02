<?php

declare(strict_types=1);

return [
    'disk' => env('ADMIN_MEDIA_DISK', 'public'),
    'path_prefix' => 'media',

    'allowed_mimes' => [
        // No SVG: on a public stand an uploaded SVG could carry script.
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'application/pdf',
        'video/mp4', 'video/webm',
        'audio/mpeg', 'audio/wav',
    ],

    'max_size_mb' => 50,

    'collections' => [
        'default' => ['label' => 'General'],
        'products' => ['label' => 'Products'],
        'posts' => ['label' => 'Blog'],
        'avatars' => ['label' => 'Avatars'],
    ],

    'responsive_sets' => [
        'product' => [
            ['name' => 'thumb', 'width' => 240, 'format' => 'webp', 'quality' => 80],
            ['name' => 'w-768', 'width' => 768, 'format' => 'webp', 'quality' => 85],
        ],
        'content' => [
            ['name' => 'thumb', 'width' => 200, 'format' => 'webp', 'quality' => 80],
            ['name' => 'w-768', 'width' => 768, 'format' => 'webp', 'quality' => 85],
            ['name' => 'w-1280', 'width' => 1280, 'format' => 'webp', 'quality' => 85],
        ],
        'avatar' => [
            // Resized, not cropped: laravel-admin-media 1.5.0 fails to crop an
            // image that has no focal point yet.
            ['name' => 'sm', 'width' => 64, 'format' => 'webp'],
            ['name' => 'md', 'width' => 128, 'format' => 'webp'],
        ],
    ],

    'image_processor' => [
        'driver' => 'auto',
        'strip_exif' => true,
        'auto_orient' => true,
    ],

    'cleanup' => [
        'orphan_after_days' => 30,
    ],
];
