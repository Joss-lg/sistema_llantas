<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Role;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los datos iniciales del sistema: sin productos y con una sola sucursal.
 */
class SeedersLimpiosTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_datos_iniciales_no_traen_productos_ni_stock(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Producto::count());
        $this->assertSame(0, StockSucursal::count());
    }

    public function test_los_datos_iniciales_traen_una_sola_sucursal(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(['Sucursal Principal'], Sucursal::pluck('nombre')->all());
    }

    public function test_el_administrador_inicial_queda_en_esa_sucursal_con_su_rol(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@llantas.com')->firstOrFail();

        $this->assertSame('Sucursal Principal', $admin->sucursal->nombre);
        $this->assertSame(Role::where('nombre', 'Administrador General')->value('id'), $admin->rol_id);
        $this->assertTrue((bool) $admin->activo);
    }

    public function test_correr_los_datos_iniciales_dos_veces_no_duplica_nada(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, Sucursal::count());
        $this->assertSame(1, User::count());
    }

    public function test_el_seeder_de_productos_ya_no_existe(): void
    {
        $this->assertFalse(class_exists(\Database\Seeders\ProductoSeeder::class));
        $this->assertFileDoesNotExist(database_path('seeders/ProductoSeeder.php'));
    }
}
