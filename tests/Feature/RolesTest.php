<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaUsuarios;
use Tests\TestCase;

/**
 * Módulo de Roles: lista con números reales, alta y edición con nombres limpios y sin repetir,
 * y borrado seguro (el Administrador General no se toca y un rol en uso no se elimina).
 */
class RolesTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios;

    private const RUTAS = [
        'roles.index', 'roles.create', 'roles.store', 'roles.edit', 'roles.update', 'roles.destroy',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** Usuario con los permisos del módulo de roles (por usuario, no por rol). */
    private function usuarioDeRoles(): User
    {
        $usuario = $this->crearUsuario('Gerente');

        foreach (self::RUTAS as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    /* ===================== LISTA ===================== */

    public function test_la_lista_abre_y_muestra_los_roles_con_sus_empleados(): void
    {
        $usuario = $this->usuarioDeRoles();
        $this->crearUsuario('Cajero');
        $this->crearUsuario('Cajero');

        $this->actingAs($usuario)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Cajero')
            ->assertSee('2 empleados')
            ->assertSee('1 empleado')
            ->assertViewHas('roles', fn ($roles) => $roles->firstWhere('nombre', 'Cajero')->usuarios_count === 2);
    }

    public function test_sin_el_permiso_de_roles_recibe_403(): void
    {
        $sinPermiso = $this->crearUsuario('Cajero');

        $this->actingAs($sinPermiso)->get(route('roles.index'))->assertForbidden();
    }

    public function test_las_tarjetas_cuentan_roles_empleados_y_roles_sin_uso(): void
    {
        $usuario = $this->usuarioDeRoles();   // rol Gerente con 1 empleado
        $this->crearUsuario('Cajero');        // rol Cajero con 1 empleado
        Role::create(['nombre' => 'Contador']);   // sin empleados
        Role::create(['nombre' => 'Mecanico']);   // sin empleados

        $this->actingAs($usuario)
            ->get(route('roles.index'))
            ->assertViewHas('totalRoles', 4)
            ->assertViewHas('totalUsuarios', 2)
            ->assertViewHas('sinUsuarios', 2);
    }

    public function test_los_empleados_inactivos_tambien_cuentan_en_su_rol(): void
    {
        $usuario = $this->usuarioDeRoles();
        $this->crearUsuario('Cajero', null, ['activo' => false]);

        $this->actingAs($usuario)
            ->get(route('roles.index'))
            ->assertViewHas('roles', fn ($roles) => $roles->firstWhere('nombre', 'Cajero')->usuarios_count === 1);
    }

    public function test_el_administrador_general_sale_protegido_y_un_rol_en_uso_no_ofrece_eliminar(): void
    {
        $usuario = $this->usuarioDeRoles();
        $this->crearUsuario('Administrador General');
        $this->crearUsuario('Cajero');
        Role::create(['nombre' => 'Contador']);

        // Dentro de la página la dirección va en JSON, con las diagonales escapadas (\/)
        $direccion = fn (string $nombre) => str_replace('/', '\/', route('roles.destroy', Role::where('nombre', $nombre)->first()));

        $this->actingAs($usuario)
            ->get(route('roles.index'))
            ->assertSee('Protegido')
            ->assertSee('En uso')                              // Cajero (y Gerente) tienen empleados
            ->assertSee($direccion('Contador'), false)
            ->assertDontSee($direccion('Cajero'), false)
            ->assertDontSee($direccion('Administrador General'), false);
    }

    /* ===================== ALTA ===================== */

    public function test_crea_un_rol_y_le_quita_los_espacios_sobrantes(): void
    {
        $usuario = $this->usuarioDeRoles();

        $this->actingAs($usuario)
            ->post(route('roles.store'), ['nombre' => '   Mecanico Jefe   '])
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['nombre' => 'Mecanico Jefe']);
    }

    public function test_no_deja_crear_un_rol_repetido_ni_con_espacios_ni_con_otras_mayusculas(): void
    {
        $usuario = $this->usuarioDeRoles();
        Role::create(['nombre' => 'Contador']);

        foreach (['Contador', '  Contador ', 'contador'] as $nombre) {
            $this->actingAs($usuario)
                ->post(route('roles.store'), ['nombre' => $nombre])
                ->assertSessionHasErrors('nombre');
        }

        $this->assertSame('Ya existe un rol con ese nombre.', session('errors')->first('nombre'));
        $this->assertSame(1, Role::where('nombre', 'Contador')->count());
    }

    public function test_un_nombre_vacio_o_muy_largo_se_rechaza_con_mensaje_en_espanol(): void
    {
        $usuario = $this->usuarioDeRoles();

        $this->actingAs($usuario)->post(route('roles.store'), ['nombre' => '   '])->assertSessionHasErrors('nombre');
        $this->assertSame('Escribe el nombre del rol.', session('errors')->first('nombre'));

        $this->actingAs($usuario)->post(route('roles.store'), ['nombre' => str_repeat('a', 51)])->assertSessionHasErrors('nombre');
        $this->assertSame('El nombre del rol no puede pasar de 50 letras.', session('errors')->first('nombre'));
    }

    public function test_el_formulario_de_nuevo_rol_abre_y_muestra_el_error_con_lo_escrito(): void
    {
        $usuario = $this->usuarioDeRoles();
        Role::create(['nombre' => 'Contador']);

        $this->actingAs($usuario)
            ->get(route('roles.create'))
            ->assertOk()
            ->assertSee('Guardar rol');

        $this->actingAs($usuario)
            ->from(route('roles.create'))
            ->followingRedirects()
            ->post(route('roles.store'), ['nombre' => 'Contador'])
            ->assertSee('Ya existe un rol con ese nombre.')
            ->assertSee('Revisa el campo marcado en rojo')
            ->assertSee('value="Contador"', false);
    }

    /* ===================== EDICIÓN ===================== */

    public function test_edita_el_nombre_de_un_rol_y_sus_empleados_lo_conservan(): void
    {
        $usuario = $this->usuarioDeRoles();
        $cajero = $this->crearUsuario('Cajero');
        $rol = Role::where('nombre', 'Cajero')->first();

        $this->actingAs($usuario)
            ->get(route('roles.edit', $rol))
            ->assertOk()
            ->assertSee('value="Cajero"', false)
            ->assertSee('Guardar cambios');

        $this->actingAs($usuario)
            ->put(route('roles.update', $rol), ['nombre' => ' Vendedor '])
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertSame('Vendedor', $rol->fresh()->nombre);
        $this->assertSame($rol->id, $cajero->fresh()->rol_id);
    }

    public function test_guardar_un_rol_con_su_mismo_nombre_no_da_error_pero_con_el_de_otro_si(): void
    {
        $usuario = $this->usuarioDeRoles();
        $cajero = Role::create(['nombre' => 'Cajero']);
        Role::create(['nombre' => 'Contador']);

        $this->actingAs($usuario)
            ->put(route('roles.update', $cajero), ['nombre' => 'Cajero'])
            ->assertSessionHasNoErrors();

        $this->actingAs($usuario)
            ->put(route('roles.update', $cajero), ['nombre' => 'Contador'])
            ->assertSessionHasErrors('nombre');

        $this->assertSame('Ya existe otro rol con ese nombre.', session('errors')->first('nombre'));
        $this->assertSame('Cajero', $cajero->fresh()->nombre);
    }

    public function test_el_administrador_general_no_se_puede_editar(): void
    {
        $usuario = $this->usuarioDeRoles();
        $admin = Role::create(['nombre' => 'Administrador General']);

        $this->actingAs($usuario)
            ->get(route('roles.edit', $admin))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('error');

        $this->actingAs($usuario)
            ->put(route('roles.update', $admin), ['nombre' => 'Otro'])
            ->assertSessionHas('error');

        $this->assertSame('Administrador General', $admin->fresh()->nombre);
    }

    /* ===================== ELIMINAR ===================== */

    public function test_elimina_un_rol_sin_empleados(): void
    {
        $usuario = $this->usuarioDeRoles();
        $rol = Role::create(['nombre' => 'Contador']);

        $this->actingAs($usuario)
            ->delete(route('roles.destroy', $rol))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $rol->id]);
    }

    public function test_no_elimina_un_rol_que_tiene_empleados_activos_o_inactivos(): void
    {
        $usuario = $this->usuarioDeRoles();
        $this->crearUsuario('Cajero', null, ['activo' => false]);
        $rol = Role::where('nombre', 'Cajero')->first();

        $this->actingAs($usuario)
            ->delete(route('roles.destroy', $rol))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Cajero') && str_contains($m, '1 empleado'));

        $this->assertDatabaseHas('roles', ['id' => $rol->id]);
    }

    public function test_el_administrador_general_no_se_puede_eliminar(): void
    {
        $usuario = $this->usuarioDeRoles();
        $admin = Role::create(['nombre' => 'Administrador General']);

        $this->actingAs($usuario)->delete(route('roles.destroy', $admin))->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $admin->id]);
    }

    public function test_el_aviso_de_error_al_eliminar_se_ve_en_la_lista(): void
    {
        $usuario = $this->usuarioDeRoles();
        $this->crearUsuario('Cajero');
        $rol = Role::where('nombre', 'Cajero')->first();

        $this->actingAs($usuario)
            ->followingRedirects()
            ->delete(route('roles.destroy', $rol))
            ->assertSee('No se puede eliminar el rol Cajero');
    }
}
