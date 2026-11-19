<?php

namespace Tests\Feature;

use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Pruebas del Dashboard (DashboardController).
 *
 * El reloj se congela en el jueves 19 de noviembre de 2026, 12:00,
 * para que los resultados no cambien según el día en que se corran.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    private Sucursal $chalco;
    private Sucursal $valle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Carbon::setTestNow('2026-11-19 12:00:00');

        $this->chalco = $this->crearSucursal('Chalco');
        $this->valle  = $this->crearSucursal('Valle');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** Una venta con la fecha que se pida (y las piezas de un producto). */
    private function venta(Sucursal $sucursal, User $usuario, float $total, string $fecha = '2026-11-19 10:00:00'): Venta
    {
        $venta = Venta::create([
            'folio'       => 'VNT-' . Str::upper(Str::random(10)),
            'sucursal_id' => $sucursal->id,
            'user_id'     => $usuario->id,
            'total'       => $total,
            'metodo_pago' => 'Efectivo',
            'fecha'       => $fecha,
        ]);

        return $venta;
    }

    private function detalle(Venta $venta, $producto, int $cantidad): void
    {
        VentaDetalle::create([
            'venta_id'        => $venta->id,
            'producto_id'     => $producto->id,
            'nombre_producto' => 'Producto de prueba',
            'cantidad'        => $cantidad,
            'precio_unitario' => 100,
            'descuento'       => 0,
            'subtotal'        => $cantidad * 100,
        ]);
    }

    private function admin(): User
    {
        return $this->crearUsuario('Administrador General', $this->chalco);
    }

    private function cajero(?Sucursal $sucursal = null): User
    {
        $usuario = $this->crearUsuario('Cajero', $sucursal ?? $this->chalco);
        $this->darPermiso($usuario, 'dashboard.datos');

        return $usuario;
    }

    public function test_hay_que_iniciar_sesion_para_ver_el_inicio(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_el_administrador_ve_las_ventas_de_todas_las_sucursales(): void
    {
        $admin = $this->admin();
        $this->venta($this->chalco, $admin, 1000);
        $this->venta($this->valle, $admin, 500);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('ventasHoy', 1500.0)
            ->assertViewHas('numVentasHoy', 2)
            ->assertViewHas('sucursalNombre', 'Todas las sucursales');
    }

    public function test_un_empleado_solo_ve_las_ventas_de_su_sucursal(): void
    {
        $cajero = $this->cajero();
        $otro   = $this->cajero($this->valle);
        $this->venta($this->chalco, $cajero, 1000);
        $this->venta($this->valle, $otro, 700);

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('ventasHoy', 1000.0)
            ->assertViewHas('numVentasHoy', 1)
            ->assertViewHas('sucursalNombre', 'Chalco');
    }

    public function test_un_empleado_sin_sucursal_no_ve_datos_de_nadie(): void
    {
        $admin = $this->admin();
        $this->venta($this->chalco, $admin, 1000);
        $this->venta($this->valle, $admin, 700);
        $this->crearCliente();

        $sinSucursal = $this->crearUsuario('Cajero', $this->chalco);
        $sinSucursal->update(['sucursal_id' => null]);
        $this->darPermiso($sinSucursal, 'dashboard.datos');

        $this->actingAs($sinSucursal)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('sinSucursal', true)
            ->assertViewHas('ventasHoy', 0)
            ->assertViewHas('numVentasHoy', 0)
            ->assertViewHas('clientesNuevos', 0)
            ->assertViewHas('bajoStock', 0)
            ->assertViewHas('ultimasVentas', fn ($v) => $v->isEmpty())
            ->assertViewHas('ventasSemana', fn ($s) => $s->count() === 7 && $s->sum('total') == 0)
            ->assertSee('no tiene una sucursal asignada', false);
    }

    public function test_las_ventas_se_cuentan_por_la_fecha_de_la_venta(): void
    {
        $cajero = $this->cajero();

        // Fecha de hoy, aunque el registro se haya creado ayer: cuenta hoy
        $hoy = $this->venta($this->chalco, $cajero, 400, '2026-11-19 09:00:00');
        $hoy->forceFill(['created_at' => '2026-11-18 09:00:00'])->save();

        // Fecha de ayer, aunque el registro se haya creado hoy: no cuenta hoy
        $ayer = $this->venta($this->chalco, $cajero, 300, '2026-11-18 09:00:00');
        $ayer->forceFill(['created_at' => '2026-11-19 09:00:00'])->save();

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('ventasHoy', 400.0)
            ->assertViewHas('ventasAyer', 300.0);
    }

    public function test_compara_las_ventas_de_hoy_contra_ayer(): void
    {
        $cajero = $this->cajero();
        $this->venta($this->chalco, $cajero, 100, '2026-11-18 10:00:00');
        $this->venta($this->chalco, $cajero, 150, '2026-11-19 10:00:00');

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('cambioVsAyer', 50)
            ->assertSee('50% más que ayer', false);
    }

    public function test_si_ayer_no_hubo_ventas_no_se_calcula_porcentaje(): void
    {
        $cajero = $this->cajero();
        $this->venta($this->chalco, $cajero, 150, '2026-11-19 10:00:00');

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('cambioVsAyer', null)
            ->assertSee('ayer no hubo ventas', false);
    }

    public function test_las_piezas_vendidas_no_cuentan_servicios_ni_otros_dias_ni_otras_sucursales(): void
    {
        $cajero  = $this->cajero();
        $llanta  = $this->crearProducto();
        $servicio = $this->crearProducto(['tipo' => 'Servicio', 'marca' => 'TALLER', 'medida' => 'ALINEACION']);

        $hoy = $this->venta($this->chalco, $cajero, 1000);
        $this->detalle($hoy, $llanta, 4);
        $this->detalle($hoy, $servicio, 1);

        $ayer = $this->venta($this->chalco, $cajero, 500, '2026-11-18 10:00:00');
        $this->detalle($ayer, $llanta, 9);

        $otraSucursal = $this->venta($this->valle, $cajero, 500);
        $this->detalle($otraSucursal, $llanta, 7);

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('llantasVendidas', 4);
    }

    public function test_bajo_stock_usa_la_misma_regla_que_inventario(): void
    {
        $cajero = $this->cajero();

        $bajo   = $this->crearProducto(['medida' => '1']);   // 3 piezas, mínimo 5  -> bajo
        $justo  = $this->crearProducto(['medida' => '2']);   // 5 piezas, mínimo 5  -> NO (llega al mínimo)
        $sin    = $this->crearProducto(['medida' => '3']);   // 0 piezas            -> NO (es "sin stock")
        $ok     = $this->crearProducto(['medida' => '4']);   // 20 piezas           -> NO

        $this->darStock($bajo, $this->chalco, 3);
        $this->darStock($justo, $this->chalco, 5);
        $this->darStock($sin, $this->chalco, 0);
        $this->darStock($ok, $this->chalco, 20);

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('bajoStock', 1);
    }

    public function test_bajo_stock_de_un_empleado_solo_mira_su_sucursal(): void
    {
        $cajero   = $this->cajero();
        $producto = $this->crearProducto();

        $this->darStock($producto, $this->chalco, 50);   // en Chalco está bien
        $this->darStock($producto, $this->valle, 2);     // en Valle está bajo

        $this->actingAs($cajero)->get(route('dashboard'))->assertViewHas('bajoStock', 0);

        $cajeroValle = $this->cajero($this->valle);
        $this->actingAs($cajeroValle)->get(route('dashboard'))->assertViewHas('bajoStock', 1);
    }

    public function test_cuenta_los_clientes_registrados_hoy(): void
    {
        $cajero = $this->cajero();

        $this->crearCliente(['nombre' => 'Nuevo']);
        $viejo = $this->crearCliente(['nombre' => 'Viejo']);
        $viejo->forceFill(['created_at' => '2026-11-10 10:00:00'])->save();

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('clientesNuevos', 1);
    }

    public function test_muestra_solo_las_ultimas_6_ventas_de_su_sucursal_de_la_mas_nueva_a_la_mas_vieja(): void
    {
        $cajero = $this->cajero();

        foreach (range(1, 8) as $i) {
            $this->venta($this->chalco, $cajero, $i * 10, "2026-11-1{$i} 10:00:00");
        }
        // Una venta de otra sucursal, la más nueva de todas: no debe salir
        $this->venta($this->valle, $cajero, 9999, '2026-11-19 11:00:00');

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('ultimasVentas', function ($ventas) {
                return $ventas->count() === 6
                    && $ventas->pluck('total')->map(fn ($t) => (float) $t)->all() === [80.0, 70.0, 60.0, 50.0, 40.0, 30.0];
            });
    }

    public function test_la_grafica_de_la_semana_tiene_7_dias_en_espanol_y_suma_bien(): void
    {
        $cajero = $this->cajero();

        $this->venta($this->chalco, $cajero, 100, '2026-11-19 10:00:00'); // hoy
        $this->venta($this->chalco, $cajero, 50, '2026-11-19 15:00:00');  // hoy
        $this->venta($this->chalco, $cajero, 200, '2026-11-17 10:00:00'); // martes
        $this->venta($this->chalco, $cajero, 999, '2026-11-12 10:00:00'); // fuera de los 7 días
        $this->venta($this->valle, $cajero, 777, '2026-11-19 10:00:00');  // otra sucursal

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('ventasSemana', function ($semana) {
                return $semana->count() === 7
                    && $semana->pluck('label')->all() === ['vie', 'sáb', 'dom', 'lun', 'mar', 'mié', 'jue']
                    && $semana->firstWhere('fecha', '2026-11-19')['total'] == 150
                    && $semana->firstWhere('fecha', '2026-11-17')['total'] == 200
                    && $semana->last()['hoy'] === true;
            })
            ->assertViewHas('totalSemana', 350.0);
    }

    public function test_la_fecha_del_encabezado_sale_en_espanol(): void
    {
        $this->actingAs($this->cajero())->get(route('dashboard'))
            ->assertViewHas('fechaHoy', 'Jueves, 19 de noviembre de 2026')
            ->assertSee('Jueves, 19 de noviembre de 2026', false);
    }

    public function test_sin_permiso_no_salen_los_enlaces_a_inventario_ni_historial(): void
    {
        $this->actingAs($this->cajero())->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('inventario.index', ['stock_status' => 'bajo_stock']), false)
            ->assertDontSee(route('ventas.historial'), false);
    }

    public function test_con_permiso_sale_el_enlace_al_historial(): void
    {
        $cajero = $this->cajero();
        $this->darPermiso($cajero, 'ventas.historial');

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertSee(route('ventas.historial'), false);
    }

    public function test_la_tarjeta_de_bajo_stock_lleva_a_inventario_con_el_filtro(): void
    {
        $cajero = $this->cajero();
        $this->darPermiso($cajero, 'inventario.index');

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertSee(route('inventario.index', ['stock_status' => 'bajo_stock']), false);
    }

    public function test_el_administrador_ve_los_enlaces_de_inventario_y_historial(): void
    {
        $this->actingAs($this->admin())->get(route('dashboard'))
            ->assertSee(route('inventario.index', ['stock_status' => 'bajo_stock']), false)
            ->assertSee(route('ventas.historial'), false);
    }

    public function test_sin_el_permiso_del_inicio_no_ve_ningun_dato(): void
    {
        $admin = $this->admin();
        $this->venta($this->chalco, $admin, 1000);

        $sinPermiso = $this->crearUsuario('Cajero', $this->chalco);

        $this->actingAs($sinPermiso)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('sinPermiso', true)
            ->assertViewHas('ventasHoy', 0)
            ->assertViewHas('ultimasVentas', fn ($v) => $v->isEmpty())
            ->assertSee('Bienvenido', false)
            ->assertDontSee('Ventas de hoy', false)
            ->assertDontSee('Últimas ventas', false);
    }

    public function test_con_el_permiso_del_inicio_ve_los_datos(): void
    {
        $cajero = $this->cajero();
        $this->venta($this->chalco, $cajero, 1000);

        $this->actingAs($cajero)->get(route('dashboard'))
            ->assertViewHas('sinPermiso', false)
            ->assertViewHas('ventasHoy', 1000.0)
            ->assertSee('Ventas de hoy', false);
    }

    public function test_el_administrador_ve_los_datos_sin_necesitar_el_permiso(): void
    {
        $admin = $this->admin();
        $this->venta($this->chalco, $admin, 1000);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertViewHas('sinPermiso', false)
            ->assertViewHas('ventasHoy', 1000.0);
    }

    public function test_sin_ventas_muestra_el_mensaje_vacio(): void
    {
        $this->actingAs($this->cajero())->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Todavía no hay ventas', false);
    }
}
