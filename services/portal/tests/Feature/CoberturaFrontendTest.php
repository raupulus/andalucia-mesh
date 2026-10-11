<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\Ingest\Cobertura;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Pruebas automatizadas de la herramienta "Mi Cobertura" en el portal público.
 */
class CoberturaFrontendTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Comprueba que la ruta base /mi-cobertura responde 200 OK y cero cookies (RN-06).
     */
    public function test_mi_cobertura_ruta_responde_200_y_cero_cookies(): void
    {
        $response = $this->get('/mi-cobertura');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Mi Cobertura', false);
        $response->assertSee('ANDALUCÍA MESH · RED MESHTASTIC ANDALUCÍA', false);
        $response->assertSee('Solo SNR positivo', false);
        $response->assertSee('Todos (incluye marginales)', false);
        $response->assertSee('id="modal-cargando-cobertura"', false);
    }

    /**
     * Comprueba que "Mi Cobertura" aparece en el navbar dentro del menú "Extras" tanto en escritorio como en móvil.
     */
    public function test_mi_cobertura_aparece_en_navbar_bajo_extras(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('href="/mi-cobertura"', false);
        $response->assertSee('Mi Cobertura', false);
    }

    /**
     * Comprueba el endpoint de autocompletado en vivo de nodos, incluyendo insensibilidad a acentos/diacríticos.
     */
    public function test_autocompletado_busca_por_id_o_nombre_e_ignora_acentos(): void
    {
        $response = $this->getJson('/mi-cobertura/buscar?q=CA01');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertSame('!30618df9', $data[0]['id']);
        $this->assertSame('CA01', $data[0]['short_name']);

        // Búsqueda sin acentos debe encontrar nodo con acentos ("hercules" -> "Hércules")
        $responseSinAcento = $this->getJson('/mi-cobertura/buscar?q=hercules');
        $responseSinAcento->assertStatus(200);
        $dataHerc = $responseSinAcento->json();
        $this->assertNotEmpty($dataHerc);
        $this->assertSame('!2b39ef06', $dataHerc[0]['id']);
        $this->assertStringContainsString('Hércules', $dataHerc[0]['long_name']);

        // Búsqueda con acentos explícitos
        $responseConAcento = $this->getJson('/mi-cobertura/buscar?q=Hércules');
        $responseConAcento->assertStatus(200);
        $this->assertNotEmpty($responseConAcento->json());

        // Término demasiado corto devuelve array vacío
        $responseShort = $this->getJson('/mi-cobertura/buscar?q=c');
        $responseShort->assertStatus(200);
        $this->assertEmpty($responseShort->json());
    }

    /**
     * Comprueba que los alias cortos (ej. CA01, CA05) se resuelven a sus IDs canónicos en la URL.
     */
    public function test_resolucion_de_alias_cortos_en_peticion(): void
    {
        $response = $this->get('/mi-cobertura?nodes=CA01,CA05&filter=snr_positivo');

        $response->assertStatus(200);
        $response->assertSee('CA01', false);
        $response->assertSee('CA05', false);
        $response->assertSee('50', false);
        $response->assertSee('NODOS ÚNICOS DETECTADOS', false);
        $response->assertSee('ENLACE MUTUO CA01 ⇄ CA05', false);
    }

    /**
     * Comprueba que al consultar el caso de referencia (CA01 y CA05) se renderiza la matriz completa
     * con los 50 nodos únicos, las 4 tarjetas KPI, el enlace mutuo y los bloques temáticos.
     */
    public function test_consulta_caso_referencia_sierra_de_lijar_muestra_50_nodos_y_kpis(): void
    {
        $response = $this->get('/mi-cobertura?nodes=!30618df9,!63760c00&filter=snr_positivo');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');

        // Título y píldora de ventana
        $response->assertSee('Matriz de Enlaces Positivos (SNR &gt; 0 dB)', false);
        $response->assertSee('Ventana de Análisis', false);
        $response->assertSee('Desde 10/10 14:00 h hasta 11/10 12:15 h', false);

        // 4 KPIs
        $response->assertSee('50', false);
        $response->assertSee('NODOS ÚNICOS DETECTADOS', false);
        $response->assertSee('+11.8 / +11.2', false);
        $response->assertSee('ENLACE MUTUO CA01 ⇄ CA05', false);
        $response->assertSee('16', false);
        $response->assertSee('COBERTURA BILATERAL (AMBOS)', false);
        $response->assertSee('15 / 18', false);
        $response->assertSee('ESPECIALIZACIÓN CA01 / CA05', false);

        // Bloques Temáticos
        $response->assertSee('ENLACE DIRECTO INTER-ROUTER', false);
        $response->assertSee('NODOS CON COBERTURA BILATERAL', false);
        $response->assertSee('COBERTURA EXCLUSIVA CA01', false);
        $response->assertSee('COBERTURA EXCLUSIVA CA05', false);

        // Nodos representativos del diseño de referencia
        $response->assertSee('MAS2', false);
        $response->assertSee('herc', false);
        $response->assertSee('CA16', false);
        $response->assertSee('TRKS', false);

        // Botones de exportación
        $response->assertSee('Descargar PDF', false);
        $response->assertSee('Descargar Markdown', false);
    }

    /**
     * Comprueba que el filtro 'todos' adapta el título, los distintivos, la leyenda y agrega nodos marginales (< 0 dB).
     */
    public function test_filtro_snr_positivo_vs_todos(): void
    {
        $response = $this->get('/mi-cobertura?nodes=!30618df9,!63760c00&filter=todos');

        $response->assertStatus(200);
        $response->assertSee('Matriz de Enlaces Completa (Todos)', false);
        $response->assertSee('&lt; 0 dB (Límite / Marginal)', false);
        // En "todos", se incluyen los enlaces marginales con SNR negativo (55 nodos únicos en total)
        $response->assertSee('55', false);
        $response->assertSee('-1.5 dB', false);
        $response->assertSee('snr-marginal', false);
    }

    /**
     * Comprueba que la descarga en PDF genera el binario, las cabeceras correctas y contiene los estilos.
     */
    public function test_descarga_pdf_devuelve_pdf_valido_con_cabeceras(): void
    {
        $response = $this->get('/mi-cobertura/pdf?nodes=!30618df9,!63760c00&filter=snr_positivo');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Cache-Control', 'max-age=3600, public');
        $this->assertStringContainsString('attachment; filename="cobertura_CA01_CA05_snr_positivo_', (string) $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->getContent());
        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());
    }

    /**
     * Comprueba que la plantilla de vista PDF contiene el logo corporativo base64 y los separadores con bandera de Andalucía.
     */
    public function test_plantilla_pdf_renderiza_logo_y_separadores_bandera(): void
    {
        $cobertura = app(Cobertura::class);
        $datos = $cobertura->obtenerMatriz(['!30618df9', '!63760c00'], 'snr_positivo');

        $rutaLogo = public_path('img/logo.png');
        $logoBase64 = file_exists($rutaLogo)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($rutaLogo))
            : null;

        $html = view('cobertura.pdf', [
            'datos' => $datos,
            'logoBase64' => $logoBase64,
            'projectDomain' => 'https://mesh.example.org',
        ])->render();

        $this->assertStringContainsString('class="header-logo"', $html);
        if ($logoBase64 !== null) {
            $this->assertStringContainsString('src="data:image/png;base64,', $html);
        }
        $this->assertStringContainsString('class="separador-bandera-sup"', $html);
        $this->assertStringContainsString('class="footer-bloque-url"', $html);
        $this->assertStringContainsString('class="separador-bandera-inf"', $html);
        $this->assertStringContainsString('https://mesh.example.org', $html);
    }

    /**
     * Comprueba que la descarga en Markdown devuelve texto plano con formato GFM y estructura correcta.
     */
    public function test_descarga_markdown_devuelve_gfm_valido(): void
    {
        $response = $this->get('/mi-cobertura/markdown?nodes=!30618df9,!63760c00&filter=snr_positivo');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $response->assertHeader('Cache-Control', 'max-age=3600, public');
        $this->assertStringContainsString('attachment; filename="cobertura_CA01_CA05_snr_positivo_', (string) $response->headers->get('Content-Disposition'));

        $content = (string) $response->getContent();
        $this->assertStringContainsString('# Matriz de Enlaces Positivos (SNR > 0 dB)', $content);
        $this->assertStringContainsString('**ANDALUCÍA MESH · RED MESHTASTIC ANDALUCÍA**', $content);
        $this->assertStringContainsString('NODOS ÚNICOS DETECTADOS:', $content);
        $this->assertStringContainsString('ENLACE DIRECTO INTER-ROUTER', $content);
        $this->assertStringContainsString('NODOS CON COBERTURA BILATERAL', $content);
        $this->assertStringContainsString('COBERTURA EXCLUSIVA CA01', $content);
    }

    /**
     * Comprueba que evaluar un único nodo ofrece su auditoría directa individual y título de sección dinámico.
     */
    public function test_consulta_un_solo_nodo_muestra_informe_individual(): void
    {
        $response = $this->get('/mi-cobertura?nodes=!30618df9&filter=snr_positivo');

        $response->assertStatus(200);
        $response->assertSee('CA01', false);
        $response->assertSee('NODOS ÚNICOS DETECTADOS', false);
        $response->assertSee('MEJOR ENLACE SNR', false);
        $response->assertSee('PROVINCIAS ALCANZADAS', false);
        $response->assertSee('ROUTERS ALCANZADOS', false);

        // Comprueba título de sección dinámico para 1 nodo en vez de "ALCANZADOS POR AMBOS REPETIDORES"
        $response->assertSee('ENLACES DIRECTOS DETECTADOS (1 SALTO', false);
        $response->assertDontSee('ALCANZADOS POR AMBOS REPETIDORES', false);
    }

    /**
     * Comprueba la persistencia estricta en servidor de 1 hora (3600 segundos) de los datos compilados.
     */
    public function test_cache_de_una_hora_en_servidor(): void
    {
        $cobertura = app(Cobertura::class);

        $matriz1 = $cobertura->obtenerMatriz(['!30618df9', '!63760c00'], 'snr_positivo');
        $this->assertTrue($matriz1['valido']);

        $hash = sha1('!30618df9,!63760c00:snr_positivo');
        $cacheKey = "cobertura:matriz:{$hash}";

        $this->assertTrue(Cache::has($cacheKey));
        $enCache = Cache::get($cacheKey);
        $this->assertIsArray($enCache);
        $this->assertSame($matriz1['ventana_texto'], $enCache['ventana_texto']);
    }

    /**
     * Comprueba la internacionalización en inglés y portugués.
     */
    public function test_traducciones_mi_cobertura(): void
    {
        $responseEn = $this->get('/?lang=en');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('My Coverage', false);

        $responsePt = $this->get('/?lang=pt');
        $responsePt->assertStatus(200);
        $responsePt->assertSee('Minha Cobertura', false);
    }

    /**
     * Comprueba el cálculo y renderizado de distancias geodésicas en km.
     */
    public function test_distancias_geodesicas_calculadas_y_renderizadas(): void
    {
        // 1. Verificación unitaria de la fórmula de Haversine y formateo
        $this->assertNull(Cobertura::calcularDistanciaKm(null, null, 37.0, -5.0));
        $this->assertNull(Cobertura::calcularDistanciaKm(37.0, -5.0, null, null));
        $this->assertNull(Cobertura::formatearDistanciaKm(null));
        $this->assertNull(Cobertura::formatearDistanciaKm(-5.0));

        // Enlace mutuo en Sierra de Líjar CA01 y CA05: separación geodésica de cumbre ~0.1 km
        $dMutuo = Cobertura::calcularDistanciaKm(36.9634, -5.3978, 36.9631, -5.3972);
        $this->assertEqualsWithDelta(0.1, $dMutuo, 0.05);
        $this->assertSame('0.1 km', Cobertura::formatearDistanciaKm($dMutuo));

        // Distancia a Mairena del Aljarafe (MAS2): ~73.4 km
        $dMas2 = Cobertura::calcularDistanciaKm(36.9634, -5.3978, 37.3562, -6.0641);
        $this->assertEqualsWithDelta(73.4, $dMas2, 0.5);
        $this->assertSame('73.4 km', Cobertura::formatearDistanciaKm($dMas2));

        // 2. Renderizado en vista web
        $response = $this->get('/mi-cobertura?nodes=!30618df9,!63760c00&filter=snr_positivo');
        $response->assertStatus(200);
        $response->assertSee('📏 0.1 km', false);
        $response->assertSee('📏 73.4 km', false);
        $response->assertSee('cobertura-distancia-enlace', false);

        // 3. Inclusión en informe Markdown
        $resMd = $this->get('/mi-cobertura/markdown?nodes=!30618df9,!63760c00&filter=snr_positivo');
        $contentMd = (string) $resMd->getContent();
        $this->assertStringContainsString('(0.1 km)', $contentMd);
        $this->assertStringContainsString('(73.4 km)', $contentMd);
    }

    /**
     * Comprueba la comunicación inequívoca de enlaces 100% directos (0 saltos RF).
     */
    public function test_enlaces_100_por_ciento_directos_aviso_y_metodologia(): void
    {
        $response = $this->get('/mi-cobertura?nodes=!30618df9,!63760c00&filter=snr_positivo');
        $response->assertStatus(200);

        // Banner visible en web
        $response->assertSee('banner-cobertura-directa', false);
        $response->assertSee('Enlaces 100% directos (0 saltos RF · Sin saltos multi-hop)', false);
        $response->assertSee('Enlaces directos 100% (0 saltos RF · positivos)', false);

        // Markdown contiene el bloque de aviso metodológico
        $resMd = $this->get('/mi-cobertura/markdown?nodes=!30618df9,!63760c00&filter=snr_positivo');
        $contentMd = (string) $resMd->getContent();
        $this->assertStringContainsString('Metodología de Análisis de Cobertura', $contentMd);
        $this->assertStringContainsString('100% directos, 0 saltos RF', $contentMd);
        $this->assertStringContainsString('No incluye retransmisiones multi-hop', $contentMd);
    }

    /**
     * Comprueba los botones de compartir y la reconstrucción fiel desde la URL compartida.
     */
    public function test_botones_compartir_y_restauracion_exacta_url(): void
    {
        $urlConsulta = '/mi-cobertura?nodes=!30618df9,!63760c00&filter=todos';
        $response = $this->get($urlConsulta);
        $response->assertStatus(200);

        // Botones de compartir presentes
        $response->assertSee('grupo-compartir-cobertura', false);
        $response->assertSee('btn-share-whatsapp', false);
        $response->assertSee('btn-share-telegram', false);
        $response->assertSee('btn-share-email', false);
        $response->assertSee('btn-share-copiar', false);
        $response->assertSee('copiarEnlaceCompartir', false);
        $response->assertSee('copiarConFallback', false);

        // Comprobación de que la URL compartida es limpia y preserva nodos y filtro
        $response->assertSee('nodes=!30618df9,!63760c00&filter=todos', false);
        $response->assertSee('api.whatsapp.com/send?text=', false);
        $response->assertSee('CA01+vs+CA05', false);
        $response->assertSee('t.me/share/url?url=', false);
        $response->assertSee('mailto:?subject=', false);
        $response->assertSee('%0D%0A', false); // CRLF en cuerpo de email

        // Apertura directa de la URL restaura la selección y filtro exactos
        $responseCompartida = $this->get($urlConsulta);
        $responseCompartida->assertStatus(200);
        $responseCompartida->assertSee('value="!30618df9,!63760c00"', false);
        $responseCompartida->assertSee('Matriz de Enlaces Completa (Todos)', false);
        $responseCompartida->assertSee('CA01', false);
        $responseCompartida->assertSee('CA05', false);
    }

    /**
     * Comprueba la normalización estricta del filtro para evitar valores inesperados o inyección en descargas.
     */
    public function test_normalizacion_de_filtros_y_saneamiento_descargas(): void
    {
        // 1. En vista web, filtro desconocido cae a 'snr_positivo'
        $response = $this->get('/mi-cobertura?nodes=!30618df9&filter=malicioso"test');
        $response->assertStatus(200);
        $response->assertSee('filter=snr_positivo', false);

        // 2. En descarga PDF, el nombre del fichero se sanea al enum válido
        $respPdf = $this->get('/mi-cobertura/pdf?nodes=!30618df9&filter=malicioso"test');
        $respPdf->assertStatus(200);
        $dispoPdf = (string) $respPdf->headers->get('Content-Disposition');
        $this->assertStringContainsString('_snr_positivo_', $dispoPdf);
        $this->assertStringNotContainsString('malicioso', $dispoPdf);

        // 3. En descarga Markdown, el nombre del fichero se sanea al enum válido
        $respMd = $this->get('/mi-cobertura/markdown?nodes=!30618df9&filter=malicioso"test');
        $respMd->assertStatus(200);
        $dispoMd = (string) $respMd->headers->get('Content-Disposition');
        $this->assertStringContainsString('_snr_positivo_', $dispoMd);
        $this->assertStringNotContainsString('malicioso', $dispoMd);
    }

    /**
     * Comprueba la comparación de 3 nodos simultáneos incluyendo el nodo de referencia Rau0.
     */
    public function test_comparacion_tres_nodos_incluyendo_rau0(): void
    {
        $response = $this->get('/mi-cobertura?nodes=!30618df9,!63760c00,rau0&filter=snr_positivo');

        $response->assertStatus(200);
        $response->assertSee('CA01', false);
        $response->assertSee('CA05', false);
        $response->assertSee('Rau0', false);
        $response->assertSee('ENLACE CA01', false);
        $response->assertSee('ENLACE CA05', false);
        $response->assertSee('ENLACE RAU0', false);
        $response->assertSee('data-node-id="!5f3a3a29"', false);
        $response->assertSee('btn-quitar-nodo', false);
    }

    /**
     * Comprueba que cualquier nodo arbitrario puede ser consultado y comparado sin errores.
     */
    public function test_comparacion_nodo_arbitrario(): void
    {
        $response = $this->get('/mi-cobertura?nodes=!30618df9,!99887766&filter=snr_positivo');

        $response->assertStatus(200);
        $response->assertSee('CA01', false);
        $response->assertSee('!99887766', false);
        $response->assertSee('ENLACE 7766', false);
    }

    /**
     * Comprueba que los tokens de diseño y colores del logotipo se aplican a la interfaz.
     */
    public function test_estilos_y_tokens_plataforma_en_interfaz(): void
    {
        $response = $this->get('/mi-cobertura?nodes=!30618df9,!63760c00');

        $response->assertStatus(200);
        $response->assertSee('var(--color-superficie)', false);
        $response->assertSee('var(--color-borde)', false);
        $response->assertSee('var(--color-acento)', false);
        $response->assertSee('logo-pdf.png', false);
        $response->assertSee('modal-cargando-cobertura', false);
    }
}
