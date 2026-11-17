<?php

namespace Tests\Feature;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreaUsuarios;
use Tests\TestCase;

/**
 * Caja, Ronda B: lo que depende de las ventas.
 * Las ventas se crean directo en la base (no pasan por el punto de venta),
 * así estas pruebas solo revisan las cuentas de la caja.
 */
class CajaVentasTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function cajeroConPermisos(?Sucursal $sucursal = null): User
    {
        $cajero = $this->crearUsuario('Cajero', $sucursal);

        foreach (['caja.index', 'caja.cerrar'] as $ruta) {
            $this->darPermiso($cajero, $ruta);
        }

        return $cajero;
    }

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

    private function crearVenta(CorteCaja $corte, float $total, string $metodo = 'Efectivo', ?int $sucursalId = null): Venta
    {
        return Venta::create([
            'folio'         => 'VNT-' . Str::upper(Str::random(10)),
            'sucursal_id'   => $sucursalId ?? $corte->sucursal_id,
            'user_id'       => $corte->user_id,
            'corte_caja_id' => $corte->id,
            'total'         => $total,
            'metodo_pago'   => $metodo,
            'fecha'         => now(),
        ]);
    }

    private function movimiento(CorteCaja $corte, string $tipo, float $monto): MovimientoCaja
    {
        return MovimientoCaja::create([
            'corte_caja_id' => $corte->id,
            'tipo'          => $tipo,
            'concepto'      => 'Prueba',
            'monto'         => $monto,
        ]);
    }

    /**
     * Esperado = saldo inicial + ventas en EFECTIVO + anticipos - gastos.
     * Las ventas con tarjeta o transferencia no meten dinero al cajón.
     */
    public function test_el_cierre_solo_cuenta_las_ventas_en_efectivo(): void
    {
        $cajero = $this->cajeroConPermisos();
        $corte = $this->abrirTurno($cajero, 500);

        $this->crearVenta($corte, 300);
        $this->crearVenta($corte, 200);
        $this->crearVenta($corte, 1000, 'Tarjeta');
        $this->crearVenta($corte, 400, 'Transferencia');
        $this->movimiento($corte, 'ingreso', 100);
        $this->movimiento($corte, 'egreso', 50);

        // 500 + 300 + 200 + 100 - 50 = 1050
        $this->actingAs($cajero)
            ->post(route('caja.cerrar'), ['efectivo_contado' => 1050])
            ->assertSessionHas('success', fn ($mensaje) => str_contains($mensaje, 'La caja cuadra exacto.'));

        $corte->refresh();
        $this->assertSame(1050.0, (float) $corte->saldo_final);
        $this->assertSame(0.0, (float) $corte->diferencia);
    }

    public function test_el_cierre_no_cuenta_las_ventas_de_otro_turno(): void
    {
        $sucursal = $this->crearSucursal('Chalco');
        $cajero = $this->cajeroConPermisos($sucursal);
        $corte = $this->abrirTurno($cajero, 500);
        $this->crearVenta($corte, 100);

        $otroCajero = $this->cajeroConPermisos($sucursal);
        $otroCorte = $this->abrirTurno($otroCajero, 0);
        $this->crearVenta($otroCorte, 999);

        // Solo cuenta lo de MI turno: 500 + 100 = 600
        $this->actingAs($cajero)
            ->post(route('caja.cerrar'), ['efectivo_contado' => 600]);

        $this->assertSame(0.0, (float) $corte->fresh()->diferencia);
    }

    public function test_pantalla_de_caja_calcula_el_saldo_estimado_solo_con_efectivo(): void
    {
        $cajero = $this->cajeroConPermisos();
        $corte = $this->abrirTurno($cajero, 500);

        $this->crearVenta($corte, 300);
        $this->crearVenta($corte, 1000, 'Tarjeta');
        $this->movimiento($corte, 'ingreso', 100);
        $this->movimiento($corte, 'egreso', 50);

        // 500 + 300 (efectivo) + 100 - 50 = 850
        $this->actingAs($cajero)
            ->get(route('caja.index'))
            ->assertOk()
            ->assertViewHas('saldoEstimado', fn ($saldo) => (float) $saldo === 850.0)
            ->assertViewHas('totalVentas', fn ($total) => (float) $total === 300.0);
    }

    /**
     * Se puede vender en otra sucursal (decisión del paso 6), pero queda marcado
     * en el historial comparando la sucursal de la venta con la del corte.
     */
    public function test_historial_marca_las_ventas_hechas_en_otra_sucursal(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $corte = $this->abrirTurno($this->cajeroConPermisos($chalco));

        $this->crearVenta($corte, 300);
        $this->crearVenta($corte, 700, 'Tarjeta', $valle->id);

        $admin = $this->crearUsuario('Administrador General', $this->crearSucursal('Administración General'));

        $this->actingAs($admin)
            ->get(route('caja.historial'))
            ->assertOk()
            ->assertViewHas('cortes', function ($cortes) {
                $fila = $cortes->first();

                return (int) $fila->total_transacciones === 2
                    && (int) $fila->ventas_otra_sucursal === 1
                    && (float) $fila->total_ventas_efectivo === 300.0
                    && (float) $fila->total_ventas === 1000.0;
            });
    }
}