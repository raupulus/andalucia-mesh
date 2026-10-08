<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Ingest\Provincias;
use App\Models\CustomPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class PortadaController extends Controller
{
    /**
     * Muestra la portada principal de Andalucía Mesh.
     */
    public function __invoke(Request $request): View
    {
        $ventanaParam = (string) $request->query('ventana', '30m');
        $ventanasValidas = ['30m', '1h', '6h', '12h', '1d', '24h', '7d'];
        $ventana = in_array($ventanaParam, $ventanasValidas, true) ? $ventanaParam : '30m';

        $datosMapa = [
            'generated_at' => now()->toIso8601String(),
            'stale' => false,
            'total_andalucia' => 0,
            'andalucia_avg' => null,
            'outside_andalucia' => 0,
            'provinces' => [],
        ];

        // Intentar obtener datos reales si el servicio de provincias está disponible
        if (class_exists(Provincias::class)) {
            try {
                /** @var Provincias $servicio */
                $servicio = app(Provincias::class);
                $datosMapa = $servicio->obtener($ventana);
            } catch (Throwable) {
                // En caso de caída de base de datos o durante tests sin BD, usamos el fallback
            }
        }

        $tarjetas = config('proyecto.tarjetas', []);

        $ultimasPaginas = collect();
        try {
            $ultimasPaginas = CustomPage::active()
                ->recent()
                ->limit(4)
                ->get();
        } catch (Throwable) {
            // Fallback silencioso en caso de pruebas sin migraciones
        }

        return view('portada', [
            'tarjetas' => $tarjetas,
            'datosMapa' => $datosMapa,
            'ventana' => $ventana,
            'ultimasPaginas' => $ultimasPaginas,
        ]);
    }
}
