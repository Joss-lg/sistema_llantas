<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Llamar primero a los seeders de tablas independientes o maestras (como sucursales)
        $this->call([
            SucursalesSeeder::class,
            ProductoSeeder::class, 
        ]);

        // 2. Crear el usuario administrador principal para acceder al sistema
        User::create([
            'name'     => 'Administrador Chalco',
            'email'    => 'admin@llantas.com',
            'password' => Hash::make('12345678'), // Contraseña por defecto
        ]);
    }
}
