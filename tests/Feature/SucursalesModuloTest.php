<?php

namespace Tests\Feature;

use App\Models\CorteCaja;
use App\Models\MovimientoInventario;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Módulo de Sucursales: lista, alta y edición con nombres limpios y sin repetir,
 * activar/desactivar con candados, y eliminar solo una sucursal que nunca se usó.
 */
class SucursalesModuloTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    private const RUTAS = [
        'sucursales.index', 'sucursales.create', 'sucursales.store',
        'sucursales.edit', 'sucursales.update', 'sucursales.toggle', 'sucursales.destroy',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** Administrador con los permisos del módulo, en una sucursal propia. */
    private function usuarioDeSucursales(string $sucursal = 'Principal'): User
    {
        $usuario = $this->crearUsuario('Gerente', $this->crearSucursal($sucursal));

        foreach (self::RUTAS as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    /* ===================== LISTA ===================== */

    public function test_la_lista_abre_y_muestra_las_sucursales_con_sus_numeros(): void
    {
        $usuario = $this->usuarioDeSucursales('Principal');
        $this->crearSucursal('Centro');
        $this->crearSucursal('Norte', false);

        $this->actingAs($usuario)
            ->get(route('sucursales.index'))
            ->assertOk()
            ->assertSee('Principal')
            ->assertSee('Centro')
            ->assertSee('Norte')
            ->assertViewHas('totalSucursales', 3)
            ->assertViewHas('activas', 2)
            ->assertViewHas('inactivas', 1);
    }

    public function test_la_lista_cuenta_solo_los_empleados_activos_de_cada_sucursal(): void
    {
        $usuario = $this->usuarioDeSucursales('Principal');
        $centro = $this->crearSucursal('Centro');
        $this->crearUsuario('Cajero', $centro);
        $this->crearUsuario('Cajero', $centro);
        $this->crearUsuario('Cajero', $centro, ['activo' => false]);

        $this->actingAs($usuario)
            ->get(route('sucursales.index'))
            ->assertOk()
            ->assertSee('2 empleados')
            ->assertViewHas('sucursales', fn ($s) => $s->firstWhere('nombre', 'Centro')->empleados_count === 2);
    }

    public function test_sin_el_permiso_de_sucursales_recibe_403(): void
    {
        $usuario = $this->crearUsuario('Cajero');

        $this->actingAs($usuario)->get(route('sucursales.index'))->assertForbidden();
        $this->actingAs($usuario)->post(route('sucursales.store'), ['nombre' => 'Nueva'])->assertForbidden();
    }

    public function test_el_menu_muestra_el_enlace_solo_a_quien_tiene_el_permiso(): void
    {
        $con = $this->usuarioDeSucursales();
        $sin = $this->crearUsuario('Cajero');
        $this->darPermiso($sin, 'empleados.index');

        $this->actingAs($con)->get(route('sucursales.index'))->assertSee(route('sucursales.index'), false);
        $this->actingAs($sin)->get(route('empleados.index'))->assertDontSee(route('sucursales.index'), false);
    }

    /* ===================== ALTA Y EDICIÓN ===================== */

    public function test_crea_una_sucursal_activa_y_le_quita_los_espacios_sobrantes(): void
    {
        $usuario = $this->usuarioDeSucursales();

        $this->actingAs($usuario)
            ->post(route('sucursales.store'), ['nombre' => '   Sucursal Centro   '])
            ->assertRedirect(route('sucursales.index'))
            ->assertSessionHas('success');

        $nueva = Sucursal::where('nombre', 'Sucursal Centro')->firstOrFail();
        $this->assertTrue((bool) $nueva->activa);
    }

    public function test_no_deja_crear_una_sucursal_repetida_ni_vacia_ni_muy_larga(): void
    {
        $usuario = $this->usuarioDeSucursales('Principal');

        $this->actingAs($usuario)->post(route('sucursales.store'), ['nombre' => '  Principal '])
            ->assertSessionHasErrors(['nombre' => 'Ya existe una sucursal con ese nombre.']);

        $this->actingAs($usuario)->post(route('sucursales.store'), ['nombre' => ''])
            ->assertSessionHasErrors(['nombre' => 'Escribe el nombre de la sucursal.']);

        $this->actingAs($usuario)->post(route('sucursales.store'), ['nombre' => str_repeat('a', 101)])
            ->assertSessionHasErrors(['nombre' => 'El nombre de la sucursal no puede pasar de 100 letras.']);

        $this->assertSame(1, Sucursal::count());
    }

    public function test_los_formularios_abren(): void
    {
        $usuario = $this->usuarioDeSucursales();
        $otra = $this->crearSucursal('Centro');

        $this->actingAs($usuario)->get(route('sucursales.create'))->assertOk()->assertSee('Nueva sucursal');
        $this->actingAs($usuario)->get(route('sucursales.edit', $otra->id))->assertOk()->assertSee('Centro');
    }

    public function test_edita_el_nombre_y_conserva_sus_empleados(): void
    {
        $usuario = $this->usuarioDeSucursales();
        $centro = $this->crearSucursal('Centro');
        $empleado = $this->crearUsuario('Cajero', $centro);

        $this->actingAs($usuario)
            ->put(route('sucursales.update', $centro->id), ['nombre' => 'Centro Histórico'])
            ->assertRedirect(route('sucursales.index'));

        $this->assertSame('Centro Histórico', $centro->fresh()->nombre);
        $this->assertSame($centro->id, $empleado->fresh()->sucursal_id);
    }

    public function test_guardar_con_su_mismo_nombre_no_da_error_pero_con_el_de_otra_si(): void
    {
        $usuario = $this->usuarioDeSucursales('Principal');
        $centro = $this->crearSucursal('Centro');

        $this->actingAs($usuario)->put(route('sucursales.update', $centro->id), ['nombre' => 'Centro'])
            ->assertSessionHasNoErrors();

        $this->actingAs($usuario)->put(route('sucursales.update', $centro->id), ['nombre' => 'Principal'])
            ->assertSessionHasErrors(['nombre' => 'Ya existe otra sucursal con ese nombre.']);
    }

    /* ===================== ACTIVAR / DESACTIVAR ===================== */

    public function test_desactiva_y_vuelve_a_activar_una_sucursal_sin_empleados(): void
    {
        $usuario = $this->usuarioDeSucursales();
        $centro = $this->crearSucursal('Centro');

        $this->actingAs($usuario)->patch(route('sucursales.toggle', $centro->id))
            ->assertSessionHas('success');
        $this->assertFalse((bool) $centro->fresh()->activa);

        $this->actingAs($usuario)->patch(route('sucursales.toggle', $centro->id))
            ->assertSessionHas('success');
        $this->assertTrue((bool) $centro->fresh()->activa);
    }

    public function test_no_se_desactiva_una_sucursal_con_empleados_activos(): void
    {
        $usuario = $this->usuarioDeSucursales();
        $centro = $this->crearSucursal('Centro');
        $this->crearUsuario('Cajero', $centro);

        $this->actingAs($usuario)->patch(route('sucursales.toggle', $centro->id))
            ->assertSessionHas('error');

        $this->assertTrue((bool) $centro->fresh()->activa);
    }

    public function test_una_sucursal_con_empleados_inactivos_si_se_puede_desactivar(): void
    {
        $usuario = $this->usuarioDeSucursales();
        $centro = $this->crearSucursal('Centro');
        $this->crearUsuario('Cajero', $centro, ['activo' => false]);

        $this->actingAs($usuario)->patch(route('sucursales.toggle', $centro->id))
            ->assertSessionHas('success');

        $this->assertFalse((bool) $centro->fresh()->activa);
    }

    public function test_no_se_desactiva_la_ultima_sucursal_activa(): void
    {
        $usuario = $this->usuarioDeSucursales('Principal');
        $unica = $usuario->sucursal;
        // El usuario no cuenta: se mueve a otra sucursal ya inactiva para aislar la regla.
        $inactiva = $this->crearSucursal('Vieja', false);
        $usuario->update(['sucursal_id' => $inactiva->id]);

        $this->actingAs($usuario)->patch(route('sucursales.toggle', $unica->id))
            ->assertSessionHas('error', 'Debe quedar al menos una sucursal activa.');

        $this->assertTrue((bool) $unica->fresh()->activa);
    }

    /* ===================== ELIMINAR ===================== */

    public function test_elimina_una_sucursal_que_nunca_se_uso(): void
    {
        $usuario = $this->usuarioDeSucursales();
        $creada = $this->crearSucursal('Creada por error');

        $this->actingAs($usuario)->delete(route('sucursales.destroy', $creada->id))
            ->assertRedirect(route('sucursales.index'))
            ->assertSessionHas('success');

        $this->assertNull(Sucursal::find($creada->id));
    }

    public function test_una_sucursal_con_stock_en_cero_si_se_elimina_y_se_lleva_ese_registro(): void
    {
        $usuario = $this->usuarioDeSucursales();
        $creada = $this->crearSucursal('Vacia');
        $this->darStock($this->crearProducto(), $creada, 0);

        $this->actingAs($usuario)->delete(route('sucursales.destroy', $creada->id))
            ->assertSessionHas('success');

        $this->assertNull(Sucursal::find($creada->id));
        $this->assertSame(0, StockSucursal::where('sucursal_id', $creada->id)->count());
    }

    /** Cada cosa que la deja "en uso" por separado. */
    public static function usos(): array
    {
        return [
            'un empleado activo'     => ['empleado'],
            'un empleado inactivo'   => ['empleado inactivo'],
            'una venta'              => ['venta'],
            'un turno de caja'       => ['caja'],
            'una cotizacion'         => ['cotizacion'],
            'un movimiento'          => ['movimiento'],
            'piezas en stock'        => ['stock'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('usos')]
    public function test_no_elimina_una_sucursal_que_ya_se_uso(string $uso): void
    {
        $usuario = $this->usuarioDeSucursales();
        $sucursal = $this->crearSucursal('Con historial');

        // El autor de ventas, cajas y movimientos es el usuario del módulo (de otra sucursal),
        // así cada caso deja a la sucursal "en uso" por una sola razón.
        match ($uso) {
            'empleado'          => $this->crearUsuario('Cajero', $sucursal),
            'empleado inactivo' => $this->crearUsuario('Cajero', $sucursal, ['activo' => false]),
            'venta'             => $this->crearVenta($usuario, $sucursal),
            'caja'              => CorteCaja::create([
                'user_id' => $usuario->id, 'sucursal_id' => $sucursal->id, 'saldo_inicial' => 100,
                'estado' => 'abierta', 'fecha_apertura' => now(),
            ]),
            'cotizacion'        => DB::table('cotizaciones')->insert([
                'folio' => 'COT-1', 'sucursal_id' => $sucursal->id, 'usuario_id' => $usuario->id,
                'total' => 100, 'fecha' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]),
            'movimiento'        => MovimientoInventario::create([
                'producto_id' => $this->crearProducto()->id, 'sucursal_id' => $sucursal->id,
                'usuario_id' => $usuario->id, 'tipo' => 'entrada', 'cantidad' => 1, 'fecha' => now(),
            ]),
            'stock'             => $this->darStock($this->crearProducto(), $sucursal, 3),
        };

        $this->actingAs($usuario)->delete(route('sucursales.destroy', $sucursal->id))
            ->assertSessionHas('error');

        $this->assertNotNull(Sucursal::find($sucursal->id));
    }

    public function test_la_lista_apaga_el_borrado_de_una_sucursal_usada_y_lo_ofrece_en_una_libre(): void
    {
        $usuario = $this->usuarioDeSucursales('Principal');
        $libre = $this->crearSucursal('Libre');

        $html = $this->actingAs($usuario)
            ->get(route('sucursales.index'))
            ->assertOk()
            ->assertViewHas('sucursales', fn ($s) => $s->firstWhere('nombre', 'Principal')->en_uso === true
                && $s->firstWhere('nombre', 'Libre')->en_uso === false)
            ->getContent();

        // Un botón de borrar activo (la libre) y un icono apagado (la usada).
        $this->assertSame(1, substr_count($html, 'modalEliminar = true'));
        $this->assertSame(1, substr_count($html, 'No se puede eliminar: ya tiene empleados'));
    }

    public function test_sin_el_permiso_de_eliminar_recibe_403(): void
    {
        $usuario = $this->crearUsuario('Cajero');
        $this->darPermiso($usuario, 'sucursales.index');
        $libre = $this->crearSucursal('Libre');

        $this->actingAs($usuario)->delete(route('sucursales.destroy', $libre->id))->assertForbidden();

        $this->assertNotNull(Sucursal::find($libre->id));
    }

    public function test_una_sucursal_nueva_aparece_al_crear_empleados(): void
    {
        $usuario = $this->usuarioDeSucursales();
        $this->darPermiso($usuario, 'empleados.create');
        $this->actingAs($usuario)->post(route('sucursales.store'), ['nombre' => 'Sucursal Sur']);

        $this->actingAs($usuario)
            ->get(route('empleados.create'))
            ->assertOk()
            ->assertSee('Sucursal Sur');
    }
}
