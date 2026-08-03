<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Si no hay usuario autenticado, mandar al login
        if (!$user) {
            return redirect()->route('login');
        }

        // Obtener el nombre de la ruta actual (ej: "empleados.index", "inventario.create")
        $nombreRuta = $request->route()->getName();

        // Si la ruta tiene nombre y el usuario no tiene permiso (y tampoco es Admin General)
        if ($nombreRuta && !$user->tienePermiso($nombreRuta)) {
            abort(403, 'No tienes permisos suficientes para realizar esta acción o acceder a este módulo.');
        }

        return $next($request);
    }
}