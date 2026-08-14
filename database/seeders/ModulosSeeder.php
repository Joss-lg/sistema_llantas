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

        // 2. Permisos alineados EXACTAMENTE con los nombres de ruta reales
        $permisosPorModulo = [

            'Empleados' => [
                'empleados.index'   => 'Ver Listado de Empleados',
                'empleados.create'  => 'Ver Formulario de Nuevo Empleado',
                'empleados.store'   => 'Registrar Empleado',
                'empleados.edit'    => 'Ver Formulario de Edición de Empleado',
                'empleados.update'  => 'Actualizar Empleado',
                'empleados.destroy' => 'Eliminar Empleado',
                'empleados.inactivos' => 'Ver Empleados Inactivos',
                'empleados.toggle'  => 'Activar/Desactivar Empleado',
                'empleados.permisos.update' => 'Actualizar Permisos de Empleado',
            ],

            'Roles y Permisos' => [
                'roles.index'   => 'Ver Listado de Roles',
                'roles.create'  => 'Ver Formulario de Nuevo Rol',
                'roles.store'   => 'Registrar Rol',
                'roles.edit'    => 'Ver Formulario de Edición de Rol',
                'roles.update'  => 'Actualizar Rol',
                'roles.destroy' => 'Eliminar Rol',
            ],

            'Inventario' => [
                'inventario.index'           => 'Ver Inventario',
                'inventario.producto.store'  => 'Agregar Producto',
                'inventario.importar'        => 'Ver Formulario de Importación',
                'inventario.procesar'        => 'Procesar Importación de Inventario',
                'inventario.entrada.store'   => 'Registrar Entrada de Stock',
                'inventario.salida.store'    => 'Registrar Salida de Stock',
                'inventario.traspaso.store'  => 'Traspasar Stock entre Sucursales',
                'inventario.historial'       => 'Ver Historial de Inventario',
                'inventario.exportar.excel'  => 'Exportar Inventario a Excel',
                'inventario.exportar.pdf'    => 'Exportar Inventario a PDF',
                'inventario.disponibilidad'  => 'Ver Disponibilidad de Producto',
            ],

            'Caja' => [
                'caja.index'  => 'Ver Módulo de Caja',
                'caja.abrir'  => 'Abrir Caja',
                'caja.cerrar' => 'Cerrar Caja',
            ],

            'Ventas' => [
                'ventas.index'    => 'Ver Punto de Venta',
                'ventas.store'    => 'Cobrar Venta',
                'ventas.ticket'   => 'Ver Ticket de Venta',
                'ventas.historial'=> 'Ver Historial de Ventas',
            ],

            'Gastos' => [
                'gastos.index' => 'Ver Gastos / Corte de Caja',
            ],

            'Clientes' => [
                'clientes.index'   => 'Ver Listado de Clientes',
                'clientes.create'  => 'Ver Formulario de Nuevo Cliente',
                'clientes.store'   => 'Registrar Cliente',
                'clientes.show'    => 'Ver Detalles del Cliente',
                'clientes.edit'    => 'Ver Formulario de Edición de Cliente',
                'clientes.update'  => 'Actualizar Cliente',
                'clientes.destroy' => 'Eliminar Cliente',
            ],

            'Reportes' => [
                'reportes.index' => 'Ver Reportes',
            ],
        ];

        // 3. Crear/actualizar cada permiso respetando el nombre real de ruta
        foreach ($permisosPorModulo as $modulo => $rutas) {
            foreach ($rutas as $ruta => $nombre) {
                Permiso::updateOrCreate(
                    ['ruta' => $ruta],
                    [
                        'nombre' => $nombre,
                        'modulo' => $modulo,
                    ]
                );
            }
        }
    }
}