<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Modelo para las categorías de hardware recomendado para la malla.
 *
 * @property int $id
 * @property string $slug
 * @property array<string, string> $name
 * @property array<string, string>|null $description
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $translated_name
 * @property-read string|null $translated_description
 */
class HardwareCategory extends Model
{
    use HasFactory;

    /**
     * Campos asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    /**
     * Conversión nativa de tipos de datos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Relación con los artículos y componentes pertenecientes a esta categoría.
     *
     * @return HasMany<HardwareItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(HardwareItem::class, 'category_id');
    }

    /**
     * Scope para filtrar únicamente categorías activas.
     *
     * @param  Builder<HardwareCategory>  $query
     * @return Builder<HardwareCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar las categorías por su orden explícito y secundariamente por id.
     *
     * @param  Builder<HardwareCategory>  $query
     * @return Builder<HardwareCategory>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Accesor para obtener el nombre en el idioma actual (fallback a 'es').
     */
    public function getTranslatedNameAttribute(): string
    {
        $locale = app()->getLocale();
        $names = $this->name;

        if (is_array($names)) {
            return (string) ($names[$locale] ?? $names['es'] ?? (reset($names) ?: ''));
        }

        return is_string($names) ? $names : '';
    }

    /**
     * Accesor para obtener la descripción en el idioma actual (fallback a 'es').
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        $descriptions = $this->description;

        if (is_array($descriptions)) {
            $val = $descriptions[$locale] ?? $descriptions['es'] ?? (reset($descriptions) ?: null);

            return $val !== null ? (string) $val : null;
        }

        return is_string($descriptions) ? $descriptions : null;
    }

    /**
     * Asigna automáticamente el siguiente orden disponible al crear una nueva categoría si no se define.
     */
    protected static function booted(): void
    {
        static::creating(function (HardwareCategory $category): void {
            if ($category->sort_order === null || $category->sort_order === 0) {
                $maxOrder = static::query()->max('sort_order');
                $category->sort_order = $maxOrder !== null ? $maxOrder + 1 : 1;
            }
        });
    }
}
