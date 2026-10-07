<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Datos\Ingesta\Provincias;
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
        $ventana = $request->query('ventana') === '24h' ? '24h' : '7d';

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

        return view('portada', [
            'tarjetas' => $tarjetas,
            'datosMapa' => $datosMapa,
            'ventana' => $ventana,
        ]);
    }
}
