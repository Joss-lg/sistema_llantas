<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Pruebas de entradas, salidas, traspasos e historial de inventario
 * (MovimientoInventarioController).
 */
class InventarioMovimientosTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function usuarioDeInventario(?Sucursal $sucursal = null, string $rol = 'Cajero'): User
    {
        $usuario = $this->crearUsuario($rol, $sucursal);

        foreach (['inventario.storeEntrada', 'inventario.salida.store',
                  'inventario.traspaso.store', 'inventario.historial'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    /** Sucursal Chalco, un cajero ahí y una llanta con stock en Chalco. */
    private function escenario(int $stock = 10): array
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeInventario($chalco);
        $producto = $this->crearProducto();
        $this->darStock($producto, $chalco, $stock);

        return [$usuario, $chalco, $producto];
    }

    /** Stock de un producto en una sucursal (null si no tiene registro). */
    private function stockDe(Producto $producto, Sucursal $sucursal): ?int
    {
        $cantidad = StockSucursal::where('producto_id', $producto->id)
            ->where('sucursal_id', $sucursal->id)
            ->value('cantidad');

        return $cantidad === null ? null : (int) $cantidad;
    }

    private function sinMovimientos(): void
    {
        $this->assertSame(0, MovimientoInventario::count());
    }

    private function movimiento(Producto $producto, Sucursal $sucursal, User $usuario, int $cantidad = 5): MovimientoInventario
    {
        return MovimientoInventario::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $sucursal->id,
            'usuario_id'  => $usuario->id,
            'tipo'        => 'entrada',
            'cantidad'    => $cantidad,
            'motivo'      => 'compra',
            'fecha'       => now(),
        ]);
    }

    /* ===================== ENTRADAS ===================== */

    public function test_entrada_suma_stock_actualiza_costo_y_precios_y_registra_el_movimiento(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(5);

        $this->actingAs($usuario)
            ->post(route('inventario.storeEntrada'), [
                'producto_id'    => $producto->id,
                'cantidad'       => 10,
                'costo_unitario' => 600,
                'precio_publico' => 1000,
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('success', 'Entrada registrada y precios actualizados con éxito.');

        $this->assertSame(15, $this->stockDe($producto, $chalco));

        // El mayoreo no se capturó: conserva el que tenía.
        $producto->refresh();
        $this->assertSame(600.0, $producto->costo);
        $this->assertSame(1000.0, $producto->precio_publico);
        $this->assertSame(700.0, $producto->precio_mayoreo);

        $movimiento = MovimientoInventario::firstOrFail();
        $this->assertSame('entrada', $movimiento->tipo);
        $this->assertSame(10, (int) $movimiento->cantidad);
        $this->assertSame('compra', $movimiento->motivo);
        $this->assertSame(600.0, (float) $movimiento->costo_unitario);
        $this->assertSame($usuario->id, (int) $movimiento->usuario_id);
        $this->assertSame($chalco->id, (int) $movimiento->sucursal_id);
    }

    public function test_entrada_en_una_sucursal_sin_registro_de_stock_lo_crea(): void
    {
        $valle = $this->crearSucursal('Valle de Chalco');
        $usuario = $this->usuarioDeInventario($valle);
        $producto = $this->crearProducto();

        $this->actingAs($usuario)
            ->post(route('inventario.storeEntrada'), [
                'producto_id'    => $producto->id,
                'cantidad'       => 4,
                'costo_unitario' => 500,
            ])
            ->assertRedirect(route('inventario.index'));

        $this->assertSame(4, $this->stockDe($producto, $valle));
    }

    public static function entradasInvalidas(): array
    {
        return [
            'cantidad cero'       => [['cantidad' => 0], 'cantidad'],
            'cantidad decimal'    => [['cantidad' => 1.5], 'cantidad'],
            'sin costo'           => [['costo_unitario' => ''], 'costo_unitario'],
            'producto inexistente' => [['producto_id' => 999999], 'producto_id'],
        ];
    }

    #[DataProvider('entradasInvalidas')]
    public function test_entrada_con_datos_invalidos_se_rechaza(array $cambio, string $campo): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(5);

        $datos = array_merge([
            'producto_id'    => $producto->id,
            'cantidad'       => 1,
            'costo_unitario' => 100,
        ], $cambio);

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.storeEntrada'), $datos)
            ->assertSessionHasErrors($campo);

        $this->assertSame(5, $this->stockDe($producto, $chalco));
        $this->sinMovimientos();
    }

    public function test_un_cajero_registra_la_entrada_siempre_en_su_sucursal(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(5);
        $valle = $this->crearSucursal('Valle de Chalco');

        $this->actingAs($usuario)
            ->post(route('inventario.storeEntrada'), [
                'producto_id'    => $producto->id,
                'cantidad'       => 3,
                'costo_unitario' => 500,
                'sucursal_id'    => $valle->id,
            ]);

        $this->assertSame(8, $this->stockDe($producto, $chalco));
        $this->assertNull($this->stockDe($producto, $valle));
    }

    public function test_el_administrador_general_puede_elegir_la_sucursal_de_la_entrada(): void
    {
        $admin = $this->usuarioDeInventario($this->crearSucursal('Administración General'), 'Administrador General');
        $valle = $this->crearSucursal('Valle de Chalco');
        $producto = $this->crearProducto();

        $this->actingAs($admin)
            ->post(route('inventario.storeEntrada'), [
                'producto_id'    => $producto->id,
                'cantidad'       => 3,
                'costo_unitario' => 500,
                'sucursal_id'    => $valle->id,
            ]);

        $this->assertSame(3, $this->stockDe($producto, $valle));
    }

    /* ===================== SALIDAS ===================== */

    public function test_salida_descuenta_stock_y_registra_motivo_y_observaciones(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(5);

        $this->actingAs($usuario)
            ->post(route('inventario.salida.store'), [
                'producto_id'   => $producto->id,
                'cantidad'      => 2,
                'motivo'        => 'Merma',
                'observaciones' => 'Se dañó en bodega',
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('success', 'Salida por Merma registrada correctamente.');

        $this->assertSame(3, $this->stockDe($producto, $chalco));

        $movimiento = MovimientoInventario::firstOrFail();
        $this->assertSame('salida', $movimiento->tipo);
        $this->assertSame(2, (int) $movimiento->cantidad);
        $this->assertSame('Merma', $movimiento->motivo);
        $this->assertSame('Se dañó en bodega', $movimiento->observaciones);
    }

    public function test_salida_con_stock_insuficiente_no_guarda_nada(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(3);

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.salida.store'), [
                'producto_id' => $producto->id,
                'cantidad'    => 5,
                'motivo'      => 'Daño',
            ])
            ->assertSessionHas('error', 'Stock insuficiente. Disponible: 3 pzas.');

        $this->assertSame(3, $this->stockDe($producto, $chalco));
        $this->sinMovimientos();
    }

    public function test_salida_de_un_producto_sin_registro_de_stock_dice_disponible_cero(): void
    {
        [$usuario] = $this->escenario(5);
        $sinStock = $this->crearProducto(['marca' => 'MICHELIN', 'medida' => '195/65R15']);

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.salida.store'), [
                'producto_id' => $sinStock->id,
                'cantidad'    => 1,
                'motivo'      => 'Daño',
            ])
            ->assertSessionHas('error', 'Stock insuficiente. Disponible: 0 pzas.');

        $this->sinMovimientos();
    }

    public function test_salida_sin_motivo_se_rechaza(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(5);

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.salida.store'), [
                'producto_id' => $producto->id,
                'cantidad'    => 1,
                'motivo'      => '',
            ])
            ->assertSessionHasErrors('motivo');

        $this->assertSame(5, $this->stockDe($producto, $chalco));
    }

    public function test_un_cajero_da_salida_siempre_en_su_sucursal_aunque_mande_otra(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(5);
        $valle = $this->crearSucursal('Valle de Chalco');
        $this->darStock($producto, $valle, 5);

        $this->actingAs($usuario)
            ->post(route('inventario.salida.store'), [
                'producto_id' => $producto->id,
                'cantidad'    => 2,
                'motivo'      => 'Pérdida',
                'sucursal_id' => $valle->id,
            ]);

        $this->assertSame(3, $this->stockDe($producto, $chalco));
        $this->assertSame(5, $this->stockDe($producto, $valle));
    }

    /* ===================== TRASPASOS ===================== */

    public function test_traspaso_mueve_el_stock_y_registra_salida_y_entrada(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);
        $valle = $this->crearSucursal('Valle de Chalco');

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.traspaso.store'), [
                'producto_id'      => $producto->id,
                'sucursal_origen'  => $chalco->id,
                'sucursal_destino' => $valle->id,
                'cantidad'         => 4,
            ])
            ->assertSessionHas('success', 'Traspaso de llantas realizado con éxito.');

        $this->assertSame(6, $this->stockDe($producto, $chalco));
        $this->assertSame(4, $this->stockDe($producto, $valle));
        $this->assertSame(2, MovimientoInventario::count());

        $salida = MovimientoInventario::where('tipo', 'salida')->firstOrFail();
        $this->assertSame($chalco->id, (int) $salida->sucursal_id);
        $this->assertSame(4, (int) $salida->cantidad);
        $this->assertSame('traspaso', $salida->motivo);

        $entrada = MovimientoInventario::where('tipo', 'entrada')->firstOrFail();
        $this->assertSame($valle->id, (int) $entrada->sucursal_id);
        $this->assertSame(4, (int) $entrada->cantidad);
        $this->assertSame('traspaso', $entrada->motivo);
        $this->assertSame(500.0, (float) $entrada->costo_unitario);
    }

    public function test_traspaso_con_stock_insuficiente_no_guarda_nada(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(2);
        $valle = $this->crearSucursal('Valle de Chalco');

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.traspaso.store'), [
                'producto_id'      => $producto->id,
                'sucursal_origen'  => $chalco->id,
                'sucursal_destino' => $valle->id,
                'cantidad'         => 5,
            ])
            ->assertSessionHas('error', 'Stock insuficiente en origen. Disponible: 2 pzas.');

        $this->assertSame(2, $this->stockDe($producto, $chalco));
        $this->assertNull($this->stockDe($producto, $valle));
        $this->sinMovimientos();
    }

    public function test_un_cajero_no_puede_traspasar_desde_otra_sucursal(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);
        $valle = $this->crearSucursal('Valle de Chalco');
        $this->darStock($producto, $valle, 10);

        // Intenta sacar de Valle (que no es la suya) hacia Chalco.
        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.traspaso.store'), [
                'producto_id'      => $producto->id,
                'sucursal_origen'  => $valle->id,
                'sucursal_destino' => $chalco->id,
                'cantidad'         => 3,
            ])
            ->assertSessionHas('error', 'Solo puedes traspasar stock desde tu propia sucursal.');

        $this->assertSame(10, $this->stockDe($producto, $valle));
        $this->assertSame(10, $this->stockDe($producto, $chalco));
        $this->sinMovimientos();
    }

    public function test_el_administrador_general_puede_traspasar_desde_cualquier_sucursal(): void
    {
        $admin = $this->usuarioDeInventario($this->crearSucursal('Administración General'), 'Administrador General');
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $producto = $this->crearProducto();
        $this->darStock($producto, $valle, 10);

        $this->actingAs($admin)
            ->from(route('inventario.index'))
            ->post(route('inventario.traspaso.store'), [
                'producto_id'      => $producto->id,
                'sucursal_origen'  => $valle->id,
                'sucursal_destino' => $chalco->id,
                'cantidad'         => 3,
            ])
            ->assertSessionHas('success', 'Traspaso de llantas realizado con éxito.');

        $this->assertSame(7, $this->stockDe($producto, $valle));
        $this->assertSame(3, $this->stockDe($producto, $chalco));
    }

    public function test_traspaso_con_origen_igual_al_destino_se_rechaza(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.traspaso.store'), [
                'producto_id'      => $producto->id,
                'sucursal_origen'  => $chalco->id,
                'sucursal_destino' => $chalco->id,
                'cantidad'         => 1,
            ])
            ->assertSessionHasErrors('sucursal_destino');

        $this->assertSame(10, $this->stockDe($producto, $chalco));
    }

    public function test_traspaso_con_cantidad_cero_se_rechaza(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);
        $valle = $this->crearSucursal('Valle de Chalco');

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.traspaso.store'), [
                'producto_id'      => $producto->id,
                'sucursal_origen'  => $chalco->id,
                'sucursal_destino' => $valle->id,
                'cantidad'         => 0,
            ])
            ->assertSessionHasErrors('cantidad');

        $this->sinMovimientos();
    }

    /* ===================== HISTORIAL ===================== */

    public function test_el_historial_de_un_cajero_solo_trae_movimientos_de_su_sucursal(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);
        $valle = $this->crearSucursal('Valle de Chalco');
        $propio = $this->movimiento($producto, $chalco, $usuario, 5);
        $this->movimiento($producto, $valle, $usuario, 7);

        $this->actingAs($usuario)
            ->get(route('inventario.historial'))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->pluck('id')->map(fn ($id) => (int) $id)->all() === [$propio->id])
            ->assertViewHas('totalEntradas', fn ($total) => (int) $total === 5);
    }

    public function test_el_historial_del_administrador_general_trae_todas_las_sucursales(): void
    {
        $admin = $this->usuarioDeInventario($this->crearSucursal('Administración General'), 'Administrador General');
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $producto = $this->crearProducto();
        $uno = $this->movimiento($producto, $chalco, $admin, 5);
        $dos = $this->movimiento($producto, $valle, $admin, 7);

        $esperados = [$uno->id, $dos->id];
        sort($esperados);

        $this->actingAs($admin)
            ->get(route('inventario.historial'))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all() === $esperados)
            ->assertViewHas('totalEntradas', fn ($total) => (int) $total === 12);
    }

    /* ============ HISTORIAL: totales, filtros y orden ============ */

    /** Un movimiento guardado directo en la base; $datos cambia cualquier campo. */
    private function movimientoCon(Producto $producto, Sucursal $sucursal, User $usuario, array $datos = []): MovimientoInventario
    {
        return MovimientoInventario::create(array_merge([
            'producto_id' => $producto->id,
            'sucursal_id' => $sucursal->id,
            'usuario_id'  => $usuario->id,
            'tipo'        => 'entrada',
            'cantidad'    => 5,
            'motivo'      => 'compra',
            'fecha'       => now(),
        ], $datos));
    }

    public function test_los_totales_del_historial_cuentan_todo_el_filtro_y_no_solo_la_pagina(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        // 25 entradas de 2 piezas y 1 salida de 3: la pagina trae 20, pero los totales deben contar las 26.
        for ($i = 0; $i < 25; $i++) {
            $this->movimientoCon($producto, $chalco, $usuario, ['cantidad' => 2]);
        }
        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'cantidad' => 3, 'motivo' => 'Merma']);

        $this->actingAs($usuario)
            ->get(route('inventario.historial'))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->total() === 26 && $movs->count() === 20)
            ->assertViewHas('totalEntradas', fn ($total) => (int) $total === 50)
            ->assertViewHas('totalSalidas', fn ($total) => (int) $total === 3)
            ->assertViewHas('totalMovimientos', fn ($total) => (int) $total === 26);
    }

    public function test_los_totales_del_historial_no_cambian_en_la_segunda_pagina(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        for ($i = 0; $i < 25; $i++) {
            $this->movimientoCon($producto, $chalco, $usuario, ['cantidad' => 2]);
        }

        $this->actingAs($usuario)
            ->get(route('inventario.historial', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->count() === 5)
            ->assertViewHas('totalEntradas', fn ($total) => (int) $total === 50)
            ->assertViewHas('totalMovimientos', fn ($total) => (int) $total === 25);
    }

    public function test_el_filtro_de_tipo_solo_trae_ese_tipo_y_sus_totales(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $this->movimientoCon($producto, $chalco, $usuario, ['cantidad' => 5]);
        $this->movimientoCon($producto, $chalco, $usuario, ['cantidad' => 7]);
        $salida = $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'cantidad' => 3, 'motivo' => 'Merma']);

        $this->actingAs($usuario)
            ->get(route('inventario.historial', ['tipo' => 'salida']))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->pluck('id')->map(fn ($id) => (int) $id)->all() === [$salida->id])
            ->assertViewHas('totalEntradas', fn ($total) => (int) $total === 0)
            ->assertViewHas('totalSalidas', fn ($total) => (int) $total === 3)
            ->assertViewHas('totalMovimientos', fn ($total) => (int) $total === 1);
    }

    public function test_los_movimientos_con_la_misma_fecha_salen_en_orden_estable(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $fecha = '2026-11-10 10:00:00';
        $primero = $this->movimientoCon($producto, $chalco, $usuario, ['fecha' => $fecha]);
        $segundo = $this->movimientoCon($producto, $chalco, $usuario, ['fecha' => $fecha]);
        $tercero = $this->movimientoCon($producto, $chalco, $usuario, ['fecha' => $fecha]);

        // Con la misma fecha, el id mas nuevo va primero.
        $this->actingAs($usuario)
            ->get(route('inventario.historial'))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->pluck('id')->map(fn ($id) => (int) $id)->all() === [$tercero->id, $segundo->id, $primero->id]);
    }

    public function test_el_filtro_de_motivo_venta_junta_todas_las_ventas(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'cantidad' => 1, 'motivo' => 'Venta VNT-AAA-111']);
        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'cantidad' => 2, 'motivo' => 'Venta VNT-BBB-222']);
        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'cantidad' => 4, 'motivo' => 'Merma']);
        $this->movimientoCon($producto, $chalco, $usuario, ['cantidad' => 9, 'motivo' => 'compra']);

        $this->actingAs($usuario)
            ->get(route('inventario.historial', ['motivo' => 'Venta']))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->total() === 2)
            ->assertViewHas('totalSalidas', fn ($total) => (int) $total === 3)
            ->assertViewHas('totalEntradas', fn ($total) => (int) $total === 0);
    }

    public function test_la_lista_de_motivos_junta_las_ventas_en_una_sola_opcion(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'motivo' => 'Venta VNT-AAA-111']);
        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'motivo' => 'Venta VNT-BBB-222']);
        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'motivo' => 'Merma']);
        $this->movimientoCon($producto, $chalco, $usuario, ['motivo' => 'compra']);

        $this->actingAs($usuario)
            ->get(route('inventario.historial'))
            ->assertOk()
            ->assertViewHas('motivosDisponibles', fn ($motivos) => $motivos->all() === ['compra', 'Merma', 'Venta']);
    }

    public function test_sin_ventas_la_lista_de_motivos_no_trae_la_opcion_venta(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $this->movimientoCon($producto, $chalco, $usuario, ['motivo' => 'compra']);

        $this->actingAs($usuario)
            ->get(route('inventario.historial'))
            ->assertOk()
            ->assertViewHas('motivosDisponibles', fn ($motivos) => $motivos->all() === ['compra']);
    }

    public function test_un_motivo_normal_sigue_filtrando_exacto(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $compra = $this->movimientoCon($producto, $chalco, $usuario, ['motivo' => 'compra']);
        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'motivo' => 'Merma']);
        $this->movimientoCon($producto, $chalco, $usuario, ['tipo' => 'salida', 'motivo' => 'Venta VNT-AAA-111']);

        $this->actingAs($usuario)
            ->get(route('inventario.historial', ['motivo' => 'compra']))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->pluck('id')->map(fn ($id) => (int) $id)->all() === [$compra->id]);
    }

    public function test_el_rango_de_fechas_del_historial_incluye_el_ultimo_dia_completo(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $this->movimientoCon($producto, $chalco, $usuario, ['cantidad' => 1, 'fecha' => '2026-11-01 10:00:00']);
        $this->movimientoCon($producto, $chalco, $usuario, ['cantidad' => 2, 'fecha' => '2026-11-15 23:30:00']);
        $this->movimientoCon($producto, $chalco, $usuario, ['cantidad' => 4, 'fecha' => '2026-11-20 09:00:00']);

        $this->actingAs($usuario)
            ->get(route('inventario.historial', ['fecha_desde' => '2026-11-05', 'fecha_hasta' => '2026-11-15']))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->total() === 1)
            ->assertViewHas('totalEntradas', fn ($total) => (int) $total === 2);
    }

    public function test_la_busqueda_por_producto_filtra_por_marca(): void
    {
        [$usuario, $chalco, $pirelli] = $this->escenario(10);
        $michelin = $this->crearProducto(['marca' => 'MICHELIN', 'medida' => '195/65R15']);

        $this->movimientoCon($pirelli, $chalco, $usuario);
        $buscado = $this->movimientoCon($michelin, $chalco, $usuario);

        $this->actingAs($usuario)
            ->get(route('inventario.historial', ['q' => 'MICHELIN']))
            ->assertOk()
            ->assertViewHas('movimientos', fn ($movs) => $movs->pluck('id')->map(fn ($id) => (int) $id)->all() === [$buscado->id])
            ->assertViewHas('totalMovimientos', fn ($total) => (int) $total === 1);
    }

    public function test_el_historial_carga_producto_sucursal_y_usuario_juntos(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);

        $this->movimientoCon($producto, $chalco, $usuario);

        $this->actingAs($usuario)
            ->get(route('inventario.historial'))
            ->assertOk()
            ->assertViewHas('movimientos', function ($movs) {
                $mov = $movs->first();

                return $mov->relationLoaded('producto')
                    && $mov->relationLoaded('sucursal')
                    && $mov->relationLoaded('usuario');
            });
    }

    public function test_el_historial_muestra_quien_registro_el_movimiento_y_sus_observaciones(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeInventario($chalco);
        $usuario->update(['name' => 'Pedro Prueba']);
        $producto = $this->crearProducto();

        $this->movimientoCon($producto, $chalco, $usuario, ['observaciones' => 'Caja dañada en bodega']);

        $this->actingAs($usuario)
            ->get(route('inventario.historial'))
            ->assertOk()
            ->assertSee('Pedro Prueba')
            ->assertSee('Caja dañada en bodega')
            ->assertSee('Compra');
    }
}
