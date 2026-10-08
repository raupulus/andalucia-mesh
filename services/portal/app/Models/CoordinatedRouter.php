<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modelo para la gestión y coordinación oficial de routers de Andalucía Mesh.
 *
 * @property int $id
 * @property string $node_id
 * @property string|null $short_name
 * @property string|null $long_name
 * @property string $province
 * @property string $role
 * @property bool $approved
 * @property bool $is_gateway
 * @property string|null $hw_model
 * @property string|null $notes
 * @property Carbon|null $last_seen_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CoordinatedRouter extends Model
{
    use HasFactory;

    /**
     * Provincias oficiales de la comunidad de Andalucía conforme a ISO 3166-2:ES.
     *
     * @var array<string, string>
     */
    public const PROVINCES = [
        'ES-AL' => 'Almería',
        'ES-CA' => 'Cádiz',
        'ES-CO' => 'Córdoba',
        'ES-GR' => 'Granada',
        'ES-H' => 'Huelva',
        'ES-J' => 'Jaén',
        'ES-MA' => 'Málaga',
        'ES-SE' => 'Sevilla',
    ];

    /**
     * Campos asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'node_id',
        'short_name',
        'long_name',
        'province',
        'role',
        'approved',
        'is_gateway',
        'hw_model',
        'notes',
        'last_seen_at',
    ];

    /**
     * Conversión nativa de tipos de datos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved' => 'boolean',
            'is_gateway' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Obtiene el nombre legible de una provincia andaluza a partir de su código ISO.
     */
    public static function provinceName(?string $code): string
    {
        if ($code === null) {
            return 'Desconocida';
        }

        return self::PROVINCES[strtoupper($code)] ?? $code;
    }

    /**
     * Scope para filtrar únicamente routers dentro de Andalucía (descarta nodos de fuera).
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopeAndalucia(Builder $query): Builder
    {
        return $query->whereIn('province', array_keys(self::PROVINCES));
    }

    /**
     * Scope para filtrar únicamente routers aprobados por la coordinación.
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approved', true);
    }

    /**
     * Scope para filtrar routers pendientes de aprobación.
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('approved', false);
    }

    /**
     * Scope para filtrar routers por código de provincia.
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopeInProvince(Builder $query, string $province): Builder
    {
        return $query->where('province', strtoupper($province));
    }

    /**
     * Determina si el router está formalmente aprobado.
     */
    public function isApproved(): bool
    {
        return (bool) $this->approved;
    }

    /**
     * Determina si el router se ubica geográficamente dentro de Andalucía.
     */
    public function isInAndalucia(): bool
    {
        return isset(self::PROVINCES[strtoupper($this->province)]);
    }

    /**
     * Retorna el nombre en español de la provincia del nodo.
     */
    public function getProvinceNameAttribute(): string
    {
        return self::provinceName($this->province);
    }
}
