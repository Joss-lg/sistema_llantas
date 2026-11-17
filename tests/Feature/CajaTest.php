<?php

namespace Tests\Feature;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaUsuarios;
use Tests\TestCase;

/**
 * Pruebas del módulo de Caja (CajaController y el candado caja.abierta),
 * SIN ventas. Lo que depende de ventas va en otra ronda.
 */
class CajaTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Cajero con los permisos que usa el módulo de caja.
     * (Los permisos son por usuario, no por rol.)
     */
    private function cajeroConPermisos(?Sucursal $sucursal = null, array $atributos = []): User
    {
        $cajero = $this->crearUsuario('Cajero', $sucursal, $atributos);

        foreach (['caja.index', 'caja.abrir', 'caja.cerrar', 'caja.gasto.store',
                  'caja.anticipo.store', 'caja.historial', 'ventas.index'] as $ruta) {
            $this->darPermiso($cajero, $ruta);
        }

        return $cajero;
    }

    /** Deja un turno abierto para el usuario, directo en la base. */
    private function abrirTurno(User $usuario, float $saldoInicial = 500): CorteCaja
    {
        return CorteCaja::create([
            'user_id'        => $usuario->id,
            'sucursal_id'    => $usuario->sucursal_id,
            'saldo_inicial'  => $saldoInicial,
            'estado'         => 'abierta',
            'fecha_apertura' => now(),
        ]);
    }

    /* ===================== ABRIR CAJA ===================== */

    public function test_abrir_caja_crea_el_turno_en_la_sucursal_del_usuario(): void
    {
        $sucursal = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroConPermisos($sucursal);

        $this->actingAs($cajero)
            ->post(route('caja.abrir'), ['saldo_inicial' => 500])
            ->assertRedirect(route('ventas.index'));

        $this->assertDatabaseHas('cortes_caja', [
            'user_id'       => $cajero->id,
            'sucursal_id'   => $sucursal->id,
            'estado'        => 'abierta',
            'saldo_inicial' => 500,
        ]);
    }

    public function test_no_se_pueden_abrir_dos_turnos_a_la_vez(): void
    {
        $cajero = $this->cajeroConPermisos();

        $this->actingAs($cajero)->post(route('caja.abrir'), ['saldo_inicial' => 500]);

        $this->from(route('caja.index'))
            ->post(route('caja.abrir'), ['saldo_inicial' => 300])
            ->assertRedirect(route('caja.index'))
            ->assertSessionHas('error', 'Ya tienes un turno activo.');

        $this->assertSame(1, CorteCaja::count());
    }

    public function test_usuario_sin_sucursal_no_puede_abrir_caja(): void
    {
        $cajero = $this->cajeroConPermisos(null, ['sucursal_id' => null]);

        $this->actingAs($cajero)
            ->from(route('caja.index'))
            ->post(route('caja.abrir'), ['saldo_inicial' => 500])
            ->assertSessionHas('error', 'Tu usuario no tiene una sucursal asignada. Pide a un administrador que te asigne una antes de abrir caja.');

        $this->assertSame(0, CorteCaja::count());
    }

    public static function saldosInvalidos(): array
    {
        return [
            'vacío'    => [''],
            'texto'    => ['abc'],
            'negativo' => [-5],
        ];
    }

    #[DataProvider('saldosInvalidos')]
    public function test_saldo_inicial_invalido_se_rechaza(mixed $saldo): void
    {
        $cajero = $this->cajeroConPermisos();

        $this->actingAs($cajero)
            ->from(route('caja.index'))
            ->post(route('caja.abrir'), ['saldo_inicial' => $saldo])
            ->assertSessionHasErrors('saldo_inicial');

        $this->assertSame(0, CorteCaja::count());
    }

    /* ===================== CERRAR CAJA ===================== */

    public static function cierres(): array
    {
        // [efectivo contado, diferencia esperada, texto del mensaje]
        return [
            'cuadra exacto' => [550, 0.0, 'La caja cuadra exacto.'],
            'faltante'      => [500, -50.0, 'FALTANTE de $50.00'],
            'sobrante'      => [600, 50.0, 'SOBRANTE de $50.00'],
        ];
    }

    /**
     * Saldo inicial 500 + anticipo 100 - gasto 50 = 550 esperados.
     * Diferencia = contado - esperado (positivo sobra, negativo falta).
     */
    #[DataProvider('cierres')]
    public function test_cerrar_caja_calcula_esperado_y_diferencia(int $contado, float $diferencia, string $texto): void
    {
        $cajero = $this->cajeroConPermisos();
        $corte = $this->abrirTurno($cajero, 500);

        MovimientoCaja::create(['corte_caja_id' => $corte->id, 'tipo' => 'ingreso', 'concepto' => 'Anticipo', 'monto' => 100]);
        MovimientoCaja::create(['corte_caja_id' => $corte->id, 'tipo' => 'egreso', 'concepto' => 'Gasto', 'monto' => 50]);

        $this->actingAs($cajero)
            ->post(route('caja.cerrar'), ['efectivo_contado' => $contado])
            ->assertRedirect(route('caja.index'))
            ->assertSessionHas('success', fn ($mensaje) => str_contains($mensaje, $texto));

        $corte->refresh();
        $this->assertSame('cerrada', $corte->estado);
        $this->assertNotNull($corte->fecha_cierre);
        $this->assertSame(550.0, (float) $corte->saldo_final);
        $this->assertSame((float) $contado, (float) $corte->efectivo_contado);
        $this->assertSame($diferencia, (float) $corte->diferencia);
    }

    public function test_cerrar_sin_turno_abierto_da_error(): void
    {
        $cajero = $this->cajeroConPermisos();

        $this->actingAs($cajero)
            ->from(route('caja.index'))
            ->post(route('caja.cerrar'), ['efectivo_contado' => 100])
            ->assertSessionHas('error', 'No hay ninguna caja abierta para cerrar.');
    }

    public function test_un_turno_cerrado_no_se_cierra_dos_veces(): void
    {
        $cajero = $this->cajeroConPermisos();
        $corte = $this->abrirTurno($cajero, 500);

        $this->actingAs($cajero)->post(route('caja.cerrar'), ['efectivo_contado' => 500]);

        $this->from(route('caja.index'))
            ->post(route('caja.cerrar'), ['efectivo_contado' => 999])
            ->assertSessionHas('error', 'No hay ninguna caja abierta para cerrar.');

        // El primer cierre no se pisó con el segundo intento.
        $this->assertSame(500.0, (float) $corte->fresh()->efectivo_contado);
    }

    public function test_cerrar_sin_capturar_el_efectivo_contado_se_rechaza(): void
    {
        $cajero = $this->cajeroConPermisos();
        $corte = $this->abrirTurno($cajero);

        $this->actingAs($cajero)
            ->from(route('caja.index'))
            ->post(route('caja.cerrar'), ['efectivo_contado' => ''])
            ->assertSessionHasErrors('efectivo_contado');

        $this->assertSame('abierta', $corte->fresh()->estado);
    }

    /* ===================== GASTOS Y ANTICIPOS ===================== */

    public function test_registrar_un_gasto_crea_un_movimiento_de_egreso(): void
    {
        $cajero = $this->cajeroConPermisos();
        $corte = $this->abrirTurno($cajero);

        $this->actingAs($cajero)
            ->from(route('caja.index'))
            ->post(route('caja.gasto.store'), ['concepto' => 'Garrafón de agua', 'monto' => 35.50])
            ->assertSessionHas('success', 'Gasto registrado correctamente.');

        $this->assertDatabaseHas('movimientos_caja', [
            'corte_caja_id' => $corte->id,
            'tipo'          => 'egreso',
            'concepto'      => 'Garrafón de agua',
            'monto'         => 35.50,
        ]);
    }

    public function test_registrar_un_anticipo_crea_un_movimiento_de_ingreso(): void
    {
        $cajero = $this->cajeroConPermisos();
        $corte = $this->abrirTurno($cajero);

        $this->actingAs($cajero)
            ->from(route('caja.index'))
            ->post(route('caja.anticipo.store'), ['concepto' => 'Anticipo de cliente', 'monto' => 200])
            ->assertSessionHas('success', 'Anticipo registrado correctamente.');

        $this->assertDatabaseHas('movimientos_caja', [
            'corte_caja_id' => $corte->id,
            'tipo'          => 'ingreso',
            'concepto'      => 'Anticipo de cliente',
            'monto'         => 200,
        ]);
    }

    public static function movimientosSinTurno(): array
    {
        return [
            'gasto'    => ['caja.gasto.store', 'No hay una caja abierta para registrar el gasto.'],
            'anticipo' => ['caja.anticipo.store', 'No hay una caja abierta para registrar el anticipo.'],
        ];
    }

    #[DataProvider('movimientosSinTurno')]
    public function test_gasto_o_anticipo_sin_turno_abierto_no_guarda_nada(string $ruta, string $mensaje): void
    {
        $cajero = $this->cajeroConPermisos();

        $this->actingAs($cajero)
            ->from(route('caja.index'))
            ->post(route($ruta), ['concepto' => 'Algo', 'monto' => 10])
            ->assertSessionHas('error', $mensaje);

        $this->assertSame(0, MovimientoCaja::count());
    }

    public static function movimientosInvalidos(): array
    {
        return [
            'monto cero'  => [['concepto' => 'Garrafón', 'monto' => 0], 'monto'],
            'sin concepto' => [['concepto' => '', 'monto' => 10], 'concepto'],
        ];
    }

    #[DataProvider('movimientosInvalidos')]
    public function test_gasto_con_datos_invalidos_se_rechaza(array $datos, string $campo): void
    {
        $cajero = $this->cajeroConPermisos();
        $this->abrirTurno($cajero);

        $this->actingAs($cajero)
            ->from(route('caja.index'))
            ->post(route('caja.gasto.store'), $datos)
            ->assertSessionHasErrors($campo);

        $this->assertSame(0, MovimientoCaja::count());
    }

    /* ===================== CANDADO caja.abierta ===================== */

    public function test_ventas_sin_caja_abierta_manda_a_caja_con_alerta(): void
    {
        $cajero = $this->cajeroConPermisos();

        $this->actingAs($cajero)
            ->get(route('ventas.index'))
            ->assertRedirect(route('caja.index'))
            ->assertSessionHas('alerta');
    }

    /* ===================== HISTORIAL ===================== */

    public function test_historial_usuario_normal_solo_ve_cortes_de_su_sucursal(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $cajeroChalco = $this->cajeroConPermisos($chalco);
        $cajeroValle = $this->cajeroConPermisos($valle);
        $corteChalco = $this->abrirTurno($cajeroChalco);
        $this->abrirTurno($cajeroValle);

        $this->actingAs($cajeroChalco)
            ->get(route('caja.historial'))
            ->assertOk()
            ->assertViewHas('cortes', fn ($cortes) => $cortes->pluck('id')->map(fn ($id) => (int) $id)->all() === [$corteChalco->id]);
    }

    public function test_historial_administrador_general_ve_cortes_de_todas_las_sucursales(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $corteChalco = $this->abrirTurno($this->cajeroConPermisos($chalco));
        $corteValle = $this->abrirTurno($this->cajeroConPermisos($valle));
        $admin = $this->crearUsuario('Administrador General', $this->crearSucursal('Administración General'));

        $esperados = [$corteChalco->id, $corteValle->id];
        sort($esperados);

        $this->actingAs($admin)
            ->get(route('caja.historial'))
            ->assertOk()
            ->assertViewHas('cortes', function ($cortes) use ($esperados) {
                $ids = $cortes->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();

                return $ids === $esperados;
            });
    }
}