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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Pruebas de Reportes (ReportesController).
 *
 * El reloj se congela en el miércoles 11 de noviembre de 2026, para que los
 * resultados no cambien según el día en que se corran las pruebas:
 *   - semana actual: lunes 9 a miércoles 11 (3 días)
 *   - semana anterior comparable: lunes 2 a miércoles 4
 *   - mes actual: 1 a 11 de noviembre; mes anterior comparable: 1 a 11 de octubre
 */
class ReportesTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));
        $this->withoutVite();
    }

    /* ===================== AYUDAS ===================== */

    private function usuarioDeReportes(?Sucursal $sucursal = null, string $rol = 'Cajero', array $atributos = []): User
    {
        $usuario = $this->crearUsuario($rol, $sucursal, $atributos);

        foreach (['reportes.index', 'reportes.exportar.pdf'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    private function venta(Sucursal $sucursal, User $usuario, float $total, string $fecha): Venta
    {
        return Venta::create([
            'folio'       => 'VNT-' . Str::upper(Str::random(10)),
            'sucursal_id' => $sucursal->id,
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

    /** Un cajero en Chalco con permiso de reportes. */
    private function escenario(): array
    {
        $chalco = $this->crearSucursal('Chalco');

        return [$this->usuarioDeReportes($chalco), $chalco];
    }

    /* ===================== SEGURIDAD Y SUCURSAL ===================== */

    public function test_un_usuario_normal_solo_ve_los_datos_de_su_sucursal(): void
    {
        [$cajero, $chalco] = $this->escenario();
        $valle = $this->crearSucursal('Valle de Chalco');
        $this->venta($chalco, $cajero, 500, '2026-11-11 10:00:00');
        $this->venta($valle, $cajero, 300, '2026-11-11 10:00:00');

        // Aunque pida ver Valle de Chalco en la dirección, se queda en la suya.
        $respuesta = $this->reporte($cajero, ['sucursal_id' => $valle->id])->assertOk();

        $this->assertSame(500.0, (float) $respuesta->viewData('ingresosTotales'));
        $this->assertSame(1, (int) $respuesta->viewData('ventasRealizadas'));
        $this->assertSame($chalco->id, (int) $respuesta->viewData('sucursalFiltro'));
        $this->assertCount(1, $respuesta->viewData('ventasPorSucursal'));
    }

    public function test_el_administrador_general_ve_todas_las_sucursales_o_filtra_por_una(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $admin = $this->usuarioDeReportes($this->crearSucursal('Administración General'), 'Administrador General');
        $this->venta($chalco, $admin, 500, '2026-11-11 10:00:00');
        $this->venta($valle, $admin, 300, '2026-11-11 10:00:00');

        $todas = $this->reporte($admin)->assertOk();
        $this->assertSame(800.0, (float) $todas->viewData('ingresosTotales'));
        $this->assertCount(2, $todas->viewData('ventasPorSucursal'));

        $soloValle = $this->reporte($admin, ['sucursal_id' => $valle->id])->assertOk();
        $this->assertSame(300.0, (float) $soloValle->viewData('ingresosTotales'));
        $this->assertCount(1, $soloValle->viewData('ventasPorSucursal'));

        $this->assertSame(800.0, (float) $this->reporte($admin, ['sucursal_id' => 'todas'])->viewData('ingresosTotales'));
    }

    public function test_un_usuario_normal_sin_sucursal_recibe_403(): void
    {
        $sinSucursal = $this->usuarioDeReportes(null, 'Cajero', ['sucursal_id' => null]);

        $this->reporte($sinSucursal)->assertForbidden();
    }

    public function test_sin_el_permiso_de_reportes_recibe_403(): void
    {
        $sinPermiso = $this->crearUsuario('Cajero');

        $this->reporte($sinPermiso)->assertForbidden();
    }

    /* ===================== VALIDACIÓN DE FECHAS ===================== */

    public static function filtrosInvalidos(): array
    {
        return [
            'formato inválido'     => [['fecha_desde' => 'abc'], 'fecha_desde'],
            'fecha futura'         => [['fecha_desde' => '2099-01-01'], 'fecha_desde'],
            'hasta antes de desde' => [['fecha_desde' => '2026-11-10', 'fecha_hasta' => '2026-11-05'], 'fecha_hasta'],
            'rango de 367 días'    => [['fecha_desde' => '2025-11-10', 'fecha_hasta' => '2026-11-11'], 'fecha_hasta'],
        ];
    }

    #[DataProvider('filtrosInvalidos')]
    public function test_fechas_invalidas_regresan_al_reporte_con_un_mensaje(array $filtros, string $campo): void
    {
        [$cajero] = $this->escenario();

        $this->actingAs($cajero)
            ->from(route('reportes.index'))
            ->get(route('reportes.index', array_merge(['periodo' => 'personalizado'], $filtros)))
            ->assertRedirect(route('reportes.index'))
            ->assertSessionHasErrors($campo);
    }

    public function test_un_rango_de_exactamente_366_dias_si_se_acepta(): void
    {
        [$cajero] = $this->escenario();

        $this->reporte($cajero, [
            'periodo'     => 'personalizado',
            'fecha_desde' => '2025-11-11',
            'fecha_hasta' => '2026-11-11',
        ])->assertOk();
    }

    public function test_un_periodo_desconocido_cae_en_hoy(): void
    {
        [$cajero] = $this->escenario();

        $this->assertSame('hoy', $this->reporte($cajero, ['periodo' => 'raro'])->assertOk()->viewData('periodo'));
    }

    /* ===================== PERIODOS Y COMPARATIVAS ===================== */

    public function test_hoy_se_compara_contra_ayer_y_solo_ayer(): void
    {
        [$cajero, $chalco] = $this->escenario();
        $this->venta($chalco, $cajero, 500, '2026-11-11 09:00:00'); // hoy
        $this->venta($chalco, $cajero, 300, '2026-11-11 11:00:00'); // hoy
        $this->venta($chalco, $cajero, 400, '2026-11-10 15:00:00'); // ayer
        $this->venta($chalco, $cajero, 5000, '2026-11-09 10:00:00'); // anteayer: NO debe contar
        $this->venta($chalco, $cajero, 7777, '2026-11-12 10:00:00'); // mañana: NO debe contar

        $respuesta = $this->reporte($cajero, ['periodo' => 'hoy'])->assertOk();

        $this->assertSame(800.0, (float) $respuesta->viewData('ingresosTotales'));
        $this->assertSame(2, (int) $respuesta->viewData('ventasRealizadas'));
        $this->assertSame('2026-11-11 00:00:00', $respuesta->viewData('fechaInicio')->toDateTimeString());
        $this->assertSame('2026-11-11 23:59:59', $respuesta->viewData('fechaFin')->toDateTimeString());

        // El periodo anterior es UN solo día (el error que se corrigió con Carbon 3).
        $this->assertSame('2026-11-10 00:00:00', $respuesta->viewData('fechaInicioAnterior')->toDateTimeString());
        $this->assertSame('2026-11-10 23:59:59', $respuesta->viewData('fechaFinAnterior')->toDateTimeString());

        $this->assertSame(100.0, $respuesta->viewData('porcentajeIngresos')); // 800 vs 400
        $this->assertSame(100.0, $respuesta->viewData('porcentajeVentas'));   // 2 vs 1
        $this->assertSame('vs. ayer', $respuesta->viewData('etiquetaComparativa'));
    }

    public function test_semana_se_compara_contra_los_mismos_dias_de_la_semana_anterior(): void
    {
        [$cajero, $chalco] = $this->escenario();
        $this->venta($chalco, $cajero, 100, '2026-11-09 10:00:00'); // lunes (esta semana)
        $this->venta($chalco, $cajero, 200, '2026-11-11 09:00:00'); // miércoles (esta semana)
        $this->venta($chalco, $cajero, 150, '2026-11-02 10:00:00'); // lunes anterior
        $this->venta($chalco, $cajero, 150, '2026-11-04 10:00:00'); // miércoles anterior
        $this->venta($chalco, $cajero, 999, '2026-11-05 10:00:00'); // jueves anterior: NO cuenta
        $this->venta($chalco, $cajero, 888, '2026-11-01 10:00:00'); // domingo antes: NO cuenta

        $respuesta = $this->reporte($cajero, ['periodo' => 'semana'])->assertOk();

        $this->assertSame('2026-11-09 00:00:00', $respuesta->viewData('fechaInicio')->toDateTimeString());
        $this->assertSame('2026-11-02 00:00:00', $respuesta->viewData('fechaInicioAnterior')->toDateTimeString());
        $this->assertSame('2026-11-04 23:59:59', $respuesta->viewData('fechaFinAnterior')->toDateTimeString());
        $this->assertSame(300.0, (float) $respuesta->viewData('ingresosTotales'));
        $this->assertSame(0.0, $respuesta->viewData('porcentajeIngresos')); // 300 vs 300
        $this->assertSame('vs. mismos días de la semana anterior', $respuesta->viewData('etiquetaComparativa'));
    }

    public function test_mes_se_compara_contra_los_mismos_dias_del_mes_anterior(): void
    {
        [$cajero, $chalco] = $this->escenario();
        $this->venta($chalco, $cajero, 1000, '2026-11-01 09:00:00');
        $this->venta($chalco, $cajero, 1000, '2026-11-10 09:00:00');
        $this->venta($chalco, $cajero, 500, '2026-10-01 09:00:00');  // octubre 1
        $this->venta($chalco, $cajero, 500, '2026-10-11 23:00:00');  // octubre 11 (último día comparable)
        $this->venta($chalco, $cajero, 9999, '2026-10-12 09:00:00'); // octubre 12: NO cuenta
        $this->venta($chalco, $cajero, 777, '2026-10-31 10:00:00');  // octubre 31: NO cuenta

        $respuesta = $this->reporte($cajero, ['periodo' => 'mes'])->assertOk();

        $this->assertSame('2026-11-01 00:00:00', $respuesta->viewData('fechaInicio')->toDateTimeString());
        $this->assertSame('2026-11-11 23:59:59', $respuesta->viewData('fechaFin')->toDateTimeString());
        $this->assertSame('2026-10-01 00:00:00', $respuesta->viewData('fechaInicioAnterior')->toDateTimeString());
        $this->assertSame('2026-10-11 23:59:59', $respuesta->viewData('fechaFinAnterior')->toDateTimeString());
        $this->assertSame(2000.0, (float) $respuesta->viewData('ingresosTotales'));
        $this->assertSame(100.0, $respuesta->viewData('porcentajeIngresos')); // 2000 vs 1000
        $this->assertSame('vs. mismos días del mes anterior', $respuesta->viewData('etiquetaComparativa'));
    }

    public function test_personalizado_se_compara_contra_los_mismos_dias_justo_antes_y_arma_la_serie_diaria(): void
    {
        [$cajero, $chalco] = $this->escenario();
        $this->venta($chalco, $cajero, 300, '2026-11-05 10:00:00');
        $this->venta($chalco, $cajero, 300, '2026-11-07 10:00:00');
        $this->venta($chalco, $cajero, 300, '2026-11-03 10:00:00'); // dentro del periodo anterior (2 al 4)

        $respuesta = $this->reporte($cajero, [
            'periodo'     => 'personalizado',
            'fecha_desde' => '2026-11-05',
            'fecha_hasta' => '2026-11-07',
        ])->assertOk();

        $this->assertSame('2026-11-02 00:00:00', $respuesta->viewData('fechaInicioAnterior')->toDateTimeString());
        $this->assertSame('2026-11-04 23:59:59', $respuesta->viewData('fechaFinAnterior')->toDateTimeString());
        $this->assertSame(600.0, (float) $respuesta->viewData('ingresosTotales'));
        $this->assertSame(100.0, $respuesta->viewData('porcentajeIngresos')); // 600 vs 300
        $this->assertSame('vs. periodo anterior', $respuesta->viewData('etiquetaComparativa'));

        // La gráfica trae todos los días (con 0 donde no hubo ventas); el PDF solo los que vendieron.
        $this->assertSame(
            ['2026-11-05' => 300.0, '2026-11-06' => 0.0, '2026-11-07' => 300.0],
            $respuesta->viewData('serieDiaria')->pluck('total', 'dia')->all()
        );
        $this->assertCount(2, $respuesta->viewData('ventasPorDia'));
    }

    public function test_sin_periodo_anterior_el_porcentaje_es_100_y_sin_ventas_es_0(): void
    {
        [$cajero, $chalco] = $this->escenario();
        $this->venta($chalco, $cajero, 500, '2026-11-11 09:00:00');

        $this->assertEquals(100, $this->reporte($cajero, ['periodo' => 'hoy'])->viewData('porcentajeIngresos'));

        $vacio = $this->reporte($cajero, [
            'periodo'     => 'personalizado',
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-01-05',
        ])->assertOk();
        $this->assertEquals(0, $vacio->viewData('porcentajeIngresos'));
    }

    /* ===================== PRODUCTOS Y CLIENTES ===================== */

    public function test_productos_mas_vendidos_trae_los_5_primeros_solo_de_la_sucursal(): void
    {
        [$cajero, $chalco] = $this->escenario();
        $valle = $this->crearSucursal('Valle de Chalco');
        $producto = $this->crearProducto();

        $ventaChalco = $this->venta($chalco, $cajero, 3000, '2026-11-11 10:00:00');
        foreach (['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 2, 'F' => 1] as $nombre => $cantidad) {
            VentaDetalle::create([
                'venta_id'        => $ventaChalco->id,
                'producto_id'     => $producto->id,
                'nombre_producto' => $nombre,
                'cantidad'        => $cantidad,
                'precio_unitario' => 100,
                'subtotal'        => $cantidad * 100,
            ]);
        }

        // Una venta de otra sucursal con muchísimas piezas: no debe aparecer.
        $ventaValle = $this->venta($valle, $cajero, 10000, '2026-11-11 10:00:00');
        VentaDetalle::create([
            'venta_id'        => $ventaValle->id,
            'producto_id'     => $producto->id,
            'nombre_producto' => 'Z',
            'cantidad'        => 100,
            'precio_unitario' => 100,
            'subtotal'        => 10000,
        ]);

        $masVendidos = $this->reporte($cajero)->assertOk()->viewData('productosMasVendidos');

        $this->assertSame(['A', 'B', 'C', 'D', 'E'], $masVendidos->pluck('nombre_producto')->all());
        $this->assertSame(10, (int) $masVendidos->first()->total_cantidad);
        $this->assertSame(1000.0, (float) $masVendidos->first()->total_ingresos);
    }

    public function test_clientes_nuevos_cuenta_todo_el_negocio_y_se_compara_contra_ayer(): void
    {
        [$cajero] = $this->escenario();

        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));
        $this->crearCliente(['nombre' => 'De ayer']);

        $this->travelTo(Carbon::parse('2026-11-11 10:00:00'));
        $this->crearCliente(['nombre' => 'De hoy 1']);
        $this->crearCliente(['nombre' => 'De hoy 2']);

        // Los clientes no tienen sucursal: el cajero de Chalco ve los 2 de hoy.
        $respuesta = $this->reporte($cajero)->assertOk();

        $this->assertSame(2, (int) $respuesta->viewData('clientesNuevos'));
        $this->assertSame(100.0, $respuesta->viewData('porcentajeClientes')); // 2 vs 1
    }

    /* ===================== PDF ===================== */

    public function test_el_pdf_se_descarga(): void
    {
        [$cajero, $chalco] = $this->escenario();
        $this->venta($chalco, $cajero, 500, '2026-11-11 10:00:00');

        $this->actingAs($cajero)
            ->get(route('reportes.exportar.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_el_pdf_tambien_valida_las_fechas(): void
    {
        [$cajero] = $this->escenario();

        $this->actingAs($cajero)
            ->from(route('reportes.index'))
            ->get(route('reportes.exportar.pdf', ['periodo' => 'personalizado', 'fecha_desde' => 'abc']))
            ->assertSessionHasErrors('fecha_desde');
    }
}