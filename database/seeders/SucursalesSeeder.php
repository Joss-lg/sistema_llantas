<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;
use App\Models\Role;
use App\Models\User;

class SucursalesSeeder extends Seeder
{
    /**
     * Se puede correr varias veces sin duplicar nada (usa firstOrCreate).
     */
    public function run(): void
    {
        // 1. Crear las 5 sucursales base (si ya existen, no se duplican)
        $adminSucursal = Sucursal::firstOrCreate(['nombre' => 'Administración General'], ['activa' => true]);
        Sucursal::firstOrCreate(['nombre' => 'Chalco'], ['activa' => true]);
        Sucursal::firstOrCreate(['nombre' => 'Atlanta'], ['activa' => true]);
        Sucursal::firstOrCreate(['nombre' => 'Las Torres'], ['activa' => true]);
        Sucursal::firstOrCreate(['nombre' => 'Valle de Chalco'], ['activa' => true]);

        // 2. Rol de Administrador General (se asegura que exista antes de asignarlo)
        $rolAdmin = Role::firstOrCreate(['nombre' => 'Administrador General']);

        // 3. Crear el Usuario Administrador General (si ya existe, NO se cambia su contraseña)
        $admin = User::firstOrCreate(
            ['email' => 'admin@llantas.com'],
            [
                'name'        => 'Administrador General',
                'password'    => '12345678',
                'sucursal_id' => $adminSucursal->id,
                'rol_id'      => $rolAdmin->id,
                'activo'      => true,
            ]
        );

        // 4. Si el admin ya existía pero SIN rol, se le asigna el de Administrador General
        if (!$admin->rol_id) {
            $admin->rol_id = $rolAdmin->id;
            $admin->save();
        }
    }
}