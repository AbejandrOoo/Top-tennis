<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado y su rol es 'admin', se le permite continuar
        if (Auth::check() && Auth::user()->rol === 'admin') {
            return $next($request);
        }

        // Si no es administrador, lo mandamos al dashboard de clientes con una alerta
        return redirect('/dashboard')->with('error', 'Acceso denegado. Área exclusiva de administración.');
    }
}