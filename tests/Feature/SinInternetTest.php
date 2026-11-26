<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El sistema debe funcionar aunque la tienda no tenga internet:
 * las letras, Alpine, el calendario, las gráficas y el QR viven dentro del proyecto.
 */
class SinInternetTest extends TestCase
{
    public function test_las_pantallas_no_cargan_nada_de_internet(): void
    {
        $permitidos = ['www.w3.org', 'wa.me'];  // w3.org es solo un identificador de SVG; wa.me es el enlace del QR
        $encontrados = [];

        foreach (File::allFiles(resource_path('views')) as $archivo) {
            preg_match_all('#https?://([a-z0-9.\-]+)#i', $archivo->getContents(), $coincidencias);

            foreach ($coincidencias[1] as $host) {
                if (! in_array(strtolower($host), $permitidos, true)) {
                    $encontrados[] = $archivo->getRelativePathname() . ' -> ' . $host;
                }
            }
        }

        $this->assertSame([], $encontrados, "Estas pantallas cargan algo de internet:\n" . implode("\n", $encontrados));
    }

    public function test_las_librerias_estan_en_el_proyecto(): void
    {
        $paquetes = json_decode(file_get_contents(base_path('package.json')), true);
        $instalados = array_merge($paquetes['dependencies'] ?? [], $paquetes['devDependencies'] ?? []);

        foreach (['alpinejs', 'chart.js', 'flatpickr', 'qrcode',
                  '@fontsource/inter', '@fontsource/oswald', '@fontsource/archivo-narrow', '@fontsource/jetbrains-mono'] as $paquete) {
            $this->assertArrayHasKey($paquete, $instalados, "Falta instalar {$paquete} con npm.");
        }
    }

    public function test_los_archivos_de_vite_existen_y_estan_registrados(): void
    {
        $vite = file_get_contents(base_path('vite.config.js'));

        foreach (['resources/css/fuentes.css', 'resources/css/app.css', 'resources/js/app.js',
                  'resources/js/reportes.js', 'resources/js/ticket.js'] as $entrada) {
            $this->assertFileExists(base_path($entrada));
            $this->assertStringContainsString("'{$entrada}'", $vite);
        }
    }

    public function test_app_js_arranca_alpine_y_el_calendario_en_espanol(): void
    {
        $app = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString("from 'alpinejs'", $app);
        $this->assertStringContainsString('Alpine.start()', $app);
        $this->assertStringContainsString("from 'flatpickr'", $app);
        $this->assertStringContainsString("l10n/es.js", $app);
        $this->assertStringContainsString('window.flatpickr', $app);
    }

    public function test_cada_pantalla_pide_sus_archivos_locales(): void
    {
        $vista = fn (string $ruta) => file_get_contents(resource_path("views/{$ruta}.blade.php"));

        $this->assertStringContainsString('resources/css/fuentes.css', $vista('layouts/app'));
        $this->assertStringContainsString('resources/css/fuentes.css', $vista('layouts/guest'));
        $this->assertStringContainsString("resources/js/reportes.js", $vista('reportes/index'));
        $this->assertStringContainsString("resources/js/ticket.js", $vista('ventas/ticket'));
        $this->assertStringContainsString('window.dibujarQr', $vista('ventas/ticket'));
    }
}
