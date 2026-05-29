<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isEmployee()) {
            return redirect()->route('empleados.login')
                ->with('error', 'Debes iniciar sesión para acceder al panel.');
        }

        if (! auth()->user()->active) {
            auth()->logout();
            return redirect()->route('empleados.login')
                ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
        }

        return $next($request);
    }
}
