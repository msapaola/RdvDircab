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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Enregistrer les middlewares personnalisés
        $middleware->alias([
            'throttle.appointments' => \App\Http\Middleware\ThrottleAppointments::class,
        ]);

        // Ne pas appliquer CSRF globalement - laisser les exclusions du middleware gérer
        // Le middleware VerifyCsrfToken a déjà les bonnes exclusions dans $except
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
