<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modelo para las sugerencias ciudadanas enviadas al portal.
 *
 * @property int $id
 * @property string $category
 * @property string $content
 * @property string $status
 * @property string|null $operator_notes
 * @property string|null $ip_hash
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Suggestion extends Model
{
    use HasFactory;

    public const CATEGORY_BOT_TELEGRAM = 'bot_telegram';

    public const CATEGORY_WEB = 'web';

    public const CATEGORY_MESHVIEW = 'meshview';

    public const CATEGORY_POTATO_MESH = 'potatomesh';

    public const CATEGORY_NEW_FEATURE = 'nueva_funcionalidad';

    public const CATEGORY_OTHER = 'otros';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Mapeo de categorías con sus etiquetas legibles en español.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        self::CATEGORY_BOT_TELEGRAM => 'Bot Telegram',
        self::CATEGORY_WEB => 'Web',
        self::CATEGORY_MESHVIEW => 'Meshview',
        self::CATEGORY_POTATO_MESH => 'Potato Mesh',
        self::CATEGORY_NEW_FEATURE => 'Nueva Funcionalidad',
        self::CATEGORY_OTHER => 'Otros',
    ];

    /**
     * Mapeo de estados con sus etiquetas en español.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_APPROVED => 'Aprobada',
        self::STATUS_REJECTED => 'Rechazada',
    ];

    /**
     * Atributos asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category',
        'content',
        'status',
        'operator_notes',
        'ip_hash',
    ];

    /**
     * Tipos nativos de las columnas del modelo.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => 'string',
            'content' => 'string',
            'status' => 'string',
            'operator_notes' => 'string',
            'ip_hash' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Devuelve la etiqueta legible de la categoría.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Devuelve la etiqueta legible del estado.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Scope para filtrar sugerencias pendientes.
     *
     * @param  Builder<Suggestion>  $query
     * @return Builder<Suggestion>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para filtrar sugerencias aprobadas.
     *
     * @param  Builder<Suggestion>  $query
     * @return Builder<Suggestion>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope para filtrar sugerencias rechazadas.
     *
     * @param  Builder<Suggestion>  $query
     * @return Builder<Suggestion>
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
