<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AlertThreshold;
use Illuminate\Database\Seeder;

/**
 * Seeder para inicializar la matriz de umbrales calibrados por defecto para los 3 grupos de roles.
 */
class AlertThresholdSeeder extends Seeder
{
    /**
     * Matriz de calibración por defecto para Andalucía Mesh.
     *
     * @var array<string, array<string, array{value: float, unit: string, description: string}>>
     */
    public const DEFAULTS = [
        AlertThreshold::GROUP_ROUTER => [
            'hop_limit_recommended' => ['value' => 3.0, 'unit' => 'hops', 'description' => 'Saltos estándar recomendados por la red'],
            'hop_limit_warning' => ['value' => 5.0, 'unit' => 'hops', 'description' => 'Límite a partir del cual se genera aviso (Riesgo Bajo)'],
            'hop_limit_critical' => ['value' => 7.0, 'unit' => 'hops', 'description' => 'Límite a partir del cual se genera alerta grave (Riesgo Alto)'],
            'nodeinfo_interval_min_s' => ['value' => 86400.0, 'unit' => 'seconds', 'description' => 'Intervalo mínimo aceptable (menos es saturación)'],
            'nodeinfo_interval_recommended_s' => ['value' => 259200.0, 'unit' => 'seconds', 'description' => 'Intervalo recomendado en guías (72h)'],
            'telemetry_interval_min_s' => ['value' => 7200.0, 'unit' => 'seconds', 'description' => 'Intervalo mínimo admisible de telemetría (2h)'],
            'telemetry_interval_recommended_s' => ['value' => 14400.0, 'unit' => 'seconds', 'description' => 'Intervalo recomendado de telemetría (4h)'],
            'battery_warning_pct' => ['value' => 60.0, 'unit' => 'percent', 'description' => 'Umbral de batería para aviso de riesgo medio'],
            'battery_critical_pct' => ['value' => 40.0, 'unit' => 'percent', 'description' => 'Umbral de batería para alerta de riesgo alto'],
            'battery_resolution_pct' => ['value' => 70.0, 'unit' => 'percent', 'description' => 'Histéresis mínima de recarga para resolver alerta'],
            'sunset_battery_min_pct' => ['value' => 60.0, 'unit' => 'percent', 'description' => 'Carga mínima al anochecer (20:00h peninsulares)'],
            'silence_warning_h' => ['value' => 6.0, 'unit' => 'hours', 'description' => 'Horas de silencio antes de alerta de repetidor caído'],
            'silence_critical_h' => ['value' => 24.0, 'unit' => 'hours', 'description' => 'Horas de silencio para riesgo alto'],
            'reboot_loop_count_5m' => ['value' => 3.0, 'unit' => 'count', 'description' => 'Reinicios en 5 minutos para sospecha de bucle'],
            'reboot_loop_count_10m' => ['value' => 5.0, 'unit' => 'count', 'description' => 'Reinicios en 10 minutos para alerta crítica'],
        ],
        AlertThreshold::GROUP_CLIENT => [
            'hop_limit_recommended' => ['value' => 3.0, 'unit' => 'hops', 'description' => 'Saltos estándar recomendados por la red'],
            'hop_limit_warning' => ['value' => 6.0, 'unit' => 'hops', 'description' => 'Límite a partir del cual se genera aviso (Riesgo Bajo)'],
            'hop_limit_critical' => ['value' => 7.0, 'unit' => 'hops', 'description' => 'Límite a partir del cual se genera alerta grave (Riesgo Alto)'],
            'nodeinfo_interval_min_s' => ['value' => 43200.0, 'unit' => 'seconds', 'description' => 'Intervalo mínimo aceptable (menos es saturación)'],
            'nodeinfo_interval_recommended_s' => ['value' => 259200.0, 'unit' => 'seconds', 'description' => 'Intervalo recomendado en guías (72h)'],
            'telemetry_interval_min_s' => ['value' => 3600.0, 'unit' => 'seconds', 'description' => 'Intervalo mínimo admisible de telemetría (1h)'],
            'telemetry_interval_recommended_s' => ['value' => 7200.0, 'unit' => 'seconds', 'description' => 'Intervalo recomendado de telemetría (2h)'],
            'battery_warning_pct' => ['value' => 35.0, 'unit' => 'percent', 'description' => 'Umbral de batería para aviso de riesgo medio/bajo'],
            'battery_critical_pct' => ['value' => 15.0, 'unit' => 'percent', 'description' => 'Umbral de batería para alerta de riesgo alto'],
            'battery_resolution_pct' => ['value' => 45.0, 'unit' => 'percent', 'description' => 'Histéresis mínima de recarga para resolver alerta'],
            'sunset_battery_min_pct' => ['value' => 0.0, 'unit' => 'percent', 'description' => 'Carga mínima al anochecer (inactivo en clientes)'],
            'silence_warning_h' => ['value' => 0.0, 'unit' => 'hours', 'description' => 'Horas de silencio antes de alerta (inactivo en clientes)'],
            'silence_critical_h' => ['value' => 0.0, 'unit' => 'hours', 'description' => 'Horas de silencio para riesgo alto (inactivo en clientes)'],
            'reboot_loop_count_5m' => ['value' => 3.0, 'unit' => 'count', 'description' => 'Reinicios en 5 minutos para sospecha de bucle'],
            'reboot_loop_count_10m' => ['value' => 5.0, 'unit' => 'count', 'description' => 'Reinicios en 10 minutos para alerta crítica'],
        ],
        AlertThreshold::GROUP_CLIENT_MUTE => [
            'hop_limit_recommended' => ['value' => 3.0, 'unit' => 'hops', 'description' => 'Saltos estándar recomendados por la red'],
            'hop_limit_warning' => ['value' => 6.0, 'unit' => 'hops', 'description' => 'Límite a partir del cual se genera aviso (Riesgo Bajo)'],
            'hop_limit_critical' => ['value' => 7.0, 'unit' => 'hops', 'description' => 'Límite a partir del cual se genera alerta grave (Riesgo Alto)'],
            'nodeinfo_interval_min_s' => ['value' => 21600.0, 'unit' => 'seconds', 'description' => 'Intervalo mínimo aceptable (menos es saturación)'],
            'nodeinfo_interval_recommended_s' => ['value' => 86400.0, 'unit' => 'seconds', 'description' => 'Intervalo recomendado en guías (24h)'],
            'telemetry_interval_min_s' => ['value' => 1800.0, 'unit' => 'seconds', 'description' => 'Intervalo mínimo admisible de telemetría (30m)'],
            'telemetry_interval_recommended_s' => ['value' => 3600.0, 'unit' => 'seconds', 'description' => 'Intervalo recomendado de telemetría (1h)'],
            'battery_warning_pct' => ['value' => 25.0, 'unit' => 'percent', 'description' => 'Umbral de batería para aviso de riesgo medio/bajo'],
            'battery_critical_pct' => ['value' => 10.0, 'unit' => 'percent', 'description' => 'Umbral de batería para alerta de riesgo alto'],
            'battery_resolution_pct' => ['value' => 30.0, 'unit' => 'percent', 'description' => 'Histéresis mínima de recarga para resolver alerta'],
            'sunset_battery_min_pct' => ['value' => 0.0, 'unit' => 'percent', 'description' => 'Carga mínima al anochecer (inactivo en dispositivos mudos)'],
            'silence_warning_h' => ['value' => 0.0, 'unit' => 'hours', 'description' => 'Horas de silencio antes de alerta (inactivo en dispositivos mudos)'],
            'silence_critical_h' => ['value' => 0.0, 'unit' => 'hours', 'description' => 'Horas de silencio para riesgo alto (inactivo en dispositivos mudos)'],
            'reboot_loop_count_5m' => ['value' => 4.0, 'unit' => 'count', 'description' => 'Reinicios en 5 minutos para sospecha de bucle'],
            'reboot_loop_count_10m' => ['value' => 6.0, 'unit' => 'count', 'description' => 'Reinicios en 10 minutos para alerta crítica'],
        ],
    ];

    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        foreach (self::DEFAULTS as $group => $parameters) {
            foreach ($parameters as $parameter => $data) {
                AlertThreshold::updateOrCreate(
                    [
                        'role_group' => $group,
                        'parameter' => $parameter,
                    ],
                    [
                        'value_numeric' => $data['value'],
                        'unit' => $data['unit'],
                        'description' => $data['description'],
                    ]
                );
            }
        }
    }
}
