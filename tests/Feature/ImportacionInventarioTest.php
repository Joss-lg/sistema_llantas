<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Pruebas de la importación de inventario desde Excel/CSV
 * (ImportacionInventarioController + ExcelInventarioImporter).
 *
 * Casi todas usan un CSV escrito en texto; una sola usa un .xlsx real.
 * Storage::fake evita escribir en la carpeta real storage/app/private.
 */
class ImportacionInventarioTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    private const ENCABEZADO = ['MARCA', 'MEDIDA', 'PRECIO PÚBLICO', 'MAYOREO', 'COSTO', 'STOCK'];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->withoutVite();
    }

    /* ===================== AYUDAS ===================== */

    private function usuarioDeImportacion(?Sucursal $sucursal = null, string $rol = 'Cajero'): User
    {
        $usuario = $this->crearUsuario($rol, $sucursal);

        foreach (['inventario.importar', 'inventario.procesar'] as $ruta) {
            $this->darPermiso($usuario, $ruta);
        }

        return $usuario;
    }

    /** Chalco, Valle de Chalco y un cajero en Chalco. */
    private function escenario(): array
    {
        $chalco = $this->crearSucursal('Chalco');
        $valle = $this->crearSucursal('Valle de Chalco');

        return [$this->usuarioDeImportacion($chalco), $chalco, $valle];
    }

    /** CSV a partir de líneas de texto tal cual (para armar casos raros). */
    private function archivoCsv(array $lineas): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('inventario.csv', implode("\n", $lineas) . "\n");
    }

    /** CSV con el encabezado normal y las filas que se le den. */
    private function csv(array $filas): UploadedFile
    {
        $lineas = array_map(fn ($fila) => implode(',', $fila), $filas);
        array_unshift($lineas, implode(',', self::ENCABEZADO));

        return $this->archivoCsv($lineas);
    }

    private function previsualizar(User $usuario, UploadedFile $archivo, array $extra = [])
    {
        return $this->actingAs($usuario)
            ->from(route('inventario.importar'))
            ->post(route('inventario.procesar'), array_merge(['archivo_excel' => $archivo], $extra));
    }

    private function confirmar(User $usuario)
    {
        return $this->actingAs($usuario)
            ->from(route('inventario.importar'))
            ->post(route('inventario.procesar'), ['accion' => 'confirmar']);
    }

    private function stockDe(Producto $producto, Sucursal $sucursal): ?int
    {
        $cantidad = StockSucursal::where('producto_id', $producto->id)
            ->where('sucursal_id', $sucursal->id)
            ->value('cantidad');

        return $cantidad === null ? null : (int) $cantidad;
    }

    private function producto(string $marca, string $medida): Producto
    {
        return Producto::where('marca', $marca)->where('medida', $medida)->firstOrFail();
    }

    private function nadaImportado(): void
    {
        $this->assertSame(0, Producto::count());
        $this->assertSame(0, StockSucursal::count());
        $this->assertSame(0, MovimientoInventario::count());
    }

    /* ===================== PANTALLA Y VISTA PREVIA ===================== */

    public function test_la_pantalla_de_importar_abre(): void
    {
        [$usuario] = $this->escenario();

        $this->actingAs($usuario)
            ->get(route('inventario.importar'))
            ->assertOk()
            ->assertViewHas('sucursales');
    }

    public function test_la_vista_previa_no_guarda_nada_y_cuenta_bien_las_filas(): void
    {
        [$usuario] = $this->escenario();

        $this->previsualizar($usuario, $this->csv([
            ['PIRELLI', '205/55R16', '900', '700', '500', '10'],
            ['MICHELIN', '195/65R15', '800', '600', '400', '5'],
            ['', '175/70R13', '500', '400', '300', '1'],
            ['HANKOOK', '185/65R15', '-5', '', '', '3'],
        ]))
            ->assertOk()
            ->assertViewIs('inventario.importar')
            ->assertViewHas('vista', fn ($vista) => $vista['total'] === 4
                && $vista['ok'] === 2
                && $vista['con_error'] === 2
                && $vista['nuevos'] === 2
                && $vista['actualizados'] === 0);

        $this->assertNotNull(session('importacion_pendiente'));
        $this->nadaImportado();
    }

    public function test_el_encabezado_puede_estar_debajo_de_filas_de_titulo(): void
    {
        [$usuario] = $this->escenario();

        $this->previsualizar($usuario, $this->archivoCsv([
            'LISTA DE PRECIOS CHALCO',
            '',
            implode(',', self::ENCABEZADO),
            'PIRELLI,205/55R16,900,700,500,10',
        ]))
            ->assertOk()
            ->assertViewHas('vista', fn ($vista) => $vista['total'] === 1 && $vista['ok'] === 1);
    }

    public function test_un_usuario_normal_importa_siempre_a_su_sucursal_aunque_mande_otra(): void
    {
        [$usuario, $chalco, $valle] = $this->escenario();

        $this->previsualizar($usuario, $this->csv([['PIRELLI', '205/55R16', '900', '700', '500', '10']]), [
            'sucursal_id' => $valle->id,
        ]);

        $this->assertSame($chalco->id, (int) session('importacion_pendiente.sucursal'));
    }

    public function test_el_administrador_general_sin_elegir_sucursal_recibe_un_error(): void
    {
        $admin = $this->usuarioDeImportacion($this->crearSucursal('Administración General'), 'Administrador General');

        $this->previsualizar($admin, $this->csv([['PIRELLI', '205/55R16', '900', '700', '500', '10']]))
            ->assertSessionHas('error', 'Selecciona una sucursal activa a la que se cargará el inventario.');

        $this->assertNull(session('importacion_pendiente'));
        $this->nadaImportado();
    }

    public function test_el_administrador_general_importa_a_la_sucursal_que_elige(): void
    {
        $admin = $this->usuarioDeImportacion($this->crearSucursal('Administración General'), 'Administrador General');
        $valle = $this->crearSucursal('Valle de Chalco');

        $this->previsualizar($admin, $this->csv([['PIRELLI', '205/55R16', '900', '700', '500', '10']]), [
            'sucursal_id' => $valle->id,
        ]);
        $this->confirmar($admin)->assertRedirect(route('inventario.index'));

        $this->assertSame(10, $this->stockDe($this->producto('PIRELLI', '205/55R16'), $valle));
    }

    /* ===================== ARCHIVOS INVALIDOS ===================== */

    public function test_sin_archivo_se_rechaza(): void
    {
        [$usuario] = $this->escenario();

        $this->actingAs($usuario)
            ->from(route('inventario.importar'))
            ->post(route('inventario.procesar'), [])
            ->assertSessionHasErrors('archivo_excel');
    }

    public function test_una_extension_no_permitida_se_rechaza(): void
    {
        [$usuario] = $this->escenario();

        $this->previsualizar($usuario, UploadedFile::fake()->create('inventario.pdf', 10))
            ->assertSessionHasErrors('archivo_excel');

        $this->nadaImportado();
    }

    public function test_un_archivo_sin_los_encabezados_esperados_da_error(): void
    {
        [$usuario] = $this->escenario();

        $this->previsualizar($usuario, $this->archivoCsv(['hola,mundo', 'uno,dos']))
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'No se encontraron los encabezados esperados'));

        $this->assertNull(session('importacion_pendiente'));
        $this->assertSame([], Storage::disk('local')->allFiles('importaciones'));
    }

    public function test_un_archivo_con_encabezados_pero_sin_productos_da_error(): void
    {
        [$usuario] = $this->escenario();

        $this->previsualizar($usuario, $this->csv([]))
            ->assertSessionHas('error', 'El archivo no tiene productos debajo de los encabezados.');

        $this->assertNull(session('importacion_pendiente'));
    }

    /* ===================== CONFIRMAR: MODO SUMAR ===================== */

    public function test_modo_sumar_crea_productos_nuevos_actualiza_los_existentes_y_registra_entradas(): void
    {
        [$usuario, $chalco, $valle] = $this->escenario();
        $pirelli = $this->crearProducto(); // PIRELLI 205/55R16: costo 500, público 900, mayoreo 700
        $this->darStock($pirelli, $chalco, 5);

        $this->previsualizar($usuario, $this->csv([
            ['PIRELLI', '205/55R16', '950', '', '', '10'],     // existe: sube el precio, suma 10
            ['MICHELIN', '195/65R15', '800', '600', '400', '5'], // nuevo con todo
            ['GOODYEAR', '215/60R16', '700', '', '', ''],      // nuevo sin stock
        ]));

        $this->confirmar($usuario)
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('success', 'Importación terminada: 2 productos nuevos, 1 actualizados, 2 movimientos de stock registrados.');

        // Existente: solo cambia lo que el archivo trae; costo y mayoreo se conservan.
        $pirelli->refresh();
        $this->assertSame(950.0, $pirelli->precio_publico);
        $this->assertSame(500.0, $pirelli->costo);
        $this->assertSame(700.0, $pirelli->precio_mayoreo);
        $this->assertSame(15, $this->stockDe($pirelli, $chalco));

        // Nuevo con todos los datos.
        $michelin = $this->producto('MICHELIN', '195/65R15');
        $this->assertSame('Llanta', $michelin->tipo);
        $this->assertSame(800.0, $michelin->precio_publico);
        $this->assertSame(600.0, $michelin->precio_mayoreo);
        $this->assertSame(400.0, $michelin->costo);
        $this->assertTrue($michelin->estado);
        $this->assertSame(5, $this->stockDe($michelin, $chalco));

        // Nuevo sin costo, mayoreo ni stock: queda en 0 y el stock no se toca.
        $goodyear = $this->producto('GOODYEAR', '215/60R16');
        $this->assertSame(0.0, $goodyear->costo);
        $this->assertSame(0.0, $goodyear->precio_mayoreo);
        $this->assertSame(0, $this->stockDe($goodyear, $chalco));

        // Todas las sucursales quedan con su registro de stock.
        $this->assertSame(0, $this->stockDe($goodyear, $valle));

        // Dos entradas, con el motivo de importación.
        $this->assertSame(2, MovimientoInventario::count());
        $this->assertSame(2, MovimientoInventario::where('tipo', 'entrada')->where('motivo', 'Importación de Excel')->count());

        // Se limpia la sesión y el archivo temporal.
        $this->assertNull(session('importacion_pendiente'));
        $this->assertSame([], Storage::disk('local')->allFiles('importaciones'));
    }

    /* ===================== CONFIRMAR: MODO REEMPLAZAR ===================== */

    public function test_modo_reemplazar_deja_el_stock_igual_al_archivo_y_registra_la_diferencia(): void
    {
        [$usuario, $chalco] = $this->escenario();
        $pirelli = $this->crearProducto();
        $michelin = $this->crearProducto(['marca' => 'MICHELIN', 'medida' => '195/65R15']);
        $hankook = $this->crearProducto(['marca' => 'HANKOOK', 'medida' => '185/65R15']);
        $this->darStock($pirelli, $chalco, 8);
        $this->darStock($michelin, $chalco, 2);
        $this->darStock($hankook, $chalco, 7);

        $this->previsualizar($usuario, $this->archivoCsv([
            'MARCA,MEDIDA,STOCK',
            'PIRELLI,205/55R16,5',   // baja de 8 a 5
            'MICHELIN,195/65R15,6',  // sube de 2 a 6
            'HANKOOK,185/65R15,7',   // igual: no se registra nada
        ]), ['modo' => 'reemplazar']);

        $this->confirmar($usuario)->assertRedirect(route('inventario.index'));

        $this->assertSame(5, $this->stockDe($pirelli, $chalco));
        $this->assertSame(6, $this->stockDe($michelin, $chalco));
        $this->assertSame(7, $this->stockDe($hankook, $chalco));
        $this->assertSame(2, MovimientoInventario::count());

        $salida = MovimientoInventario::where('tipo', 'salida')->firstOrFail();
        $this->assertSame($pirelli->id, (int) $salida->producto_id);
        $this->assertSame(3, (int) $salida->cantidad);
        $this->assertSame('Ajuste por importación', $salida->motivo);

        $entrada = MovimientoInventario::where('tipo', 'entrada')->firstOrFail();
        $this->assertSame($michelin->id, (int) $entrada->producto_id);
        $this->assertSame(4, (int) $entrada->cantidad);
        $this->assertSame('Ajuste por importación', $entrada->motivo);
    }

    /* ===================== FILAS CON PROBLEMAS ===================== */

    public function test_las_filas_con_error_se_saltan_y_las_buenas_si_se_importan(): void
    {
        [$usuario, $chalco] = $this->escenario();

        $this->previsualizar($usuario, $this->csv([
            ['PIRELLI', '205/55R16', '900', '700', '500', '10'],  // buena
            ['', '175/70R13', '500', '400', '300', '1'],          // falta la marca
            ['HANKOOK', '185/65R15', '0', '', '', '3'],           // precio en 0
            ['MICHELIN', '195/65R15', '800', '600', '400', '2.5'], // stock con decimales
            ['NUEVO', '100/10/10', '', '', '', '4'],              // nuevo sin precio público
            ['PIRELLI', '205/55R16', '900', '700', '500', '10'],  // repetida
        ]));

        $this->confirmar($usuario)
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('success', fn ($mensaje) => str_contains($mensaje, '5 filas omitidas'));

        $this->assertSame(1, Producto::count());
        $this->assertSame(10, $this->stockDe($this->producto('PIRELLI', '205/55R16'), $chalco));
    }

    public function test_un_stock_muy_alto_solo_avisa_y_se_importa_igual(): void
    {
        [$usuario, $chalco] = $this->escenario();

        $this->previsualizar($usuario, $this->csv([['PIRELLI', '205/55R16', '900', '700', '500', '10000']]))
            ->assertViewHas('vista', fn ($vista) => $vista['con_aviso'] === 1 && $vista['con_error'] === 0);

        $this->confirmar($usuario);

        $this->assertSame(10000, $this->stockDe($this->producto('PIRELLI', '205/55R16'), $chalco));
    }

    /* ===================== CANCELAR Y CONFIRMACIONES INVALIDAS ===================== */

    public function test_cancelar_borra_el_archivo_temporal_y_no_importa_nada(): void
    {
        [$usuario] = $this->escenario();
        $this->previsualizar($usuario, $this->csv([['PIRELLI', '205/55R16', '900', '700', '500', '10']]));

        $this->assertCount(1, Storage::disk('local')->allFiles('importaciones'));

        $this->actingAs($usuario)
            ->post(route('inventario.procesar'), ['accion' => 'cancelar'])
            ->assertRedirect(route('inventario.importar'));

        $this->assertSame([], Storage::disk('local')->allFiles('importaciones'));
        $this->assertNull(session('importacion_pendiente'));
        $this->nadaImportado();
    }

    public function test_confirmar_sin_vista_previa_da_error(): void
    {
        [$usuario] = $this->escenario();

        $this->confirmar($usuario)
            ->assertRedirect(route('inventario.importar'))
            ->assertSessionHas('error', 'La vista previa ya no está disponible. Sube el archivo otra vez.');

        $this->nadaImportado();
    }

    public function test_otro_usuario_no_puede_confirmar_la_vista_previa_ajena(): void
    {
        [$usuario, $chalco] = $this->escenario();
        $otro = $this->usuarioDeImportacion($chalco);
        $this->previsualizar($usuario, $this->csv([['PIRELLI', '205/55R16', '900', '700', '500', '10']]));

        $this->confirmar($otro)
            ->assertRedirect(route('inventario.importar'))
            ->assertSessionHas('error', 'La vista previa ya no está disponible. Sube el archivo otra vez.');

        $this->nadaImportado();
    }

    /* ===================== EXCEL REAL (.xlsx) ===================== */

    public function test_tambien_se_lee_un_archivo_xlsx_real(): void
    {
        [$usuario, $chalco] = $this->escenario();

        $ruta = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'inv_' . uniqid() . '.xlsx';
        (new FastExcel(collect([
            ['MARCA' => 'PIRELLI', 'MEDIDA' => '205/55R16', 'PRECIO PÚBLICO' => 900, 'STOCK' => 4],
        ])))->export($ruta);

        try {
            $archivo = new UploadedFile($ruta, 'inventario.xlsx', null, null, true);

            $this->previsualizar($usuario, $archivo)
                ->assertViewHas('vista', fn ($vista) => $vista['total'] === 1 && $vista['ok'] === 1);

            $this->confirmar($usuario)->assertRedirect(route('inventario.index'));

            $this->assertSame(4, $this->stockDe($this->producto('PIRELLI', '205/55R16'), $chalco));
        } finally {
            @unlink($ruta);
        }
    }
}