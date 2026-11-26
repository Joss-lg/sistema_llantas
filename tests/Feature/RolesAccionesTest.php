<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaUsuarios;
use Tests\TestCase;

/**
 * Botones de la lista de roles: el lápiz tiene que quedar en el mismo lugar en todas las filas,
 * tenga el rol empleados o no (antes, en los roles "En uso" se corría hacia la izquierda).
 */
class RolesAccionesTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function usuarioDeRoles(): User
    {
        $usuario = $this->crearUsuario('Gerente');

        foreach (['roles.index', 'roles.create', 'roles.store', 'roles.edit', 'roles.update', 'roles.destroy'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    /** Dibuja la lista con un rol en uso (Cajero), uno libre (Contador) y el protegido, y regresa el HTML de cada fila. */
    private function filas(): array
    {
        $usuario = $this->usuarioDeRoles();
        $this->crearUsuario('Cajero');
        $this->crearUsuario('Administrador General');
        Role::create(['nombre' => 'Contador']);

        $html = $this->actingAs($usuario)->get(route('roles.index'))->assertOk()->getContent();

        $filas = [];
        foreach (preg_split('/<tr\b/', $html) as $fila) {
            foreach (['Cajero', 'Contador', 'Administrador General'] as $nombre) {
                if (preg_match('/<p class="font-bold[^>]*>\s*' . preg_quote($nombre, '/') . '\b/u', $fila)) {
                    $filas[$nombre] = $fila;
                }
            }
        }

        $this->assertCount(3, $filas, 'No se encontraron las 3 filas esperadas');

        return $filas;
    }

    public function test_un_rol_en_uso_y_uno_libre_tienen_los_botones_del_mismo_tamano_y_en_el_mismo_lugar(): void
    {
        $filas = $this->filas();

        // En cada fila: la letra del rol + el lápiz + el espacio de borrar (3 piezas de 36 px)
        $this->assertSame(3, substr_count($filas['Cajero'], 'w-9 h-9'));
        $this->assertSame(3, substr_count($filas['Contador'], 'w-9 h-9'));
    }

    public function test_el_rol_en_uso_muestra_el_borrado_apagado_y_el_libre_el_boton_de_borrar(): void
    {
        $filas = $this->filas();

        $this->assertStringContainsString('cursor-not-allowed', $filas['Cajero']);
        $this->assertStringNotContainsString('title="Eliminar"', $filas['Cajero']);

        $this->assertStringContainsString('title="Eliminar"', $filas['Contador']);
        $this->assertStringNotContainsString('cursor-not-allowed', $filas['Contador']);
    }

    public function test_el_aviso_en_uso_sale_solo_en_el_rol_que_tiene_empleados(): void
    {
        $filas = $this->filas();

        $this->assertStringContainsString('En uso', $filas['Cajero']);
        $this->assertStringNotContainsString('En uso', $filas['Contador']);
        $this->assertStringNotContainsString('En uso', $filas['Administrador General']);
    }

    public function test_el_administrador_general_sigue_protegido_sin_botones(): void
    {
        $filas = $this->filas();

        $this->assertStringContainsString('Protegido', $filas['Administrador General']);
        $this->assertStringNotContainsString('title="Editar"', $filas['Administrador General']);
        $this->assertStringNotContainsString('title="Eliminar"', $filas['Administrador General']);
    }

    public function test_ya_no_se_usa_el_texto_de_ancho_fijo_que_movia_el_lapiz(): void
    {
        $usuario = $this->usuarioDeRoles();
        $this->crearUsuario('Cajero');

        $this->actingAs($usuario)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertDontSee('w-20 text-right', false);
    }
}
