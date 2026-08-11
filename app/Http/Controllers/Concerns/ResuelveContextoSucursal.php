<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait ResuelveContextoSucursal
{
    protected function usuarioEsAdmin(): bool
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return false;
        }

        // Eliminado id === 1 hardcodeado. Basado puramente en roles/permisos.
        if (method_exists($usuario, 'tieneRol')) {
            return $usuario->tieneRol('Administrador General');
        }

        return $usuario->role?->nombre === 'Administrador General';
    }

    protected function sucursalDelUsuario(): ?int
    {
        return Auth::user()?->sucursal_id;
    }

    /**
     * Sucursal que se debe usar para filtrar/operar:
     * - Admin: la que venga en el request (si es 'todas' o vacía, retorna null)
     * - No admin: siempre la suya asignada
     */
    protected function sucursalSeleccionada(Request $request): ?int
    {
        if ($this->usuarioEsAdmin()) {
            $val = $request->input('sucursal_id');
            return ($val === 'todas' || empty($val)) ? null : (int) $val;
        }

        return $this->sucursalDelUsuario();
    }

    protected function sucursalesDisponibles(): Collection
    {
        return $this->usuarioEsAdmin()
            ? Sucursal::where('activa', true)->get()
            : Sucursal::where('id', $this->sucursalDelUsuario())->where('activa', true)->get();
    }
}