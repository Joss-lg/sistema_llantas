<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Los mensajes de error de los formularios deben salir en español y con
 * nombres de campo entendibles (nunca "validation.required" ni en inglés).
 */
class IdiomaEspanolTest extends TestCase
{
    public function test_el_ejemplo_de_configuracion_viene_en_espanol(): void
    {
        $ejemplo = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString("APP_LOCALE=es\n", $ejemplo);
        $this->assertStringContainsString("APP_FALLBACK_LOCALE=es\n", $ejemplo);
    }

    public function test_existen_los_archivos_de_textos_en_espanol(): void
    {
        foreach (['validation', 'auth', 'pagination', 'passwords'] as $archivo) {
            $this->assertFileExists(base_path("lang/es/{$archivo}.php"));
        }
    }

    public function test_los_errores_de_validacion_salen_en_espanol(): void
    {
        app()->setLocale('es');

        $v = Validator::make(
            ['email' => 'no-es-correo', 'nombre' => ''],
            [
                'nombre' => 'required',
                'email' => 'email',
                'precio_publico' => 'required|numeric',
            ]
        );

        $errores = $v->errors();

        $this->assertSame('El campo nombre es obligatorio.', $errores->first('nombre'));
        $this->assertSame('El campo correo electrónico debe ser un correo electrónico válido.', $errores->first('email'));
        $this->assertSame('El campo precio al público es obligatorio.', $errores->first('precio_publico'));

        foreach ($errores->all() as $mensaje) {
            $this->assertStringNotContainsString('validation.', $mensaje);
        }
    }

    public function test_los_campos_del_sistema_tienen_nombre_en_espanol(): void
    {
        $atributos = require base_path('lang/es/validation.php');

        foreach (['precio_publico', 'precio_mayoreo', 'costo_unitario', 'sucursal_id', 'rfc', 'password'] as $campo) {
            $this->assertArrayHasKey($campo, $atributos['attributes']);
        }
    }
}
