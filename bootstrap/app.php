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
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        
        // Agregar middleware global para manejar errores 419
        $middleware->append(\App\Http\Middleware\HandleTokenMismatch::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejar error 419 - Token Mismatch (CSRF)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Limpiar y regenerar la sesión
            if ($request->hasSession()) {
                $request->session()->flush();
                $request->session()->regenerate();
            }
            
            return redirect()->route('login')
                ->with('error', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        });
    })->create();
