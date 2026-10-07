<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Servicios\ContenidoMarkdown;
use Tests\TestCase;

class ContenidoTest extends TestCase
{
    /**
     * Comprueba que todos los archivos Markdown de contenidos se pueden procesar
     * sin dejar variables sin interpolar y generando HTML válido.
     */
    public function test_todos_los_archivos_markdown_se_renderizan_sin_errores(): void
    {
        $servicio = app(ContenidoMarkdown::class);
        $directorio = resource_path('contenido');
        $archivos = glob($directorio . '/*.md');

        $this->assertNotEmpty($archivos, 'El directorio resources/contenido debe contener archivos Markdown.');

        foreach ($archivos as $ruta) {
            $nombreFichero = basename($ruta);

            $resultado = $servicio->render($nombreFichero);

            $this->assertNotEmpty($resultado['titulo'], "El título en {$nombreFichero} no puede estar vacío.");
            $this->assertNotEmpty($resultado['descripcion'], "La descripción en {$nombreFichero} no puede estar vacía.");
            $this->assertNotEmpty($resultado['h1'], "El H1 en {$nombreFichero} no puede estar vacío.");
            $this->assertNotEmpty($resultado['html'], "El HTML generado en {$nombreFichero} no puede estar vacío.");

            // Asegurar que no quedan variables entre corchetes {VARIABLE}
            $this->assertDoesNotMatchRegularExpression(
                '/\{[A-Z0-9_]+\}/',
                $resultado['html'],
                "Existen variables no interpoladas en el HTML generado de {$nombreFichero}"
            );
        }
    }
}
