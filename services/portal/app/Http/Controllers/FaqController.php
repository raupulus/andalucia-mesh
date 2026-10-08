<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Servicios\ContenidoMarkdown;
use Illuminate\View\View;

/**
 * Controlador de la página pública de preguntas frecuentes (FAQ).
 */
class FaqController extends Controller
{
    /**
     * Muestra la lista de preguntas frecuentes activas y ordenadas por prioridad.
     */
    public function index(ContenidoMarkdown $markdown): View
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $faqItems = $faqs->map(function (Faq $faq) use ($markdown): array {
            return [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'answer_html' => $markdown->convertText($faq->answer),
            ];
        });

        return view('faq', [
            'faqs' => $faqItems,
        ]);
    }
}
