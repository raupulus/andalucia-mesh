<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\FilamentLocaleMiddleware;
use App\Http\Middleware\PortalLocaleMiddleware;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Pruebas del sistema de internacionalización y traducciones del portal (RN-48).
 *
 * Valida la detección de idioma por navegador (Accept-Language), parámetros de consulta (?lang=),
 * fallback obligatorio a español (es), bandera de Andalucía para la opción en español,
 * cumplimiento de cero cookies (RN-06) en el frontal y persistencia en sesión técnica en el panel de control.
 */
class TraduccionesTest extends TestCase
{
    /**
     * Comprueba que una petición sin cabecera Accept-Language aplica español como idioma por defecto.
     */
    public function test_peticion_sin_cabecera_idioma_usa_espanol_por_defecto(): void
    {
        // 1. Verificación directa en el middleware sin cabecera
        $requestSinCabecera = Request::create('/');
        $requestSinCabecera->headers->remove('accept-language');
        $middleware = new PortalLocaleMiddleware;
        $this->assertSame('es', $middleware->determineLocale($requestSinCabecera));

        // 2. Verificación de respuesta HTTP completa
        $response = $this->withHeader('Accept-Language', '')->get('/');
        $response->assertStatus(200);
        $response->assertSee('<html lang="es"', false);
        $response->assertSee('Malla en Andalucía');
        $response->assertSee('Configura tu nodo');
        $response->assertSee('Sin rastreadores ni cookies de terceros');
    }

    /**
     * Comprueba que la cabecera Accept-Language con inglés activa las traducciones en inglés.
     */
    public function test_cabecera_accept_language_activa_idioma_ingles(): void
    {
        $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/');

        $response->assertStatus(200);
        $response->assertSee('<html lang="en"', false);
        $response->assertSee('Mesh in Andalusia');
        $response->assertSee('Configure your node');
        $response->assertSee('No trackers or third-party cookies');
    }

    /**
     * Comprueba que la cabecera Accept-Language con portugués activa las traducciones en portugués.
     */
    public function test_cabecera_accept_language_activa_idioma_portugues(): void
    {
        $response = $this->withHeader('Accept-Language', 'pt-PT,pt;q=0.9')->get('/');

        $response->assertStatus(200);
        $response->assertSee('<html lang="pt"', false);
        $response->assertSee('Malha na Andaluzia');
        $response->assertSee('Configura o teu nó');
        $response->assertSee('Sem rastreadores nem cookies de terceiros');
    }

    /**
     * Comprueba que un idioma no soportado (p. ej. francés o alemán) aplica fallback seguro a español.
     */
    public function test_idioma_no_soportado_aplica_fallback_en_espanol(): void
    {
        $response = $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9,de;q=0.8')->get('/');

        $response->assertStatus(200);
        $response->assertSee('<html lang="es"', false);
        $response->assertSee('Malla en Andalucía');
        $response->assertSee('Configura tu nodo');
        $response->assertSee('Sin rastreadores ni cookies de terceros');
    }

    /**
     * Comprueba que el parámetro ?lang= tiene precedencia absoluta sobre la cabecera del navegador.
     */
    public function test_parametro_url_lang_tiene_precedencia_sobre_cabecera(): void
    {
        // Con navegador en español pero solicitando inglés explícitamente
        $responseEn = $this->withHeader('Accept-Language', 'es-ES,es;q=0.9')->get('/?lang=en');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('<html lang="en"', false);
        $responseEn->assertSee('Mesh in Andalusia');

        // Con navegador en inglés pero solicitando portugués explícitamente
        $responsePt = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/?lang=pt');
        $responsePt->assertStatus(200);
        $responsePt->assertSee('<html lang="pt"', false);
        $responsePt->assertSee('Malha na Andaluzia');

        // Parámetro inválido ignora el valor y recurre a la cabecera o fallback
        $responseInvalido = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/?lang=invalido');
        $responseInvalido->assertStatus(200);
        $responseInvalido->assertSee('<html lang="en"', false);
    }

    /**
     * Comprueba que el selector de idiomas renderiza la bandera de Andalucía para español y las banderas respectivas.
     */
    public function test_selector_de_idiomas_muestra_bandera_de_andalucia_para_espanol(): void
    {
        // En español, el icono del selector debe mostrar la bandera de Andalucía (#007A33 y blanco #FFFFFF)
        $responseEs = $this->get('/?lang=es');
        $responseEs->assertStatus(200);
        $contenidoEs = (string) $responseEs->getContent();
        $this->assertStringContainsString('#007A33', $contenidoEs, 'El selector debe incluir el color verde de la bandera de Andalucía');
        $this->assertStringContainsString('fill="#FFFFFF"', $contenidoEs, 'El selector debe incluir la franja blanca de la bandera de Andalucía');

        // En inglés, el selector debe mostrar los colores de la Union Jack (#012169 y #C8102E)
        $responseEn = $this->get('/?lang=en');
        $responseEn->assertStatus(200);
        $contenidoEn = (string) $responseEn->getContent();
        $this->assertStringContainsString('#012169', $contenidoEn, 'El selector debe incluir el azul de la Union Jack');
        $this->assertStringContainsString('#C8102E', $contenidoEn, 'El selector debe incluir el rojo de la Union Jack');

        // En portugués, el selector debe mostrar los colores de la bandera portuguesa (#006600 y #FF0000)
        $responsePt = $this->get('/?lang=pt');
        $responsePt->assertStatus(200);
        $contenidoPt = (string) $responsePt->getContent();
        $this->assertStringContainsString('#006600', $contenidoPt, 'El selector debe incluir el verde de la bandera portuguesa');
    }

    /**
     * Comprueba que el selector de idiomas en cabecera y panel contiene solo el icono redondeado
     * sin texto visible en el activador, y que el orden del menú desplegable es estrictamente:
     * 1. Español (es)
     * 2. Portugués (pt)
     * 3. Inglés (en)
     */
    public function test_selector_de_idiomas_muestra_solo_icono_redondo_y_orden_estricto_es_pt_en(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $html = (string) $response->getContent();

        // 1. El botón activador del selector contiene la clase btn-idioma-redondo y ningún texto de idioma
        $this->assertMatchesRegularExpression(
            '/<button[^>]*id="btn-idioma-nav"[^>]*class="[^"]*btn-idioma-redondo[^"]*"[^>]*>\s*<svg[^>]*class="[^"]*bandera-redonda[^"]*"[\s\S]*?<\/svg>\s*<\/button>/',
            $html,
            'El botón activador de escritorio debe contener exclusivamente el SVG redondeado de la bandera sin texto'
        );

        // 2. El orden de las opciones en el menú desplegable debe ser estrictamente es -> pt -> en
        $posEs = strpos($html, 'data-lang="es"');
        $posPt = strpos($html, 'data-lang="pt"');
        $posEn = strpos($html, 'data-lang="en"');

        $this->assertNotFalse($posEs, 'Debe existir la opción en español');
        $this->assertNotFalse($posPt, 'Debe existir la opción en portugués');
        $this->assertNotFalse($posEn, 'Debe existir la opción en inglés');

        $this->assertTrue(
            $posEs < $posPt && $posPt < $posEn,
            "El orden de los idiomas en el selector debe ser estrictamente Español -> Portugués -> Inglés (es: {$posEs}, pt: {$posPt}, en: {$posEn})"
        );

        // 3. Verificación en la pantalla de login del panel de administración
        $responseLogin = $this->get('/admin/login');
        $responseLogin->assertStatus(200);
        $htmlLogin = (string) $responseLogin->getContent();

        $posLoginEs = strpos($htmlLogin, 'title="Español (Andalucía)"');
        $posLoginPt = strpos($htmlLogin, 'title="Português"');
        $posLoginEn = strpos($htmlLogin, 'title="English"');

        $this->assertNotFalse($posLoginEs, 'Login debe contener la opción en español');
        $this->assertNotFalse($posLoginPt, 'Login debe contener la opción en portugués');
        $this->assertNotFalse($posLoginEn, 'Login debe contener la opción en inglés');

        $this->assertTrue(
            $posLoginEs < $posLoginPt && $posLoginPt < $posLoginEn,
            "El orden en el selector de login debe ser estrictamente Español -> Portugués -> Inglés (es: {$posLoginEs}, pt: {$posLoginPt}, en: {$posLoginEn})"
        );

        // 4. Verificación en el componente de idiomas del panel (/admin): desplegable flotante idéntico al frontend, sin ensanchar navbar
        $htmlAdmin = (string) view('filament.language-switch')->render();

        $this->assertStringContainsString('fi-language-switch-wrapper', $htmlAdmin, 'El selector debe tener su wrapper dedicado');
        $this->assertStringContainsString('fi-language-dropdown-menu', $htmlAdmin, 'El menú desplegable debe tener la clase fi-language-dropdown-menu');
        $this->assertStringContainsString('position: absolute', $htmlAdmin, 'El desplegable debe flotar de forma absoluta sin ensanchar el navbar');

        $posAdminEs = strpos($htmlAdmin, 'data-lang="es"');
        $posAdminPt = strpos($htmlAdmin, 'data-lang="pt"');
        $posAdminEn = strpos($htmlAdmin, 'data-lang="en"');

        $this->assertNotFalse($posAdminEs, 'El componente del panel debe incluir la opción en español');
        $this->assertNotFalse($posAdminPt, 'El componente del panel debe incluir la opción en portugués');
        $this->assertNotFalse($posAdminEn, 'El componente del panel debe incluir la opción en inglés');

        $this->assertTrue(
            $posAdminEs < $posAdminPt && $posAdminPt < $posAdminEn,
            'El orden en el panel de administración debe ser estrictamente Español -> Portugués -> Inglés'
        );
    }

    /**
     * Comprueba que la navegación en cualquier idioma respeta estrictamente la regla RN-06 (cero cookies).
     */
    public function test_rutas_publicas_traducidas_mantienen_cero_cookies_rn06(): void
    {
        $rutas = [
            '/',
            '/?lang=en',
            '/?lang=pt',
            '/proyecto?lang=en',
            '/proyecto?lang=pt',
            '/rankings?lang=en',
            '/alertas?lang=pt',
            '/sugerencias?lang=en',
        ];

        foreach ($rutas as $ruta) {
            $response = $this->get($ruta);
            $response->assertStatus(200);
            $this->assertFalse(
                $response->headers->has('Set-Cookie'),
                "La ruta {$ruta} no debe emitir la cabecera Set-Cookie bajo ninguna circunstancia (RN-06)"
            );
        }
    }

    /**
     * Comprueba que el panel de administración (/admin) detecta y persiste la preferencia de idioma en sesión.
     */
    public function test_panel_admin_detecta_y_persiste_idioma_en_sesion(): void
    {
        // 1. Acceso con ?lang=en persiste el idioma en sesión técnica
        $response = $this->get('/admin/login?lang=en');
        $response->assertStatus(200);
        $response->assertSessionHas(FilamentLocaleMiddleware::SESSION_KEY, 'en');

        // 2. Petición subsiguiente sin parámetro mantiene el idioma de la sesión
        $responseSesion = $this->withSession([
            FilamentLocaleMiddleware::SESSION_KEY => 'pt',
        ])->get('/admin/login');

        $responseSesion->assertStatus(200);
        $this->assertSame('pt', app()->getLocale());
    }

    /**
     * Comprueba que las páginas estáticas cargan los archivos markdown localizados correspondientes (.en.md, .pt.md).
     */
    public function test_paginas_estaticas_cargan_contenido_markdown_localizado(): void
    {
        // Español por defecto
        $responseEs = $this->get('/proyecto?lang=es');
        $responseEs->assertStatus(200);
        $responseEs->assertSee('El proyecto');
        $responseEs->assertSee('Andalucía Mesh');

        // Inglés
        $responseEn = $this->get('/proyecto?lang=en');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('The Project');
        $responseEn->assertSee('gateway to the LoRa radio mesh network');

        // Portugués
        $responsePt = $this->get('/proyecto?lang=pt');
        $responsePt->assertStatus(200);
        $responsePt->assertSee('O Projeto');
        $responsePt->assertSee('porta de entrada para a malha de rádio LoRa');
    }

    /**
     * Comprueba que la búsqueda directa por ID de nodo en Revisa tu nodo conserva el parámetro ?lang=.
     */
    public function test_revisa_tu_nodo_preserva_parametro_lang_en_redireccion_directa(): void
    {
        $response = $this->get('/revisa-tu-nodo?buscar=!a1b2c3d4&lang=en');
        $response->assertRedirect('/revisa-tu-nodo/!a1b2c3d4?lang=en');

        $responsePt = $this->get('/revisa-tu-nodo?buscar=11223344&lang=pt');
        $responsePt->assertRedirect('/revisa-tu-nodo/!11223344?lang=pt');
    }

    /**
     * Comprueba que las vistas dinámicas (rankings, alertas, sugerencias, revisa-nodo) se traducen correctamente.
     */
    public function test_vistas_dinamicas_se_traducen_correctamente(): void
    {
        // Rankings en inglés y portugués
        $rankingsEn = $this->get('/rankings?lang=en');
        $rankingsEn->assertStatus(200);
        $rankingsEn->assertSee('Traffic Distribution (Packet Mix)');
        $rankingsEn->assertSee('Packet Type');
        $rankingsEn->assertSee('Airtime');

        $rankingsPt = $this->get('/rankings?lang=pt');
        $rankingsPt->assertStatus(200);
        $rankingsPt->assertSee('Distribuição de Tráfego (Mix de Pacotes)');
        $rankingsPt->assertSee('Tipo de Pacote');
        $rankingsPt->assertSee('Tempo de Ar');

        // Alertas en inglés
        $alertasEn = $this->get('/alertas?lang=en');
        $alertasEn->assertStatus(200);
        $alertasEn->assertSee('High');
        $alertasEn->assertSee('Medium');
        $alertasEn->assertSee('Low');

        // Buzón de sugerencias en inglés y portugués
        $sugEn = $this->get('/sugerencias?lang=en');
        $sugEn->assertStatus(200);
        $sugEn->assertSee('Suggestions Box');
        $sugEn->assertSee('Submit suggestion');

        $sugPt = $this->get('/sugerencias?lang=pt');
        $sugPt->assertStatus(200);
        $sugPt->assertSee('Caixa de Sugestões');
        $sugPt->assertSee('Enviar sugestão');

        // Revisa tu nodo en inglés
        $revisaEn = $this->get('/revisa-tu-nodo?lang=en');
        $revisaEn->assertStatus(200);
        $revisaEn->assertSee('Check your node');
        $revisaEn->assertSee('Search node');
    }
}
