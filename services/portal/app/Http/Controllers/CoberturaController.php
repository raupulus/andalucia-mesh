<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Ingest\Cobertura;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador de la herramienta "Mi Cobertura" en el portal público.
 *
 * Ofrece la interfaz interactiva, autocompletado en vivo de nodos,
 * visualización de la matriz de enlaces de cobertura y exportación
 * en formatos PDF y Markdown cacheados 1 hora en servidor.
 */
class CoberturaController extends Controller
{
    public function __construct(
        protected Cobertura $cobertura
    ) {}

    /**
     * Muestra la interfaz del verificador de cobertura.
     * Si se especifican nodos en la petición (1 a 3), compila y muestra la matriz comparativa.
     */
    public function index(Request $request): View
    {
        $rawNodos = $request->query('nodes') ?? $request->query('nodos') ?? '';
        $rawFiltro = (string) ($request->query('filter') ?? $request->query('filtro') ?? 'snr_positivo');
        $filtro = $rawFiltro === 'todos' ? 'todos' : 'snr_positivo';

        $listaNodos = $this->extraerNodosDePeticion($rawNodos);
        $datos = null;

        if (! empty($listaNodos)) {
            $datos = $this->cobertura->obtenerMatriz($listaNodos, $filtro);
        }

        $baseUrl = url('/mi-cobertura');
        $urlCompartir = ! empty($listaNodos)
            ? "{$baseUrl}?nodes=".implode(',', $listaNodos)."&filter={$filtro}"
            : $baseUrl;

        $nombresEvaluados = ! empty($datos['nodos_evaluados'])
            ? implode(' vs ', array_column($datos['nodos_evaluados'], 'short_name'))
            : '';

        $shareText = $nombresEvaluados !== ''
            ? "Verificador de Cobertura Meshtastic ({$nombresEvaluados}): Enlaces directos 100% (0 saltos RF). Consulta el análisis en:\n{$urlCompartir}"
            : "Verificador de Cobertura Meshtastic: Enlaces directos 100% (0 saltos RF). Analiza la cobertura de tus nodos en:\n{$urlCompartir}";

        $shareWhatsApp = 'https://api.whatsapp.com/send?text='.urlencode($shareText);

        $shareTelegramText = $nombresEvaluados !== ''
            ? "Verificador de Cobertura Meshtastic ({$nombresEvaluados}) — Enlaces directos 100% (0 saltos RF)"
            : 'Verificador de Cobertura Meshtastic — Enlaces directos 100% (0 saltos RF)';
        $shareTelegram = 'https://t.me/share/url?url='.urlencode($urlCompartir).'&text='.urlencode($shareTelegramText);

        $shareEmailSubject = $nombresEvaluados !== ''
            ? "Verificador de Cobertura Meshtastic ({$nombresEvaluados}) — Enlaces 100% directos"
            : 'Verificador de Cobertura Meshtastic (Enlaces directos 100%)';
        $emailBody = str_replace("\n", "\r\n", $shareText);
        $shareEmail = 'mailto:?subject='.rawurlencode($shareEmailSubject).'&body='.rawurlencode($emailBody);

        return view('cobertura.index', [
            'nodosSeleccionados' => $listaNodos,
            'filtro' => $filtro,
            'datos' => $datos,
            'urlCompartir' => $urlCompartir,
            'shareWhatsApp' => $shareWhatsApp,
            'shareTelegram' => $shareTelegram,
            'shareEmail' => $shareEmail,
        ]);
    }

    /**
     * Endpoint asíncrono para el autocompletado reactivo de nodos en el buscador.
     */
    public function buscar(Request $request): JsonResponse
    {
        $query = trim((string) ($request->query('q') ?? $request->query('search') ?? $request->query('buscar') ?? ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $resultados = $this->cobertura->buscar($query, 15);

        return response()->json($resultados);
    }

    /**
     * Genera y descarga el informe de cobertura en formato PDF estilizado.
     */
    public function descargarPdf(Request $request): Response
    {
        $rawNodos = $request->query('nodes') ?? $request->query('nodos') ?? '';
        $rawFiltro = (string) ($request->query('filter') ?? $request->query('filtro') ?? 'snr_positivo');
        $filtro = $rawFiltro === 'todos' ? 'todos' : 'snr_positivo';

        $listaNodos = $this->extraerNodosDePeticion($rawNodos);
        if (empty($listaNodos)) {
            $listaNodos = ['!30618df9', '!63760c00'];
        }

        $datos = $this->cobertura->obtenerMatriz($listaNodos, $filtro);
        $pdfBinario = $this->cobertura->generarPdf($datos);

        $alias = array_map(function ($n) {
            return ltrim($n['short_name'] ?? $n['id'], '!');
        }, $datos['nodos_evaluados'] ?? []);
        $slug = implode('_', $alias) ?: 'red';
        $nombreFichero = "cobertura_{$slug}_{$filtro}_".date('Ymd_His').'.pdf';

        return response($pdfBinario, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$nombreFichero.'"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Genera y descarga el informe de cobertura en formato Markdown (GFM).
     */
    public function descargarMarkdown(Request $request): Response
    {
        $rawNodos = $request->query('nodes') ?? $request->query('nodos') ?? '';
        $rawFiltro = (string) ($request->query('filter') ?? $request->query('filtro') ?? 'snr_positivo');
        $filtro = $rawFiltro === 'todos' ? 'todos' : 'snr_positivo';

        $listaNodos = $this->extraerNodosDePeticion($rawNodos);
        if (empty($listaNodos)) {
            $listaNodos = ['!30618df9', '!63760c00'];
        }

        $datos = $this->cobertura->obtenerMatriz($listaNodos, $filtro);
        $markdown = $this->cobertura->generarMarkdown($datos);

        $alias = array_map(function ($n) {
            return ltrim($n['short_name'] ?? $n['id'], '!');
        }, $datos['nodos_evaluados'] ?? []);
        $slug = implode('_', $alias) ?: 'red';
        $nombreFichero = "cobertura_{$slug}_{$filtro}_".date('Ymd_His').'.md';

        return response($markdown, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombreFichero.'"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Extrae y normaliza un array de IDs de nodo desde la petición (cadena o array).
     *
     * @return array<int, string>
     */
    protected function extraerNodosDePeticion(mixed $raw): array
    {
        if (is_array($raw)) {
            $nodos = $raw;
        } elseif (is_string($raw)) {
            $nodos = array_filter(array_map('trim', explode(',', $raw)));
        } else {
            return [];
        }

        $limpios = [];
        foreach ($nodos as $item) {
            if ($item === '') {
                continue;
            }
            $resolved = $this->cobertura->resolverAliasANodoId((string) $item);
            $limpios[] = Cobertura::normalizarId($resolved);
            if (count($limpios) >= 3) {
                break;
            }
        }

        return array_values(array_unique($limpios));
    }
}
