<?php

namespace Tests\Concerns;

use App\Models\Cliente;
use App\Models\CorteCaja;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Str;

/**
 * Ayudas para las pruebas de ventas: productos, stock, clientes, turnos y ventas.
 * Se usa junto con CreaUsuarios (que crea usuarios, roles y permisos).
 */
trait CreaVentas
{
    /** Usuario con los permisos del punto de venta (por usuario, no por rol). */
    protected function cajeroDeVentas(?Sucursal $sucursal = null, string $rol = 'Cajero'): User
    {
        $usuario = $this->crearUsuario($rol, $sucursal);

        foreach (['ventas.index', 'ventas.store', 'ventas.ticket', 'ventas.historial'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    protected function abrirTurno(User $usuario, float $saldoInicial = 500): CorteCaja
    {
        return CorteCaja::create([
            'user_id'        => $usuario->id,
            'sucursal_id'    => $usuario->sucursal_id,
            'saldo_inicial'  => $saldoInicial,
            'estado'         => 'abierta',
            'fecha_apertura' => now(),
        ]);
    }

    /** Una llanta: público $900, mayoreo $700, costo $500. */
    protected function crearProducto(array $datos = []): Producto
    {
        return Producto::create(array_merge([
            'tipo'           => 'Llanta',
            'marca'          => 'PIRELLI',
            'medida'         => '205/55R16',
            'costo'          => 500,
            'precio_publico' => 900,
            'precio_mayoreo' => 700,
            'estado'         => true,
        ], $datos));
    }

    protected function darStock(Producto $producto, Sucursal $sucursal, int $cantidad): StockSucursal
    {
        return StockSucursal::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $sucursal->id,
            'cantidad'    => $cantidad,
        ]);
    }

    protected function crearCliente(array $datos = []): Cliente
    {
        return Cliente::create(array_merge(['nombre' => 'Cliente de Prueba'], $datos));
    }

    /** Una venta guardada directo en la base (sin pasar por el punto de venta). */
    protected function crearVenta(User $usuario, Sucursal $sucursal, float $total = 900): Venta
    {
        return Venta::create([
            'folio'       => 'VNT-' . Str::upper(Str::random(10)),
            'sucursal_id' => $sucursal->id,
            'user_id'     => $usuario->id,
            'total'       => $total,
            'metodo_pago' => 'Efectivo',
            'fecha'       => now(),
        ]);
    }
}