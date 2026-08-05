<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    /**
     * Rutas autenticadas a las que cualquier usuario activo puede acceder
     * sin necesidad de estar registradas en la tabla 'permisos'.
     */
    protected array $rutasExentas = [
        'dashboard',
        'logout.back',
        'home',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Si no hay usuario autenticado, redirigir al login
        if (! $user) {
            return redirect()->route('login');
        }

        // 2. Si el usuario fue desactivado en el sistema, cerrar sesión
        if (! $user->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Tu cuenta se encuentra inactiva. Contacta al Administrador.');
        }

        // 3. Obtener el nombre de la ruta actual
        $nombreRuta = $request->route() ? $request->route()->getName() : null;

        // 4. Permitir acceso si la ruta es exenta de permisos individuales (ej: dashboard)
        if ($nombreRuta && in_array($nombreRuta, $this->rutasExentas, true)) {
            return $next($request);
        }

        // 5. Validar si la ruta tiene nombre y el usuario carece del permiso asignado
        if ($nombreRuta && ! $user->tienePermiso($nombreRuta)) {
            abort(403, 'No tienes permisos suficientes para realizar esta acción o acceder a este módulo.');
        }

        return $next($request);
    }
}