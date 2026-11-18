<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Módulo de Clientes: lista con búsqueda y números reales, alta, y borrado
 * (un cliente con compras no se borra para conservar el historial de ventas).
 */
class ClientesTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** Usuario con los permisos del módulo de clientes (por usuario, no por rol). */
    private function usuarioDeClientes(?Sucursal $sucursal = null): User
    {
        $usuario = $this->crearUsuario('Cajero', $sucursal);

        foreach (['clientes.index', 'clientes.store', 'clientes.show', 'clientes.update', 'clientes.destroy'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    /** Le agrega una compra a un cliente, guardada directo en la base. */
    private function comprarComo(Cliente $cliente, User $usuario, float $total): Venta
    {
        $venta = $this->crearVenta($usuario, Sucursal::find($usuario->sucursal_id), $total);
        $venta->cliente_id = $cliente->id;
        $venta->save();

        return $venta;
    }

    public function test_la_lista_de_clientes_abre_y_muestra_los_nombres(): void
    {
        $usuario = $this->usuarioDeClientes();
        $this->crearCliente(['nombre' => 'Fernando Martinez']);

        $this->actingAs($usuario)
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertSee('Fernando Martinez');
    }

    public function test_sin_el_permiso_de_clientes_recibe_403(): void
    {
        $sinPermiso = $this->crearUsuario('Cajero');

        $this->actingAs($sinPermiso)
            ->get(route('clientes.index'))
            ->assertForbidden();
    }

    public function test_los_numeros_de_arriba_cuentan_todos_los_clientes_y_no_solo_la_pagina(): void
    {
        $usuario = $this->usuarioDeClientes();

        // 12 clientes, 3 VIP: la pagina trae 10, pero los numeros deben contar los 12.
        for ($i = 1; $i <= 12; $i++) {
            $this->crearCliente(['nombre' => "Cliente {$i}", 'is_vip' => $i <= 3]);
        }

        $this->actingAs($usuario)
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertViewHas('clientes', fn ($clientes) => $clientes->total() === 12 && $clientes->count() === 10)
            ->assertViewHas('totalClientes', fn ($total) => (int) $total === 12)
            ->assertViewHas('totalVip', fn ($total) => (int) $total === 3);
    }

    public function test_la_busqueda_filtra_por_nombre_telefono_email_y_rfc(): void
    {
        $usuario = $this->usuarioDeClientes();
        $ana = $this->crearCliente(['nombre' => 'Ana Lopez', 'telefono' => '5511112222', 'email' => 'ana@correo.com', 'rfc' => 'LOPA800101AAA']);
        $this->crearCliente(['nombre' => 'Beto Ruiz', 'telefono' => '5533334444', 'email' => 'beto@correo.com', 'rfc' => 'RUIB900202BBB']);

        foreach (['Ana', '5511112222', 'ana@correo', 'LOPA8001'] as $busqueda) {
            $this->actingAs($usuario)
                ->get(route('clientes.index', ['q' => $busqueda]))
                ->assertOk()
                ->assertViewHas('clientes', fn ($clientes) => $clientes->pluck('id')->map(fn ($id) => (int) $id)->all() === [$ana->id])
                ->assertViewHas('totalClientes', fn ($total) => (int) $total === 1);
        }
    }

    public function test_una_busqueda_sin_resultados_muestra_el_aviso(): void
    {
        $usuario = $this->usuarioDeClientes();
        $this->crearCliente(['nombre' => 'Ana Lopez']);

        $this->actingAs($usuario)
            ->get(route('clientes.index', ['q' => 'zzzz']))
            ->assertOk()
            ->assertViewHas('totalClientes', fn ($total) => (int) $total === 0)
            ->assertSee('Ningún cliente coincide con tu búsqueda');
    }

    public function test_el_ticket_promedio_y_los_clientes_con_compras_salen_de_las_ventas_reales(): void
    {
        $sucursal = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeClientes($sucursal);
        $ana = $this->crearCliente(['nombre' => 'Ana Lopez']);
        $beto = $this->crearCliente(['nombre' => 'Beto Ruiz']);
        $this->crearCliente(['nombre' => 'Carla Sin Compras']);

        // Ana compra 1000 y 500, Beto compra 300: 1800 entre 3 compras = 600 por compra.
        $this->comprarComo($ana, $usuario, 1000);
        $this->comprarComo($ana, $usuario, 500);
        $this->comprarComo($beto, $usuario, 300);

        // Una venta de Publico General (sin cliente) no cuenta en estos numeros.
        $this->crearVenta($usuario, $sucursal, 9999);

        $this->actingAs($usuario)
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertViewHas('clientesConCompras', fn ($total) => (int) $total === 2)
            ->assertViewHas('ticketPromedio', fn ($promedio) => round((float) $promedio, 2) === 600.0);
    }

    public function test_sin_compras_el_ticket_promedio_es_cero(): void
    {
        $usuario = $this->usuarioDeClientes();
        $this->crearCliente();

        $this->actingAs($usuario)
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertViewHas('clientesConCompras', fn ($total) => (int) $total === 0)
            ->assertViewHas('ticketPromedio', fn ($promedio) => (float) $promedio === 0.0);
    }

    public function test_el_total_comprado_de_cada_cliente_se_suma_en_la_lista(): void
    {
        $sucursal = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeClientes($sucursal);
        $ana = $this->crearCliente(['nombre' => 'Ana Lopez']);

        $this->comprarComo($ana, $usuario, 1000);
        $this->comprarComo($ana, $usuario, 500);

        $this->actingAs($usuario)
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertViewHas('clientes', fn ($clientes) => round((float) $clientes->first()->compras_sum, 2) === 1500.0);
    }

    public function test_crear_un_cliente_lo_guarda_y_marca_vip(): void
    {
        $usuario = $this->usuarioDeClientes();

        $this->actingAs($usuario)
            ->post(route('clientes.store'), ['nombre' => 'Diana Torres', 'telefono' => '5500001111', 'is_vip' => '1'])
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success');

        $cliente = Cliente::where('nombre', 'Diana Torres')->first();
        $this->assertNotNull($cliente);
        $this->assertTrue($cliente->is_vip);
    }

    public function test_crear_un_cliente_sin_nombre_se_rechaza(): void
    {
        $usuario = $this->usuarioDeClientes();

        $this->actingAs($usuario)
            ->post(route('clientes.store'), ['nombre' => '', 'telefono' => '5500001111'])
            ->assertSessionHasErrors('nombre');

        $this->assertSame(0, Cliente::count());
    }

    public function test_editar_un_cliente_cambia_sus_datos(): void
    {
        $usuario = $this->usuarioDeClientes();
        $cliente = $this->crearCliente(['nombre' => 'Nombre Viejo']);

        $this->actingAs($usuario)
            ->put(route('clientes.update', $cliente->id), ['nombre' => 'Nombre Nuevo', 'rfc' => 'ABCD800101AAA'])
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success');

        $cliente->refresh();
        $this->assertSame('Nombre Nuevo', $cliente->nombre);
        $this->assertSame('ABCD800101AAA', $cliente->rfc);
    }

    public function test_eliminar_un_cliente_sin_compras_lo_borra(): void
    {
        $usuario = $this->usuarioDeClientes();
        $cliente = $this->crearCliente(['nombre' => 'Sin Compras']);

        $this->actingAs($usuario)
            ->delete(route('clientes.destroy', $cliente->id))
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    public function test_eliminar_un_cliente_con_compras_no_lo_borra_y_conserva_sus_ventas(): void
    {
        $sucursal = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeClientes($sucursal);
        $cliente = $this->crearCliente(['nombre' => 'Con Compras']);
        $venta = $this->comprarComo($cliente, $usuario, 900);

        $this->actingAs($usuario)
            ->delete(route('clientes.destroy', $cliente->id))
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id]);
        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'cliente_id' => $cliente->id]);
    }

    public function test_el_aviso_de_error_al_eliminar_se_ve_en_la_lista(): void
    {
        $sucursal = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeClientes($sucursal);
        $cliente = $this->crearCliente(['nombre' => 'Con Compras']);
        $this->comprarComo($cliente, $usuario, 900);

        $this->actingAs($usuario)
            ->followingRedirects()
            ->delete(route('clientes.destroy', $cliente->id))
            ->assertOk()
            ->assertSee('No se puede eliminar a Con Compras')
            ->assertSee('1 compra registrada');
    }

    public function test_el_detalle_del_cliente_muestra_sus_compras(): void
    {
        $sucursal = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeClientes($sucursal);
        $cliente = $this->crearCliente(['nombre' => 'Ana Lopez']);
        $venta = $this->comprarComo($cliente, $usuario, 900);

        $this->actingAs($usuario)
            ->get(route('clientes.show', $cliente->id))
            ->assertOk()
            ->assertSee('Ana Lopez')
            ->assertSee($venta->folio);
    }
}
