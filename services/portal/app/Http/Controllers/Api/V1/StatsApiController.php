<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\Ingest\Provincias;
use App\Data\Ingest\Rankings;
use App\Data\Ingest\Resumen;
use App\Data\Ingest\Routers;
use App\Data\Ingest\Trafico;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StatsApiController extends Controller
{
    public function __construct(
        protected Resumen $resumen,
        protected Provincias $provincias,
        protected Rankings $rankings,
        protected Trafico $trafico,
        protected Routers $routers,
    ) {}

    /**
     * GET /api/v1/stats/summary
     */
    public function summary(): JsonResponse
    {
        $res = $this->resumen->obtenerResultado();
        return response()->json($res->aRespuesta(), 200, [
            'Cache-Control' => 'public, max-age=' . ($res->stale ? 15 : $res->ttl),
            'X-Cache' => $res->xCache,
        ]);
    }

    /**
     * GET /api/v1/stats/provinces
     */
    public function provinces(Request $request): JsonResponse
    {
        $ventana = $request->query('window', '7d');
        if (!in_array($ventana, ['24h', '7d', '30d'], true)) {
            return response()->json([
                'error' => [
                    'status' => 400,
                    'code' => 'invalid_parameter',
                    'message' => 'Valor no admitido en window: usa 24h, 7d o 30d.',
                    'parameters' => [
                        'window' => ['Valor no admitido: usa 24h, 7d o 30d.'],
                    ],
                ],
            ], 400, ['Cache-Control' => 'no-store']);
        }

        $res = $this->provincias->obtenerResultado((string) $ventana);
        return response()->json($res->aRespuesta(), 200, [
            'Cache-Control' => 'public, max-age=' . ($res->stale ? 15 : $res->ttl),
            'X-Cache' => $res->xCache,
        ]);
    }

    /**
     * GET /api/v1/stats/rankings
     */
    public function rankingsList(): JsonResponse
    {
        $catalogo = Rankings::catalogo();
        return response()->json([
            'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'stale' => false,
            'count' => count($catalogo),
            'items' => array_values($catalogo),
        ], 200, [
            'Cache-Control' => 'public, max-age=3600',
            'X-Cache' => 'HIT',
        ]);
    }

    /**
     * GET /api/v1/stats/rankings/{id}
     */
    public function rankingDetail(Request $request, string $id): JsonResponse
    {
        $period = (string) $request->query('period', 'day');
        $which = (string) $request->query('which', 'current');
        $limit = (int) $request->query('limit', 10);

        try {
            $res = $this->rankings->obtenerRanking($id, $period, $which, $limit);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => [
                    'status' => 400,
                    'code' => 'invalid_parameter',
                    'message' => $e->getMessage(),
                ],
            ], 400, ['Cache-Control' => 'no-store']);
        }

        return response()->json($res->aRespuesta(), 200, [
            'Cache-Control' => 'public, max-age=' . ($res->stale ? 15 : $res->ttl),
            'X-Cache' => $res->xCache,
        ]);
    }

    /**
     * GET /api/v1/stats/traffic-mix
     */
    public function trafficMix(Request $request): JsonResponse
    {
        $period = (string) $request->query('period', 'day');
        $which = (string) $request->query('which', 'current');

        try {
            $res = $this->trafico->obtenerResultado($period, $which);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => [
                    'status' => 400,
                    'code' => 'invalid_parameter',
                    'message' => $e->getMessage(),
                ],
            ], 400, ['Cache-Control' => 'no-store']);
        }

        return response()->json($res->aRespuesta(), 200, [
            'Cache-Control' => 'public, max-age=' . ($res->stale ? 15 : $res->ttl),
            'X-Cache' => $res->xCache,
        ]);
    }

    /**
     * GET /api/v1/routers
     */
    public function routers(Request $request): JsonResponse
    {
        $province = $request->query('province');
        $sort = (string) $request->query('sort', 'battery_asc');

        $res = $this->routers->obtenerResultado($province ? (string) $province : null, $sort);
        return response()->json($res->aRespuesta(), 200, [
            'Cache-Control' => 'public, max-age=' . ($res->stale ? 15 : $res->ttl),
            'X-Cache' => $res->xCache,
        ]);
    }
}
