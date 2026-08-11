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
        // Registro de middleware global para el grupo 'web' (evitar caché de historial)
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        // Registro de los alias para Middlewares
        $middleware->alias([
            'permiso' => \App\Http\Middleware\CheckPermiso::class,
            'caja.abierta' => \App\Http\Middleware\VerificarCajaAbierta::class, // <--- NUESTRO NUEVO CANDADO
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();