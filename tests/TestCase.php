<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seguro: antes de que arranque cualquier prueba, revisa que la base de
     * datos sea una base de pruebas. Si no lo es, se detiene sin borrar nada.
     */
    protected function refreshApplication()
    {
        parent::refreshApplication();

        $conexion = config('database.default');
        $base = (string) config("database.connections.{$conexion}.database");

        if (! str_ends_with($base, '_test')) {
            throw new RuntimeException(
                "Pruebas detenidas: la base de datos '{$base}' no es una base de pruebas."
            );
        }
    }
}