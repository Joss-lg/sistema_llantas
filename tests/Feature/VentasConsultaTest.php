<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}