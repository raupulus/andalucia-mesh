<?php

declare(strict_types=1);

namespace App\Excepciones;

use RuntimeException;

class FuenteNoDisponible extends RuntimeException
{
    public function __construct(string $mensaje = 'La fuente de datos requerida no está disponible y no existe copia de respaldo.')
    {
        parent::__construct($mensaje);
    }
}
