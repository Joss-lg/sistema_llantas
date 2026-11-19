<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Lista de inventario (ProductoController): tarjetas, filtros, orden,
 * alta de productos y ventanas que se vuelven a abrir cuando algo falla.
 */
class InventarioIndexTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** Usuario con los permisos de la lista, de "Nuevo producto" y de "Registrar entrada". */
    private function usuarioDeLista(?Sucursal $sucursal = null, string $rol = 'Cajero'): User
    {
        $usuario = $this->crearUsuario($rol, $sucursal);

        foreach (['inventario.index', 'inventario.storeProducto', 'inventario.storeEntrada'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    /** Un producto con su stock en una sucursal (el mínimo queda en 5, como en la base). */
    private function productoConStock(Sucursal $sucursal, int $cantidad, array $datos = []): Producto
    {
        $producto = $this->crearProducto($datos);
        $this->darStock($producto, $sucursal, $cantidad);

        return $producto;
    }

    /* ===================== LISTA Y TARJETAS ===================== */

    public function test_la_lista_abre_y_muestra_el_nombre_del_producto(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $this->productoConStock($chalco, 10, ['descripcion' => 'Llanta Pirelli Cinturato']);

        $this->actingAs($usuario)
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertSee('Llanta Pirelli Cinturato');
    }

    public function test_sin_el_permiso_de_inventario_recibe_403(): void
    {
        $usuario = $this->crearUsuario('Cajero');

        $this->actingAs($usuario)
            ->get(route('inventario.index'))
            ->assertForbidden();
    }

    public function test_las_tarjetas_cuentan_todo_el_filtro_y_no_solo_la_pagina(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);

        // 5 en stock, 3 bajo mínimo, 2 con registro en 0 y 2 sin registro de stock = 12
        foreach (range(1, 5) as $i) {
            $this->productoConStock($chalco, 20, ['marca' => "EN{$i}", 'medida' => "M{$i}"]);
        }
        foreach (range(1, 3) as $i) {
            $this->productoConStock($chalco, 2, ['marca' => "BA{$i}", 'medida' => "M{$i}"]);
        }
        foreach (range(1, 2) as $i) {
            $this->productoConStock($chalco, 0, ['marca' => "CE{$i}", 'medida' => "M{$i}"]);
            $this->crearProducto(['marca' => "SR{$i}", 'medida' => "M{$i}"]);
        }

        foreach ([1, 2] as $pagina) {
            $this->actingAs($usuario)
                ->get(route('inventario.index', ['page' => $pagina]))
                ->assertOk()
                ->assertViewHas('totalProductos', fn ($n) => (int) $n === 12)
                ->assertViewHas('enStock', fn ($n) => (int) $n === 5)
                ->assertViewHas('bajoStock', fn ($n) => (int) $n === 3)
                ->assertViewHas('sinStock', fn ($n) => (int) $n === 4);
        }

        $this->actingAs($usuario)
            ->get(route('inventario.index', ['page' => 2]))
            ->assertViewHas('productos', fn ($p) => $p->total() === 12 && $p->count() === 2);
    }

    public function test_stock_igual_al_minimo_cuenta_como_en_stock(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $this->productoConStock($chalco, 5);   // mínimo 5: llega justo

        $this->actingAs($usuario)
            ->get(route('inventario.index'))
            ->assertViewHas('enStock', fn ($n) => (int) $n === 1)
            ->assertViewHas('bajoStock', fn ($n) => (int) $n === 0)
            ->assertDontSee('Bajo stock');
    }

    public function test_un_producto_con_menos_del_minimo_sale_como_bajo_stock(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $this->productoConStock($chalco, 4);   // mínimo 5: le falta una

        $this->actingAs($usuario)
            ->get(route('inventario.index'))
            ->assertViewHas('enStock', fn ($n) => (int) $n === 0)
            ->assertViewHas('bajoStock', fn ($n) => (int) $n === 1)
            ->assertSee('Bajo stock');
    }

    public function test_el_filtro_de_estado_cambia_la_lista_pero_no_las_tarjetas(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $this->productoConStock($chalco, 20, ['marca' => 'A', 'medida' => '1']);
        $this->productoConStock($chalco, 2, ['marca' => 'B', 'medida' => '2']);
        $this->productoConStock($chalco, 0, ['marca' => 'C', 'medida' => '3']);

        foreach (['sin_stock', 'bajo_stock', 'ok'] as $estado) {
            $this->actingAs($usuario)
                ->get(route('inventario.index', ['stock_status' => $estado]))
                ->assertViewHas('productos', fn ($p) => $p->total() === 1)
                ->assertViewHas('totalProductos', fn ($n) => (int) $n === 3);
        }
    }

    public function test_un_cajero_ve_el_stock_solo_de_su_sucursal_y_el_admin_la_suma(): void
    {
        $chalco  = $this->crearSucursal('Chalco');
        $texcoco = $this->crearSucursal('Texcoco');
        $cajero  = $this->usuarioDeLista($chalco);
        $admin   = $this->usuarioDeLista($chalco, 'Administrador General');

        $producto = $this->productoConStock($chalco, 3);
        $this->darStock($producto, $texcoco, 50);

        // El cajero solo ve lo suyo, aunque mande otra sucursal en la dirección
        $this->actingAs($cajero)
            ->get(route('inventario.index', ['sucursal_id' => $texcoco->id]))
            ->assertViewHas('productos', fn ($p) => (int) $p->first()->stock_cantidad === 3)
            ->assertViewHas('bajoStock', fn ($n) => (int) $n === 1);

        // El administrador general sin elegir sucursal ve todo junto
        $this->actingAs($admin)
            ->get(route('inventario.index'))
            ->assertViewHas('productos', fn ($p) => (int) $p->first()->stock_cantidad === 53)
            ->assertViewHas('enStock', fn ($n) => (int) $n === 1);

        // Y puede elegir una sucursal
        $this->actingAs($admin)
            ->get(route('inventario.index', ['sucursal_id' => $texcoco->id]))
            ->assertViewHas('productos', fn ($p) => (int) $p->first()->stock_cantidad === 50);
    }

    /* ===================== FILTROS Y ORDEN ===================== */

    public function test_la_busqueda_filtra_por_marca_y_por_medida(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $this->productoConStock($chalco, 10, ['marca' => 'FIRESTONE', 'medida' => '11R22']);
        $this->productoConStock($chalco, 10, ['marca' => 'PIRELLI', 'medida' => '205/55R16']);

        $this->actingAs($usuario)
            ->get(route('inventario.index', ['q' => 'firestone']))
            ->assertViewHas('productos', fn ($p) => $p->total() === 1 && $p->first()->marca === 'FIRESTONE')
            ->assertViewHas('totalProductos', fn ($n) => (int) $n === 1);

        $this->actingAs($usuario)
            ->get(route('inventario.index', ['q' => '205']))
            ->assertViewHas('productos', fn ($p) => $p->total() === 1 && $p->first()->marca === 'PIRELLI');
    }

    public function test_el_filtro_de_categoria_y_el_de_marca_tambien_cambian_las_tarjetas(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $this->productoConStock($chalco, 10, ['tipo' => 'Llanta', 'marca' => 'PIRELLI', 'medida' => '1']);
        $this->productoConStock($chalco, 10, ['tipo' => 'Rin', 'marca' => 'AMERICAN', 'medida' => '2']);

        $this->actingAs($usuario)
            ->get(route('inventario.index', ['tipo' => 'Rin']))
            ->assertViewHas('productos', fn ($p) => $p->total() === 1 && $p->first()->tipo === 'Rin')
            ->assertViewHas('totalProductos', fn ($n) => (int) $n === 1);

        $this->actingAs($usuario)
            ->get(route('inventario.index', ['marca_filtro' => 'PIRELLI']))
            ->assertViewHas('productos', fn ($p) => $p->total() === 1 && $p->first()->marca === 'PIRELLI')
            ->assertViewHas('totalProductos', fn ($n) => (int) $n === 1);
    }

    public function test_llego_hoy_solo_trae_los_productos_con_entrada_de_hoy(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $nuevo = $this->productoConStock($chalco, 10, ['marca' => 'NUEVO', 'medida' => '1']);
        $this->productoConStock($chalco, 10, ['marca' => 'VIEJO', 'medida' => '2']);

        MovimientoInventario::create([
            'producto_id' => $nuevo->id,
            'sucursal_id' => $chalco->id,
            'usuario_id'  => $usuario->id,
            'tipo'        => 'entrada',
            'cantidad'    => 10,
            'motivo'      => 'compra',
            'fecha'       => now(),
        ]);

        $this->actingAs($usuario)
            ->get(route('inventario.index', ['solo_nuevos' => '1']))
            ->assertViewHas('productos', fn ($p) => $p->total() === 1 && (int) $p->first()->id === $nuevo->id)
            ->assertViewHas('totalProductos', fn ($n) => (int) $n === 1);
    }

    public function test_ordenar_por_precio_con_precios_iguales_no_repite_ni_pierde_productos_entre_paginas(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);

        // 12 productos con exactamente el mismo precio
        $ids = [];
        foreach (range(1, 12) as $i) {
            $ids[] = $this->productoConStock($chalco, 10, ['marca' => "M{$i}", 'medida' => "X{$i}"])->id;
        }

        $vistos = [];
        foreach ([1, 2] as $pagina) {
            $lista = $this->actingAs($usuario)
                ->get(route('inventario.index', ['ordenar_precio' => 'publico_mayor', 'page' => $pagina]))
                ->viewData('productos');

            foreach ($lista as $producto) {
                $vistos[] = (int) $producto->id;
            }
        }

        sort($ids);
        sort($vistos);
        $this->assertSame($ids, $vistos);
    }

    /* ===================== NUEVO PRODUCTO ===================== */

    public function test_crear_un_producto_lo_guarda_en_mayusculas_y_lo_inicia_en_todas_las_sucursales(): void
    {
        $chalco  = $this->crearSucursal('Chalco');
        $texcoco = $this->crearSucursal('Texcoco');
        $usuario = $this->usuarioDeLista($chalco);

        $this->actingAs($usuario)
            ->post(route('inventario.storeProducto'), [
                'tipo'   => 'Llanta',
                'marca'  => '  firestone ',
                'medida' => '11r22.5',
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('success');

        $producto = Producto::firstOrFail();
        $this->assertSame('FIRESTONE', $producto->marca);
        $this->assertSame('11R22.5', $producto->medida);
        $this->assertSame('Llanta', $producto->tipo);
        $this->assertTrue($producto->estado);

        $this->assertSame(2, StockSucursal::where('producto_id', $producto->id)->count());
        $this->assertSame(0, (int) StockSucursal::where('producto_id', $producto->id)->sum('cantidad'));
        $this->assertSame(2, StockSucursal::where('producto_id', $producto->id)->where('stock_minimo', 5)->count());
    }

    public function test_un_tipo_fuera_de_la_lista_se_rechaza(): void
    {
        $usuario = $this->usuarioDeLista();

        $this->actingAs($usuario)
            ->post(route('inventario.storeProducto'), ['tipo' => 'Llantas', 'marca' => 'PIRELLI', 'medida' => '1'])
            ->assertSessionHasErrors('tipo');

        $this->assertSame(0, Producto::count());
    }

    public function test_no_se_crea_un_producto_con_la_misma_marca_y_medida(): void
    {
        $usuario = $this->usuarioDeLista();
        $this->crearProducto(['marca' => 'PIRELLI', 'medida' => '205/55R16']);

        // Aunque venga en minúsculas y con espacios, es el mismo producto
        $this->actingAs($usuario)
            ->post(route('inventario.storeProducto'), ['tipo' => 'Llanta', 'marca' => ' pirelli ', 'medida' => '205/55r16 '])
            ->assertSessionHasErrors('medida');

        $this->assertSame(1, Producto::count());
    }

    /* ===================== VENTANAS QUE SE VUELVEN A ABRIR ===================== */

    public function test_si_falla_nuevo_producto_se_vuelve_a_abrir_la_ventana_con_el_error(): void
    {
        $usuario = $this->usuarioDeLista();

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.storeProducto'), ['_form' => 'producto', 'tipo' => 'Llanta', 'marca' => '', 'medida' => '11R22'])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHasErrors('marca');

        $this->actingAs($usuario)
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertSee('modalProducto: true', false)
            ->assertSee('modalEntrada: false', false)
            ->assertSee('Escribe la marca.')
            ->assertSee('11R22');
    }

    public function test_si_falla_registrar_entrada_se_vuelve_a_abrir_la_ventana_con_los_errores(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $producto = $this->productoConStock($chalco, 10, ['descripcion' => 'Llanta Pirelli Cinturato']);

        $this->actingAs($usuario)
            ->from(route('inventario.index'))
            ->post(route('inventario.storeEntrada'), [
                '_form'          => 'entrada',
                'producto_id'    => $producto->id,
                'cantidad'       => 0,
                'costo_unitario' => '',
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHasErrors(['cantidad', 'costo_unitario']);

        $this->actingAs($usuario)
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertSee('modalEntrada: true', false)
            ->assertSee('modalProducto: false', false)
            ->assertSee('La cantidad debe ser al menos 1.')
            ->assertSee('Escribe el costo unitario.');
    }

    public function test_la_ventana_de_entrada_recibe_el_catalogo_completo_para_buscar(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);
        $this->productoConStock($chalco, 10, ['marca' => 'A', 'medida' => '1', 'descripcion' => 'Llanta A']);
        $this->productoConStock($chalco, 0, ['marca' => 'B', 'medida' => '2', 'descripcion' => 'Llanta B']);
        $this->crearProducto(['marca' => 'C', 'medida' => '3', 'descripcion' => null]);

        $this->actingAs($usuario)
            ->get(route('inventario.index'))
            ->assertViewHas('catalogo', fn ($c) => count($c) === 3
                && $c[0]['t'] === 'Llanta A (1)'
                && $c[2]['t'] === 'C 3 (3)'
                && str_contains($c[1]['b'], 'llanta b'));
    }
}
