<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modelo para la representación y caché local de destinos de webhooks gestionados dinámicamente.
 *
 * @property int $id
 * @property string $nombre
 * @property string $host
 * @property string $url
 * @property bool $activo
 * @property string|null $motivo_baja
 * @property int $fallos_seguidos
 * @property Carbon|null $ultimo_ok
 * @property int $pendientes
 * @property array<string>|null $riesgos
 * @property array<string>|null $tipos
 * @property array<string>|null $provincias
 * @property array<string>|null $nodos
 * @property string|null $secreto
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WebhookDestination extends Model
{
    use HasFactory;

    protected $table = 'webhook_destinations';

    protected $fillable = [
        'nombre',
        'host',
        'url',
        'activo',
        'motivo_baja',
        'fallos_seguidos',
        'ultimo_ok',
        'pendientes',
        'riesgos',
        'tipos',
        'provincias',
        'nodos',
        'secreto',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fallos_seguidos' => 'integer',
            'pendientes' => 'integer',
            'ultimo_ok' => 'datetime',
            'riesgos' => 'array',
            'tipos' => 'array',
            'provincias' => 'array',
            'nodos' => 'array',
        ];
    }
}
