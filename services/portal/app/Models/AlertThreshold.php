<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modelo para la persistencia de umbrales dinámicos de alertas y diagnósticos.
 *
 * @property int $id
 * @property string $role_group
 * @property string $parameter
 * @property float $value_numeric
 * @property string $unit
 * @property string|null $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Builder<AlertThreshold> group(string $group)
 */
class AlertThreshold extends Model
{
    use HasFactory;

    public const GROUP_ROUTER = 'router';

    public const GROUP_CLIENT = 'client';

    public const GROUP_CLIENT_MUTE = 'client_mute';

    /**
     * Grupos canónicos de roles en la red.
     *
     * @var list<string>
     */
    public const GROUPS = [
        self::GROUP_ROUTER,
        self::GROUP_CLIENT,
        self::GROUP_CLIENT_MUTE,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'role_group',
        'parameter',
        'value_numeric',
        'unit',
        'description',
    ];

    /**
     * Conversión nativa de tipos de datos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value_numeric' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope para filtrar parámetros por grupo de roles.
     *
     * @param  Builder<AlertThreshold>  $query
     * @return Builder<AlertThreshold>
     */
    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('role_group', $group);
    }
}
