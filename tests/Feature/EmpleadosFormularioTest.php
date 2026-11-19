<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaUsuarios;
use Tests\TestCase;

/**
 * Formulario compartido de crear y editar empleado: que se pinte bien,
 * que muestre los errores de cada campo y que explique lo que está bloqueado.
 */
class EmpleadosFormularioTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios;

    private const RUTAS = [
        'empleados.index', 'empleados.create', 'empleados.store',
        'empleados.edit', 'empleados.update',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function gerente(?Sucursal $sucursal = null): User
    {
        $gerente = $this->crearUsuario('Gerente', $sucursal);

        foreach (self::RUTAS as $ruta) {
            $this->darPermiso($gerente, $ruta);
        }

        return $gerente;
    }

    public function test_el_formulario_de_nuevo_empleado_muestra_campos_y_permisos(): void
    {
        $suc = $this->crearSucursal('Chalco');
        $gerente = $this->gerente($suc);
        Permiso::create(['nombre' => 'Ver ventas del dia', 'ruta' => 'ventas.index', 'modulo' => 'Ventas']);

        $this->actingAs($gerente)
            ->get(route('empleados.create'))
            ->assertOk()
            ->assertSee('Datos del empleado')
            ->assertSee('Mínimo 8 caracteres')
            ->assertSee('Chalco')
            ->assertSee('Ventas')
            ->assertSee('Ver ventas del dia')
            ->assertSee('Registrar empleado')
            ->assertDontSee('Dejar en blanco para conservar');
    }

    public function test_el_formulario_de_editar_trae_los_datos_y_los_permisos_marcados(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc, ['name' => 'Marta Ríos', 'email' => 'marta@llantas.com']);
        $permiso = Permiso::create(['nombre' => 'Ver ventas del dia', 'ruta' => 'ventas.index', 'modulo' => 'Ventas']);
        $cajero->permisos()->attach($permiso->id);

        $this->actingAs($gerente)
            ->get(route('empleados.edit', $cajero))
            ->assertOk()
            ->assertSee('Marta Ríos')
            ->assertSee('marta@llantas.com')
            ->assertSee('Dejar en blanco para conservar')
            ->assertSee('Guardar cambios')
            ->assertSee("permisosSeleccionados: JSON.parse('[{$permiso->id}]')", false);
    }

    public function test_al_crear_con_errores_se_ven_los_mensajes_de_cada_campo(): void
    {
        $gerente = $this->gerente();

        $this->actingAs($gerente)
            ->from(route('empleados.create'))
            ->followingRedirects()
            ->post(route('empleados.store'), ['name' => 'Laura Pérez'])
            ->assertSee('Revisa los campos marcados en rojo')
            ->assertSee('Escribe el correo del empleado.')
            ->assertSee('Escribe una contraseña.')
            ->assertSee('Elige una sucursal.')
            ->assertSee('Elige un rol.')
            ->assertSee('Laura Pérez');   // lo que escribió se conserva
    }

    public function test_al_editar_con_errores_se_conserva_lo_escrito_y_se_ve_el_mensaje(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc);

        $this->actingAs($gerente)
            ->from(route('empleados.edit', $cajero))
            ->followingRedirects()
            ->put(route('empleados.update', $cajero), [
                'name' => '', 'email' => 'no-es-correo',
                'sucursal_id' => $suc->id, 'rol_id' => $cajero->rol_id,
            ])
            ->assertSee('Escribe el nombre del empleado.')
            ->assertSee('Escribe un correo válido.')
            ->assertSee('no-es-correo');
    }

    public function test_si_se_quitan_todos_los_permisos_y_hay_error_no_reaparecen_los_anteriores(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc);
        $permiso = Permiso::create(['nombre' => 'Ver ventas del dia', 'ruta' => 'ventas.index', 'modulo' => 'Ventas']);
        $cajero->permisos()->attach($permiso->id);

        // Se mandan SIN permisos y con un error en el nombre
        $this->actingAs($gerente)
            ->from(route('empleados.edit', $cajero))
            ->followingRedirects()
            ->put(route('empleados.update', $cajero), [
                'name' => '', 'email' => $cajero->email,
                'sucursal_id' => $suc->id, 'rol_id' => $cajero->rol_id, '_form' => '1',
            ])
            ->assertSee('permisosSeleccionados: []', false)
            ->assertDontSee("permisosSeleccionados: JSON.parse('[{$permiso->id}]')", false);
    }

    public function test_el_formulario_del_administrador_principal_explica_lo_bloqueado(): void
    {
        $suc = $this->crearSucursal();
        $principal = $this->crearUsuario('Administrador General', $suc, ['email' => 'admin@llantas.com']);

        $this->actingAs($principal)
            ->get(route('empleados.edit', $principal))
            ->assertOk()
            ->assertViewHas('esPrincipal', true)
            ->assertSee('no puede cambiar su correo ni su rol');
    }

    public function test_el_formulario_de_uno_mismo_avisa_que_no_puede_deshabilitarse(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $otro = $this->crearUsuario('Cajero', $suc);

        $this->actingAs($gerente)
            ->get(route('empleados.edit', $gerente))
            ->assertViewHas('esYo', true)
            ->assertSee('No puedes deshabilitar tu propia cuenta');

        $this->actingAs($gerente)
            ->get(route('empleados.edit', $otro))
            ->assertViewHas('esYo', false)
            ->assertDontSee('No puedes deshabilitar tu propia cuenta')
            ->assertDontSee('no puede cambiar su correo ni su rol');
    }

    public function test_el_formulario_avisa_que_el_administrador_general_no_necesita_permisos(): void
    {
        $suc = $this->crearSucursal();
        $admin = $this->crearUsuario('Administrador General', $suc);
        Role::firstOrCreate(['nombre' => 'Cajero']);

        $this->actingAs($admin)
            ->get(route('empleados.create'))
            ->assertOk()
            ->assertSee('tiene acceso total al sistema y no necesita permisos individuales');
    }
}
