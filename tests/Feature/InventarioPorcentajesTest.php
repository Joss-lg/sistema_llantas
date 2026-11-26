<?php

namespace Tests\Feature;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaUsuarios;
use Tests\Concerns\CreaVentas;
use Tests\TestCase;

/**
 * Los porcentajes de las 3 tarjetas del inventario (en stock, bajo mínimo, sin stock).
 * Un redondeo normal engaña: 1 de 589 salía "0%" y 588 de 589 salía "100%".
 */
class InventarioPorcentajesTest extends TestCase
{
    use RefreshDatabase, CreaUsuarios, CreaVentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function usuarioDeLista(Sucursal $sucursal): User
    {
        $usuario = $this->crearUsuario('Cajero', $sucursal);
        $this->darPermiso($usuario, 'inventario.index');

        return $usuario;
    }

    /** Crea $cantidad productos distintos, todos con la misma cantidad de stock en la sucursal. */
    private function productos(Sucursal $sucursal, int $cuantos, int $cantidad, string $marca): void
    {
        for ($i = 1; $i <= $cuantos; $i++) {
            $producto = $this->crearProducto(['marca' => $marca, 'medida' => 'M' . $i]);
            $this->darStock($producto, $sucursal, $cantidad);
        }
    }

    /** Los textos "X del total" que salen en las tarjetas, en orden: en stock, bajo mínimo, sin stock. */
    private function porcentajes(User $usuario): array
    {
        $html = $this->actingAs($usuario)->get(route('inventario.index'))->assertOk()->getContent();

        preg_match_all('/>\s*([^<>]*?) del total\s*</u', $html, $coincidencias);

        return array_map('trim', $coincidencias[1]);
    }

    public function test_un_producto_entre_muchos_dice_menos_de_1_y_el_resto_mas_de_99(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);

        $this->productos($chalco, 1, 10, 'CON');
        $this->productos($chalco, 149, 0, 'SIN');

        $this->assertSame(['menos de 1%', '0%', 'más de 99%'], $this->porcentajes($usuario));
    }

    public function test_los_porcentajes_normales_se_redondean(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);

        $this->productos($chalco, 1, 10, 'CON');
        $this->productos($chalco, 1, 2, 'BAJO');
        $this->productos($chalco, 2, 0, 'SIN');

        // 4 productos: 1 en stock (25%), 1 bajo mínimo (25%), 2 sin stock (50%)
        $this->assertSame(['25%', '25%', '50%'], $this->porcentajes($usuario));
    }

    public function test_todo_con_stock_es_100_y_los_demas_son_0(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);

        $this->productos($chalco, 3, 10, 'CON');

        $this->assertSame(['100%', '0%', '0%'], $this->porcentajes($usuario));
    }

    public function test_sin_productos_todo_dice_0(): void
    {
        $chalco = $this->crearSucursal('Chalco');
        $usuario = $this->usuarioDeLista($chalco);

        $this->assertSame(['0%', '0%', '0%'], $this->porcentajes($usuario));
    }
}
