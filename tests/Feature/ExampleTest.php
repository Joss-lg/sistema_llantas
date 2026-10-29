<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Sin sesión iniciada, la página principal manda al login.
     */
    public function test_inicio_sin_sesion_redirige_al_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * La página de login abre correctamente.
     */
    public function test_la_pagina_de_login_abre(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}