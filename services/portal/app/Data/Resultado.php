<?php

declare(strict_types=1);

namespace App\Data;

class Resultado
{
    /**
     * @param  mixed  $datos  Contenido devuelto por la consulta o respaldo
     * @param  string  $generadoEn  Fecha ISO 8601 UTC en que se originó el dato
     * @param  bool  $stale  True si el dato proviene de una copia de respaldo ante fallo
     * @param  string  $xCache  Estado de la caché ('HIT', 'MISS', 'STALE')
     * @param  int  $ttl  Segundos recomendados de caché
     */
    public function __construct(
        public mixed $datos,
        public string $generadoEn,
        public bool $stale = false,
        public string $xCache = 'MISS',
        public int $ttl = 60,
    ) {}

    /**
     * Devuelve el array formateado para la respuesta JSON.
     *
     * @return array<string, mixed>
     */
    public function aRespuesta(): array
    {
        if (is_array($this->datos)) {
            return array_merge([
                'generated_at' => $this->generadoEn,
                'stale' => $this->stale,
            ], $this->datos);
        }

        return [
            'generated_at' => $this->generadoEn,
            'stale' => $this->stale,
            'data' => $this->datos,
        ];
    }
}
