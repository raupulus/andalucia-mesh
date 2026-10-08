<?php

declare(strict_types=1);

namespace App\Servicios;

use App\Models\WebhookDestination;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Cliente HTTP para comunicarse con la API interna del microservicio de Webhooks.
 *
 * Utiliza la red Docker interna 'mesh' para enviar órdenes de gestión de destinos
 * autenticadas mediante el token X-Internal-Secret sin tocar la base de datos de webhooks.
 */
class WebhooksClient
{
    protected string $baseUrl;

    protected string $secret;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('servicios.webhooks_api.url', 'http://webhooks:8080'), '/');
        $this->secret = (string) config('servicios.webhooks_api.secret', '');
        $this->timeout = (int) config('servicios.webhooks_api.timeout', 5);
    }

    /**
     * Construye una petición HTTP autenticada con timeout y cabeceras internas.
     */
    protected function request(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->acceptJson()
            ->withHeaders([
                'X-Internal-Secret' => $this->secret,
            ]);
    }

    /**
     * Obtiene la lista completa de destinos registrados y su estado actual.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDestinations(): array
    {
        $response = $this->request()->get("{$this->baseUrl}/internal/destinos");

        if (! $response->successful()) {
            Log::error('Fallo al obtener destinos de webhooks desde microservicio', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException("Error al consultar destinos ({$response->status()}): ".($response->json('error') ?? $response->body()));
        }

        /** @var array<int, array<string, mixed>> $destinos */
        $destinos = $response->json('destinos', []);

        return $destinos;
    }

    /**
     * Da de alta un nuevo destino de webhook.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createDestination(array $data): array
    {
        $response = $this->request()->post("{$this->baseUrl}/internal/destinos", $data);

        if (! $response->successful()) {
            $error = (string) ($response->json('error') ?? $response->body());
            Log::warning('Fallo al crear destino de webhook en microservicio', [
                'status' => $response->status(),
                'error' => $error,
            ]);
            throw new RuntimeException($error, $response->status());
        }

        /** @var array<string, mixed> $resultado */
        $resultado = $response->json();

        return $resultado;
    }

    /**
     * Actualiza la configuración o filtros de un destino existente.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateDestination(string $name, array $data): array
    {
        $response = $this->request()->put("{$this->baseUrl}/internal/destinos/{$name}", $data);

        if (! $response->successful()) {
            $error = (string) ($response->json('error') ?? $response->body());
            Log::warning("Fallo al actualizar destino de webhook {$name}", [
                'status' => $response->status(),
                'error' => $error,
            ]);
            throw new RuntimeException($error, $response->status());
        }

        /** @var array<string, mixed> $resultado */
        $resultado = $response->json();

        return $resultado;
    }

    /**
     * Marca un destino como retirado y purga entregas pendientes en el microservicio.
     *
     * @return array<string, mixed>
     */
    public function deleteDestination(string $name): array
    {
        $response = $this->request()->delete("{$this->baseUrl}/internal/destinos/{$name}");

        if (! $response->successful()) {
            $error = (string) ($response->json('error') ?? $response->body());
            Log::warning("Fallo al eliminar destino de webhook {$name}", [
                'status' => $response->status(),
                'error' => $error,
            ]);
            throw new RuntimeException($error, $response->status());
        }

        /** @var array<string, mixed> $resultado */
        $resultado = $response->json();

        return $resultado;
    }

    /**
     * Envía un ping de prueba firmado fuera de cola y retorna el resultado.
     *
     * @return array{ok: bool, status_code: int, duration_ms: int, error: ?string}
     */
    public function testDestination(string $name): array
    {
        $response = $this->request()->post("{$this->baseUrl}/internal/destinos/{$name}/probar");

        if (! $response->successful() && $response->status() !== 200) {
            $error = (string) ($response->json('error') ?? $response->body());

            return [
                'ok' => false,
                'status_code' => $response->status(),
                'duration_ms' => 0,
                'error' => $error,
            ];
        }

        return [
            'ok' => (bool) $response->json('ok', false),
            'status_code' => (int) $response->json('status_code', 0),
            'duration_ms' => (int) $response->json('duration_ms', 0),
            'error' => $response->json('error'),
        ];
    }

    /**
     * Reactiva un destino suspendido por fallos consecutivos.
     *
     * @return array<string, mixed>
     */
    public function reactivateDestination(string $name): array
    {
        $response = $this->request()->post("{$this->baseUrl}/internal/destinos/{$name}/reactivar");

        if (! $response->successful()) {
            $error = (string) ($response->json('error') ?? $response->body());
            Log::warning("Fallo al reactivar destino de webhook {$name}", [
                'status' => $response->status(),
                'error' => $error,
            ]);
            throw new RuntimeException($error, $response->status());
        }

        /** @var array<string, mixed> $resultado */
        $resultado = $response->json();

        return $resultado;
    }

    /**
     * Sincroniza los destinos obtenidos de la API interna en la tabla local de réplica.
     *
     * @return array<int, WebhookDestination>
     */
    public function syncLocalDestinations(): array
    {
        $destinosApi = $this->getDestinations();
        $nombresVistos = [];

        foreach ($destinosApi as $d) {
            $nombre = (string) $d['nombre'];
            $nombresVistos[] = $nombre;

            WebhookDestination::query()->updateOrCreate(
                ['nombre' => $nombre],
                [
                    'url' => $d['url'] ?? '',
                    'host' => $d['host'] ?? '',
                    'activo' => (bool) ($d['activo'] ?? true),
                    'motivo_baja' => $d['motivo_baja'] ?? null,
                    'fallos_seguidos' => (int) ($d['fallos_seguidos'] ?? 0),
                    'ultimo_ok' => isset($d['ultimo_ok']) && $d['ultimo_ok'] ? Carbon::parse($d['ultimo_ok']) : null,
                    'pendientes' => (int) ($d['pendientes'] ?? 0),
                    'riesgos' => $d['riesgos'] ?? [],
                    'tipos' => $d['tipos'] ?? [],
                    'provincias' => $d['provincias'] ?? [],
                    'nodos' => $d['nodos'] ?? [],
                    'secreto' => $d['secreto'] ?? '',
                ]
            );
        }

        if (! empty($nombresVistos)) {
            WebhookDestination::query()->whereNotIn('nombre', $nombresVistos)->delete();
        }

        /** @var array<int, WebhookDestination> $destinosLocales */
        $destinosLocales = WebhookDestination::query()->orderBy('nombre')->get()->all();

        return $destinosLocales;
    }
}
