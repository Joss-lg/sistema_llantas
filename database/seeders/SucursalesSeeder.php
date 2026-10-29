<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Sucursal;
use App\Models\Role;
use App\Models\User;

class SucursalesSeeder extends Seeder
{
    /**
     * Se puede correr varias veces sin duplicar nada (usa firstOrCreate).
     *
     * La contraseña del primer administrador se toma de la variable
     * ADMIN_PASSWORD del archivo .env. Si no existe, se genera una
     * aleatoria y se muestra una sola vez en la terminal.
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

        // 3. Contraseña inicial del administrador (nunca escrita en el código)
        $passwordInicial = env('ADMIN_PASSWORD');
        $fueGenerada     = false;

        if (empty($passwordInicial)) {
            $passwordInicial = Str::random(16);
            $fueGenerada     = true;
        } elseif (strlen($passwordInicial) < 12) {
            throw new \RuntimeException('ADMIN_PASSWORD debe tener al menos 12 caracteres.');
        }

        // 4. Crear el Usuario Administrador General (si ya existe, NO se cambia su contraseña)
        $admin = User::firstOrCreate(
            ['email' => 'admin@llantas.com'],
            [
                'name'        => 'Administrador General',
                'password'    => $passwordInicial,
                'sucursal_id' => $adminSucursal->id,
                'rol_id'      => $rolAdmin->id,
                'activo'      => true,
            ]
        );

        // 5. Si se acaba de crear con contraseña aleatoria, mostrarla una sola vez
        if ($admin->wasRecentlyCreated && $fueGenerada) {
            $this->command->warn('Administrador creado: admin@llantas.com');
            $this->command->warn('Contraseña generada (guárdala, no se volverá a mostrar): ' . $passwordInicial);
        }

        // 6. Si el admin ya existía pero SIN rol, se le asigna el de Administrador General
        if (!$admin->rol_id) {
            $admin->rol_id = $rolAdmin->id;
            $admin->save();
        }
    }
}