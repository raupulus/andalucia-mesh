<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Configuración previa de cada prueba.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // En entornos de testing de Laravel/Symfony, el cliente emula por defecto Accept-Language: en-us.
        // Establecemos explícitamente español como idioma por defecto del portal para las pruebas base.
        $this->withHeader('Accept-Language', 'es-ES,es;q=0.9');
    }
}
