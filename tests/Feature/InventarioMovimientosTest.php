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
}