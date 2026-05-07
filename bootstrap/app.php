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
        // Admin API использует session cookies (web middleware) для auth.
        // CSRF-protection избыточна для same-origin XHR (axios + withCredentials)
        // и создаёт race condition при session.regenerate() после login.
        // Same-origin policy браузера + Authorization-via-session — достаточная защита.
        $middleware->validateCsrfTokens(except: [
            'api/admin/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
