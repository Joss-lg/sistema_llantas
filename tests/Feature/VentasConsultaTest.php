<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Ticket e historial de ventas: cada usuario solo ve lo de su sucursal,
 * excepto el Administrador General.
 */
class VentasConsultaTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Venta con un renglon de detalle, para probar piezas y totales del historial.
     * $extra deja cambiar cualquier campo de la venta (fecha, metodo_pago, folio...).
     */
    private function ventaConDetalle(User $usuario, Sucursal $sucursal, Producto $producto, float $total, int $piezas, array $extra = []): Venta
    {
        $venta = Venta::create(array_merge([
            'folio'       => 'VNT-' . Str::upper(Str::random(10)),
            'sucursal_id' => $sucursal->id,
            'user_id'     => $usuario->id,
            'total'       => $total,
            'metodo_pago' => 'Efectivo',
            'fecha'       => now(),
        ], $extra));

        VentaDetalle::create([
            'venta_id'        => $venta->id,
            'producto_id'     => $producto->id,
            'nombre_producto' => 'PIRELLI 205/55R16',
            'cantidad'        => $piezas,
            'precio_unitario' => $total / $piezas,
            'descuento'       => 0,
            'subtotal'        => $total,
        ]);

        return $venta;
    }

    public function test_un_cajero_ve_el_ticket_de_una_venta_de_su_sucursal(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroDeVentas($chalco);
        $venta = $this->crearVenta($cajero, $chalco);

        $this->actingAs($cajero)
            ->get(route('ventas.ticket', $venta->id))
            ->assertOk();
    }

    public function test_un_cajero_no_puede_ver_el_ticket_de_otra_sucursal(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $cajeroChalco = $this->cajeroDeVentas($chalco);
        $cajeroValle = $this->cajeroDeVentas($valle);
        $ventaDeValle = $this->crearVenta($cajeroValle, $valle);

        // 404, igual que si la venta no existiera.
        $this->actingAs($cajeroChalco)
            ->get(route('ventas.ticket', $ventaDeValle->id))
            ->assertNotFound();
    }

    public function test_el_administrador_general_ve_el_ticket_de_cualquier_sucursal(): void
    {
        $valle = $this->crearSucursal('Valle de Chalco');
        $cajeroValle = $this->cajeroDeVentas($valle);
        $ventaDeValle = $this->crearVenta($cajeroValle, $valle);
        $admin = $this->cajeroDeVentas($this->crearSucursal('Administración General'), 'Administrador General');

        $this->actingAs($admin)
            ->get(route('ventas.ticket', $ventaDeValle->id))
            ->assertOk();
    }

    public function test_el_historial_de_un_cajero_solo_trae_ventas_de_su_sucursal(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $cajeroChalco = $this->cajeroDeVentas($chalco);
        $cajeroValle = $this->cajeroDeVentas($valle);
        $ventaChalco = $this->crearVenta($cajeroChalco, $chalco);
        $this->crearVenta($cajeroValle, $valle);

        $this->actingAs($cajeroChalco)
            ->get(route('ventas.historial'))
            ->assertOk()
            ->assertViewHas('ventas', fn ($ventas) => $ventas->pluck('id')->map(fn ($id) => (int) $id)->all() === [$ventaChalco->id]);
    }

    public function test_el_historial_del_administrador_general_trae_todas_las_sucursales(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $ventaChalco = $this->crearVenta($this->cajeroDeVentas($chalco), $chalco);
        $ventaValle = $this->crearVenta($this->cajeroDeVentas($valle), $valle);
        $admin = $this->cajeroDeVentas($this->crearSucursal('Administración General'), 'Administrador General');

        $esperados = [$ventaChalco->id, $ventaValle->id];
        sort($esperados);

        $this->actingAs($admin)
            ->get(route('ventas.historial'))
            ->assertOk()
            ->assertViewHas('ventas', function ($ventas) use ($esperados) {
                return $ventas->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all() === $esperados;
            });
    }

    public function test_los_totales_del_historial_cuentan_todo_el_filtro_y_no_solo_la_pagina(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroDeVentas($chalco);
        $producto = $this->crearProducto();

        // 17 ventas: la pagina trae 15, pero los totales deben contar las 17.
        for ($i = 0; $i < 17; $i++) {
            $this->ventaConDetalle($cajero, $chalco, $producto, 100, 2);
        }

        $this->actingAs($cajero)
            ->get(route('ventas.historial'))
            ->assertOk()
            ->assertViewHas('ventas', fn ($ventas) => $ventas->total() === 17 && $ventas->count() === 15)
            ->assertViewHas('totalFiltrado', 1700.0)
            ->assertViewHas('piezasFiltradas', 34);
    }

    public function test_los_totales_no_cambian_al_pasar_a_la_segunda_pagina(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroDeVentas($chalco);
        $producto = $this->crearProducto();

        for ($i = 0; $i < 17; $i++) {
            $this->ventaConDetalle($cajero, $chalco, $producto, 100, 2);
        }

        $this->actingAs($cajero)
            ->get(route('ventas.historial', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('ventas', fn ($ventas) => $ventas->count() === 2)
            ->assertViewHas('totalFiltrado', 1700.0)
            ->assertViewHas('piezasFiltradas', 34);
    }

    public function test_los_totales_de_un_cajero_no_incluyen_otras_sucursales(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $cajeroChalco = $this->cajeroDeVentas($chalco);
        $cajeroValle = $this->cajeroDeVentas($valle);
        $producto = $this->crearProducto();

        $this->ventaConDetalle($cajeroChalco, $chalco, $producto, 900, 1);
        $this->ventaConDetalle($cajeroValle, $valle, $producto, 5000, 3);

        $this->actingAs($cajeroChalco)
            ->get(route('ventas.historial'))
            ->assertOk()
            ->assertViewHas('totalFiltrado', 900.0)
            ->assertViewHas('piezasFiltradas', 1);
    }

    public function test_el_administrador_general_suma_todo_o_filtra_por_sucursal(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $producto = $this->crearProducto();
        $admin = $this->cajeroDeVentas($this->crearSucursal('Administración General'), 'Administrador General');

        $this->ventaConDetalle($this->cajeroDeVentas($chalco), $chalco, $producto, 900, 1);
        $this->ventaConDetalle($this->cajeroDeVentas($valle), $valle, $producto, 5000, 3);

        $this->actingAs($admin)
            ->get(route('ventas.historial'))
            ->assertViewHas('totalFiltrado', 5900.0)
            ->assertViewHas('piezasFiltradas', 4);

        $this->actingAs($admin)
            ->get(route('ventas.historial', ['sucursal_id' => $valle->id]))
            ->assertViewHas('totalFiltrado', 5000.0)
            ->assertViewHas('piezasFiltradas', 3);
    }

    public function test_los_totales_respetan_el_rango_de_fechas(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroDeVentas($chalco);
        $producto = $this->crearProducto();

        $this->ventaConDetalle($cajero, $chalco, $producto, 100, 1, ['fecha' => '2026-11-01 10:00:00']);
        // El ultimo dia del rango cuenta completo (hasta las 23:59:59).
        $this->ventaConDetalle($cajero, $chalco, $producto, 200, 2, ['fecha' => '2026-11-15 23:30:00']);
        $this->ventaConDetalle($cajero, $chalco, $producto, 400, 4, ['fecha' => '2026-11-20 09:00:00']);

        $this->actingAs($cajero)
            ->get(route('ventas.historial', ['fecha_inicio' => '2026-11-05', 'fecha_fin' => '2026-11-15']))
            ->assertOk()
            ->assertViewHas('ventas', fn ($ventas) => $ventas->total() === 1)
            ->assertViewHas('totalFiltrado', 200.0)
            ->assertViewHas('piezasFiltradas', 2);
    }

    public function test_los_totales_respetan_el_filtro_de_folio(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroDeVentas($chalco);
        $producto = $this->crearProducto();

        $this->ventaConDetalle($cajero, $chalco, $producto, 300, 1, ['folio' => 'VNT-AAA-111']);
        $this->ventaConDetalle($cajero, $chalco, $producto, 700, 2, ['folio' => 'VNT-BBB-222']);

        $this->actingAs($cajero)
            ->get(route('ventas.historial', ['folio' => 'BBB']))
            ->assertOk()
            ->assertViewHas('ventas', fn ($ventas) => $ventas->total() === 1)
            ->assertViewHas('totalFiltrado', 700.0)
            ->assertViewHas('piezasFiltradas', 2);
    }

    public function test_sin_ventas_los_totales_son_cero(): void
    {
        $cajero = $this->cajeroDeVentas($this->crearSucursal('Chalco'));

        $this->actingAs($cajero)
            ->get(route('ventas.historial'))
            ->assertOk()
            ->assertViewHas('totalFiltrado', 0.0)
            ->assertViewHas('piezasFiltradas', 0);
    }

    public function test_el_historial_carga_sucursal_cajero_y_detalles_juntos(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroDeVentas($chalco);
        $producto = $this->crearProducto();

        $this->ventaConDetalle($cajero, $chalco, $producto, 900, 1);

        $this->actingAs($cajero)
            ->get(route('ventas.historial'))
            ->assertOk()
            ->assertViewHas('ventas', function ($ventas) {
                $venta = $ventas->first();

                return $venta->relationLoaded('sucursal')
                    && $venta->relationLoaded('user')
                    && $venta->relationLoaded('detalles');
            });
    }

    public function test_el_historial_muestra_el_metodo_de_pago_de_cada_venta(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroDeVentas($chalco);
        $producto = $this->crearProducto();

        $this->ventaConDetalle($cajero, $chalco, $producto, 900, 1, ['metodo_pago' => 'Transferencia']);

        $this->actingAs($cajero)
            ->get(route('ventas.historial'))
            ->assertOk()
            ->assertSee('Transferencia');
    }
}
