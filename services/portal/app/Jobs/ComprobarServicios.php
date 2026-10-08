<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use Throwable;

/**
 * Tarea programada para auditar periódicamente la salud de los servicios del ecosistema.
 *
 * Conforme a docs/info/portal/14-operator-panel.md e integration.md §11.
 */
class ComprobarServicios
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Ejecuta el escaneo de salud de todos los destinos configurados.
     */
    public function handle(): void
    {
        $servicios = config('servicios.lista', []);
        $timeout = (int) config('servicios.timeout_segundos', 3);

        foreach ($servicios as $clave => $cfg) {
            $activo = (bool) ($cfg['activo'] ?? true);
            if (! $activo) {
                $this->marcarDesactivado((string) $clave, (array) $cfg);

                continue;
            }

            $this->auditarServicio((string) $clave, (array) $cfg, $timeout);
        }

        // Actualizar latido de la propia tarea
        try {
            DB::table('tareas_latido')->updateOrInsert(
                ['tarea' => 'comprobar-servicios'],
                ['ultima_ejecucion' => now()]
            );

            // Purgar historial antiguo (> 90 días) según regla UT-06.14.6
            DB::table('estado_servicio_cambio')
                ->where('en', '<', now()->subDays(90))
                ->delete();
        } catch (Throwable) {
            // No interrumpir si la base portal experimenta un bloqueo transitorio
        }
    }

    /**
     * Audita un servicio específico según su tipo de sonda.
     *
     * @param  array<string, mixed>  $cfg
     */
    private function auditarServicio(string $clave, array $cfg, int $timeout): void
    {
        $inicio = hrtime(true);
        $tipo = (string) ($cfg['tipo'] ?? 'http');
        $fase = (int) ($cfg['fase'] ?? 1);

        $ok = false;
        $codigo = null;
        $motivo = null;
        $detalle = null;

        try {
            switch ($tipo) {
                case 'http':
                    $url = (string) ($cfg['url'] ?? '');
                    $res = Http::timeout($timeout)->get($url);
                    $codigo = $res->status();
                    $latenciaMs = (int) round((hrtime(true) - $inicio) / 1e6);

                    $json = $res->json();
                    if (is_array($json)) {
                        $detalle = json_encode(array_slice($json, 0, 50, true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        if (strlen((string) $detalle) > 4096) {
                            $detalle = substr((string) $detalle, 0, 4096);
                        }

                        if (isset($json['ok'])) {
                            $ok = (bool) $json['ok'];
                            $motivo = $ok ? null : (string) ($json['motivo'] ?? $json['error'] ?? 'Estado ok: false');
                        } else {
                            $ok = $res->successful();
                            $motivo = $ok ? null : 'Código HTTP '.$codigo;
                        }
                    } else {
                        $ok = $res->successful();
                        $motivo = $ok ? null : 'Código HTTP '.$codigo;
                    }
                    break;

                case 'mqtt':
                    $host = (string) ($cfg['host'] ?? '172.30.0.1');
                    $port = (int) ($cfg['port'] ?? 1884);
                    $user = ! empty($cfg['user']) ? (string) $cfg['user'] : null;
                    $pass = ! empty($cfg['password']) ? (string) $cfg['password'] : null;

                    $client = new MqttClient($host, $port, 'portal-health-'.bin2hex(random_bytes(4)));
                    $settings = (new ConnectionSettings)
                        ->setUsername($user)
                        ->setPassword($pass)
                        ->setConnectTimeout($timeout)
                        ->setSocketTimeout($timeout);

                    $client->connect($settings, true);
                    $client->disconnect();

                    $ok = true;
                    $latenciaMs = (int) round((hrtime(true) - $inicio) / 1e6);
                    break;

                case 'database':
                    $conexiones = (array) ($cfg['conexiones'] ?? ['pgsql']);
                    $falladas = [];

                    foreach ($conexiones as $conn) {
                        try {
                            DB::connection((string) $conn)->select('SELECT 1');
                        } catch (Throwable $e) {
                            $falladas[] = $conn;
                        }
                    }

                    $latenciaMs = (int) round((hrtime(true) - $inicio) / 1e6);

                    if (empty($falladas)) {
                        $ok = true;
                    } else {
                        $ok = false;
                        $motivo = 'Fallo en conexiones: '.implode(', ', $falladas);
                    }
                    break;

                case 'latido':
                    $latenciaMs = (int) round((hrtime(true) - $inicio) / 1e6);
                    $tarea = (string) ($cfg['tarea'] ?? 'comprobar-servicios');
                    $maxRetraso = (int) ($cfg['max_retraso_segundos'] ?? 180);

                    $latido = DB::table('tareas_latido')->where('tarea', $tarea)->value('ultima_ejecucion');

                    if ($latido !== null && (now()->timestamp - strtotime((string) $latido)) <= $maxRetraso) {
                        $ok = true;
                    } else {
                        $ok = false;
                        $motivo = $latido === null ? 'Sin registros de ejecución previos' : 'Sin latido en los últimos '.$maxRetraso.'s';
                    }
                    break;

                default:
                    $latenciaMs = 0;
                    $motivo = 'Tipo de sonda no soportado: '.$tipo;
                    break;
            }
        } catch (Throwable $e) {
            $latenciaMs = (int) round((hrtime(true) - $inicio) / 1e6);
            $ok = false;

            if ($fase > 5) {
                $motivo = 'En fase posterior (no desplegado)';
            } else {
                $motivo = $e->getMessage();
            }
        }

        $this->persistirEstado($clave, $ok, $codigo, $latenciaMs, $motivo, $detalle);
    }

    /**
     * Persiste el estado del servicio en la base de datos y registra cambios de transición.
     */
    private function persistirEstado(
        string $servicio,
        bool $ok,
        ?int $codigo,
        int $latenciaMs,
        ?string $motivo,
        ?string $detalle
    ): void {
        try {
            $actual = DB::table('estado_servicio')->where('servicio', $servicio)->first();

            $fallosSeguidos = $ok ? 0 : (($actual->fallos_seguidos ?? 0) + 1);

            // Registrar transición de estado en historial si cambia ok
            if ($actual === null || (bool) $actual->ok !== $ok) {
                DB::table('estado_servicio_cambio')->insert([
                    'servicio' => $servicio,
                    'ok' => $ok,
                    'motivo' => $motivo,
                    'en' => now(),
                ]);
            }

            DB::table('estado_servicio')->updateOrInsert(
                ['servicio' => $servicio],
                [
                    'ok' => $ok,
                    'fallos_seguidos' => $fallosSeguidos,
                    'codigo' => $codigo,
                    'latencia_ms' => $latenciaMs,
                    'motivo' => $motivo,
                    'detalle' => $detalle,
                    'comprobado_en' => now(),
                ]
            );
        } catch (Throwable) {
            // Silenciar fallos de persistencia transitorios para no abortar el ciclo
        }
    }

    /**
     * Registra un servicio marcado explícitamente como desactivado en la configuración.
     *
     * @param  array<string, mixed>  $cfg
     */
    private function marcarDesactivado(string $clave, array $cfg): void
    {
        $ahora = now();

        try {
            DB::table('estado_servicio')->updateOrInsert(
                ['servicio' => $clave],
                [
                    'ok' => false,
                    'codigo' => null,
                    'latencia_ms' => null,
                    'fallos_seguidos' => 0,
                    'motivo' => 'Servicio desactivado',
                    'detalle' => json_encode(['activo' => false], JSON_UNESCAPED_UNICODE),
                    'comprobado_en' => $ahora,
                ]
            );
        } catch (Throwable) {
            // Silenciar fallos de persistencia transitorios
        }
    }
}
