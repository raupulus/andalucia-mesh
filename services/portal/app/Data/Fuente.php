<?php

declare(strict_types=1);

namespace App\Data;

use App\Excepciones\FuenteNoDisponible;
use Closure;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Fuente
{
    /**
     * Ejecuta una consulta con caché de dos niveles (fresco y último respaldo).
     *
     * @param string $consulta Identificador semántico de la consulta
     * @param array<string, mixed> $params Parámetros normalizados
     * @param int $ttl Segundos de vigencia para el resultado fresco
     * @param Closure(): mixed $leer Función que ejecuta la consulta en la base de datos
     * @throws FuenteNoDisponible Si la base de datos falla y no existe respaldo
     */
    public static function recordar(string $consulta, array $params, int $ttl, Closure $leer): Resultado
    {
        ksort($params);
        $hash = sha1(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '');

        $keyFresco = "api1:{$consulta}:{$hash}:fresco";
        $keyUltimo = "api1:{$consulta}:{$hash}:ultimo";
        $keyFallo  = "api1:{$consulta}:{$hash}:fallo";

        // 1. Acierto en caché fresca
        $fresco = Cache::get($keyFresco);
        if ($fresco !== null && is_array($fresco) && isset($fresco['datos'], $fresco['generado_en'])) {
            return new Resultado(
                datos: $fresco['datos'],
                generadoEn: $fresco['generado_en'],
                stale: false,
                xCache: 'HIT',
                ttl: $ttl,
            );
        }

        // 2. Si el disyuntor de fallo está activo (10 s), intentar servir inmediatamente el último respaldo
        if (Cache::has($keyFallo)) {
            $ultimo = Cache::get($keyUltimo);
            if ($ultimo !== null && is_array($ultimo) && isset($ultimo['datos'], $ultimo['generado_en'])) {
                return new Resultado(
                    datos: $ultimo['datos'],
                    generadoEn: $ultimo['generado_en'],
                    stale: true,
                    xCache: 'STALE',
                    ttl: 15,
                );
            }
            throw new FuenteNoDisponible("La fuente de datos para {$consulta} se encuentra en estado de fallo temporal.");
        }

        // 3. Ejecutar lectura
        try {
            $datos = $leer();
            $generadoEn = gmdate('Y-m-d\TH:i:s\Z');

            $payload = [
                'datos' => $datos,
                'generado_en' => $generadoEn,
            ];

            // Almacenar copia fresca y copia de respaldo duradera (24 horas)
            Cache::put($keyFresco, $payload, $ttl);
            Cache::put($keyUltimo, $payload, 86400);

            return new Resultado(
                datos: $datos,
                generadoEn: $generadoEn,
                stale: false,
                xCache: 'MISS',
                ttl: $ttl,
            );
        } catch (Throwable $e) {
            // Activar disyuntor de fallo durante 10 segundos para no martillear la base
            Cache::put($keyFallo, true, 10);

            // Intentar recuperar el último respaldo
            $ultimo = Cache::get($keyUltimo);
            if ($ultimo !== null && is_array($ultimo) && isset($ultimo['datos'], $ultimo['generado_en'])) {
                return new Resultado(
                    datos: $ultimo['datos'],
                    generadoEn: $ultimo['generado_en'],
                    stale: true,
                    xCache: 'STALE',
                    ttl: 15,
                );
            }

            throw new FuenteNoDisponible("Error consultando {$consulta}: {$e->getMessage()}");
        }
    }
}
