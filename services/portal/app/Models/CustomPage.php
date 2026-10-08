<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo para las páginas y artículos dinámicos del portal.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $content
 * @property array<int, string>|null $keywords
 * @property string|null $featured_image
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $cover_image_url
 * @property-read string $formatted_date
 */
class CustomPage extends Model
{
    use HasFactory;

    /**
     * Campos asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'keywords',
        'featured_image',
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
            'keywords' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope para filtrar únicamente páginas activas y publicadas.
     *
     * @param  Builder<CustomPage>  $query
     * @return Builder<CustomPage>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por fecha de publicación descendente.
     *
     * @param  Builder<CustomPage>  $query
     * @return Builder<CustomPage>
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Obtiene la URL completa de la imagen de portada o un fallback seguro.
     */
    public function getCoverImageUrlAttribute(): string
    {
        if (empty($this->featured_image)) {
            return asset('img/revisa-nodo-banner.webp');
        }

        if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
            return $this->featured_image;
        }

        if (str_starts_with($this->featured_image, '/')) {
            return asset(ltrim($this->featured_image, '/'));
        }

        return Storage::disk('public')->url($this->featured_image);
    }

    /**
     * Obtiene la fecha en formato estándar europeo legible.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->locale(app()->getLocale())->isoFormat('LL');
    }
}
