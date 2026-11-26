<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;

/**
 * Texto del cambio contra el periodo anterior en el PDF de Reportes.
 * Tiene que decir lo mismo que la pantalla: "Sin cambio" cuando no hay cambio (antes decía "+0%").
 */
class ReportesPdfTextoTest extends TestCase
{
    /** Dibuja el PDF como página (sin convertirlo a PDF) con los porcentajes que se le pasen. */
    private function pagina(float $ingresos, float $ventas, float $clientes, string $etiqueta = 'vs. ayer'): string
    {
        $inicio = Carbon::parse('2026-11-10');

        $html = view('reportes.pdf', [
            'periodo'              => 'hoy',
            'fechaInicio'          => $inicio,
            'fechaFin'             => $inicio,
            'fechaInicioAnterior'  => $inicio->copy()->subDay(),
            'fechaFinAnterior'     => $inicio->copy()->subDay(),
            'etiquetaComparativa'  => $etiqueta,
            'sucursalFiltro'       => null,
            'sucursales'           => collect(),
            'fechaGeneracion'      => '10/11/2026 10:00',
            'ingresosTotales'      => 1000,
            'ventasRealizadas'     => 5,
            'clientesNuevos'       => 2,
            'porcentajeIngresos'   => $ingresos,
            'porcentajeVentas'     => $ventas,
            'porcentajeClientes'   => $clientes,
            'ventasPorDia'         => collect(),
            'ventasPorSucursal'    => collect(),
            'productosMasVendidos' => collect(),
        ])->render();

        // Sin etiquetas y con los espacios juntos, para comparar el texto como se lee
        return preg_replace('/\s+/', ' ', strip_tags($html));
    }

    public function test_sin_cambio_dice_sin_cambio_y_no_mas_cero_por_ciento(): void
    {
        $texto = $this->pagina(0, 0, 0);

        $this->assertSame(3, substr_count($texto, 'Sin cambio vs. ayer'));
        $this->assertStringNotContainsString('+0%', $texto);
        $this->assertStringNotContainsString('0% vs', $texto);
    }

    public function test_si_subio_dice_cuanto_mas(): void
    {
        $texto = $this->pagina(12.5, 100, 25.0);

        $this->assertStringContainsString('12.5% más vs. ayer', $texto);
        $this->assertStringContainsString('100% más vs. ayer', $texto);
        $this->assertStringContainsString('25% más vs. ayer', $texto);   // 25.0 sale como 25
        $this->assertStringNotContainsString('+', $texto);
    }

    public function test_si_bajo_dice_cuanto_menos_sin_signo_menos(): void
    {
        $texto = $this->pagina(-3.0, -42.5, 0);

        $this->assertStringContainsString('3% menos vs. ayer', $texto);
        $this->assertStringContainsString('42.5% menos vs. ayer', $texto);
        $this->assertStringNotContainsString('-3%', $texto);
        $this->assertStringContainsString('Sin cambio vs. ayer', $texto);
    }

    public function test_cada_tarjeta_usa_su_propio_porcentaje(): void
    {
        $texto = $this->pagina(10, -10, 0);

        $this->assertMatchesRegularExpression(
            '/Ingresos Totales.*10% más.*Ventas Realizadas.*10% menos.*Nuevos Clientes.*Sin cambio/u',
            $texto
        );
    }

    public function test_la_etiqueta_del_periodo_se_conserva(): void
    {
        $texto = $this->pagina(5, 5, 5, 'vs. mismos días de la semana anterior');

        $this->assertStringContainsString('5% más vs. mismos días de la semana anterior', $texto);
    }
}
