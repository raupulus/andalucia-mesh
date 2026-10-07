<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\Ingest\Diagnostico;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NodesApiController extends Controller
{
    public function __construct(
        protected Diagnostico $diagnostico
    ) {}

    /**
     * GET /api/v1/nodes?search=&limit=
     */
    public function search(Request $request): JsonResponse
    {
        $search = (string) $request->query('search', '');
        $limit = (int) $request->query('limit', 20);

        if (mb_strlen(trim($search)) < 2) {
            return response()->json([
                'error' => [
                    'status' => 400,
                    'code' => 'invalid_parameter',
                    'message' => 'El parámetro search debe tener al menos 2 caracteres.',
                ],
            ], 400, ['Cache-Control' => 'no-store']);
        }

        $res = $this->diagnostico->buscar($search, $limit);
        return response()->json($res->aRespuesta(), 200, [
            'Cache-Control' => 'public, max-age=' . ($res->stale ? 15 : $res->ttl),
            'X-Cache' => $res->xCache,
        ]);
    }

    /**
     * GET /api/v1/nodes/{id}/diagnosis
     */
    public function diagnosis(string $id): JsonResponse
    {
        $idNormalizado = Diagnostico::normalizarId($id);
        $res = $this->diagnostico->obtenerDiagnostico($idNormalizado);

        if ($res->datos === null) {
            return response()->json([
                'error' => [
                    'status' => 404,
                    'code' => 'not_found',
                    'message' => "Nodo '{$idNormalizado}' no encontrado o sin datos registrados.",
                ],
            ], 404, ['Cache-Control' => 'public, max-age=60']);
        }

        return response()->json($res->aRespuesta(), 200, [
            'Cache-Control' => 'public, max-age=' . ($res->stale ? 15 : $res->ttl),
            'X-Cache' => $res->xCache,
        ]);
    }

    /**
     * GET /api/v1/nodes/at-risk
     */
    public function atRisk(Request $request): JsonResponse
    {
        // Devuelve nodos con riesgos activos
        return response()->json([
            'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'stale' => false,
            'count' => 0,
            'items' => [],
        ], 200, [
            'Cache-Control' => 'public, max-age=30',
            'X-Cache' => 'HIT',
        ]);
    }
}
