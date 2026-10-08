<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Jobs\ComprobarServicios;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Widget de supervisión de salud y estado de los 13 servicios de la red.
 *
 * Conforme a docs/info/portal/14-operator-panel.md (UT-06.14.3).
 */
class EstadoServiciosWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.estado-servicios-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    /**
     * Permite al operador forzar un chequeo inmediato desde la interfaz.
     */
    public function comprobarAhora(): void
    {
        (new ComprobarServicios)->handle();
    }

    /**
     * Prepara los datos para la vista del widget.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $configServicios = config('servicios.lista', []);

        try {
            $registros = DB::table('estado_servicio')->get()->keyBy('servicio');
        } catch (Throwable) {
            $registros = collect();
        }

        $lista = [];
        $operativos = 0;
        $caidos = 0;
        $pendientes = 0;

        foreach ($configServicios as $clave => $cfg) {
            $reg = $registros->get($clave);
            $fase = (int) ($cfg['fase'] ?? 1);
            $nombreClave = 'admin.services.'.$clave;
            $nombre = __($nombreClave) !== $nombreClave ? __($nombreClave) : (string) ($cfg['nombre'] ?? $clave);

            $ok = $reg ? (bool) $reg->ok : false;
            $fallos = $reg ? (int) $reg->fallos_seguidos : 0;
            $motivo = $reg ? $reg->motivo : null;
            $latencia = $reg ? $reg->latencia_ms : null;
            $detalleRaw = $reg ? $reg->detalle : null;
            $comprobadoEn = $reg && $reg->comprobado_en ? Carbon::parse($reg->comprobado_en) : null;

            $detalle = null;
            if ($detalleRaw) {
                $detalle = json_decode((string) $detalleRaw, true);
            }

            $activo = (bool) ($cfg['activo'] ?? true);

            // Determinación de color y etiqueta según regla anti-parpadeo y fases
            if (! $activo || (string) $motivo === 'Servicio desactivado') {
                $color = 'gris';
                $etiqueta = __('admin.widget_network_status.status_disabled');
                $pendientes++;
            } elseif ($ok) {
                $color = 'verde';
                $etiqueta = __('admin.widget_network_status.status_ok');
                $operativos++;
            } elseif ($fase > 5 && (str_contains((string) $motivo, 'En fase posterior') || $reg === null)) {
                $color = 'gris';
                $etiqueta = __('admin.widget_network_status.status_pending_phase', ['phase' => $fase]);
                $pendientes++;
            } elseif ($fallos >= 2) {
                $color = 'rojo';
                $etiqueta = __('admin.widget_network_status.status_down_failures', ['count' => $fallos]);
                $caidos++;
            } elseif ($fallos === 1) {
                $color = 'amarillo';
                $etiqueta = __('admin.widget_network_status.status_warning_transient');
            } else {
                $color = 'gris';
                $etiqueta = __('admin.widget_network_status.status_untested');
                $pendientes++;
            }

            $lista[] = [
                'clave' => $clave,
                'nombre' => $nombre,
                'tipo' => $cfg['tipo'] ?? 'http',
                'fase' => $fase,
                'ok' => $ok,
                'color' => $color,
                'etiqueta' => $etiqueta,
                'fallos' => $fallos,
                'latencia_ms' => $latencia,
                'motivo' => $motivo,
                'detalle' => $detalle,
                'hace' => $comprobadoEn ? $comprobadoEn->diffForHumans() : __('admin.widget_network_status.time_never'),
            ];
        }

        // Latido del daemon portal-tareas
        $latidoOk = false;
        $latidoHace = __('admin.widget_network_status.no_data');
        try {
            $latido = DB::table('tareas_latido')->where('tarea', 'comprobar-servicios')->value('ultima_ejecucion');
            if ($latido !== null) {
                $dt = Carbon::parse($latido);
                $latidoOk = (now()->timestamp - $dt->timestamp) <= 180;
                $latidoHace = $dt->diffForHumans();
            }
        } catch (Throwable) {
            // Ignorar
        }

        // Historial reciente de transiciones (últimos 8 cambios)
        try {
            $transiciones = DB::table('estado_servicio_cambio')
                ->orderByDesc('en')
                ->limit(8)
                ->get()
                ->map(function ($t) {
                    $nombreClave = 'admin.services.'.$t->servicio;
                    $nombreServicio = __($nombreClave) !== $nombreClave ? __($nombreClave) : $t->servicio;

                    return [
                        'servicio' => $nombreServicio,
                        'ok' => (bool) $t->ok,
                        'motivo' => $t->motivo,
                        'hace' => Carbon::parse($t->en)->diffForHumans(),
                    ];
                });
        } catch (Throwable) {
            $transiciones = collect();
        }

        return [
            'servicios' => $lista,
            'operativos' => $operativos,
            'caidos' => $caidos,
            'pendientes' => $pendientes,
            'latido_ok' => $latidoOk,
            'latido_hace' => $latidoHace,
            'transiciones' => $transiciones,
        ];
    }
}
