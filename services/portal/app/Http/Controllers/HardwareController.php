<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HardwareCategory;
use App\Models\HardwareItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Controlador para el catálogo público interactivo de hardware recomendado en la red mesh.
 */
class HardwareController extends Controller
{
    /**
     * Muestra la página del catálogo con filtrado por categorías y buscador textual.
     */
    public function index(Request $request): View
    {
        $categoriaSlug = $request->query('categoria') ?: $request->query('category');
        $busqueda = trim((string) ($request->query('q') ?? ''));

        // Recupera categorías activas ordenadas por sort_order, con recuento de artículos activos
        $categorias = HardwareCategory::query()
            ->active()
            ->ordered()
            ->withCount(['items' => fn (Builder $query) => $query->active()])
            ->get();

        // Consulta de artículos activos con su categoría cargada
        $itemsQuery = HardwareItem::query()
            ->active()
            ->with('category');

        // Filtrado por categoría seleccionada
        $categoriaSeleccionada = null;
        if (! empty($categoriaSlug) && is_string($categoriaSlug)) {
            $categoriaSeleccionada = $categorias->firstWhere('slug', $categoriaSlug);
            if ($categoriaSeleccionada) {
                $itemsQuery->where('category_id', $categoriaSeleccionada->id);
            }
        }

        // Búsqueda textual opcional sobre nombre y descripción
        if ($busqueda !== '') {
            $termino = '%'.mb_strtolower($busqueda).'%';
            $itemsQuery->where(function (Builder $query) use ($termino): void {
                $query->whereRaw('LOWER(CAST(name AS TEXT)) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(CAST(description AS TEXT)) LIKE ?', [$termino]);
            });
        }

        // Orden: destacados primero, luego sort_order, y por último created_at desc
        $articulos = $itemsQuery
            ->ordered()
            ->get();

        $totalArticulosActivos = (int) $categorias->sum('items_count');

        return view('paginas.hardware', [
            'categorias' => $categorias,
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'articulos' => $articulos,
            'busqueda' => $busqueda,
            'slugActual' => $categoriaSeleccionada?->slug,
            'totalArticulos' => $totalArticulosActivos,
        ]);
    }
}
