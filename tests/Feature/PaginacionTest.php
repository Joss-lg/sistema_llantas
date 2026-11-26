<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Concerns\CreaUsuarios;
use Tests\TestCase;

class PaginacionTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios;

    /** Arma un paginador a mano para probar la vista sola. */
    private function paginador(int $total, int $porPagina, int $pagina): LengthAwarePaginator
    {
        $items = collect(range(1, min($porPagina, max(0, $total - ($pagina - 1) * $porPagina))));

        return new LengthAwarePaginator($items, $total, $porPagina, $pagina, [
            'path' => 'http://localhost/lista',
        ]);
    }

    /** Dibuja la vista igual que lo hace Laravel (el mismo paginador arma los elementos). */
    private function dibujar(LengthAwarePaginator $p): string
    {
        return $p->links('vendor.pagination.llantas')->toHtml();
    }

    public function test_es_la_vista_que_usa_laravel_por_defecto(): void
    {
        $html = $this->paginador(25, 10, 1)->links()->toHtml();

        $this->assertStringContainsString('Mostrando', $html);
        $this->assertStringNotContainsString('Showing', $html);
    }

    public function test_todo_el_texto_sale_en_espanol(): void
    {
        $html = $this->dibujar($this->paginador(45, 10, 2));

        foreach (['Mostrando', 'resultados', 'Anterior', 'Siguiente', 'Paginación', 'Ir a la página 3'] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }

        foreach (['Showing', 'results', 'Previous', 'Next', 'Pagination Navigation', 'Go to page'] as $texto) {
            $this->assertStringNotContainsString($texto, $html);
        }
    }

    public function test_el_resumen_dice_del_uno_al_diez_de_cuarenta_y_cinco(): void
    {
        $html = $this->dibujar($this->paginador(45, 10, 1));
        $texto = preg_replace('/\s+/', ' ', strip_tags($html));

        $this->assertStringContainsString('Mostrando 1 a 10 de 45 resultados', $texto);
    }

    public function test_la_ultima_pagina_muestra_solo_lo_que_queda(): void
    {
        $html = $this->dibujar($this->paginador(45, 10, 5));
        $texto = preg_replace('/\s+/', ' ', strip_tags($html));

        $this->assertStringContainsString('Mostrando 41 a 45 de 45 resultados', $texto);
    }

    public function test_en_la_primera_pagina_anterior_esta_apagado_y_siguiente_es_enlace(): void
    {
        $html = $this->dibujar($this->paginador(45, 10, 1));

        $this->assertStringNotContainsString('rel="prev"', $html);
        $this->assertStringContainsString('rel="next"', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
    }

    public function test_en_la_ultima_pagina_siguiente_esta_apagado_y_anterior_es_enlace(): void
    {
        $html = $this->dibujar($this->paginador(45, 10, 5));

        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringNotContainsString('rel="next"', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
    }

    public function test_la_pagina_actual_va_marcada_y_las_demas_son_enlaces(): void
    {
        $html = $this->dibujar($this->paginador(45, 10, 3));

        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertStringContainsString('aria-label="Ir a la página 2"', $html);
        $this->assertStringNotContainsString('aria-label="Ir a la página 3"', $html);
    }

    public function test_con_muchas_paginas_salen_puntos_suspensivos(): void
    {
        $html = $this->dibujar($this->paginador(500, 10, 25));

        $this->assertStringContainsString('...', $html);
    }

    public function test_con_una_sola_pagina_no_dibuja_nada(): void
    {
        $html = $this->dibujar($this->paginador(8, 10, 1));

        $this->assertSame('', trim($html));
    }

    public function test_en_pantallas_chicas_dice_en_que_pagina_va(): void
    {
        $html = $this->dibujar($this->paginador(45, 10, 2));
        $texto = preg_replace('/\s+/', ' ', strip_tags($html));

        $this->assertStringContainsString('Página 2 de 5', $texto);
    }

    public function test_los_enlaces_conservan_la_busqueda(): void
    {
        $p = $this->paginador(45, 10, 1)->appends(['busqueda' => 'pirelli']);
        $html = $this->dibujar($p);

        $this->assertStringContainsString('busqueda=pirelli', $html);
    }

    public function test_la_lista_de_clientes_sale_con_la_paginacion_en_espanol(): void
    {
        $this->withoutVite();

        $sucursal = $this->crearSucursal();
        $usuario = $this->crearUsuario('Administrador General', $sucursal);

        for ($i = 1; $i <= 12; $i++) {
            Cliente::create(['nombre' => 'Cliente ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT)]);
        }

        $respuesta = $this->actingAs($usuario)->get(route('clientes.index'));

        $respuesta->assertOk();
        $respuesta->assertSee('Mostrando');
        $respuesta->assertSee('Siguiente');
        $respuesta->assertDontSee('Showing');
        $respuesta->assertDontSee('Next');
    }
}
