<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Cuando algo falla por dentro al mover inventario, el usuario ve un mensaje
 * claro (sin datos técnicos), el detalle queda en el log y no se guarda nada a medias.
 */
class InventarioErroresTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function usuarioDeInventario(Sucursal $sucursal): User
    {
        $usuario = $this->crearUsuario('Cajero', $sucursal);

        foreach (['inventario.storeEntrada', 'inventario.salida.store', 'inventario.traspaso.store'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    /** Una sucursal, un cajero y una llanta con 10 piezas. */
    private function escenario(): array
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeInventario($chalco);
        $producto = $this->crearProducto();
        $this->darStock($producto, $chalco, 10);

        return [$usuario, $chalco, $producto];
    }

    private function stockDe(Producto $producto, Sucursal $sucursal): int
    {
        return (int) StockSucursal::where('producto_id', $producto->id)
            ->where('sucursal_id', $sucursal->id)
            ->value('cantidad');
    }

    /** Hace que guardar cualquier movimiento falle con un error técnico. */
    private function romperLosMovimientos(): void
    {
        MovimientoInventario::creating(function () {
            throw new \RuntimeException('SQLSTATE[HY000] detalle tecnico interno');
        });
    }

    protected function tearDown(): void
    {
        MovimientoInventario::flushEventListeners();

        parent::tearDown();
    }

    public static function acciones(): array
    {
        return [
            'entrada'  => ['entrada', 'No se pudo registrar la entrada.'],
            'salida'   => ['salida', 'No se pudo registrar la salida.'],
            'traspaso' => ['traspaso', 'No se pudo realizar el traspaso.'],
        ];
    }

    #[DataProvider('acciones')]
    public function test_si_algo_falla_se_ve_un_mensaje_claro_y_no_el_error_tecnico(string $accion, string $inicio): void
    {
        [$usuario, $chalco, $producto] = $this->escenario();
        $valle = $this->crearSucursal('Valle de Chalco');

        $this->romperLosMovimientos();
        Log::spy();

        match ($accion) {
            'entrada' => $this->actingAs($usuario)->post(route('inventario.storeEntrada'), [
                'producto_id' => $producto->id, 'cantidad' => 5, 'costo_unitario' => 900,
            ]),
            'salida' => $this->actingAs($usuario)->post(route('inventario.salida.store'), [
                'producto_id' => $producto->id, 'cantidad' => 2, 'motivo' => 'merma',
            ]),
            'traspaso' => $this->actingAs($usuario)->post(route('inventario.traspaso.store'), [
                'producto_id' => $producto->id, 'sucursal_origen' => $chalco->id,
                'sucursal_destino' => $valle->id, 'cantidad' => 3,
            ]),
        };

        $mensaje = session('error');

        $this->assertStringStartsWith($inicio, $mensaje);
        $this->assertStringNotContainsString('SQLSTATE', $mensaje);
        $this->assertStringNotContainsString('tecnico', $mensaje);
    }

    #[DataProvider('acciones')]
    public function test_si_algo_falla_no_se_guarda_nada_a_medias(string $accion): void
    {
        [$usuario, $chalco, $producto] = $this->escenario();
        $valle = $this->crearSucursal('Valle de Chalco');
        $costoAntes = (float) $producto->fresh()->costo;

        $this->romperLosMovimientos();
        Log::spy();

        match ($accion) {
            'entrada' => $this->actingAs($usuario)->post(route('inventario.storeEntrada'), [
                'producto_id' => $producto->id, 'cantidad' => 5, 'costo_unitario' => 900,
            ]),
            'salida' => $this->actingAs($usuario)->post(route('inventario.salida.store'), [
                'producto_id' => $producto->id, 'cantidad' => 2, 'motivo' => 'merma',
            ]),
            'traspaso' => $this->actingAs($usuario)->post(route('inventario.traspaso.store'), [
                'producto_id' => $producto->id, 'sucursal_origen' => $chalco->id,
                'sucursal_destino' => $valle->id, 'cantidad' => 3,
            ]),
        };

        $this->assertSame(10, $this->stockDe($producto, $chalco));
        $this->assertSame(0, $this->stockDe($producto, $valle));
        $this->assertSame($costoAntes, (float) $producto->fresh()->costo);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[DataProvider('acciones')]
    public function test_si_algo_falla_el_detalle_queda_en_el_log(string $accion): void
    {
        [$usuario, $chalco, $producto] = $this->escenario();
        $valle = $this->crearSucursal('Valle de Chalco');

        $this->romperLosMovimientos();
        Log::spy();

        match ($accion) {
            'entrada' => $this->actingAs($usuario)->post(route('inventario.storeEntrada'), [
                'producto_id' => $producto->id, 'cantidad' => 5, 'costo_unitario' => 900,
            ]),
            'salida' => $this->actingAs($usuario)->post(route('inventario.salida.store'), [
                'producto_id' => $producto->id, 'cantidad' => 2, 'motivo' => 'merma',
            ]),
            'traspaso' => $this->actingAs($usuario)->post(route('inventario.traspaso.store'), [
                'producto_id' => $producto->id, 'sucursal_origen' => $chalco->id,
                'sucursal_destino' => $valle->id, 'cantidad' => 3,
            ]),
        };

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn ($mensaje, $contexto) => str_starts_with($mensaje, 'Inventario:')
                && $contexto['usuario_id'] === $usuario->id
                && $contexto['exception'] instanceof \RuntimeException);
    }

    public function test_las_observaciones_del_traspaso_dicen_el_nombre_de_la_sucursal(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario();
        $valle = $this->crearSucursal('Valle de Chalco');

        $this->actingAs($usuario)->post(route('inventario.traspaso.store'), [
            'producto_id' => $producto->id, 'sucursal_origen' => $chalco->id,
            'sucursal_destino' => $valle->id, 'cantidad' => 3,
        ]);

        $this->assertSame(
            'Traspaso enviado a Valle de Chalco',
            MovimientoInventario::where('tipo', 'salida')->value('observaciones')
        );
        $this->assertSame(
            'Traspaso recibido de Chalco',
            MovimientoInventario::where('tipo', 'entrada')->value('observaciones')
        );
    }
}
