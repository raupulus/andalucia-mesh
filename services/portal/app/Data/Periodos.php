<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class Periodos
{
    /**
     * Calcula los límites temporales en UTC para las consultas de estadísticas y rankings.
     *
     * @param string $period Granularidad ('hour', 'day', 'week', 'month')
     * @param string $which Momento ('current', 'previous')
     * @return array{
     *     granularity: string,
     *     bucket_start: string,
     *     from: string,
     *     to: string,
     *     partial: bool,
     *     ttl: int
     * }
     */
    public static function rango(string $period, string $which = 'current'): array
    {
        $tz = (string) config('proyecto.zona_horaria', 'Europe/Madrid');
        $ahora = CarbonImmutable::now($tz);

        $validPeriods = ['hour', 'day', 'week', 'month'];
        if (!in_array($period, $validPeriods, true)) {
            throw new InvalidArgumentException("Periodo inválido '{$period}'. Usa hour, day, week o month.");
        }

        if (!in_array($which, ['current', 'previous'], true)) {
            throw new InvalidArgumentException("Momento inválido '{$which}'. Usa current o previous.");
        }

        $isCurrent = $which === 'current';

        switch ($period) {
            case 'hour':
                $start = $isCurrent ? $ahora->startOfHour() : $ahora->subHour()->startOfHour();
                $end = $start->addHour();
                $ttl = $isCurrent ? 60 : 3600;
                break;

            case 'day':
                $start = $isCurrent ? $ahora->startOfDay() : $ahora->subDay()->startOfDay();
                $end = $start->addDay();
                $ttl = $isCurrent ? 300 : 3600;
                break;

            case 'week':
                $start = $isCurrent ? $ahora->startOfWeek() : $ahora->subWeek()->startOfWeek();
                $end = $start->addWeek();
                $ttl = $isCurrent ? 900 : 3600;
                break;

            case 'month':
                $start = $isCurrent ? $ahora->startOfMonth() : $ahora->subMonth()->startOfMonth();
                $end = $start->addMonth();
                $ttl = $isCurrent ? 900 : 3600;
                break;

            default:
                throw new InvalidArgumentException("Periodo no soportado");
        }

        $fromUtc = $start->setTimezone('UTC');
        $toUtc = $end->setTimezone('UTC');

        return [
            'granularity' => $period,
            'bucket_start' => $fromUtc->toIso8601String(),
            'from' => $fromUtc->toIso8601String(),
            'to' => $toUtc->toIso8601String(),
            'partial' => $isCurrent,
            'ttl' => $ttl,
        ];
    }
}
