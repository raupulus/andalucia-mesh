<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Ingest\MapaData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MapaController extends Controller
{
    public function __construct(
        protected MapaData $mapaData
    ) {}

    /**
     * Vista principal interactiva del Mapa de la red.
     */
    public function index(Request $request): View
    {
        $stats = $this->mapaData->obtenerStats();

        return view('mapa.index', [
            'stats' => $stats,
            'lora' => config('proyecto.lora'),
            'mapaConfig' => config('proyecto.mapa'),
            'dominio' => config('proyecto.dominio'),
        ]);
    }

    /**
     * Endpoint API para obtener el listado compacto de nodos geolocalizados.
     * Soporta compresión gzip directa, cabeceras ETag y respuestas 304 Not Modified.
     */
    public function nodes(Request $request): Response|BinaryFileResponse|JsonResponse
    {
        $filePath = public_path(MapaData::CACHE_DIR.'/nodes.json');
        $gzPath = public_path(MapaData::CACHE_DIR.'/nodes.json.gz');

        if (! File::exists($filePath)) {
            $this->mapaData->compilarCache();
        }

        if (File::exists($filePath)) {
            $etag = '"'.md5_file($filePath).'"';
            $ifNoneMatch = $request->header('If-None-Match');

            if ($ifNoneMatch === $etag) {
                return response('', 304, [
                    'ETag' => $etag,
                    'Cache-Control' => 'public, max-age=120, stale-while-revalidate=60',
                ]);
            }

            // Servir pre-gzipeado si el cliente lo acepta
            $acceptEncoding = (string) $request->header('Accept-Encoding', '');
            if (str_contains($acceptEncoding, 'gzip') && File::exists($gzPath)) {
                return response(File::get($gzPath), 200, [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Content-Encoding' => 'gzip',
                    'ETag' => $etag,
                    'Cache-Control' => 'public, max-age=120, stale-while-revalidate=60',
                ]);
            }

            return response(File::get($filePath), 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'ETag' => $etag,
                'Cache-Control' => 'public, max-age=120, stale-while-revalidate=60',
            ]);
        }

        return response()->json([], 200);
    }

    /**
     * Endpoint API para métricas y contadores globales en vivo.
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->mapaData->obtenerStats();

        return response()->json($stats, 200, [
            'Cache-Control' => 'public, max-age=60, stale-while-revalidate=30',
        ]);
    }

    /**
     * Endpoint API para catálogo de nodos no optimizados (modal de avisos).
     */
    public function unoptimized(Request $request): JsonResponse
    {
        $nodos = $this->mapaData->obtenerNoOptimizados();

        return response()->json([
            'count' => count($nodos),
            'items' => $nodos,
        ], 200, [
            'Cache-Control' => 'public, max-age=120, stale-while-revalidate=60',
        ]);
    }

    /**
     * Endpoint API para obtener el diagnóstico completo de un nodo para el modal central.
     */
    public function nodeDetail(string $id): JsonResponse
    {
        $detalle = $this->mapaData->obtenerDetalleNodo($id);

        if (! $detalle) {
            return response()->json([
                'ok' => false,
                'error' => 'Nodo no encontrado o sin datos de diagnóstico disponibles.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'node' => $detalle,
        ], 200, [
            'Cache-Control' => 'public, max-age=120, stale-while-revalidate=60',
        ]);
    }
}
