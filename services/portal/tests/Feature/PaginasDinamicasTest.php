<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\AlertasController;
use Tests\TestCase;

class PaginasDinamicasTest extends TestCase
{
    /**
     * Comprueba que la página de rankings devuelve 200 OK y cero cookies.
     */
    public function test_pagina_rankings_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/rankings');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Rankings y Actividad de la Red', false);
        $response->assertSee('Distribución de Tráfico', false);
        $response->assertSee('Top Nodos por Uso del Espectro', false);
        $response->assertSee('Catálogo de Rankings de la Red', false);
    }

    /**
     * Comprueba los diferentes periodos de la página de rankings.
     */
    public function test_pagina_rankings_admite_periodos(): void
    {
        $response = $this->get('/rankings?period=week&which=current');
        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');

        $response14d = $this->get('/rankings?period=14d&which=current&ambito=espana');
        $response14d->assertStatus(200);
        $response14d->assertHeaderMissing('Set-Cookie');

        $responseAndalucia = $this->get('/rankings?period=month&which=current&ambito=andalucia');
        $responseAndalucia->assertStatus(200);
        $responseAndalucia->assertHeaderMissing('Set-Cookie');
    }

    /**
     * Comprueba que la página de alertas devuelve 200 OK y cero cookies.
     */
    public function test_pagina_alertas_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/alertas');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Alertas de la Malla', false);
        $response->assertSee('Catálogo de Anomalías Monitorizadas', false);
        $response->assertSee('Repetidor en movimiento físico', false);
        $response->assertSee('Concentración redundante de routers', false);
        $response->assertSee('Enlace RF asimétrico severo', false);
        $response->assertSee('Seguridad y alteración de clave pública', false);
        $response->assertSee('Pasarela sin tráfico LoRa', false);
        $response->assertSee('Uso de tiempo de aire excesivo', false);
        $response->assertSee('Batería y Energía', false);
        $response->assertSee('Spam e Inundación', false);
    }

    /**
     * Comprueba que filtrar por temática de problema devuelve 200, cero cookies y adapta el catálogo.
     */
    public function test_pagina_alertas_con_filtro_problema_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/alertas?problema=bateria');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Batería y Energía', false);
        $response->assertSee('Filtros activos:', false);
        $response->assertSee('Limpiar filtros', false);
        // Reglas de batería visibles
        $response->assertSee('Batería baja', false);
        $response->assertSee('Batería insuficiente al anochecer', false);
        // Reglas de otra temática ocultas en el catálogo filtrado
        $response->assertDontSee('Inundación de mensajes de texto', false);
    }

    /**
     * Comprueba que la combinación de filtros (estado, riesgo y temática) opera y muestra la barra activa.
     */
    public function test_pagina_alertas_con_filtros_combinados(): void
    {
        $response = $this->get('/alertas?estado=abierta&riesgo=alto&problema=posicion');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Filtros activos:', false);
        $response->assertSee('Alto', false);
        $response->assertSee('Posición y GPS', false);
        $response->assertSee('Repetidor en movimiento físico', false);
        $response->assertDontSee('Batería insuficiente al anochecer', false);
    }

    /**
     * Comprueba que un valor de problema no reconocido no rompe la vista ni produce error.
     */
    public function test_pagina_alertas_con_problema_desconocido_ignora_filtro(): void
    {
        $response = $this->get('/alertas?problema=inexistente_xyz');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Alertas de la Malla', false);
    }

    /**
     * Comprueba que la selección múltiple de temáticas filtra simultáneamente varias categorías.
     */
    public function test_pagina_alertas_con_multiples_tematicas(): void
    {
        $response = $this->get('/alertas?problemas=bateria,posicion');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Batería baja', false);
        $response->assertSee('Repetidor en movimiento físico', false);
        $response->assertDontSee('Inundación de mensajes de texto', false);
    }

    /**
     * Comprueba la landing page del buscador de "Revisa tu nodo".
     */
    public function test_revisa_tu_nodo_buscador_devuelve_200(): void
    {
        $response = $this->get('/revisa-tu-nodo');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Revisa tu nodo', false);
        $response->assertSee('name="buscar"', false);
        $response->assertSee('grupo-botones-compartir', false);
        $response->assertSee('btn-compartir-whatsapp', false);
    }

    /**
     * Comprueba que buscar un ID directo redirige a la URL canónica.
     */
    public function test_revisa_tu_nodo_redirige_si_es_un_id(): void
    {
        $response = $this->get('/revisa-tu-nodo?buscar=!aabb1122');

        $response->assertRedirect('/revisa-tu-nodo/!aabb1122');
    }

    /**
     * Comprueba el informe de un nodo no encontrado.
     */
    public function test_revisa_tu_nodo_muestra_aviso_si_no_existe(): void
    {
        $response = $this->get('/revisa-tu-nodo/!99999999');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Nodo no localizado', false);
        $response->assertSee('!99999999', false);
    }

    /**
     * Comprueba que una alerta inexistente devuelve 404.
     */
    public function test_pagina_alerta_inexistente_devuelve_404(): void
    {
        $response = $this->get('/alertas/01NONEXISTENTID00000000000');

        $response->assertStatus(404);
    }

    /**
     * Comprueba que el normalizador de alertas enriquece con metadatos explicativos y didácticos.
     */
    public function test_normalizador_alertas_enriquece_metadatos_explicativos(): void
    {
        $alertaCruda = [
            'id' => '01M4DHF92D5PDDQ2HMXX9H5X1T',
            'regla' => 'hops-high',
            'riesgo' => 'bajo',
            'tipo' => 'clientes',
            'mensaje' => 'GAT5 usa 6 saltos (recomendado 3, máximo 5)',
            'nodo' => '!50dd2a87',
            'nodo_info' => json_encode(['corto' => 'GAT5', 'largo' => 'CC SERRILLA', 'rol' => 'CLIENT', 'provincia' => 'FUERA']),
            'datos' => json_encode(['hop_start' => 6, 'recomendado' => 3, 'maximo_valido' => 5]),
            'estado' => 'resuelta',
            'abierta_en' => '2026-10-08 12:38:14',
            'resuelta_en' => '2026-10-08 12:43:00',
            'reaperturas' => 1,
        ];

        $normalizada = AlertasController::normalizarAlerta($alertaCruda);

        $this->assertSame('Saltos excesivos (hop_start)', $normalizada['regla_nombre']);
        $this->assertSame('🔀', $normalizada['regla_icono']);
        $this->assertStringContainsString('En una red mallada como Meshtastic', $normalizada['regla_por_que']);
        $this->assertStringContainsString('Hop Limit', $normalizada['regla_como_solucionar']);
        $this->assertSame('GAT5', $normalizada['nodo_nombre_corto']);
        $this->assertSame('CC SERRILLA', $normalizada['nodo_nombre_largo']);
        $this->assertFalse($normalizada['nodo_es_global']);
        $this->assertSame(6, $normalizada['datos']['hop_start']);
    }

    /**
     * Comprueba que la vista de detalle de alerta renderiza las marcas temporales con time.fecha-local.
     */
    public function test_alerta_show_renderiza_timestamps_con_time_fecha_local(): void
    {
        $alerta = [
            'id' => '01M4DHF92D5PDDQ2HMXX9H5X1T',
            'regla' => 'infra-silent',
            'riesgo' => 'alto',
            'tipo' => 'infraestructura',
            'mensaje' => 'Nodo repetidor sin actividad',
            'nodo_codigo' => '!50dd2a87',
            'nodo_nombre_corto' => 'RPT1',
            'nodo_nombre_largo' => 'Repetidor 1',
            'nodo_rol' => 'ROUTER',
            'nodo_provincia' => 'Cadiz',
            'nodo_es_global' => false,
            'regla_nombre' => 'Repetidor silencioso',
            'regla_icono' => '🔇',
            'regla_por_que' => 'Explicación del fallo',
            'regla_como_solucionar' => 'Instrucciones para solucionar',
            'datos' => [
                'ultimo_visto' => '2026-10-08T13:20:41.023741+00:00',
                'minutos_silencio' => 120,
            ],
            'estado' => 'resuelta',
            'abierta_en' => '2026-10-08T11:20:41+00:00',
            'actualizada_en' => '2026-10-08T12:00:00+00:00',
            'resuelta_en' => '2026-10-08T13:30:00+00:00',
            'reaperturas' => 0,
        ];

        $view = $this->view('alertas.show', [
            'alerta' => $alerta,
            'id' => $alerta['id'],
            'estadoKey' => 'resuelta',
            'textoEstado' => 'Resuelta',
            'chipTipo' => 'exito',
            'esGlobal' => false,
            'nodoCodigo' => $alerta['nodo_codigo'],
            'nodoNombre' => $alerta['nodo_nombre_corto'],
            'nodoLargo' => $alerta['nodo_nombre_largo'],
            'nodoRol' => $alerta['nodo_rol'],
            'nodoProvincia' => $alerta['nodo_provincia'],
            'datos' => $alerta['datos'],
            'reaperturas' => 0,
            'langQuery' => '',
        ]);

        $view->assertSee('class="fecha-local"', false);
        $view->assertSee('datetime="2026-10-08T13:20:41+00:00"', false);
        $view->assertSee('datetime="2026-10-08T13:30:00+00:00"', false);
    }

    /**
     * Comprueba que la página de alertas renderiza el selector con Andalucía, Toda España y las 8 provincias andaluzas exclusivamente.
     */
    public function test_pagina_alertas_muestra_selector_de_provincias(): void
    {
        $response = $this->get('/alertas');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('id="filtro-provincia-select"', false);
        $response->assertSee('🟢⚪🟢 Andalucía', false);
        $response->assertSee('🇪🇸 Toda España', false);
        $response->assertDontSee('Toda España (por defecto)', false);
        $response->assertDontSee('Todas las ubicaciones', false);
        $response->assertDontSee('Fuera de España', false);
        $response->assertDontSee('Resto de España', false);
        $response->assertSee('Provincias de Andalucía', false);
        $response->assertSee('Cádiz (ES-CA)', false);
        $response->assertSee('Sevilla (ES-SE)', false);
        $response->assertDontSee('Madrid (ES-M)', false);
        $response->assertDontSee('Barcelona (ES-B)', false);
    }

    /**
     * Comprueba que filtrar por el ámbito regional de Andalucía muestra el chip activo correspondiente.
     */
    public function test_pagina_alertas_con_filtro_andalucia(): void
    {
        $response = $this->get('/alertas?provincia=andalucia');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Filtros activos:', false);
        $response->assertSee('🟢⚪🟢 Andalucía', false);
    }

    /**
     * Comprueba que seleccionar una provincia específica muestra su chip en la barra de filtros activos.
     */
    public function test_pagina_alertas_con_provincia_especifica_muestra_chip_activo(): void
    {
        $response = $this->get('/alertas?provincia=ES-CA');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Filtros activos:', false);
        $response->assertSee('Cádiz (ES-CA)', false);
    }

    /**
     * Comprueba el catálogo y la normalización de códigos y nombres de provincias.
     */
    public function test_catalogo_y_normalizacion_de_provincias(): void
    {
        $this->assertCount(8, AlertasController::catalogoProvincias());
        $this->assertSame('espana', AlertasController::normalizarProvincia(null));
        $this->assertSame('espana', AlertasController::normalizarProvincia(''));
        $this->assertSame('andalucia', AlertasController::normalizarProvincia('andalucia'));
        $this->assertSame('espana', AlertasController::normalizarProvincia('espana'));
        $this->assertSame('ES-CA', AlertasController::normalizarProvincia('cadiz'));
        $this->assertSame('ES-SE', AlertasController::normalizarProvincia('ES-SE'));
        $this->assertSame('ES-J', AlertasController::normalizarProvincia('ES-JA'));

        $this->assertSame('Cádiz', AlertasController::nombreProvincia('ES-CA'));
        $this->assertSame('Jaén', AlertasController::nombreProvincia('ES-JA'));
        $this->assertSame('Fuera de España', AlertasController::nombreProvincia('FUERA'));
        $this->assertNull(AlertasController::nombreProvincia(null));

        $alertaNormalizada = AlertasController::normalizarAlerta([
            'id' => 'TEST01',
            'regla' => 'hops-high',
            'nodo' => '!11223344',
            'provincia' => 'ES-CA',
            'nodo_info' => ['provincia' => 'ES-CA'],
        ]);
        $this->assertSame('Cádiz', $alertaNormalizada['nodo_provincia_nombre']);
        $this->assertSame('ES-CA', $alertaNormalizada['nodo_provincia']);
    }

    /**
     * Comprueba que el endpoint de búsqueda reactiva en revisa-tu-nodo responde JSON adecuadamente.
     */
    public function test_revisa_nodo_buscar_endpoint(): void
    {
        // Con menos de 2 caracteres responde array vacío
        $responseVacio = $this->get('/revisa-tu-nodo/buscar?q=a');
        $responseVacio->assertStatus(200);
        $responseVacio->assertJson([]);

        // Con query suficiente responde JSON con nodos coincidentes
        $response = $this->get('/revisa-tu-nodo/buscar?q=al01');
        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $items = $response->json();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $this->assertSame('!91a2b3c4', $items[0]['id']);
        $this->assertSame('AL01', $items[0]['short_name']);

        // Búsqueda por formulario en la vista HTML
        $responseHtml = $this->get('/revisa-tu-nodo?buscar=al01');
        $responseHtml->assertStatus(200);
        $responseHtml->assertSee('AL01');
        $responseHtml->assertSee('!91a2b3c4');
        $responseHtml->assertSee('Resultados para &quot;al01&quot; (1)', false);
    }
}
