<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modelo Eloquent para las preguntas frecuentes (FAQ) del portal.
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    /**
     * Tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'faqs';

    /**
     * Atributos asignables de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'question',
        'answer',
        'is_active',
        'sort_order',
    ];

    /**
     * Conversión nativa de tipos de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'question' => 'array',
            'answer' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Codifica a JSON preservando caracteres UTF-8 sin escapar (acentos, signos de interrogación).
     *
     * @param  mixed  $value
     * @param  int  $flags
     * @return string|false
     */
    protected function asJson($value, $flags = 0)
    {
        return json_encode($value, $flags | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Mutador para asegurar que la pregunta se almacena como JSON válido tanto si recibe un array como un string.
     */
    public function setQuestionAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['question'] = json_encode(
                is_array($decoded) ? $decoded : ['es' => $value],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            return;
        }

        $this->attributes['question'] = json_encode(
            is_array($value) ? $value : [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Accesor para asegurar que la pregunta siempre se devuelve como array asociativo por idioma.
     *
     * @return array<string, string>
     */
    public function getQuestionAttribute(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return ['es' => $value];
        }

        return [];
    }

    /**
     * Mutador para asegurar que la respuesta se almacena como JSON válido tanto si recibe un array como un string.
     */
    public function setAnswerAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['answer'] = json_encode(
                is_array($decoded) ? $decoded : ['es' => $value],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            return;
        }

        $this->attributes['answer'] = json_encode(
            is_array($value) ? $value : [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Accesor para asegurar que la respuesta siempre se devuelve como array asociativo por idioma.
     *
     * @return array<string, string>
     */
    public function getAnswerAttribute(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return ['es' => $value];
        }

        return [];
    }

    /**
     * Obtiene la pregunta traducida según el locale especificado (o el actual de la app).
     * Si no existe traducción en dicho idioma, realiza fallback a español ('es') o al primer idioma disponible.
     */
    public function getTranslatedQuestion(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $questions = $this->question;

        if (is_array($questions)) {
            $val = $questions[$locale] ?? null;
            if (is_string($val) && trim($val) !== '') {
                return trim($val);
            }

            $fallback = $questions['es'] ?? (reset($questions) ?: '');

            return is_string($fallback) ? trim($fallback) : '';
        }

        return is_string($questions) ? trim($questions) : '';
    }

    /**
     * Obtiene la respuesta traducida según el locale especificado (o el actual de la app).
     * Si no existe traducción en dicho idioma, realiza fallback a español ('es') o al primer idioma disponible.
     */
    public function getTranslatedAnswer(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $answers = $this->answer;

        if (is_array($answers)) {
            $val = $answers[$locale] ?? null;
            if (is_string($val) && trim($val) !== '') {
                return trim($val);
            }

            $fallback = $answers['es'] ?? (reset($answers) ?: '');

            return is_string($fallback) ? trim($fallback) : '';
        }

        return is_string($answers) ? trim($answers) : '';
    }

    /**
     * Accesor para translated_question.
     */
    public function getTranslatedQuestionAttribute(): string
    {
        return $this->getTranslatedQuestion();
    }

    /**
     * Accesor para translated_answer.
     */
    public function getTranslatedAnswerAttribute(): string
    {
        return $this->getTranslatedAnswer();
    }

    /**
     * Asigna automáticamente el siguiente orden disponible al crear un nuevo registro si no se define.
     */
    protected static function booted(): void
    {
        static::creating(function (Faq $faq): void {
            if ($faq->sort_order === null || $faq->sort_order === 0) {
                $maxOrder = static::max('sort_order');
                $faq->sort_order = $maxOrder !== null ? $maxOrder + 1 : 1;
            }
        });
    }
}
