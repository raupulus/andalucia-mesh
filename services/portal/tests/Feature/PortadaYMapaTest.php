<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PortadaYMapaTest extends TestCase
{
    /**
     * Comprueba que la portada principal devuelve código 200 y cero cookies.
     */
    public function test_portada_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
    }

    /**
     * Comprueba que las 3 tarjetas de servicio se muestran en el orden estricto de especificación.
     */
    public function test_tres_tarjetas_en_orden_estricto(): void
    {
        $response = $this->get('/');

        $contenido = $response->getContent();
        $this->assertNotFalse($contenido);

        $posMeshView = strpos($contenido, 'MeshView');
        $posPotatoMesh = strpos($contenido, 'PotatoMesh');
        $posRankings = strpos($contenido, 'Rankings y Actividad');

        $this->assertNotFalse($posMeshView, 'Tarjeta MeshView debe existir');
        $this->assertNotFalse($posPotatoMesh, 'Tarjeta PotatoMesh debe existir');
        $this->assertNotFalse($posRankings, 'Tarjeta Rankings debe existir');

        $this->assertTrue($posMeshView < $posPotatoMesh, 'MeshView debe preceder a PotatoMesh');
        $this->assertTrue($posPotatoMesh < $posRankings, 'PotatoMesh debe preceder a Rankings');

        // Verificar que MeshView y PotatoMesh abren en nueva pestaña
        $this->assertMatchesRegularExpression('/href="[^"]*meshview[^"]*"[^>]*target="_blank"/i', $contenido);
        $this->assertMatchesRegularExpression('/href="[^"]*potato[^"]*"[^>]*target="_blank"/i', $contenido);
        $this->assertDoesNotMatchRegularExpression('/href="[^"]*rankings"[^>]*target="_blank"/i', $contenido);
    }

    /**
     * Comprueba que el mapa SVG vectorial contiene las 8 provincias y la burbuja destacada de Cádiz.
     */
    public function test_mapa_svg_contiene_todas_las_provincias_y_cadiz_destacado(): void
    {
        $response = $this->get('/');

        $response->assertSee('class="mapa-andalucia-svg"', false);

        $provincias = ['ES-AL', 'ES-CA', 'ES-CO', 'ES-GR', 'ES-H', 'ES-J', 'ES-MA', 'ES-SE'];
        foreach ($provincias as $p) {
            $response->assertSee("id=\"prov-{$p}\"", false);
        }

        // Cádiz destacado
        $response->assertSee('burbuja-cadiz', false);
    }

    /**
     * Comprueba que la tabla accesible y el selector de ventana existen con las 6 opciones.
     */
    public function test_tabla_accesible_y_selector_ventana_presentes(): void
    {
        $response = $this->get('/');

        $response->assertSee('Tabla de nodos y carga por provincia', false);
        $response->assertSee('data-ventana="30m"', false);
        $response->assertSee('data-ventana="1h"', false);
        $response->assertSee('data-ventana="6h"', false);
        $response->assertSee('data-ventana="12h"', false);
        $response->assertSee('data-ventana="1d"', false);
        $response->assertSee('data-ventana="7d"', false);
        $response->assertSee('Últimos 30 minutos', false);
        $response->assertSee('NO es un servicio de emergencias', false);
        $response->assertSee(config('autoria.nick'));
    }

    /**
     * Comprueba el soporte de la query string de ventana temporal (?ventana=24h y ?ventana=1h).
     */
    public function test_ventana_modifica_label_y_selector(): void
    {
        $response24h = $this->get('/?ventana=24h');
        $response24h->assertStatus(200);
        $response24h->assertSee('Últimas 24 horas', false);

        $response1h = $this->get('/?ventana=1h');
        $response1h->assertStatus(200);
        $response1h->assertSee('Última hora', false);

        $response7d = $this->get('/?ventana=7d');
        $response7d->assertStatus(200);
        $response7d->assertSee('Últimos 7 días', false);
    }

    /**
     * Comprueba que las filas de la tabla accesible tienen chips con estado y clases semánticas correctas.
     */
    public function test_tabla_accesible_muestra_chips_con_estado_y_colores_correctos(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Cada provincia tiene su fila con id fila-prov-ES-*
        $response->assertSee('id="fila-prov-ES-CA"', false);
        $response->assertSee('class="col-estado"', false);

        // Los chips deben renderizarse con alguna de las clases de estado (correcto, aviso, critico o neutro)
        $contenido = (string) $response->getContent();
        $this->assertMatchesRegularExpression('/chip-(correcto|aviso|critico|neutro)/', $contenido);
        // Los estados textuales posibles en la tabla
        $this->assertMatchesRegularExpression('/(Holgado|Cargado|Saturado|Sin datos)/', $contenido);
    }

    /**
     * Comprueba que el mapa incluye el bloque JSON con datos iniciales para la interactividad de JS.
     */
    public function test_mapa_incluye_script_con_datos_iniciales_json(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="mapa-datos-iniciales"', false);

        $contenido = (string) $response->getContent();
        $this->assertMatchesRegularExpression('/<script type="application\/json" id="mapa-datos-iniciales">\s*\{.*"provinces".*\}\s*<\/script>/s', $contenido);
    }

    /**
     * Comprueba que las tarjetas destacadas de sugerencias, revisa tu nodo y quién está detrás aparecen correctamente.
     */
    public function test_tarjetas_destacadas_y_bloque_autoria_en_portada(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Tarjeta horizontal de sugerencias
        $response->assertSee('/img/sugerencias-banner.webp', false);
        $response->assertSee('¡Envía tu Sugerencia!', false);
        $response->assertSee('/sugerencias', false);

        // Tarjeta horizontal de revisa tu nodo
        $response->assertSee('/img/revisa-nodo-banner.webp', false);
        $response->assertSee('Revisa la configuración de tu nodo', false);
        $response->assertSee('/revisa-tu-nodo', false);

        // Bloque Quién está detrás con logotipo oficial de raupulus.dev
        $response->assertSee('/img/raupulus-logo.webp', false);
        $response->assertSee('https://raupulus.dev', false);
    }

    /**
     * Comprueba que la cabecera muestra el texto MQTT y el desplegable Extras.
     */
    public function test_cabecera_muestra_mqtt_y_menu_extras(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // MQTT en lugar de Conecta tu gateway en la navegación
        $response->assertSee('MQTT', false);
        $response->assertSee('/conecta-tu-gateway', false);

        // Menú Extras con Revisa tu nodo, Sugerencias y API/Websockets
        $response->assertSee('Extras', false);
        $response->assertSee('btn-extras-nav', false);
        $response->assertSee('dropdown-extras-nav', false);
        $response->assertSee('API/Websockets', false);
        $response->assertSee('/api', false);
    }

    /**
     * Comprueba que el pie de página incluye la tarjeta rectangular de código fuente con enlaces a GitLab y GitHub.
     */
    public function test_pie_muestra_tarjeta_repositorios_codigo_fuente(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Enlace de descarga de QR
        $response->assertSee('/qr.svg', false);

        // Tarjeta rectangular de código fuente
        $response->assertSee('tarjeta-repo-footer', false);
        $response->assertSee('Código Fuente', false);
        $response->assertSee('https://gitlab.com/raupulus/andalucia-mesh', false);
        $response->assertSee('https://github.com/raupulus/andalucia-mesh', false);
        $response->assertSee('GitLab', false);
        $response->assertSee('GitHub', false);
        $response->assertSee('principal', false);
        $response->assertSee('mirror', false);
    }

    /**
     * Comprueba que el modo oscuro está configurado por defecto en la etiqueta HTML.
     */
    public function test_modo_oscuro_por_defecto_en_html(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('data-theme="dark"', false);
    }

    /**
     * Comprueba que el botón para el configurador automático en portada se presenta destacado y verde.
     */
    public function test_boton_configurador_automatico_destacado_en_portada(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('acciones-configura-nodo', false);
        $response->assertSee('btn-configurador-destacado', false);
        $response->assertSee('/configurador', false);
        $response->assertSee(__('portal.home.btn_auto_configurator'), false);
    }

    /**
     * Comprueba que las tablas de configuración y gateway en portada tienen la clase tarjeta-tabla-andalucia.
     */
    public function test_tablas_configuracion_tienen_clase_tarjeta_tabla_andalucia(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = (string) $response->getContent();
        // Debe aparecer al menos dos veces (tabla radio y datos gateway)
        $this->assertEquals(2, substr_count($content, 'tarjeta-tabla-andalucia'));
    }
}
