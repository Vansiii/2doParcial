<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleTokenMismatch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si hay un error 419 (token mismatch), redirigir al login con mensaje
        if ($response->getStatusCode() === 419) {
            // Limpiar la sesión
            $request->session()->flush();
            $request->session()->regenerate();
            
            return redirect()->route('login')
                ->with('error', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        return $response;
    }
}
