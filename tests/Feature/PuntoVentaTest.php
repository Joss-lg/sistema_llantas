<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use App\Models\StockSucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Pruebas del cobro (PuntoVentaController@store).
 * El cobro responde JSON con success true/false (siempre código 200),
 * por eso se revisa el contenido de la respuesta.
 */
class PuntoVentaTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    private function cobrar(User $usuario, array $carrito, array $extra = [])
    {
        return $this->actingAs($usuario)->postJson(route('ventas.store'), array_merge([
            'carrito' => $carrito,
            'pagoCon' => 'Efectivo',
        ], $extra));
    }

    /** El caso más común: sucursal Chalco, usuario con turno abierto y una llanta con stock. */
    private function escenario(int $stock = 10, string $rol = 'Cajero'): array
    {
        $sucursal = $this->crearSucursal('Chalco');
        $usuario = $this->cajeroDeVentas($sucursal, $rol);
        $corte = $this->abrirTurno($usuario);
        $producto = $this->crearProducto();
        $this->darStock($producto, $sucursal, $stock);

        return [$usuario, $sucursal, $producto, $corte];
    }

    private function nadaGuardado(): void
    {
        $this->assertSame(0, Venta::count());
        $this->assertSame(0, VentaDetalle::count());
        $this->assertSame(0, MovimientoInventario::count());
    }

    private function stockDe($producto, $sucursal): int
    {
        return (int) StockSucursal::where('producto_id', $producto->id)
            ->where('sucursal_id', $sucursal->id)
            ->value('cantidad');
    }

    /* ===================== COBRO CORRECTO ===================== */

    public function test_cobro_correcto_guarda_la_venta_descuenta_stock_y_registra_la_salida(): void
    {
        [$usuario, $sucursal, $producto, $corte] = $this->escenario(10);

        $respuesta = $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 2]]);
        $respuesta->assertOk()->assertJson(['success' => true]);

        $venta = Venta::firstOrFail();
        $this->assertSame(route('ventas.ticket', $venta->id), $respuesta->json('ticket_url'));
        $this->assertSame($sucursal->id, (int) $venta->sucursal_id);
        $this->assertSame($usuario->id, (int) $venta->user_id);
        $this->assertSame($corte->id, (int) $venta->corte_caja_id);
        $this->assertSame(1800.0, (float) $venta->total);
        $this->assertSame('Efectivo', $venta->metodo_pago);
        $this->assertSame('menudeo', $venta->tipo_precio);
        $this->assertSame('Público General', $venta->nombre_cliente_temporal);

        $detalle = VentaDetalle::firstOrFail();
        $this->assertSame($venta->id, (int) $detalle->venta_id);
        $this->assertSame(2, (int) $detalle->cantidad);
        $this->assertSame(900.0, (float) $detalle->precio_unitario);
        $this->assertSame(1800.0, (float) $detalle->subtotal);
        $this->assertSame('PIRELLI 205/55R16', $detalle->nombre_producto);

        $this->assertSame(8, $this->stockDe($producto, $sucursal));

        $movimiento = MovimientoInventario::firstOrFail();
        $this->assertSame('salida', $movimiento->tipo);
        $this->assertSame(2, (int) $movimiento->cantidad);
        $this->assertSame('Venta ' . $venta->folio, $movimiento->motivo);
        $this->assertSame($sucursal->id, (int) $movimiento->sucursal_id);
    }

    public function test_el_precio_sale_de_la_base_aunque_el_navegador_mande_otro(): void
    {
        [$usuario, , $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1, 'precio' => 1]])
            ->assertJson(['success' => true]);

        $this->assertSame(900.0, (float) Venta::firstOrFail()->total);
    }

    public function test_carrito_vacio_no_cobra(): void
    {
        [$usuario] = $this->escenario();

        $this->cobrar($usuario, [])
            ->assertOk()
            ->assertJson(['success' => false, 'message' => 'El carrito está vacío.']);

        $this->nadaGuardado();
    }

    public function test_sin_turno_abierto_el_candado_manda_a_caja_y_no_cobra(): void
    {
        $sucursal = $this->crearSucursal('Chalco');
        $usuario = $this->cajeroDeVentas($sucursal);
        $producto = $this->crearProducto();
        $this->darStock($producto, $sucursal, 10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]])
            ->assertRedirect(route('caja.index'));

        $this->nadaGuardado();
        $this->assertSame(10, $this->stockDe($producto, $sucursal));
    }

    /* ===================== MÉTODO DE PAGO ===================== */

    public static function metodosNormalizados(): array
    {
        return [
            'minúsculas'    => ['efectivo', 'Efectivo'],
            'mayúsculas'    => ['TARJETA', 'Tarjeta'],
            'transferencia' => ['transferencia', 'Transferencia'],
        ];
    }

    #[DataProvider('metodosNormalizados')]
    public function test_el_metodo_de_pago_se_normaliza(string $enviado, string $guardado): void
    {
        [$usuario, , $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['pagoCon' => $enviado])
            ->assertJson(['success' => true]);

        $this->assertSame($guardado, Venta::firstOrFail()->metodo_pago);
    }

    public function test_metodo_de_pago_invalido_no_cobra(): void
    {
        [$usuario, , $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['pagoCon' => 'Cheque'])
            ->assertJson([
                'success' => false,
                'message' => 'Método de pago no válido. Usa Efectivo, Tarjeta o Transferencia.',
            ]);

        $this->nadaGuardado();
    }

    /* ===================== CANTIDADES Y STOCK ===================== */

    public static function cantidadesInvalidas(): array
    {
        return [
            'cero'    => [0],
            'decimal' => [1.5],
            'texto'   => ['abc'],
            'negativa' => [-1],
            'vacía'   => [null],
        ];
    }

    #[DataProvider('cantidadesInvalidas')]
    public function test_cantidad_invalida_no_cobra(mixed $cantidad): void
    {
        [$usuario, $sucursal, $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => $cantidad]])
            ->assertJson(['success' => false, 'message' => 'Hay una cantidad no válida en el carrito.']);

        $this->nadaGuardado();
        $this->assertSame(10, $this->stockDe($producto, $sucursal));
    }

    public function test_stock_insuficiente_no_guarda_nada_ni_siquiera_de_los_otros_productos(): void
    {
        [$usuario, $sucursal, $producto] = $this->escenario(10);
        $escaso = $this->crearProducto(['marca' => 'MICHELIN', 'medida' => '195/65R15']);
        $this->darStock($escaso, $sucursal, 1);

        $respuesta = $this->cobrar($usuario, [
            ['producto_id' => $producto->id, 'cantidad' => 1],
            ['producto_id' => $escaso->id, 'cantidad' => 5],
        ]);

        $respuesta->assertOk()->assertJson(['success' => false]);
        $this->assertStringContainsString('Disponibles en esta sucursal: 1', $respuesta->json('message'));

        // Todo se deshace: venta, detalles, movimientos y el stock del producto que sí alcanzaba.
        $this->nadaGuardado();
        $this->assertSame(10, $this->stockDe($producto, $sucursal));
        $this->assertSame(1, $this->stockDe($escaso, $sucursal));
    }

    public function test_producto_sin_registro_de_stock_en_la_sucursal_no_cobra(): void
    {
        [$usuario, , ] = $this->escenario(10);
        $sinStock = $this->crearProducto(['marca' => 'GOODYEAR', 'medida' => '215/60R16']);

        $respuesta = $this->cobrar($usuario, [['producto_id' => $sinStock->id, 'cantidad' => 1]]);

        $respuesta->assertJson(['success' => false]);
        $this->assertStringContainsString('No existe registro de inventario', $respuesta->json('message'));
        $this->nadaGuardado();
    }

    public function test_producto_desactivado_no_se_puede_cobrar(): void
    {
        [$usuario, $sucursal] = $this->escenario(10);
        $inactivo = $this->crearProducto(['marca' => 'VIEJA', 'medida' => '100/10/10', 'estado' => false]);
        $this->darStock($inactivo, $sucursal, 5);

        $this->cobrar($usuario, [['producto_id' => $inactivo->id, 'cantidad' => 1]])
            ->assertJson([
                'success' => false,
                'message' => 'Un producto del carrito ya no existe o está desactivado. Recarga la página.',
            ]);

        $this->nadaGuardado();
    }

    public function test_un_servicio_se_cobra_sin_descontar_inventario(): void
    {
        [$usuario] = $this->escenario(10);
        $servicio = $this->crearProducto([
            'tipo'           => 'Servicio',
            'marca'          => 'ALINEACION',
            'medida'         => 'GENERAL',
            'descripcion'    => 'Alineación y balanceo',
            'precio_publico' => 250,
        ]);

        $this->cobrar($usuario, [['producto_id' => $servicio->id, 'cantidad' => 1]])
            ->assertJson(['success' => true]);

        $this->assertSame(250.0, (float) Venta::firstOrFail()->total);
        $this->assertSame('Alineación y balanceo', VentaDetalle::firstOrFail()->nombre_producto);
        $this->assertSame(0, MovimientoInventario::count());
    }

    /* ===================== MAYOREO ===================== */

    public function test_un_administrador_puede_vender_a_precio_de_mayoreo(): void
    {
        [$usuario, , $producto] = $this->escenario(10, 'Administrador');

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['tipo_precio' => 'mayoreo'])
            ->assertJson(['success' => true]);

        $venta = Venta::firstOrFail();
        $this->assertSame(700.0, (float) $venta->total);
        $this->assertSame('mayoreo', $venta->tipo_precio);
    }

    public function test_mayoreo_sin_precio_de_mayoreo_cobra_el_precio_publico(): void
    {
        [$usuario, $sucursal] = $this->escenario(10, 'Administrador');
        $sinMayoreo = $this->crearProducto(['marca' => 'HANKOOK', 'medida' => '185/65R15', 'precio_mayoreo' => 0]);
        $this->darStock($sinMayoreo, $sucursal, 5);

        $this->cobrar($usuario, [['producto_id' => $sinMayoreo->id, 'cantidad' => 1]], ['tipo_precio' => 'mayoreo'])
            ->assertJson(['success' => true]);

        $this->assertSame(900.0, (float) Venta::firstOrFail()->total);
    }

    public function test_un_cajero_no_puede_vender_a_mayoreo(): void
    {
        [$usuario, , $producto] = $this->escenario(10, 'Cajero');

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['tipo_precio' => 'mayoreo'])
            ->assertJson([
                'success' => false,
                'message' => 'Tu usuario no tiene permiso para vender a precio de mayoreo.',
            ]);

        $this->nadaGuardado();
    }

    public function test_tipo_de_precio_invalido_no_cobra(): void
    {
        [$usuario, , $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['tipo_precio' => 'regalado'])
            ->assertJson(['success' => false, 'message' => 'Tipo de precio no válido.']);

        $this->nadaGuardado();
    }

    /* ===================== FACTURA ===================== */

    public function test_facturar_sin_cliente_no_cobra(): void
    {
        [$usuario, , $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['requiereFactura' => true])
            ->assertJson([
                'success' => false,
                'message' => 'Para facturar debes elegir un cliente que tenga RFC registrado.',
            ]);

        $this->nadaGuardado();
    }

    public function test_facturar_con_cliente_sin_rfc_no_cobra(): void
    {
        [$usuario, , $producto] = $this->escenario(10);
        $cliente = $this->crearCliente(['nombre' => 'Jose Emilio', 'rfc' => null]);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], [
            'requiereFactura' => true,
            'cliente_id'      => $cliente->id,
        ])->assertJson([
            'success' => false,
            'message' => 'Para facturar debes elegir un cliente que tenga RFC registrado.',
        ]);

        $this->nadaGuardado();
    }

    public function test_facturar_con_cliente_con_rfc_suma_el_iva_del_16_por_ciento(): void
    {
        [$usuario, , $producto] = $this->escenario(10);
        $cliente = $this->crearCliente(['nombre' => 'Fernando Martinez', 'rfc' => 'XAXX010101000']);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], [
            'requiereFactura' => true,
            'cliente_id'      => $cliente->id,
        ])->assertJson(['success' => true]);

        // 900 + 16% = 1,044
        $venta = Venta::firstOrFail();
        $this->assertSame(1044.0, (float) $venta->total);
        $this->assertTrue((bool) $venta->requiere_factura);
        $this->assertSame($cliente->id, (int) $venta->cliente_id);
        $this->assertSame('Fernando Martinez', $venta->nombre_cliente_temporal);
    }

    public function test_cliente_que_ya_no_existe_no_cobra(): void
    {
        [$usuario, , $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['cliente_id' => 999999])
            ->assertJson([
                'success' => false,
                'message' => 'El cliente seleccionado ya no existe. Recarga la página.',
            ]);

        $this->nadaGuardado();
    }

    /* ===================== PAGO Y CAMBIO ===================== */

    public function test_se_guarda_con_cuanto_pago_el_cliente_y_el_cambio(): void
    {
        [$usuario, , $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['montoRecibido' => 1000])
            ->assertJson(['success' => true]);

        $venta = Venta::firstOrFail();
        $this->assertSame(1000.0, (float) $venta->pago_con);
        $this->assertSame(100.0, (float) $venta->cambio);
    }

    public function test_sin_monto_recibido_se_toma_el_total_exacto(): void
    {
        [$usuario, , $producto] = $this->escenario(10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]])
            ->assertJson(['success' => true]);

        $venta = Venta::firstOrFail();
        $this->assertSame(900.0, (float) $venta->pago_con);
        $this->assertSame(0.0, (float) $venta->cambio);
    }

    /* ===================== SUCURSAL DE LA VENTA ===================== */

    public function test_un_cajero_siempre_vende_en_su_sucursal_aunque_mande_otra(): void
    {
        [$usuario, $chalco, $producto] = $this->escenario(10);
        $valle = $this->crearSucursal('Valle de Chalco');
        $this->darStock($producto, $valle, 10);

        $this->cobrar($usuario, [['producto_id' => $producto->id, 'cantidad' => 1]], ['sucursal_id' => $valle->id])
            ->assertJson(['success' => true]);

        $this->assertSame($chalco->id, (int) Venta::firstOrFail()->sucursal_id);
        $this->assertSame(9, $this->stockDe($producto, $chalco));
        $this->assertSame(10, $this->stockDe($producto, $valle));
    }

    public function test_el_administrador_general_puede_elegir_la_sucursal_donde_vende(): void
    {
        $propia = $this->crearSucursal('Administración General');
        $valle = $this->crearSucursal('Valle de Chalco');
        $admin = $this->cajeroDeVentas($propia, 'Administrador General');
        $this->abrirTurno($admin);
        $producto = $this->crearProducto();
        $this->darStock($producto, $valle, 10);

        $this->cobrar($admin, [['producto_id' => $producto->id, 'cantidad' => 1]], ['sucursal_id' => $valle->id])
            ->assertJson(['success' => true]);

        $this->assertSame($valle->id, (int) Venta::firstOrFail()->sucursal_id);
        $this->assertSame(9, $this->stockDe($producto, $valle));
    }
}