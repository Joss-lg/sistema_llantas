<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Concerns\CreaUsuarios;
use Tests\TestCase;

/**
 * Pruebas de la regla de sucursal (trait ResuelveContextoSucursal):
 * solo "Administrador General" ve y opera en todas; los demás, solo en la suya.
 */
class SucursalesTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios;

    /**
     * El trait tiene métodos protegidos; esta clase de prueba los vuelve públicos.
     */
    private function contexto(): object
    {
        return new class {
            use ResuelveContextoSucursal {
                usuarioEsAdmin as public;
                sucursalSeleccionada as public;
                sucursalParaOperar as public;
                sucursalesDisponibles as public;
            }
        };
    }

    private function peticion(array $datos = []): Request
    {
        return Request::create('/', 'GET', $datos);
    }

    public function test_admin_general_con_todas_o_vacio_no_filtra_por_sucursal(): void
    {
        $admin = $this->crearUsuario('Administrador General', $this->crearSucursal('Administración General'));
        $this->actingAs($admin);
        $contexto = $this->contexto();

        $this->assertTrue($contexto->usuarioEsAdmin());
        $this->assertNull($contexto->sucursalSeleccionada($this->peticion(['sucursal_id' => 'todas'])));
        $this->assertNull($contexto->sucursalSeleccionada($this->peticion(['sucursal_id' => ''])));
        $this->assertNull($contexto->sucursalSeleccionada($this->peticion()));
    }

    public function test_admin_general_puede_elegir_otra_sucursal(): void
    {
        $admin = $this->crearUsuario('Administrador General', $this->crearSucursal('Administración General'));
        $chalco = $this->crearSucursal('Chalco');
        $this->actingAs($admin);

        $this->assertSame(
            $chalco->id,
            $this->contexto()->sucursalSeleccionada($this->peticion(['sucursal_id' => $chalco->id]))
        );
    }

    public function test_usuario_normal_siempre_queda_en_su_sucursal_aunque_manipule_el_formulario(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $cajero = $this->crearUsuario('Cajero', $chalco);
        $this->actingAs($cajero);
        $contexto = $this->contexto();

        $this->assertFalse($contexto->usuarioEsAdmin());
        $this->assertSame($chalco->id, $contexto->sucursalSeleccionada($this->peticion(['sucursal_id' => $valle->id])));
        $this->assertSame($chalco->id, $contexto->sucursalParaOperar($this->peticion(['sucursal_id' => $valle->id])));
    }

    public function test_el_rol_administrador_no_cuenta_como_administrador_general(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');
        $kevin = $this->crearUsuario('Administrador', $valle);
        $this->actingAs($kevin);
        $contexto = $this->contexto();

        $this->assertFalse($contexto->usuarioEsAdmin());
        $this->assertSame($valle->id, $contexto->sucursalSeleccionada($this->peticion(['sucursal_id' => $chalco->id])));
        $this->assertSame($valle->id, $contexto->sucursalParaOperar($this->peticion(['sucursal_id' => $chalco->id])));
    }

    public function test_admin_general_al_operar_en_sucursal_inactiva_o_inexistente_cae_a_la_suya(): void
    {
        $propia = $this->crearSucursal('Administración General');
        $inactiva = $this->crearSucursal('Atlanta', false);
        $activa = $this->crearSucursal('Chalco');
        $admin = $this->crearUsuario('Administrador General', $propia);
        $this->actingAs($admin);
        $contexto = $this->contexto();

        $this->assertSame($activa->id, $contexto->sucursalParaOperar($this->peticion(['sucursal_id' => $activa->id])));
        $this->assertSame($propia->id, $contexto->sucursalParaOperar($this->peticion(['sucursal_id' => $inactiva->id])));
        $this->assertSame($propia->id, $contexto->sucursalParaOperar($this->peticion(['sucursal_id' => 999999])));
    }

    public function test_sucursales_disponibles_para_admin_y_para_usuario_normal(): void
    {
        $propia = $this->crearSucursal('Administración General');
        $chalco = $this->crearSucursal('Chalco');
        $this->crearSucursal('Atlanta', false);

        $admin = $this->crearUsuario('Administrador General', $propia, ['email' => 'admin@prueba.test']);
        $cajero = $this->crearUsuario('Cajero', $chalco, ['email' => 'cajero@prueba.test']);
        $contexto = $this->contexto();

        // El admin ve todas las ACTIVAS (la inactiva no aparece).
        $this->actingAs($admin);
        $this->assertSame(
            ['Administración General', 'Chalco'],
            $contexto->sucursalesDisponibles()->pluck('nombre')->sort()->values()->all()
        );

        // El usuario normal ve solo la suya.
        $this->actingAs($cajero);
        $this->assertSame(['Chalco'], $contexto->sucursalesDisponibles()->pluck('nombre')->values()->all());
    }
}