<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permiso;

class ModulosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Roles Base
        Role::firstOrCreate(['nombre' => 'Administrador General']);
        Role::firstOrCreate(['nombre' => 'Gerente de Sucursal']);
        Role::firstOrCreate(['nombre' => 'Vendedor']);
        Role::firstOrCreate(['nombre' => 'Cajero']);

        // 2. Lista de Módulos del Sistema
        $modulos = [
            'Dashboard'            => 'dashboard',
            'Punto de Venta'       => 'puntodeventa',
            'Historial de Ventas'  => 'ventas',
            'Caja'                 => 'caja',
            'Flujo de Caja'        => 'flujocaja',
            'Historial de Caja'    => 'historialcaja',
            'Inventario'           => 'inventario',
            'Historial Inventario' => 'historialinventario',
            'Clientes'             => 'clientes',
            'Reportes'             => 'reportes',
            'Empleados'            => 'empleados',
            'Roles y Permisos'     => 'roles',
            'Sucursales'           => 'sucursales',
        ];

        // 3. Generar permisos CRUD (Ver, Crear, Editar, Eliminar) para cada módulo
        foreach ($modulos as $nombreModulo => $prefixRuta) {
            $acciones = [
                'Ver'      => "{$prefixRuta}.index",
                'Crear'    => "{$prefixRuta}.create",
                'Editar'   => "{$prefixRuta}.edit",
                'Eliminar' => "{$prefixRuta}.destroy",
            ];

            foreach ($acciones as $accion => $ruta) {
                Permiso::firstOrCreate(
                    ['ruta' => $ruta],
                    [
                        'nombre' => "{$accion} {$nombreModulo}",
                        'modulo' => $nombreModulo,
                    ]
                );
            }
        }
    }
}