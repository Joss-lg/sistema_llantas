<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Llamar primero a los seeders de tablas independientes o maestras
        $this->call([
            SucursalesSeeder::class,
            ProductoSeeder::class, 
        ]);

        // 2. Crear el usuario administrador principal (sin Hash::make para evitar doble encriptado)
        User::firstOrCreate(
            ['email' => 'admin@llantas.com'],
            [
                'name'     => 'Administrador Principal',
                'password' => '12345678', // El modelo User lo encripta automáticamente
            ]
        );
    }
}