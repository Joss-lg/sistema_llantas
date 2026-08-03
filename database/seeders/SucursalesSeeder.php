<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal; // <-- Importación corregida en singular
use App\Models\User;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear las 5 sucursales base
        $adminSucursal = Sucursal::create(['nombre' => 'Administración General', 'activa' => true]);
        Sucursal::create(['nombre' => 'Chalco', 'activa' => true]);
        Sucursal::create(['nombre' => 'Atlanta', 'activa' => true]);
        Sucursal::create(['nombre' => 'Las Torres', 'activa' => true]);
        Sucursal::create(['nombre' => 'Valle de Chalco', 'activa' => true]);

        // 2. Crear el Usuario Administrador General
        User::firstOrCreate(
            ['email' => 'admin@llantas.com'],
            [
                'name'        => 'Administrador General',
                'password'    => '12345678',
                'sucursal_id' => $adminSucursal->id,
                'activo'      => true,
            ]
        );
    }
}