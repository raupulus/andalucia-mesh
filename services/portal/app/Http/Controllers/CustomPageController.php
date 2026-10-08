<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CustomPage;
use App\Servicios\ContenidoMarkdown;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controlador para la visualización pública de páginas y artículos dinámicos del portal.
 */
class CustomPageController extends Controller
{
    /**
     * Muestra el listado público de todas las páginas activas en formato de tarjetas horizontales.
     */
    public function index(Request $request): View
    {
        $paginas = CustomPage::active()
            ->recent()
            ->get();

        return view('paginas.index', [
            'paginas' => $paginas,
        ]);
    }

    /**
     * Muestra el detalle completo de una página individual a partir de su slug.
     */
    public function show(string $slug, ContenidoMarkdown $markdown): View
    {
        $pagina = CustomPage::active()
            ->where('slug', $slug)
            ->first();

        if (! $pagina) {
            throw new NotFoundHttpException("La página '{$slug}' no existe o no está publicada.");
        }

        $contenidoHtml = $markdown->convertText($pagina->content);

        return view('paginas.show', [
            'pagina' => $pagina,
            'contenidoHtml' => $contenidoHtml,
        ]);
    }
}
