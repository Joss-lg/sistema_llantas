<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Los productos NO se cargan aquí: se dan de alta desde el sistema (Inventario)
        // para que cada uno quede registrado en la base de datos.
        $this->call([
            SucursalesSeeder::class,
            ModulosSeeder::class,
        ]);
    }
}