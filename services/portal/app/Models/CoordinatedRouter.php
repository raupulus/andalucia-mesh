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
 * @property string $status
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
     * Estados de coordinación y gobernanza del router en la malla.
     */
    public const STATUS_MANAGED = 'managed';

    public const STATUS_KNOWN = 'known';

    public const STATUS_NEW = 'new';

    /**
     * Catálogo legible de estados.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        self::STATUS_MANAGED => 'Gestionado',
        self::STATUS_KNOWN => 'Conocido',
        self::STATUS_NEW => 'Nuevo',
    ];

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
        'status',
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
            'status' => 'string',
            'approved' => 'boolean',
            'is_gateway' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Evento de arranque del modelo: asegura consistencia entre status y approved.
     */
    protected static function booted(): void
    {
        static::saving(function (self $router): void {
            // Asegurar que el booleano approved se mantenga sincronizado con el estado 'managed'
            if (empty($router->status)) {
                $router->status = self::STATUS_NEW;
            }
            $router->approved = ($router->status === self::STATUS_MANAGED);
        });
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
     * Scope para filtrar routers gestionados (aprobados y coordinados oficialmente).
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopeManaged(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MANAGED);
    }

    /**
     * Scope para filtrar routers conocidos (asumidos pero sin gestión directa).
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopeKnown(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_KNOWN);
    }

    /**
     * Scope para filtrar routers nuevos detectados pendientes de revisión.
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope para filtrar únicamente routers aprobados por la coordinación (compatibilidad).
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $this->scopeManaged($query);
    }

    /**
     * Scope para filtrar routers no gestionados / pendientes de aprobación.
     *
     * @param  Builder<CoordinatedRouter>  $query
     * @return Builder<CoordinatedRouter>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_MANAGED);
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
     * Determina si el router está formalmente gestionado/aprobado.
     */
    public function isManaged(): bool
    {
        return $this->status === self::STATUS_MANAGED;
    }

    /**
     * Determina si el router es conocido (asumido pero sin control directo).
     */
    public function isKnown(): bool
    {
        return $this->status === self::STATUS_KNOWN;
    }

    /**
     * Determina si el router es nuevo detectado.
     */
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * Determina si el router está aprobado (alias de isManaged).
     */
    public function isApproved(): bool
    {
        return $this->isManaged();
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

    /**
     * Retorna la etiqueta legible del estado actual.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
