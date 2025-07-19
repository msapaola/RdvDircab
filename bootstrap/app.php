<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
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
            'disable.csrf.appointments' => \App\Http\Middleware\DisableCsrfForAppointments::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Apply custom middleware to disable CSRF for appointments
        $middleware->prependToGroup('web', \App\Http\Middleware\DisableCsrfForAppointments::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
