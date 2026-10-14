<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controlador para la sección de Herramientas y Calculadoras Técnicas de Meshtastic.
 *
 * Ofrece el catálogo de utilidades (/herramientas) y herramientas especializadas
 * como la calculadora interactiva de divisor de tensión y calibración del adc_multiplier
 * para baterías de litio (/herramientas/calculadora-bateria-adc).
 */
class HerramientasController extends Controller
{
    /**
     * Muestra el catálogo de herramientas disponibles para la red y nodos Meshtastic.
     */
    public function index(): View
    {
        $herramientas = [
            [
                'id' => 'configurador',
                'titulo' => __('portal.tools.items.configurator.title'),
                'descripcion' => __('portal.tools.items.configurator.desc'),
                'url' => '/configurador',
                'imagen' => asset('img/herramientas/configurador.webp'),
                'badge' => __('portal.tools.badge_interactive'),
                'destacado' => true,
            ],
            [
                'id' => 'calculadora-bateria',
                'titulo' => __('portal.tools.items.battery_calc.title'),
                'descripcion' => __('portal.tools.items.battery_calc.desc'),
                'url' => '/herramientas/calculadora-bateria-adc',
                'imagen' => asset('img/herramientas/calculadora-bateria.webp'),
                'badge' => __('portal.tools.badge_hardware'),
                'destacado' => false,
            ],
        ];

        return view('herramientas.index', [
            'herramientas' => $herramientas,
        ]);
    }

    /**
     * Muestra la calculadora técnica para el divisor de tensión y adc_multiplier de batería.
     */
    public function calculadoraBateria(): View
    {
        $recomendaciones = [
            [
                'id' => 'par-1m-1m',
                'nombre' => '1 MΩ + 1 MΩ',
                'tipo' => __('portal.tools.battery.rec_1_tag'),
                'r1' => 1000000,
                'r2' => 1000000,
                'r1_formato' => '1 MΩ',
                'r2_formato' => '1 MΩ',
                'ratio_teorico' => 2.00,
                'ratio_recomendado' => 2.05,
                'voltaje_pin_42' => 2.10,
                'corriente_microamperios' => 2.1,
                'descripcion' => __('portal.tools.battery.rec_1_desc'),
            ],
            [
                'id' => 'par-1m-15m',
                'nombre' => '1 MΩ + 1.5 MΩ',
                'tipo' => __('portal.tools.battery.rec_2_tag'),
                'r1' => 1000000,
                'r2' => 1500000,
                'r1_formato' => '1 MΩ',
                'r2_formato' => '1.5 MΩ',
                'ratio_teorico' => 1.67,
                'ratio_recomendado' => 1.67,
                'voltaje_pin_42' => 2.52,
                'corriente_microamperios' => 1.68,
                'descripcion' => __('portal.tools.battery.rec_2_desc'),
            ],
        ];

        return view('herramientas.calculadora-bateria', [
            'recomendaciones' => $recomendaciones,
        ]);
    }
}
