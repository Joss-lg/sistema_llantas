<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Pruebas de lo que carga la pantalla del Punto de Venta (PuntoVentaController@index).
 * El cobro ya lo prueba PuntoVentaTest.
 */
class PuntoVentaPantallaTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** Un cajero en Chalco con turno abierto (hace falta para entrar a la pantalla). */
    private function cajeroConTurno(?string $rol = 'Cajero'): array
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->cajeroDeVentas($chalco, $rol);
        $this->abrirTurno($usuario);

        return [$usuario, $chalco];
    }

    private function productosDe($respuesta): array
    {
        return $respuesta->viewData('productos')->map->toArray()->all();
    }

    public function test_la_pantalla_abre_y_muestra_lo_nuevo_del_cobro(): void
    {
        [$cajero, $chalco] = $this->cajeroConTurno();
        $this->darStock($this->crearProducto(), $chalco, 10);

        $this->actingAs($cajero)->get(route('ventas.index'))
            ->assertOk()
            ->assertSee('Productos y servicios', false)
            ->assertSee('PIRELLI', false)
            ->assertSee('Recibe (opcional)', false)
            ->assertSee('Transferencia', false)
            ->assertSee('pv-scroll', false);
    }

    public function test_sin_el_permiso_de_ventas_recibe_403(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $sinPermiso = $this->crearUsuario('Cajero', $chalco);

        $this->actingAs($sinPermiso)->get(route('ventas.index'))->assertForbidden();
    }

    public function test_un_cajero_recibe_solo_el_stock_de_su_sucursal_y_nada_de_las_demas(): void
    {
        [$cajero, $chalco] = $this->cajeroConTurno();
        $valle = $this->crearSucursal('Valle de Chalco');
        $producto = $this->crearProducto();
        $this->darStock($producto, $chalco, 10);
        $this->darStock($producto, $valle, 77);

        $respuesta = $this->actingAs($cajero)->get(route('ventas.index'))->assertOk();
        $datos = $this->productosDe($respuesta)[0];

        $this->assertSame(10, $datos['stock_cantidad']);
        $this->assertArrayNotHasKey('stock', $datos);
        $this->assertArrayNotHasKey('stocks', $datos);

        // El 77 de la otra sucursal no aparece en ninguna parte de los datos de la página
        $this->assertStringNotContainsString('"cantidad":77', $respuesta->getContent());
        $this->assertStringNotContainsString('"1":10,"2":77', $respuesta->getContent());
    }

    public function test_el_costo_nunca_viaja_al_navegador(): void
    {
        [$cajero, $chalco] = $this->cajeroConTurno();
        $this->darStock($this->crearProducto(['costo' => 123.45]), $chalco, 5);

        $respuesta = $this->actingAs($cajero)->get(route('ventas.index'))->assertOk();

        $this->assertArrayNotHasKey('costo', $this->productosDe($respuesta)[0]);

        $admin = $this->cajeroDeVentas($chalco, 'Administrador General');
        $this->abrirTurno($admin);
        $respuestaAdmin = $this->actingAs($admin)->get(route('ventas.index'))->assertOk();

        $this->assertArrayNotHasKey('costo', $this->productosDe($respuestaAdmin)[0]);
    }

    public function test_el_precio_de_mayoreo_solo_viaja_a_quien_puede_usarlo(): void
    {
        [$cajero, $chalco] = $this->cajeroConTurno();
        $this->darStock($this->crearProducto(), $chalco, 5);

        $respuesta = $this->actingAs($cajero)->get(route('ventas.index'))->assertOk();
        $this->assertArrayNotHasKey('precio_mayoreo', $this->productosDe($respuesta)[0]);
        $this->assertFalse($respuesta->viewData('puedeMayoreo'));

        $gerenteAdmin = $this->cajeroDeVentas($chalco, 'Administrador');
        $this->abrirTurno($gerenteAdmin);

        $respuestaAdmin = $this->actingAs($gerenteAdmin)->get(route('ventas.index'))->assertOk();
        $this->assertSame(700.0, (float) $this->productosDe($respuestaAdmin)[0]['precio_mayoreo']);
        $this->assertTrue($respuestaAdmin->viewData('puedeMayoreo'));
    }

    public function test_solo_salen_los_productos_activos(): void
    {
        [$cajero, $chalco] = $this->cajeroConTurno();
        $activo = $this->crearProducto(['marca' => 'ACTIVA']);
        $apagado = $this->crearProducto(['marca' => 'APAGADA', 'estado' => false]);
        $this->darStock($activo, $chalco, 5);
        $this->darStock($apagado, $chalco, 5);

        $marcas = collect($this->productosDe($this->actingAs($cajero)->get(route('ventas.index'))))->pluck('marca')->all();

        $this->assertSame(['ACTIVA'], $marcas);
    }

    public function test_los_productos_salen_ordenados_por_marca_y_medida(): void
    {
        [$cajero] = $this->cajeroConTurno();
        $this->crearProducto(['marca' => 'MICHELIN', 'medida' => '215/60R16']);
        $this->crearProducto(['marca' => 'GOODYEAR', 'medida' => '195/65R15']);
        $this->crearProducto(['marca' => 'MICHELIN', 'medida' => '185/65R15']);

        $lista = collect($this->productosDe($this->actingAs($cajero)->get(route('ventas.index'))))
            ->map(fn ($p) => $p['marca'] . ' ' . $p['medida'])
            ->all();

        $this->assertSame(['GOODYEAR 195/65R15', 'MICHELIN 185/65R15', 'MICHELIN 215/60R16'], $lista);
    }

    public function test_un_producto_con_entrada_de_hoy_sale_como_nuevo(): void
    {
        [$cajero, $chalco] = $this->cajeroConTurno();
        $nuevo = $this->crearProducto(['marca' => 'NUEVA']);
        $viejo = $this->crearProducto(['marca' => 'VIEJA']);
        $this->darStock($nuevo, $chalco, 5);
        $this->darStock($viejo, $chalco, 5);

        MovimientoInventario::create([
            'producto_id' => $nuevo->id,
            'sucursal_id' => $chalco->id,
            'usuario_id'  => $cajero->id,
            'tipo'        => 'entrada',
            'cantidad'    => 5,
            'motivo'      => 'compra',
            'fecha'       => now(),
        ]);

        $porMarca = collect($this->productosDe($this->actingAs($cajero)->get(route('ventas.index'))))->keyBy('marca');

        $this->assertTrue($porMarca['NUEVA']['es_nuevo']);
        $this->assertFalse($porMarca['VIEJA']['es_nuevo']);
    }

    public function test_un_servicio_sale_aunque_no_tenga_stock(): void
    {
        [$cajero] = $this->cajeroConTurno();
        $this->crearProducto(['tipo' => 'Servicio', 'marca' => 'TALLER', 'medida' => 'ALINEACION', 'descripcion' => 'Alineación y balanceo']);

        $datos = $this->productosDe($this->actingAs($cajero)->get(route('ventas.index')))[0];

        $this->assertSame('Servicio', $datos['tipo']);
        $this->assertSame(0, $datos['stock_cantidad']);
    }

    public function test_el_cajero_solo_ve_su_sucursal_y_el_admin_puede_elegir(): void
    {
        [$cajero, $chalco] = $this->cajeroConTurno();
        $valle = $this->crearSucursal('Valle de Chalco');

        $deCajero = $this->actingAs($cajero)->get(route('ventas.index'))->assertOk();
        $this->assertSame([['id' => $chalco->id, 'nombre' => 'Chalco']], $deCajero->viewData('sucursales')->all());
        $this->assertFalse($deCajero->viewData('esAdmin'));

        $admin = $this->cajeroDeVentas($chalco, 'Administrador General');
        $this->abrirTurno($admin);

        $deAdmin = $this->actingAs($admin)->get(route('ventas.index'))->assertOk();
        $this->assertTrue($deAdmin->viewData('esAdmin'));
        $this->assertEqualsCanonicalizing(
            [$chalco->id, $valle->id],
            $deAdmin->viewData('sucursales')->pluck('id')->all()
        );
        // Solo viajan el id y el nombre de cada sucursal
        $this->assertSame(['id', 'nombre'], array_keys($deAdmin->viewData('sucursales')->first()));
    }

    public function test_el_admin_ve_el_stock_de_la_sucursal_que_elige(): void
    {
        [$cajero, $chalco] = $this->cajeroConTurno();
        $valle = $this->crearSucursal('Valle de Chalco');
        $producto = $this->crearProducto();
        $this->darStock($producto, $chalco, 10);
        $this->darStock($producto, $valle, 77);

        $admin = $this->cajeroDeVentas($chalco, 'Administrador General');
        $this->abrirTurno($admin);

        $enValle = $this->actingAs($admin)->get(route('ventas.index', ['sucursal_id' => $valle->id]))->assertOk();

        $this->assertSame($valle->id, $enValle->viewData('sucursalSeleccionada'));
        $this->assertSame(77, $this->productosDe($enValle)[0]['stock_cantidad']);
    }

    public function test_el_selector_de_clientes_trae_solo_lo_necesario(): void
    {
        [$cajero] = $this->cajeroConTurno();
        $this->crearCliente(['nombre' => 'Zeta', 'rfc' => 'ZET010101AAA']);
        $this->crearCliente(['nombre' => 'Alfa']);

        $clientes = $this->actingAs($cajero)->get(route('ventas.index'))->viewData('clientes');

        $this->assertSame(['Alfa', 'Zeta'], $clientes->pluck('nombre')->all());
        $this->assertEqualsCanonicalizing(['id', 'nombre', 'rfc', 'razon_social'], array_keys($clientes->first()->toArray()));
    }
}
