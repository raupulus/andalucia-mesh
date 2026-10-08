<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo para los artículos, placas y componentes de hardware recomendados para la red.
 *
 * @property int $id
 * @property int $category_id
 * @property string $slug
 * @property mixed $name
 * @property array<string, string> $description
 * @property string $image_path
 * @property string $buy_url
 * @property string|null $guide_url
 * @property string|null $last_price
 * @property string $currency
 * @property bool $is_featured
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read HardwareCategory|null $category
 * @property-read string $translated_name
 * @property-read string $translated_description
 * @property-read string $image_url
 * @property-read string|null $formatted_price
 */
class HardwareItem extends Model
{
    use HasFactory;

    /**
     * Campos asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'slug',
        'name',
        'description',
        'image_path',
        'buy_url',
        'guide_url',
        'last_price',
        'currency',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    /**
     * Conversión nativa de tipos de datos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'description' => 'array',
            'last_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Relación con la categoría a la que pertenece el artículo.
     *
     * @return BelongsTo<HardwareCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(HardwareCategory::class, 'category_id');
    }

    /**
     * Scope para filtrar únicamente artículos activos.
     *
     * @param  Builder<HardwareItem>  $query
     * @return Builder<HardwareItem>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar únicamente artículos destacados.
     *
     * @param  Builder<HardwareItem>  $query
     * @return Builder<HardwareItem>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope para ordenar los artículos: destacados primero, luego sort_order ascendente y por último fecha descendente.
     *
     * @param  Builder<HardwareItem>  $query
     * @return Builder<HardwareItem>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order', 'asc')
            ->orderByDesc('created_at');
    }

    /**
     * Mutador de nombre: permite almacenar tanto arrays con claves de idioma como cadenas simples en JSON.
     */
    public function setNameAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['name'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->attributes['name'] = $value;
            } else {
                $this->attributes['name'] = json_encode(['es' => $value, 'en' => $value], JSON_UNESCAPED_UNICODE);
            }
        } else {
            $this->attributes['name'] = json_encode([], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Accesor para obtener el nombre localizado según el idioma activo con fallback a español.
     */
    public function getTranslatedNameAttribute(): string
    {
        $raw = $this->attributes['name'] ?? null;
        if (empty($raw)) {
            return '';
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (is_array($decoded)) {
            $locale = app()->getLocale();

            return (string) ($decoded[$locale] ?? $decoded['es'] ?? (reset($decoded) ?: ''));
        }

        return (string) $raw;
    }

    /**
     * Accesor para obtener el nombre (coherente con translated_name).
     */
    public function getNameAttribute(mixed $value): string
    {
        if (empty($value)) {
            return '';
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($decoded)) {
            $locale = app()->getLocale();

            return (string) ($decoded[$locale] ?? $decoded['es'] ?? (reset($decoded) ?: ''));
        }

        return (string) $value;
    }

    /**
     * Accesor para obtener la descripción localizada según el idioma activo con fallback a español.
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        $desc = $this->description;

        if (is_array($desc)) {
            return (string) ($desc[$locale] ?? $desc['es'] ?? (reset($desc) ?: ''));
        }

        return is_string($desc) ? $desc : '';
    }

    /**
     * Accesor para obtener la URL pública de la imagen del producto.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return asset('img/logo.png');
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Accesor para formatear el último precio con símbolo de divisa o nulo si no está fijado.
     */
    public function getFormattedPriceAttribute(): ?string
    {
        if ($this->last_price === null || $this->last_price === '') {
            return null;
        }

        return number_format((float) $this->last_price, 2, ',', '.').' '.($this->currency === 'EUR' ? '€' : $this->currency);
    }
}
