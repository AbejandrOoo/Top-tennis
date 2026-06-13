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
        // Esta revision deja pasar solo a usuarios con rol de administrador
        // Es el candado principal antes de entrar a las pantallas de control
        if (Auth::check() && Auth::user()->rol === 'admin') {
            return $next($request);
        }

        // Si el usuario no tiene permiso se manda al panel normal con un aviso
        // Asi no queda perdido en una pagina sin salida
        return redirect('/dashboard')->with('error', 'Acceso denegado. Area exclusiva de administracion.');
    }
}
