<?php

// The @dskripchenko/embed showcase: a plain HTML page (no admin panel) with
// every embed element cooperating. Its assets are static files under
// public/embed/ (served by FrankenPHP directly); these routes only hand out
// the page itself, since the server looks for index.php, not index.html.
//
// Include from routes/web.php:  require __DIR__.'/embed.php';

use Illuminate\Support\Facades\Route;

$page = static fn (string $file) => response()->file(public_path("embed/{$file}"), [
    'Content-Type' => 'text/html; charset=utf-8',
    'Cache-Control' => 'public, max-age=300',
]);

// From jsDelivr: works once @dskripchenko/embed is published.
Route::get('/embed', fn () => $page('index.html'))->name('embed');

// From public/embed/npm/ (scripts/mirror.mjs of the embed repo): the same page with no CDN at all.
Route::get('/embed/local', function () use ($page) {
    abort_unless(is_dir(public_path('embed/npm/@dskripchenko')), 404);

    return $page('local.html');
})->name('embed.local');
