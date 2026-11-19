<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Módulo de Empleados: listas con búsqueda y números reales, y las reglas de seguridad
 * (administrador principal, un administrador solo lo toca otro administrador,
 * nadie se deshabilita ni se elimina a sí mismo, y con historial no se elimina).
 */
class EmpleadosTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    private const RUTAS = [
        'empleados.index', 'empleados.inactivos', 'empleados.create', 'empleados.store',
        'empleados.edit', 'empleados.update', 'empleados.destroy', 'empleados.toggle',
        'empleados.permisos.update',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** Un gerente con permiso en todas las rutas de empleados (pero que NO es administrador). */
    private function gerente(?Sucursal $sucursal = null, array $atributos = []): User
    {
        $gerente = $this->crearUsuario('Gerente', $sucursal, $atributos);

        foreach (self::RUTAS as $ruta) {
            $this->darPermiso($gerente, $ruta);
        }

        return $gerente;
    }

    private function admin(?Sucursal $sucursal = null, array $atributos = []): User
    {
        return $this->crearUsuario('Administrador General', $sucursal, $atributos);
    }

    private function principal(?Sucursal $sucursal = null): User
    {
        return $this->admin($sucursal, ['name' => 'Administrador Principal', 'email' => 'admin@llantas.com']);
    }

    private function datosValidos(Sucursal $sucursal, Role $rol, array $extra = []): array
    {
        return array_merge([
            'name'        => 'Laura Pérez',
            'email'       => 'laura@llantas.com',
            'password'    => 'secreta123',
            'sucursal_id' => $sucursal->id,
            'rol_id'      => $rol->id,
            'activo'      => 'on',
        ], $extra);
    }

    /* ===================== LISTA DE ACTIVOS ===================== */

    public function test_la_lista_abre_y_muestra_nombres_y_correos(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Marta Ríos', 'email' => 'marta@llantas.com']);

        $this->actingAs($gerente)
            ->get(route('empleados.index'))
            ->assertOk()
            ->assertSee('Marta Ríos')
            ->assertSee('marta@llantas.com');
    }

    public function test_sin_el_permiso_de_empleados_recibe_403(): void
    {
        $sinPermiso = $this->crearUsuario('Cajero');

        $this->actingAs($sinPermiso)->get(route('empleados.index'))->assertForbidden();
        $this->actingAs($sinPermiso)->get(route('empleados.inactivos'))->assertForbidden();
    }

    public function test_la_lista_de_activos_no_muestra_a_los_deshabilitados(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Persona Activa']);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Persona Baja', 'activo' => false]);

        $this->actingAs($gerente)
            ->get(route('empleados.index'))
            ->assertSee('Persona Activa')
            ->assertDontSee('Persona Baja');
    }

    public function test_el_administrador_principal_siempre_va_primero(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc, ['name' => 'Aaron Gerente']);
        $principal = $this->principal($suc);
        $principal->update(['name' => 'Zeta Principal']);

        $this->actingAs($gerente)
            ->get(route('empleados.index'))
            ->assertViewHas('empleados', fn ($p) => $p->items()[0]->id === $principal->id);
    }

    public function test_las_tarjetas_cuentan_todo_el_filtro_y_no_solo_la_pagina(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $this->principal($suc);

        foreach (range(1, 16) as $i) {
            $this->crearUsuario('Cajero', $suc, ['name' => "Cajero Numero {$i}"]);
        }
        $this->crearUsuario('Cajero', $suc, ['name' => 'Baja Uno', 'activo' => false]);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Baja Dos', 'activo' => false]);

        // 1 gerente + 1 principal + 16 cajeros = 18 activos, pero la página trae 15
        $this->actingAs($gerente)
            ->get(route('empleados.index'))
            ->assertViewHas('totalActivos', 18)
            ->assertViewHas('totalAdmins', 1)
            ->assertViewHas('totalInactivos', 2)
            ->assertViewHas('totalRoles', 3)
            ->assertViewHas('empleados', fn ($p) => count($p->items()) === 15 && $p->total() === 18);
    }

    public function test_busca_por_nombre_y_por_correo(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Rosa Montes', 'email' => 'rosa@llantas.com']);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Pedro Soto', 'email' => 'pedro@taller.com']);

        $this->actingAs($gerente)
            ->get(route('empleados.index', ['q' => 'Montes']))
            ->assertSee('Rosa Montes')
            ->assertDontSee('Pedro Soto')
            ->assertViewHas('totalActivos', 1);

        $this->actingAs($gerente)
            ->get(route('empleados.index', ['q' => 'taller.com']))
            ->assertSee('Pedro Soto')
            ->assertDontSee('Rosa Montes');
    }

    public function test_el_signo_de_porcentaje_en_la_busqueda_no_trae_a_todos(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Rosa Montes']);

        $this->actingAs($gerente)
            ->get(route('empleados.index', ['q' => '%']))
            ->assertViewHas('totalActivos', 0)
            ->assertSee('Sin resultados');
    }

    public function test_un_gerente_ve_que_solo_un_administrador_puede_tocar_a_otro_administrador(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $this->admin($suc, ['name' => 'Jefa Admin', 'email' => 'jefa@llantas.com']);

        $this->actingAs($gerente)
            ->get(route('empleados.index'))
            ->assertSee('Jefa Admin')
            ->assertSee('Solo un administrador');
    }

    /* ===================== LISTA DE INACTIVOS ===================== */

    public function test_inactivos_muestra_solo_deshabilitados_y_busca(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Baja Ana', 'activo' => false]);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Baja Luis', 'activo' => false]);
        $this->crearUsuario('Cajero', $suc, ['name' => 'Sigue Activo']);

        $this->actingAs($gerente)
            ->get(route('empleados.inactivos'))
            ->assertOk()
            ->assertSee('Baja Ana')
            ->assertSee('Baja Luis')
            ->assertDontSee('Sigue Activo')
            ->assertViewHas('totalInactivos', 2);

        $this->actingAs($gerente)
            ->get(route('empleados.inactivos', ['q' => 'Luis']))
            ->assertSee('Baja Luis')
            ->assertDontSee('Baja Ana')
            ->assertViewHas('totalInactivos', 1);
    }

    public function test_inactivos_marca_a_quien_tiene_historial_y_no_ofrece_eliminarlo(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $conVentas = $this->crearUsuario('Cajero', $suc, ['name' => 'Baja Con Ventas']);
        $this->crearVenta($conVentas, $suc, 500);
        $conVentas->update(['activo' => false]);
        $sinNada = $this->crearUsuario('Cajero', $suc, ['name' => 'Baja Sin Nada', 'activo' => false]);

        $this->actingAs($gerente)
            ->get(route('empleados.inactivos'))
            ->assertViewHas('conHistorial', fn ($ids) => in_array($conVentas->id, $ids, true) && ! in_array($sinNada->id, $ids, true))
            ->assertSee('Con historial');
    }

    /* ===================== ALTA ===================== */

    public function test_crea_un_empleado_con_sus_permisos(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $rol = Role::firstOrCreate(['nombre' => 'Cajero']);
        $permiso = Permiso::create(['nombre' => 'Ver ventas', 'ruta' => 'ventas.index', 'modulo' => 'Ventas']);

        $this->actingAs($gerente)
            ->post(route('empleados.store'), $this->datosValidos($suc, $rol, ['permisos' => [$permiso->id]]))
            ->assertRedirect(route('empleados.index'))
            ->assertSessionHas('success');

        $nuevo = User::where('email', 'laura@llantas.com')->firstOrFail();
        $this->assertTrue($nuevo->activo);
        $this->assertTrue(Hash::check('secreta123', $nuevo->password));
        $this->assertSame([$permiso->id], $nuevo->permisos()->pluck('permisos.id')->all());
    }

    public function test_al_crear_los_mensajes_de_error_salen_en_espanol(): void
    {
        $gerente = $this->gerente();

        $respuesta = $this->actingAs($gerente)->post(route('empleados.store'), []);

        $respuesta->assertSessionHasErrors(['name', 'email', 'password', 'sucursal_id', 'rol_id']);
        $this->assertSame('Escribe el correo del empleado.', session('errors')->first('email'));
        $this->assertSame('Escribe una contraseña.', session('errors')->first('password'));
        $this->assertSame('Elige un rol.', session('errors')->first('rol_id'));
    }

    public function test_no_deja_crear_dos_empleados_con_el_mismo_correo(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $rol = Role::firstOrCreate(['nombre' => 'Cajero']);
        $this->crearUsuario('Cajero', $suc, ['email' => 'laura@llantas.com']);

        $this->actingAs($gerente)
            ->post(route('empleados.store'), $this->datosValidos($suc, $rol))
            ->assertSessionHasErrors('email');

        $this->assertSame('Ya hay un empleado con ese correo.', session('errors')->first('email'));
    }

    public function test_un_gerente_no_puede_crear_administradores(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $rolAdmin = Role::firstOrCreate(['nombre' => 'Administrador General']);

        $this->actingAs($gerente)
            ->post(route('empleados.store'), $this->datosValidos($suc, $rolAdmin))
            ->assertSessionHasErrors('rol_id');

        $this->assertDatabaseMissing('users', ['email' => 'laura@llantas.com']);
    }

    public function test_un_administrador_si_puede_crear_administradores(): void
    {
        $suc = $this->crearSucursal();
        $admin = $this->admin($suc);
        $rolAdmin = Role::firstOrCreate(['nombre' => 'Administrador General']);

        $this->actingAs($admin)
            ->post(route('empleados.store'), $this->datosValidos($suc, $rolAdmin))
            ->assertRedirect(route('empleados.index'));

        $this->assertDatabaseHas('users', ['email' => 'laura@llantas.com', 'rol_id' => $rolAdmin->id]);
    }

    public function test_el_formulario_solo_ofrece_el_rol_de_administrador_a_los_administradores(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $this->admin($suc); // asegura que el rol existe

        $this->actingAs($gerente)
            ->get(route('empleados.create'))
            ->assertViewHas('roles', fn ($roles) => ! $roles->contains('nombre', 'Administrador General') && $roles->contains('nombre', 'Gerente'));

        $this->actingAs($this->admin($suc))
            ->get(route('empleados.create'))
            ->assertViewHas('roles', fn ($roles) => $roles->contains('nombre', 'Administrador General'));
    }

    /* ===================== EDICIÓN ===================== */

    public function test_edita_un_empleado_y_cambia_sus_permisos(): void
    {
        $suc = $this->crearSucursal();
        $otraSuc = $this->crearSucursal('Texcoco');
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc, ['name' => 'Nombre Viejo']);
        $rolGerente = Role::firstOrCreate(['nombre' => 'Gerente']);
        $permiso = Permiso::create(['nombre' => 'Ver ventas', 'ruta' => 'ventas.index', 'modulo' => 'Ventas']);

        $this->actingAs($gerente)
            ->put(route('empleados.update', $cajero), [
                'name'        => 'Nombre Nuevo',
                'email'       => $cajero->email,
                'password'    => '',
                'sucursal_id' => $otraSuc->id,
                'rol_id'      => $rolGerente->id,
                'permisos'    => [$permiso->id],
                // sin "activo": queda deshabilitado
            ])
            ->assertRedirect(route('empleados.index'))
            ->assertSessionHas('success');

        $cajero->refresh();
        $this->assertSame('Nombre Nuevo', $cajero->name);
        $this->assertSame($otraSuc->id, $cajero->sucursal_id);
        $this->assertFalse($cajero->activo);
        $this->assertSame([$permiso->id], $cajero->permisos()->pluck('permisos.id')->all());
    }

    public function test_si_no_se_escribe_contrasena_se_conserva_la_anterior(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc);
        $hashAntes = $cajero->password;

        $this->actingAs($gerente)->put(route('empleados.update', $cajero), [
            'name' => 'Otro Nombre', 'email' => $cajero->email, 'password' => '',
            'sucursal_id' => $suc->id, 'rol_id' => $cajero->rol_id, 'activo' => 'on',
        ])->assertRedirect(route('empleados.index'));

        $this->assertSame($hashAntes, $cajero->fresh()->password);
    }

    public function test_un_gerente_no_puede_editar_a_un_administrador(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $admin = $this->admin($suc, ['name' => 'Jefa Admin']);

        $this->actingAs($gerente)
            ->get(route('empleados.edit', $admin))
            ->assertRedirect(route('empleados.index'))
            ->assertSessionHas('error');

        $this->actingAs($gerente)
            ->put(route('empleados.update', $admin), [
                'name' => 'Hackeada', 'email' => $admin->email, 'sucursal_id' => $suc->id, 'rol_id' => $admin->rol_id,
            ])
            ->assertRedirect(route('empleados.index'))
            ->assertSessionHas('error');

        $this->assertSame('Jefa Admin', $admin->fresh()->name);
    }

    public function test_un_gerente_no_puede_ascender_a_alguien_a_administrador(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc);
        $rolAdmin = Role::firstOrCreate(['nombre' => 'Administrador General']);

        $this->actingAs($gerente)
            ->put(route('empleados.update', $cajero), [
                'name' => $cajero->name, 'email' => $cajero->email,
                'sucursal_id' => $suc->id, 'rol_id' => $rolAdmin->id, 'activo' => 'on',
            ])
            ->assertSessionHasErrors('rol_id');

        $this->assertNotSame($rolAdmin->id, $cajero->fresh()->rol_id);
    }

    public function test_al_administrador_principal_no_se_le_cambia_correo_rol_ni_estado(): void
    {
        $suc = $this->crearSucursal();
        $principal = $this->principal($suc);
        $rolOriginal = $principal->rol_id;
        $rolCajero = Role::firstOrCreate(['nombre' => 'Cajero']);

        $this->actingAs($principal)
            ->put(route('empleados.update', $principal), [
                'name'        => 'Nombre Cambiado',
                'email'       => 'otro@correo.com',
                'sucursal_id' => $suc->id,
                'rol_id'      => $rolCajero->id,
                // sin "activo"
            ])
            ->assertRedirect(route('empleados.index'));

        $principal->refresh();
        $this->assertSame('Nombre Cambiado', $principal->name);   // el nombre sí se puede cambiar
        $this->assertSame('admin@llantas.com', $principal->email);
        $this->assertSame($rolOriginal, $principal->rol_id);
        $this->assertTrue($principal->activo);
    }

    public function test_nadie_se_deshabilita_a_si_mismo_desde_editar(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);

        $this->actingAs($gerente)
            ->put(route('empleados.update', $gerente), [
                'name' => $gerente->name, 'email' => $gerente->email,
                'sucursal_id' => $suc->id, 'rol_id' => $gerente->rol_id,
                // sin "activo"
            ])
            ->assertRedirect(route('empleados.index'));

        $this->assertTrue($gerente->fresh()->activo);
    }

    /* ===================== PERMISOS (ventana Configurar) ===================== */

    public function test_configurar_permisos_guarda_solo_los_elegidos(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc);
        $a = Permiso::create(['nombre' => 'Ver ventas', 'ruta' => 'ventas.index', 'modulo' => 'Ventas']);
        $b = Permiso::create(['nombre' => 'Ver gastos', 'ruta' => 'gastos.index', 'modulo' => 'Gastos']);
        $cajero->permisos()->attach($a->id);

        $this->actingAs($gerente)
            ->put(route('empleados.permisos.update', $cajero), ['permisos' => [$b->id]])
            ->assertRedirect(route('empleados.index'))
            ->assertSessionHas('success');

        $this->assertSame([$b->id], $cajero->permisos()->pluck('permisos.id')->all());
    }

    public function test_un_gerente_no_puede_cambiar_los_permisos_de_un_administrador(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $admin = $this->admin($suc);
        $a = Permiso::create(['nombre' => 'Ver ventas', 'ruta' => 'ventas.index', 'modulo' => 'Ventas']);

        $this->actingAs($gerente)
            ->put(route('empleados.permisos.update', $admin), ['permisos' => [$a->id]])
            ->assertSessionHas('error');

        $this->assertSame(0, $admin->permisos()->count());
    }

    /* ===================== HABILITAR / DESHABILITAR ===================== */

    public function test_deshabilita_y_vuelve_a_habilitar_a_un_empleado(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc);

        $this->actingAs($gerente)->patch(route('empleados.toggle', $cajero))->assertSessionHas('success');
        $this->assertFalse($cajero->fresh()->activo);

        $this->actingAs($gerente)->patch(route('empleados.toggle', $cajero))->assertSessionHas('success');
        $this->assertTrue($cajero->fresh()->activo);
    }

    public function test_no_se_puede_deshabilitar_al_administrador_principal(): void
    {
        $suc = $this->crearSucursal();
        $admin = $this->admin($suc);
        $principal = $this->principal($suc);

        $this->actingAs($admin)->patch(route('empleados.toggle', $principal))->assertSessionHas('error');

        $this->assertTrue($principal->fresh()->activo);
    }

    public function test_nadie_puede_deshabilitar_su_propia_cuenta(): void
    {
        $gerente = $this->gerente();

        $this->actingAs($gerente)->patch(route('empleados.toggle', $gerente))->assertSessionHas('error');

        $this->assertTrue($gerente->fresh()->activo);
    }

    public function test_un_gerente_no_puede_deshabilitar_a_un_administrador(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $admin = $this->admin($suc);

        $this->actingAs($gerente)->patch(route('empleados.toggle', $admin))->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->activo);
    }

    public function test_un_administrador_si_puede_deshabilitar_a_otro_administrador(): void
    {
        $suc = $this->crearSucursal();
        $admin = $this->admin($suc);
        $otro = $this->admin($suc);

        $this->actingAs($admin)->patch(route('empleados.toggle', $otro))->assertSessionHas('success');

        $this->assertFalse($otro->fresh()->activo);
    }

    /* ===================== ELIMINAR ===================== */

    public function test_elimina_a_un_empleado_sin_historial(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $baja = $this->crearUsuario('Cajero', $suc, ['activo' => false]);

        $this->actingAs($gerente)
            ->delete(route('empleados.destroy', $baja))
            ->assertRedirect(route('empleados.inactivos'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $baja->id]);
    }

    public function test_no_elimina_a_un_empleado_con_ventas_y_lo_explica(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $cajero = $this->crearUsuario('Cajero', $suc, ['name' => 'Cajero Con Ventas']);
        $this->crearVenta($cajero, $suc, 800);
        $cajero->update(['activo' => false]);

        $this->actingAs($gerente)
            ->delete(route('empleados.destroy', $cajero))
            ->assertRedirect(route('empleados.inactivos'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Cajero Con Ventas') && str_contains($m, 'deshabilitado'));

        $this->assertDatabaseHas('users', ['id' => $cajero->id]);
    }

    public function test_no_se_puede_eliminar_al_administrador_principal(): void
    {
        $suc = $this->crearSucursal();
        $admin = $this->admin($suc);
        $principal = $this->principal($suc);

        $this->actingAs($admin)->delete(route('empleados.destroy', $principal))->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $principal->id]);
    }

    public function test_nadie_puede_eliminar_su_propia_cuenta(): void
    {
        $gerente = $this->gerente();

        $this->actingAs($gerente)->delete(route('empleados.destroy', $gerente))->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $gerente->id]);
    }

    public function test_un_gerente_no_puede_eliminar_a_un_administrador(): void
    {
        $suc = $this->crearSucursal();
        $gerente = $this->gerente($suc);
        $admin = $this->admin($suc, ['activo' => false]);

        $this->actingAs($gerente)->delete(route('empleados.destroy', $admin))->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
