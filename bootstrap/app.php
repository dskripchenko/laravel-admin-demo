<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The admin API uses session cookies (the web middleware) for the
        // authentication. CSRF protection is redundant for a same-origin XHR
        // (axios with withCredentials) and creates a race condition on
        // session.regenerate() after a login. The browser's same-origin policy
        // plus authorization through the session is protection enough.
        $middleware->validateCsrfTokens(except: [
            'api/admin/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
