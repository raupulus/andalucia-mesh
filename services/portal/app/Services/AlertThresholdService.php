<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AlertThreshold;
use Database\Seeders\AlertThresholdSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Servicio centralizado para la lectura en memoria RAM, calibración y sincronización
 * de los umbrales de alertas y diagnóstico entre servicios.
 */
class AlertThresholdService
{
    public const CACHE_KEY = 'alert_thresholds_matrix';

    /**
     * Resuelve el grupo operativo de roles correspondiente a un rol de Meshtastic.
     *
     * Grupo 1 (router): ROUTER, ROUTER_LATE, REPEATER.
     * Grupo 2 (client): CLIENT, CLIENT_HIDDEN, CLIENT_BASE.
     * Grupo 3 (client_mute): CLIENT_MUTE y resto de roles auxiliares (TRACKER, LOST_AND_FOUND, TAK, etc.).
     */
    public function resolveRoleGroup(?string $role): string
    {
        if ($role === null) {
            return AlertThreshold::GROUP_CLIENT_MUTE;
        }

        $rolNormalizado = strtoupper(trim($role));

        return match ($rolNormalizado) {
            'ROUTER', 'ROUTER_LATE', 'REPEATER' => AlertThreshold::GROUP_ROUTER,
            'CLIENT', 'CLIENT_HIDDEN', 'CLIENT_BASE' => AlertThreshold::GROUP_CLIENT,
            default => AlertThreshold::GROUP_CLIENT_MUTE,
        };
    }

    /**
     * Obtiene la matriz completa de umbrales desde la caché en memoria RAM.
     * Si la caché está vacía, la reconstruye desde la base de datos o carga los valores por defecto.
     *
     * @return array<string, array<string, float>>
     */
    public function getAllThresholds(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $matrix = [];
            foreach (AlertThreshold::GROUPS as $group) {
                $matrix[$group] = [];
            }

            try {
                $thresholds = AlertThreshold::all();

                if ($thresholds->isEmpty()) {
                    // Auto-inicialización con valores de fábrica si la tabla estuviera sin inicializar
                    $seeder = new AlertThresholdSeeder;
                    $seeder->run();
                    $thresholds = AlertThreshold::all();
                }

                foreach ($thresholds as $t) {
                    $matrix[$t->role_group][$t->parameter] = (float) $t->value_numeric;
                }
            } catch (Throwable $e) {
                Log::warning('No se pudieron leer los umbrales de base de datos; usando valores por defecto', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Asegurar que no falten claves completando con valores por defecto
            foreach (AlertThresholdSeeder::DEFAULTS as $group => $parameters) {
                foreach ($parameters as $parameter => $def) {
                    if (! isset($matrix[$group][$parameter])) {
                        $matrix[$group][$parameter] = (float) $def['value'];
                    }
                }
            }

            return $matrix;
        });
    }

    /**
     * Obtiene un parámetro numérico específico para el rol Meshtastic dado.
     */
    public function getForRole(?string $role, string $parameter, ?float $default = null): float
    {
        $group = $this->resolveRoleGroup($role);

        return $this->getForGroup($group, $parameter, $default);
    }

    /**
     * Obtiene un parámetro numérico específico para un grupo de roles concreto.
     */
    public function getForGroup(string $group, string $parameter, ?float $default = null): float
    {
        $all = $this->getAllThresholds();

        if (isset($all[$group][$parameter])) {
            return (float) $all[$group][$parameter];
        }

        if ($default !== null) {
            return $default;
        }

        return (float) (AlertThresholdSeeder::DEFAULTS[$group][$parameter]['value'] ?? 0.0);
    }

    /**
     * Obtiene todos los parámetros numéricos de un grupo de roles.
     *
     * @return array<string, float>
     */
    public function getAllForGroup(string $group): array
    {
        $all = $this->getAllThresholds();

        return $all[$group] ?? [];
    }

    /**
     * Persiste los umbrales modificados en base de datos, invalida la caché en RAM
     * y notifica en caliente al microservicio detector-alertas vía HTTP.
     *
     * @param  array<string, array<string, float|int|string>>  $matrix
     * @return array{success: bool, detector_notified: bool, message: string}
     */
    public function saveThresholds(array $matrix): array
    {
        $defaults = AlertThresholdSeeder::DEFAULTS;

        foreach ($matrix as $group => $parameters) {
            if (! in_array($group, AlertThreshold::GROUPS, true)) {
                continue;
            }

            foreach ($parameters as $parameter => $val) {
                $numVal = (float) $val;
                $unit = $defaults[$group][$parameter]['unit'] ?? 'number';
                $desc = $defaults[$group][$parameter]['description'] ?? null;

                AlertThreshold::updateOrCreate(
                    [
                        'role_group' => $group,
                        'parameter' => $parameter,
                    ],
                    [
                        'value_numeric' => $numVal,
                        'unit' => $unit,
                        'description' => $desc,
                    ]
                );
            }
        }

        // Invalidar caché en memoria RAM de Laravel
        Cache::forget(self::CACHE_KEY);

        // Obtener matriz fresca
        $freshMatrix = $this->getAllThresholds();

        // Notificar en caliente a detector-alertas
        $notified = $this->notifyDetector($freshMatrix);

        $msg = $notified
            ? 'Umbrales guardados y propagados en caliente al detector de alertas.'
            : 'Umbrales guardados en base de datos. El detector los aplicará en el próximo ciclo de sincronización.';

        return [
            'success' => true,
            'detector_notified' => $notified,
            'message' => $msg,
        ];
    }

    /**
     * Restablece todos los umbrales a los valores de fábrica predefinidos.
     *
     * @return array{success: bool, detector_notified: bool, message: string}
     */
    public function resetToDefaults(): array
    {
        $seeder = new AlertThresholdSeeder;
        $seeder->run();

        Cache::forget(self::CACHE_KEY);
        $freshMatrix = $this->getAllThresholds();
        $notified = $this->notifyDetector($freshMatrix);

        $msg = $notified
            ? 'Valores de fábrica restablecidos y sincronizados en vivo con el detector.'
            : 'Valores de fábrica restablecidos en base de datos. El detector los sincronizará periódicamente.';

        return [
            'success' => true,
            'detector_notified' => $notified,
            'message' => $msg,
        ];
    }

    /**
     * Envía una notificación HTTP POST al microservicio detector-alertas para recargar en RAM.
     *
     * @param  array<string, array<string, float>>  $matrix
     */
    public function notifyDetector(array $matrix): bool
    {
        $url = rtrim((string) config('servicios.detector_api.url', 'http://detector-alertas:8080'), '/').'/internal/recargar-umbrales';
        $secret = (string) config('servicios.detector_api.secret', '');
        $timeout = (int) config('servicios.detector_api.timeout', 5);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'X-Internal-Secret' => $secret,
                ])
                ->post($url, [
                    'version' => time(),
                    'umbrales' => $matrix,
                ]);

            if ($response->successful()) {
                Log::info('Umbrales sincronizados con éxito en detector-alertas', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return true;
            }

            Log::warning('Respuesta no satisfactoria al notificar umbrales a detector-alertas', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::warning('No se pudo conectar con detector-alertas para sincronizar umbrales en caliente', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Retorna la matriz de valores de fábrica.
     *
     * @return array<string, array<string, array{value: float, unit: string, description: string}>>
     */
    public function getDefaultMatrix(): array
    {
        return AlertThresholdSeeder::DEFAULTS;
    }
}
