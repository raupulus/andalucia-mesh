<?php

declare(strict_types=1);

namespace App\Data;

class Saturacion
{
    /**
     * Calcula la saturación ponderada provincial y global a partir de las filas de api_province_load.
     *
     * @param  array<int, array{province: string, grupo: string, channel_utilization: float|int}>  $filas
     * @return array{
     *     andalucia_avg: float|null,
     *     andalucia_max: float|null,
     *     provinces: array<string, array{
     *         code: string,
     *         name: string,
     *         avg: float|null,
     *         max: float|null,
     *         level: string,
     *         groups: array{
     *             routers: array{avg: float|null, n: int},
     *             clients: array{avg: float|null, n: int}
     *         }
     *     }>
     * }
     */
    public static function calcular(array $filas): array
    {
        $pesoRouter = (float) config('proyecto.mapa.pesos.router', 0.6);
        $pesoCliente = (float) config('proyecto.mapa.pesos.cliente', 0.4);
        $verdeMax = (float) config('proyecto.mapa.carga.verde_max', 20.0);
        $rojoMin = (float) config('proyecto.mapa.carga.rojo_min', 40.0);

        $provinciasConfig = config('proyecto.provincias', [
            'ES-AL' => 'Almería',
            'ES-CA' => 'Cádiz',
            'ES-CO' => 'Córdoba',
            'ES-GR' => 'Granada',
            'ES-H' => 'Huelva',
            'ES-J' => 'Jaén',
            'ES-MA' => 'Málaga',
            'ES-SE' => 'Sevilla',
        ]);

        // Agrupar filas por provincia
        $porProvincia = [];
        foreach ($provinciasConfig as $code => $name) {
            $porProvincia[$code] = [
                'router' => [],
                'cliente' => [],
            ];
        }

        $todasRouters = [];
        $todasClientes = [];
        $maxGlobal = null;

        foreach ($filas as $fila) {
            $code = (string) $fila['province'];
            $grupo = (string) $fila['grupo'];
            $util = (float) $fila['channel_utilization'];

            if (! isset($porProvincia[$code])) {
                continue;
            }

            if ($grupo === 'router') {
                $porProvincia[$code]['router'][] = $util;
                $todasRouters[] = $util;
            } elseif ($grupo === 'cliente') {
                $porProvincia[$code]['cliente'][] = $util;
                $todasClientes[] = $util;
            }

            if ($maxGlobal === null || $util > $maxGlobal) {
                $maxGlobal = $util;
            }
        }

        // Calcular por cada provincia
        $resultadoProvincias = [];
        foreach ($provinciasConfig as $code => $name) {
            $routers = $porProvincia[$code]['router'];
            $clientes = $porProvincia[$code]['cliente'];

            $nR = count($routers);
            $nC = count($clientes);

            $avgR = $nR > 0 ? (array_sum($routers) / $nR) : null;
            $avgC = $nC > 0 ? (array_sum($clientes) / $nC) : null;

            $maxProv = null;
            $todosValores = array_merge($routers, $clientes);
            if (! empty($todosValores)) {
                $maxProv = max($todosValores);
            }

            $avgPonderada = null;
            if ($avgR !== null && $avgC !== null) {
                $avgPonderada = ($pesoRouter * $avgR) + ($pesoCliente * $avgC);
            } elseif ($avgR !== null) {
                $avgPonderada = $avgR;
            } elseif ($avgC !== null) {
                $avgPonderada = $avgC;
            }

            $level = 'nodata';
            if ($avgPonderada !== null) {
                if ($avgPonderada <= $verdeMax) {
                    $level = 'green';
                } elseif ($avgPonderada >= $rojoMin) {
                    $level = 'red';
                } else {
                    $level = 'orange';
                }
            }

            $resultadoProvincias[$code] = [
                'code' => $code,
                'name' => $name,
                'avg' => $avgPonderada !== null ? round($avgPonderada, 1) : null,
                'max' => $maxProv !== null ? round($maxProv, 1) : null,
                'level' => $level,
                'groups' => [
                    'routers' => [
                        'avg' => $avgR !== null ? round($avgR, 1) : null,
                        'n' => $nR,
                    ],
                    'clients' => [
                        'avg' => $avgC !== null ? round($avgC, 1) : null,
                        'n' => $nC,
                    ],
                ],
            ];
        }

        // Calcular global Andalucía
        $nRGlobal = count($todasRouters);
        $nCGlobal = count($todasClientes);
        $avgRGlobal = $nRGlobal > 0 ? (array_sum($todasRouters) / $nRGlobal) : null;
        $avgCGlobal = $nCGlobal > 0 ? (array_sum($todasClientes) / $nCGlobal) : null;

        $andaluciaAvg = null;
        if ($avgRGlobal !== null && $avgCGlobal !== null) {
            $andaluciaAvg = ($pesoRouter * $avgRGlobal) + ($pesoCliente * $avgCGlobal);
        } elseif ($avgRGlobal !== null) {
            $andaluciaAvg = $avgRGlobal;
        } elseif ($avgCGlobal !== null) {
            $andaluciaAvg = $avgCGlobal;
        }

        return [
            'andalucia_avg' => $andaluciaAvg !== null ? round($andaluciaAvg, 1) : null,
            'andalucia_max' => $maxGlobal !== null ? round($maxGlobal, 1) : null,
            'provinces' => $resultadoProvincias,
        ];
    }
}
