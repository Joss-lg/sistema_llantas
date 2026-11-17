<?php

namespace Tests\Concerns;

use App\Models\Permiso;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;

/**
 * Ayudas para crear datos de prueba (sucursales, roles, usuarios y permisos)
 * sin depender de los seeders.
 */
trait CreaUsuarios
{
    protected function crearSucursal(string $nombre = 'Chalco', bool $activa = true): Sucursal
    {
        return Sucursal::create(['nombre' => $nombre, 'activa' => $activa]);
    }

    /**
     * Crea un usuario con el rol indicado (por nombre) y en la sucursal dada.
     * Si no se da sucursal, se crea una nueva llamada "Chalco".
     */
    protected function crearUsuario(string $rol = 'Cajero', ?Sucursal $sucursal = null, array $atributos = []): User
    {
        $sucursal ??= $this->crearSucursal();
        $rolModelo = Role::firstOrCreate(['nombre' => $rol]);

        return User::factory()->create(array_merge([
            'sucursal_id' => $sucursal->id,
            'rol_id'      => $rolModelo->id,
            'activo'      => true,
        ], $atributos));
    }

    /**
     * Le da a un usuario el permiso de una ruta (ej: "gastos.index").
     * Los permisos se guardan POR USUARIO, en la tabla permiso_user.
     */
    protected function darPermiso(User $usuario, string $ruta): Permiso
    {
        $permiso = Permiso::firstOrCreate(
            ['ruta' => $ruta],
            ['nombre' => "Permiso {$ruta}", 'modulo' => 'Pruebas']
        );

        $usuario->permisos()->attach($permiso->id);

        return $permiso;
    }
}