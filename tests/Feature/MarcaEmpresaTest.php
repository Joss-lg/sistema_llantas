<?php

namespace Tests\Feature;

use App\Exports\InventarioExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Marca de la empresa: el nombre y el logo salen de config/empresa.php
 * y ya no queda nada del cliente anterior.
 */
class MarcaEmpresaTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['empresa.nombre' => 'OllinLlantas']);
    }

    public function test_el_login_muestra_el_nombre_y_el_logo_de_la_empresa(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>OllinLlantas</title>', false)
            ->assertSee('img/logo-ollintem-blanco.png', false)
            ->assertSee('favicon.ico', false)
            ->assertDontSee('fondo-local')
            ->assertDontSee('logo-llantas');
    }

    public function test_el_nombre_de_la_empresa_se_cambia_en_un_solo_lugar(): void
    {
        config(['empresa.nombre' => 'Otra Empresa']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>Otra Empresa</title>', false);
    }

    public function test_el_menu_lateral_usa_el_nombre_y_el_simbolo_de_la_empresa(): void
    {
        $chalco = $this->crearSucursal('Principal');
        $cajero = $this->cajeroDeVentas($chalco);
        $this->abrirTurno($cajero);

        $this->actingAs($cajero)
            ->get(route('ventas.index'))
            ->assertOk()
            ->assertSee('OllinLlantas')
            ->assertSee('img/icono-ollintem.png', false)
            ->assertDontSee('logo-llantas');
    }

    public function test_el_ticket_sin_contacto_no_muestra_telefono_ni_qr_ni_datos_del_cliente_anterior(): void
    {
        config(['empresa.telefono' => null, 'empresa.whatsapp' => null]);

        $sucursal = $this->crearSucursal('Principal');
        $cajero = $this->cajeroDeVentas($sucursal);
        $venta = $this->crearVenta($cajero, $sucursal);

        $this->actingAs($cajero)
            ->get(route('ventas.ticket', $venta->id))
            ->assertOk()
            ->assertSee('OLLINLLANTAS')
            ->assertSee('img/logo-ollintem.png', false)
            ->assertDontSee('Tel:')
            ->assertDontSee('id="qrcode"', false)
            ->assertDontSee('qrcode.min.js', false)
            ->assertDontSee('wa.me')
            ->assertDontSee('5535690077')
            ->assertDontSee('L-E-C')
            ->assertDontSee('ECONÓMICAS');
    }

    public function test_el_ticket_con_contacto_muestra_telefono_y_qr_con_su_numero(): void
    {
        config(['empresa.telefono' => '55 1234 5678', 'empresa.whatsapp' => '+52 55 1234 5678']);

        $sucursal = $this->crearSucursal('Principal');
        $cajero = $this->cajeroDeVentas($sucursal);
        $venta = $this->crearVenta($cajero, $sucursal);

        $this->actingAs($cajero)
            ->get(route('ventas.ticket', $venta->id))
            ->assertOk()
            ->assertSee('Tel: 55 1234 5678')
            ->assertSee('https://wa.me/525512345678?text=', false)
            ->assertSee('id="qrcode"', false);
    }

    public function test_el_pdf_de_inventario_lleva_el_nombre_de_la_empresa(): void
    {
        $html = view('inventario.pdf', [
            'productos'      => collect(),
            'fecha'          => '01/01/2026 10:00',
            'nombreSucursal' => 'Todas las Sucursales',
        ])->render();

        $this->assertStringContainsString('OLLINLLANTAS', $html);
        $this->assertStringContainsString('Generado por OllinLlantas', $html);
        $this->assertStringNotContainsString('CHALCO', $html);
    }

    public function test_el_excel_de_inventario_lleva_el_nombre_de_la_empresa(): void
    {
        $archivo = tempnam(sys_get_temp_dir(), 'inv') . '.xlsx';
        file_put_contents($archivo, Excel::raw(new InventarioExport(collect(), 'Todas las Sucursales'), \Maatwebsite\Excel\Excel::XLSX));

        $titulo = IOFactory::load($archivo)->getActiveSheet()->getCell('A1')->getValue();
        @unlink($archivo);

        $this->assertSame('OLLINLLANTAS', $titulo);
    }

    public function test_ya_no_queda_la_marca_del_cliente_anterior_en_el_codigo_ni_en_las_imagenes(): void
    {
        $prohibidas = ['Económicas', 'ECONÓMICAS', 'Economicas', 'ECONOMICAS', 'logo-llantas', 'fondo-local', '5535690077', 'L-E-C'];

        foreach (['app', 'resources', 'config', 'routes'] as $carpeta) {
            $archivos = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($carpeta), \FilesystemIterator::SKIP_DOTS));

            foreach ($archivos as $archivo) {
                if (! $archivo->isFile() || ! preg_match('/\.(php|js|css|json)$/', $archivo->getFilename())) {
                    continue;
                }

                $contenido = file_get_contents($archivo->getPathname());

                foreach ($prohibidas as $texto) {
                    $this->assertStringNotContainsString($texto, $contenido, $archivo->getPathname() . ' todavía tiene: ' . $texto);
                }
            }
        }

        $this->assertFileDoesNotExist(public_path('img/logo-llantas.webp'));
        $this->assertFileDoesNotExist(public_path('img/fondo-local.webp'));
        $this->assertFileExists(public_path('img/logo-ollintem.png'));
        $this->assertFileExists(public_path('img/icono-ollintem.png'));
    }
}
