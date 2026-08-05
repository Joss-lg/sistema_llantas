<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Centraliza la lógica de "quién es el usuario y qué sucursal le corresponde",
 * que antes estaba copiada en casi todos los métodos del InventarioController.
 *
 * NOTA: se mantiene el mismo criterio original (id === 1 || rol Administrador General).
 * Es un candidato a mejora futura: lo ideal es que "superadmin" sea solo un rol,
 * no un ID fijo.
 */
trait ResuelveContextoSucursal
{
    protected function usuarioEsAdmin(): bool
    {
        $usuario = Auth::user();
        return $usuario->id === 1 || $usuario->tieneRol('Administrador General');
    }

    protected function sucursalDelUsuario(): int
    {
        return Auth::user()->sucursal_id ?? 1;
    }

    /**
     * Sucursal que se debe usar para filtrar/operar:
     * - Admin: la que venga en el request (puede ser null = "todas")
     * - No admin: siempre la suya
     */
    protected function sucursalSeleccionada(Request $request): ?int
    {
        return $this->usuarioEsAdmin()
            ? $request->input('sucursal_id')
            : $this->sucursalDelUsuario();
    }

    protected function sucursalesDisponibles(): Collection
    {
        return $this->usuarioEsAdmin()
            ? Sucursal::all()
            : Sucursal::where('id', $this->sucursalDelUsuario())->get();
    }
}