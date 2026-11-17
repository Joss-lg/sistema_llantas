<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\CreaUsuarios;
use Tests\TestCase;

/**
 * Pruebas del middleware CheckPermiso y de User::tienePermiso().
 *
 * Se usa la ruta /gastos como "sensor": solo redirige a Caja, no pinta
 * ninguna vista, así que prueba el permiso sin depender de pantallas.
 */
class PermisosTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios;

    protected function setUp(): void
    {
        parent::setUp();

        // Las pruebas no necesitan el CSS compilado (public/build).
        $this->withoutVite();
    }

    public function test_sin_sesion_una_ruta_protegida_manda_al_login(): void
    {
        $this->get('/gastos')->assertRedirect(route('login'));
    }

    public function test_administrador_general_entra_sin_tener_permisos_guardados(): void
    {
        $admin = $this->crearUsuario('Administrador General');

        $this->actingAs($admin)
            ->get('/gastos')
            ->assertRedirect(route('caja.index'));
    }

    public function test_usuario_sin_el_permiso_recibe_403(): void
    {
        $cajero = $this->crearUsuario('Cajero');

        $this->actingAs($cajero)
            ->get('/gastos')
            ->assertForbidden();
    }

    public function test_usuario_con_el_permiso_pasa(): void
    {
        $cajero = $this->crearUsuario('Cajero');
        $this->darPermiso($cajero, 'gastos.index');

        $this->actingAs($cajero)
            ->get('/gastos')
            ->assertRedirect(route('caja.index'));
    }

    public function test_el_permiso_es_por_usuario_y_no_por_rol(): void
    {
        $sucursal = $this->crearSucursal();
        $conPermiso = $this->crearUsuario('Cajero', $sucursal, ['email' => 'a@prueba.test']);
        $sinPermiso = $this->crearUsuario('Cajero', $sucursal, ['email' => 'b@prueba.test']);
        $this->darPermiso($conPermiso, 'gastos.index');

        $this->actingAs($conPermiso)->get('/gastos')->assertRedirect(route('caja.index'));
        $this->actingAs($sinPermiso)->get('/gastos')->assertForbidden();
    }

    public function test_usuario_inactivo_pierde_la_sesion_y_va_al_login(): void
    {
        $inactivo = $this->crearUsuario('Administrador General', null, ['activo' => false]);

        $this->actingAs($inactivo)
            ->get('/gastos')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Tu cuenta se encuentra inactiva. Contacta al Administrador.');

        $this->assertGuest();
    }

    /**
     * Candado: CheckPermiso deja pasar las rutas SIN nombre. Si alguien agrega
     * una ruta protegida y olvida ponerle nombre, cualquier usuario con sesión
     * podría entrar. Esta prueba avisa si pasa.
     */
    public function test_todas_las_rutas_con_middleware_permiso_tienen_nombre(): void
    {
        $conPermiso = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($ruta) => in_array('permiso', $ruta->gatherMiddleware(), true));

        // Si esto falla, el filtro no encontró rutas y la prueba no probaría nada.
        $this->assertGreaterThan(10, $conPermiso->count());

        $sinNombre = $conPermiso
            ->filter(fn ($ruta) => empty($ruta->getName()))
            ->map(fn ($ruta) => $ruta->methods()[0] . ' /' . ltrim($ruta->uri(), '/'))
            ->values()
            ->all();

        $this->assertSame([], $sinNombre, 'Rutas con middleware permiso pero sin nombre: ' . implode(', ', $sinNombre));
    }
}