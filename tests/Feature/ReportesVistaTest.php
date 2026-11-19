<?php

namespace Tests\Feature;

use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Pruebas de lo que SE VE en la pantalla de Reportes.
 * (Las cuentas y la seguridad ya las prueban ReportesTest.)
 *
 * El reloj se congela en el miércoles 11 de noviembre de 2026.
 */
class ReportesVistaTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    private Sucursal $chalco;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));
        $this->withoutVite();

        $this->chalco = $this->crearSucursal('Chalco');
    }

    private function conPermisos(User $usuario): User
    {
        foreach (['reportes.index', 'reportes.exportar.pdf'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    private function cajero(): User
    {
        return $this->conPermisos($this->crearUsuario('Cajero', $this->chalco));
    }

    private function admin(): User
    {
        return $this->conPermisos($this->crearUsuario('Administrador General', $this->chalco));
    }

    private function venta(User $usuario, float $total, string $fecha): Venta
    {
        return Venta::create([
            'folio'       => 'VNT-' . Str::upper(Str::random(10)),
            'sucursal_id' => $this->chalco->id,
            'user_id'     => $usuario->id,
            'total'       => $total,
            'metodo_pago' => 'Efectivo',
            'fecha'       => $fecha,
        ]);
    }

    private function reporte(User $usuario, array $filtros = []): TestResponse
    {
        return $this->actingAs($usuario)->get(route('reportes.index', $filtros));
    }

    public function test_el_boton_del_periodo_elegido_sale_marcado_y_los_demas_no(): void
    {
        $respuesta = $this->reporte($this->cajero(), ['periodo' => 'semana'])->assertOk();

        $this->assertSame(1, substr_count($respuesta->getContent(), 'aria-pressed="true"'));
        $this->assertSame(3, substr_count($respuesta->getContent(), 'aria-pressed="false"'));
        $respuesta->assertSee('name="periodo" value="semana"', false);
    }

    public function test_el_administrador_ve_el_selector_de_sucursal(): void
    {
        $this->crearSucursal('Valle de Chalco');

        $this->reporte($this->admin())
            ->assertSee('id="filtro_sucursal"', false)
            ->assertSee('Todas las sucursales')
            ->assertSee('Valle de Chalco')
            ->assertDontSee('únicamente los datos de tu sucursal', false);
    }

    public function test_un_empleado_no_ve_el_selector_y_si_el_aviso_de_su_sucursal(): void
    {
        $this->reporte($this->cajero())
            ->assertDontSee('id="filtro_sucursal"', false)
            ->assertSee('únicamente los datos de tu sucursal', false);
    }

    public function test_sin_ventas_salen_los_mensajes_vacios_y_no_las_graficas(): void
    {
        $this->reporte($this->cajero())
            ->assertOk()
            ->assertSee('Sin ventas en este periodo', false)
            ->assertSee('Todavía no hay productos vendidos', false)
            ->assertDontSee('id="chartVentasPorDia"', false)
            ->assertDontSee('id="chartVentasPorSucursal"', false);
    }

    public function test_con_ventas_salen_las_dos_graficas_y_los_totales(): void
    {
        $cajero = $this->cajero();
        $this->venta($cajero, 1250.5, '2026-11-11 10:00:00');

        $this->reporte($cajero)
            ->assertOk()
            ->assertSee('id="chartVentasPorDia"', false)
            ->assertSee('id="chartVentasPorSucursal"', false)
            ->assertSee('$1,250.50', false)
            ->assertDontSee('Sin ventas en este periodo', false);
    }

    public function test_muestra_si_subio_bajo_o_quedo_igual_contra_el_periodo_anterior(): void
    {
        $cajero = $this->cajero();

        // Hoy 200 contra ayer 100: subió 100%
        $this->venta($cajero, 100, '2026-11-10 10:00:00');
        $this->venta($cajero, 200, '2026-11-11 10:00:00');
        $this->reporte($cajero)->assertSee('↑ 100% más', false);

        // Hoy 50 contra ayer 100: bajó 50%
        Venta::query()->where('total', 200)->update(['total' => 50]);
        $this->reporte($cajero)->assertSee('↓ 50% menos', false);

        // Hoy 100 contra ayer 100: igual
        Venta::query()->where('total', 50)->update(['total' => 100]);
        $this->reporte($cajero)->assertSee('Sin cambio', false);
    }

    public function test_muestra_el_rango_del_periodo_y_contra_que_se_compara(): void
    {
        $this->reporte($this->cajero(), ['periodo' => 'hoy'])
            ->assertSee('11/11/2026 al 11/11/2026', false)
            ->assertSee('10/11/2026 al 10/11/2026', false);
    }

    public function test_unas_fechas_malas_muestran_el_aviso_en_rojo(): void
    {
        $this->actingAs($this->cajero())
            ->from(route('reportes.index'))
            ->followingRedirects()
            ->get(route('reportes.index', [
                'periodo'     => 'personalizado',
                'fecha_desde' => '2026-11-10',
                'fecha_hasta' => '2026-11-01',
            ]))
            ->assertOk()
            ->assertSee('Revisa las fechas del reporte', false)
            ->assertSee('La fecha final no puede ser anterior a la fecha inicial.', false);
    }

    public function test_el_enlace_de_exportar_conserva_los_filtros(): void
    {
        $url = route('reportes.exportar.pdf', ['periodo' => 'mes']);

        $this->reporte($this->cajero(), ['periodo' => 'mes'])
            ->assertSee(str_replace('&', '&amp;', $url), false);
    }

    public function test_el_calendario_solo_sale_en_el_periodo_personalizado(): void
    {
        $this->reporte($this->cajero(), ['periodo' => 'semana'])
            ->assertDontSee('id="rango_fechas_reporte"', false);

        $this->reporte($this->cajero(), [
                'periodo'     => 'personalizado',
                'fecha_desde' => '2026-11-01',
                'fecha_hasta' => '2026-11-05',
            ])
            ->assertSee('id="rango_fechas_reporte"', false)
            ->assertSee('Aplicar', false)
            ->assertSee('name="fecha_desde"', false);
    }

    public function test_la_tabla_de_productos_numera_y_el_enlace_a_inventario_pide_permiso(): void
    {
        $cajero   = $this->cajero();
        $producto = $this->crearProducto();
        $venta    = $this->venta($cajero, 900, '2026-11-11 10:00:00');

        VentaDetalle::create([
            'venta_id'        => $venta->id,
            'producto_id'     => $producto->id,
            'nombre_producto' => 'PIRELLI 205/55R16',
            'cantidad'        => 4,
            'precio_unitario' => 225,
            'descuento'       => 0,
            'subtotal'        => 900,
        ]);

        $this->reporte($cajero)
            ->assertSee('PIRELLI 205/55R16', false)
            ->assertSee('$900.00', false)
            ->assertDontSee(route('inventario.index'), false);

        $this->darPermiso($cajero, 'inventario.index');

        $this->reporte($cajero)->assertSee(route('inventario.index'), false);
    }

    public function test_los_clientes_nuevos_avisan_que_son_de_todo_el_negocio(): void
    {
        $this->reporte($this->cajero())
            ->assertSee('De todo el negocio, no se separa por sucursal.', false);
    }
}
