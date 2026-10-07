{{--
    Componente: x-mapa-provincias
    Mapa interactivo vectorial de Andalucía y tabla accesible con cálculo de saturación.
--}}
@props([
    'datos' => null, // Array o colección con estadísticas por provincia
    'ventana' => '7d',
])

@php
    $provinciasConfig = config('proyecto.provincias', []);
    $burbujasConfig = config('proyecto.mapa.burbujas', []);

    // Indexar datos recibidos por código de provincia
    $mapaDatos = [];
    if (!empty($datos['provinces'])) {
        foreach ($datos['provinces'] as $p) {
            $mapaDatos[$p['code']] = $p;
        }
    }

    $totalAndalucia = $datos['total_andalucia'] ?? 0;
    $saturacionAndalucia = $datos['andalucia_avg'] ?? null;
    $fueraAndalucia = $datos['outside_andalucia'] ?? 0;
    $actualizado = !empty($datos['generated_at']) ? \Carbon\Carbon::parse($datos['generated_at'])->diffForHumans() : 'recientemente';
@endphp

<div class="mapa-contenedor-completo" id="contenedor-mapa-andalucia">
    <!-- Encabezado de estadísticas del mapa -->
    <div class="mapa-cabecera" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; gap: 1rem;">
        <div>
            <div style="font-size: 2.25rem; font-weight: 700; color: var(--color-texto-1); line-height: 1.1;" id="mapa-total-nodos">
                {{ number_format((int) $totalAndalucia, 0, ',', '.') }}
            </div>
            <div style="font-size: 1.1rem; color: var(--color-texto-2); font-weight: 500;">
                nodos activos en Andalucía
            </div>
            <div style="font-size: 0.85rem; color: var(--color-texto-3); margin-top: 0.25rem;" id="mapa-actualizado-texto">
                <span id="mapa-ventana-label">{{ $ventana === '24h' ? 'Últimas 24 horas' : 'Últimos 7 días' }}</span> · actualizado {{ $actualizado }}
            </div>
        </div>

        <!-- Selector accesible de ventana temporal -->
        <div class="selector-ventana" role="group" aria-label="Ventana del recuento de nodos" style="display: flex; background: var(--color-superficie-sutil); padding: 4px; border-radius: var(--radio-sm); border: 1px solid var(--color-borde);">
            <button type="button" 
                    class="btn-ventana {{ $ventana === '7d' ? 'activo' : '' }}" 
                    data-ventana="7d"
                    aria-pressed="{{ $ventana === '7d' ? 'true' : 'false' }}">
                7 días
            </button>
            <button type="button" 
                    class="btn-ventana {{ $ventana === '24h' ? 'activo' : '' }}" 
                    data-ventana="24h"
                    aria-pressed="{{ $ventana === '24h' ? 'true' : 'false' }}">
                24 horas
            </button>
        </div>
    </div>

    <!-- Contenedor del Mapa SVG con Tooltip Emergente -->
    <div style="position: relative; width: 100%; border-radius: var(--radio-md); border: 1px solid var(--color-borde); background: var(--color-superficie); padding: 1rem; overflow: hidden;">
        
        <svg viewBox="0 0 1000 620" 
             class="mapa-andalucia-svg" 
             role="region" 
             aria-label="Mapa de saturación del canal y recuento de nodos por provincia en Andalucía"
             style="width: 100%; height: auto; display: block;">
            
            <defs>
                <!-- Trama diagonal accesible para provincias sin datos de telemetría -->
                <pattern id="trama-sin-datos" width="10" height="10" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                    <rect width="10" height="10" fill="var(--color-mapa-gris)" />
                    <line x1="0" y1="0" x2="0" y2="10" stroke="rgba(255,255,255,0.4)" stroke-width="3" />
                </pattern>
            </defs>

            <!-- Trazados provinciales -->
            <g class="capa-provincias">
                <!-- Almería (ES-AL) -->
                <g class="provincia-grupo" id="prov-ES-AL" data-code="ES-AL" data-name="Almería">
                    <path class="provincia-path nivel-{{ $mapaDatos['ES-AL']['level'] ?? 'nodata' }}"
                          d="M 886.1,192.9 887.7,194.9 895.6,192.5 899.3,197.3 911.9,199.1 916.3,196.6 919.6,199 916.2,204.8 914,218 916.1,227.6 913.4,233.1 913.6,238.9 919.2,249.8 940.4,283 946.3,287.4 945.8,283.7 957.9,285.5 975.1,299.2 967.6,303.8 965.6,306.5 962.5,313.7 954.4,322 952,325.5 951.8,326 951.7,326.8 948.1,328.8 939.7,361.5 931.5,377.9 931,388.1 925.1,388.3 920.7,396 916.7,397 915.5,407.9 905.8,414.1 905.7,420.6 898.3,423.6 895.4,429.9 885.7,432.4 868.5,412 857.1,407.6 846.6,413.9 838.8,408.5 840.6,410.8 837.7,408.8 824.8,412.8 815.5,430.9 802.5,439.8 791,440.1 783.9,435.1 778.4,437.3 766.2,425.9 757.7,428.6 733.4,426 731.5,418.5 749.4,407 742.5,391.8 749.1,390.7 749.4,386.1 755.7,383.9 758,379.9 753.2,370.7 749.3,352.4 754.1,356.1 762.4,355.8 765.5,343.6 773.3,337.4 774.3,328.4 779.1,318.7 789.8,322.1 793,329.4 797.2,327.2 809,332.4 808.1,317.5 809.8,306.2 812.6,305.7 811.9,296.7 832.5,278 841.6,275.7 841.8,272 857.6,269.3 858.1,264.3 855.1,262.2 856.1,255.3 852.3,247 857.4,249.2 865.6,246.8 863.3,234.5 870.8,217.8 866.8,211.3 868.8,206.6 867.9,197.4 882,189.3 886.1,192.9 Z M 944.5,339.3 944.6,339.2 944.6,339.1 944.5,339.2 944.5,339.3 Z M 944.5,339.9 944.5,339.7 944.5,339.7 944.4,339.8 944.5,339.9 Z M 824,413.8 824,413.7 824.1,413.7 823.9,413.8 824,413.8 Z M 960.9,315.2 960.9,315.2 960.9,315.2 960.9,315.2 960.9,315.2 Z M 887.3,431.8 887.2,431.7 887.2,431.8 887.2,431.8 887.3,431.8 Z M 960.8,315.3 960.8,315.2 960.8,315.2 960.8,315.2 960.8,315.3 Z M 960.5,315.4 960.5,315.4 960.5,315.4 960.5,315.4 960.5,315.4 Z M 962.4,313.8 962.4,313.7 962.4,313.7 962.4,313.8 962.4,313.8 Z M 960.7,315.2 960.7,315.2 960.7,315.2 960.7,315.2 960.7,315.2 Z M 961,315.1 961,315.1 961,315.1 961,315.1 961,315.1 Z M 960.8,315.3 960.7,315.3 960.7,315.3 960.7,315.3 960.8,315.3 Z M 748.5,591.1 748.1,590.9 749.1,590.4 748.7,590.7 748.5,591.1 Z M 933.9,376.8 934,376.8 934,377 933.8,376.9 933.9,376.8 Z M 971.7,304.9 971.6,305 971.4,304.9 971.6,304.8 971.7,304.9 Z M 972.3,302.3 972.2,302.4 972.1,302.4 972.1,302.4 972.3,302.3 Z M 907.6,413.6 907.5,413.6 907.5,413.5 907.5,413.5 907.6,413.6 Z M 884.2,431.9 884.2,431.9 884.2,431.8 884.2,431.8 884.2,431.9 Z M 884.7,432.2 884.6,432.2 884.7,432.1 884.7,432.1 884.7,432.2 Z M 915.1,408.6 915,408.6 915,408.6 915.1,408.6 915.1,408.6 Z M 963.9,311 963.8,311 963.8,311 963.9,310.9 963.9,311 Z M 964.2,310.1 964.2,310 964.3,309.9 964.3,310 964.2,310.1 Z M 961.8,314.5 961.8,314.5 961.8,314.5 961.9,314.5 961.8,314.5 Z M 749.3,590.3 749.2,590.3 749.2,590.3 749.2,590.3 749.3,590.3 Z M 960,316.5 960,316.5 960,316.5 960,316.4 960,316.5 Z M 952.1,325.6 952,325.6 952,325.6 952,325.6 952.1,325.6 Z M 884.6,432.3 884.6,432.3 884.6,432.3 884.6,432.2 884.6,432.3 Z M 959.8,316.8 959.8,316.8 959.8,316.8 959.8,316.8 959.8,316.8 Z M 964,310.4 964,310.4 964,310.4 964,310.4 964,310.4 Z M 952.1,325.7 952.1,325.7 952.1,325.7 952.1,325.7 952.1,325.7 Z M 951.9,325.9 952,325.9 952,326 951.9,325.9 951.9,325.9 Z M 952,325.8 952,325.8 952,325.8 952,325.8 952,325.8 Z M 960.1,316.4 960.1,316.4 960.1,316.3 960.1,316.4 960.1,316.4 Z M 964.1,310.1 964.1,310.1 964.1,310.1 964.2,310.1 964.1,310.1 Z M 952.7,324.9 952.7,324.9 952.6,324.9 952.7,324.9 952.7,324.9 Z M 964.1,310.4 964,310.4 964,310.3 964.1,310.3 964.1,310.4 Z M 952,325.8 952,325.8 952,325.8 952,325.7 952,325.8 Z M 963.9,311 963.9,311 963.9,310.9 963.9,310.9 963.9,311 Z M 964.1,310.4 964.1,310.4 964.1,310.4 964.1,310.4 964.1,310.4 Z M 953.2,324.3 953.3,324.3 953.3,324.3 953.2,324.3 953.2,324.3 Z M 964.3,309.8 964.3,309.8 964.3,309.9 964.3,309.8 964.3,309.8 Z M 953.6,323.8 953.5,323.8 953.5,323.8 953.6,323.8 953.6,323.8 Z M 964.1,310.6 964,310.6 964,310.6 964,310.5 964.1,310.6 Z M 963.2,312.3 963.2,312.3 963.2,312.2 963.2,312.3 963.2,312.3 Z M 956.7,320.1 956.7,320.1 956.7,320.1 956.7,320.1 956.7,320.1 Z M 952.7,324.8 952.7,324.8 952.7,324.8 952.7,324.8 952.7,324.8 Z M 963.2,312.2 963.2,312.2 963.2,312.2 963.2,312.2 963.2,312.2 Z M 953.6,323.9 953.5,323.9 953.6,323.8 953.6,323.9 953.6,323.9 Z M 955.7,321.3 955.7,321.4 955.7,321.3 955.7,321.3 955.7,321.3 Z M 957.2,319.5 957.2,319.5 957.2,319.4 957.2,319.4 957.2,319.5 Z M 957.5,318.9 957.5,318.9 957.6,318.9 957.6,318.9 957.5,318.9 Z M 951.9,325.9 951.9,325.9 951.9,325.9 951.9,325.9 951.9,325.9 Z M 960.2,316.2 960.2,316.2 960.2,316.2 960.2,316.2 960.2,316.2 Z M 960.1,316.5 960,316.5 960,316.5 960.1,316.5 960.1,316.5 Z M 952.1,325.6 952.1,325.6 952.1,325.6 952.1,325.6 952.1,325.6 Z M 959.8,316.7 959.8,316.7 959.8,316.6 959.8,316.6 959.8,316.7 Z M 953.3,324.3 953.3,324.3 953.3,324.3 953.3,324.3 953.3,324.3 Z M 952.7,325.1 952.7,325.1 952.6,325.1 952.6,325.1 952.7,325.1 Z M 960.1,316.4 960.1,316.4 960.1,316.4 960.1,316.4 960.1,316.4 960.1,316.4 Z M 958.7,317.9 958.7,318 958.7,318 958.7,317.9 958.7,317.9 Z M 953.5,324 953.4,324 953.4,324 953.5,324 953.5,324 Z M 953.7,323.7 953.7,323.7 953.7,323.7 953.7,323.6 953.7,323.7 Z M 964.2,310 964.2,310 964.2,310 964.2,310 964.2,310 Z M 959.3,317.2 959.3,317.2 959.3,317.2 959.3,317.2 959.3,317.2 Z M 958.7,318 958.7,318 958.7,318 958.7,318 958.7,318 Z M 960,316.6 960,316.6 960,316.5 960,316.5 960,316.6 Z M 963.9,310.5 963.9,310.5 963.9,310.5 963.9,310.5 963.9,310.5 Z M 964.4,309.4 964.4,309.4 964.4,309.4 964.4,309.4 964.4,309.4 Z M 954,323.2 953.9,323.2 954,323.2 954,323.2 954,323.2 Z M 963.9,310.9 963.9,310.9 963.9,310.9 963.9,310.9 Z M 953.7,323.7 953.7,323.7 953.7,323.7 953.7,323.7 953.7,323.7 Z M 953.3,324.2 953.3,324.3 953.2,324.3 953.3,324.2 953.3,324.2 Z M 956.5,320.3 956.5,320.3 956.5,320.3 956.5,320.3 956.5,320.3 Z M 964.1,310.3 964.1,310.3 964.1,310.3 964.1,310.3 964.1,310.3 Z M 959,317.8 958.9,317.8 958.9,317.8 958.9,317.8 959,317.8 Z M 953.4,324.1 953.4,324.1 953.4,324.1 953.4,324.1 953.4,324.1 Z M 964.1,310.4 964.1,310.4 964.1,310.4 964.1,310.4 Z M 961.7,314.5 961.7,314.5 961.7,314.5 961.7,314.5 961.7,314.5 Z M 963.9,310.7 963.9,310.7 963.9,310.7 963.9,310.6 963.9,310.7 Z M 952.6,325 952.6,325 952.6,325 952.6,324.9 952.6,325 Z M 952,325.6 952,325.6 952,325.6 952,325.6 952,325.6 Z M 962.6,313.6 962.6,313.6 962.6,313.6 962.6,313.6 962.6,313.6 Z M 963.4,312 963.3,312 963.3,312 963.4,312 963.4,312 Z M 960.1,316.4 960.1,316.4 960.1,316.4 960.1,316.4 960.1,316.4 Z M 964.3,309.9 964.3,309.9 964.3,309.9 964.3,309.9 Z M 963.2,312.3 963.2,312.3 963.2,312.3 963.2,312.3 963.2,312.3 Z M 963.7,311.4 963.6,311.4 963.6,311.4 963.6,311.4 963.7,311.4 Z M 953.4,324.2 953.3,324.2 953.3,324.2 953.4,324.2 953.4,324.2 Z M 962.7,313.5 962.7,313.6 962.7,313.5 962.7,313.5 962.7,313.5 Z M 957.2,319.4 957.2,319.4 957.1,319.4 957.2,319.4 957.2,319.4 Z M 957.4,319 957.4,319 957.4,319 957.4,319 957.4,319 Z M 964,310.3 964,310.3 964,310.3 964,310.3 964,310.3 Z M 963.3,312 963.3,312 963.3,312 963.3,312 963.3,312 Z M 951.9,326 951.9,326 951.9,326 951.9,326 Z"
                          tabindex="0"
                          role="button"
                          aria-haspopup="dialog"
                          aria-label="{{ $mapaDatos['ES-AL']['name'] ?? 'Almería' }}: {{ $mapaDatos['ES-AL']['nodes'] ?? 0 }} nodos"
                          data-code="ES-AL" />
                    <g class="burbuja-grupo " transform="translate(930, 330)" pointer-events="none">
                        <circle r="22" class="burbuja-fondo" />
                        <text y="6" text-anchor="middle" class="burbuja-texto">{{ $mapaDatos['ES-AL']['nodes'] ?? '0' }}</text>
                    </g>
                </g>

                <!-- Cádiz (ES-CA) -->
                <g class="provincia-grupo" id="prov-ES-CA" data-code="ES-CA" data-name="Cádiz">
                    <path class="provincia-path nivel-{{ $mapaDatos['ES-CA']['level'] ?? 'nodata' }}"
                          d="M 361.8,365.2 363.6,372.7 357.2,380.4 360.6,388.6 364.8,387.5 368.1,380.8 382.7,367.8 383,372.6 393.6,387.4 408.5,374.6 412.9,377.7 416,381.9 413.7,383.7 417.4,394.7 414.5,396.2 409.9,408.1 403.9,411.3 395.6,407.4 387.9,398.9 382.8,401.9 376.5,412.1 379.8,418.7 384.5,421.1 379.1,431.5 379.1,441.8 370,450.2 357.9,452.7 357.1,458.9 347,470.7 332.9,468 333.3,474.5 346.8,477.4 352,469.6 362.2,470.2 375.4,493.5 379.8,512.5 386.6,510.1 390.7,515.3 380.3,532 376.8,547.4 372.5,547 374.6,546.8 368.3,541.8 360.3,545.6 359.3,550.3 359.9,552.4 361.1,549.9 359.9,552.6 360.4,553.3 362,549.9 360,554.2 361.9,557.1 359.9,560.3 362.1,564.1 332.8,578.5 326.2,567.7 320.2,564.8 314.6,566.3 306.2,560.7 302.8,562.6 283.8,541 264.5,541.7 253.5,520.8 245.7,516.3 240.7,503.7 234.8,498.8 223.5,472.2 220.1,471.2 220.9,469.5 223.2,469.3 223.7,468.3 225,468.2 222.9,470.3 225.4,468.7 226.7,470.5 224.6,471.4 228.7,475.4 228.5,482.4 233,484.6 242.1,472.6 237.7,471.1 236.5,475 230.5,475.2 228.5,471 233.1,471.9 232.5,459.7 229.8,462.3 231.6,459.8 227.2,460.8 221.7,453 218.4,454.2 219.9,453 216.8,451.7 217.5,454.6 215.7,451.5 213,453.8 207.4,450.8 198.6,428.7 214.8,417.2 213.1,404.9 216.7,396 225.2,393.5 229.1,397 244.9,398.6 253.2,404.4 287.7,407.5 290,396.8 295,392.6 316.3,391.6 323.7,380.3 331.7,387.6 336.7,386.2 337.3,392.7 345.3,397.1 343.3,389.6 355.6,378.7 349,368.6 361.8,365.2 Z M 194.4,428.6 194.5,428.8 194.5,429.8 193.9,428.8 194.4,428.6 Z M 234.5,501.1 234.6,501.6 234.4,501.3 233.7,498.1 234.5,501.1 Z M 219.2,471.2 219.6,471.2 219.7,471.3 218.8,471.3 219.2,471.2 Z M 234.7,500.1 234.7,500.4 234.8,500.6 234.5,499.9 234.7,500.1 Z M 234.4,499.9 234.4,499.9 234.3,499.3 234.5,499.7 234.4,499.9 Z M 235.3,500.8 235.2,500.8 235.3,500.5 235.4,500.7 235.3,500.8 Z M 384.1,528.7 383.8,528.7 383.8,528.7 383.9,528.6 384.1,528.7 Z M 361.3,565.3 361.4,565.3 361.2,565.4 361.2,565.3 361.3,565.3 Z M 196.3,429.1 196.2,429.2 196.2,429.1 196.3,429.1 196.3,429.1 Z M 268.8,541.7 268.7,541.7 268.6,541.7 268.6,541.6 268.8,541.7 Z M 269.2,541.7 269.1,541.7 269,541.7 269.1,541.7 269.2,541.7 Z M 363.1,562.9 363,562.9 363,562.8 363,562.8 363.1,562.9 Z M 363.1,563.1 363.1,563 363,563 363.1,563 363.1,563.1 Z M 361.1,565.2 361.1,565.2 361,565.2 361.1,565.2 361.1,565.2 Z M 361,565.2 361,565.2 361,565.2 361,565.2 361,565.2 Z M 361,565.3 361,565.3 361,565.3 361,565.2 361,565.3 Z M 360.9,565.4 360.9,565.4 360.9,565.4 360.9,565.4 360.9,565.4 Z M 360.9,565.5 360.9,565.5 360.8,565.5 360.8,565.5 360.9,565.5 Z"
                          tabindex="0"
                          role="button"
                          aria-haspopup="dialog"
                          aria-label="{{ $mapaDatos['ES-CA']['name'] ?? 'Cádiz' }}: {{ $mapaDatos['ES-CA']['nodes'] ?? 0 }} nodos"
                          data-code="ES-CA" />
                    <g class="burbuja-grupo burbuja-cadiz" transform="translate(305, 490)" pointer-events="none">
                        <circle r="22" class="burbuja-fondo" />
                        <text y="6" text-anchor="middle" class="burbuja-texto">{{ $mapaDatos['ES-CA']['nodes'] ?? '0' }}</text>
                    </g>
                </g>

                <!-- Córdoba (ES-CO) -->
                <g class="provincia-grupo" id="prov-ES-CO" data-code="ES-CO" data-name="Córdoba">
                    <path class="provincia-path nivel-{{ $mapaDatos['ES-CO']['level'] ?? 'nodata' }}"
                          d="M 425.7,24.8 430.7,27.8 430.7,31.2 451.3,33 454,47.6 478.6,56.2 479.2,60 492.3,65.9 497,73.5 501.4,72.6 510.1,78.1 519.7,90.9 546.4,102.7 554.5,103 554.3,108.3 558.9,111.1 557.8,122.2 561.1,124 560.3,128.8 565.7,135.6 566.8,142.9 557.6,149.8 558.2,154.2 552.1,159.4 554,162.8 549.4,164.2 550.8,167.8 547.2,167.7 551.1,184.6 546.5,213.6 559.4,217.3 554.3,229.2 563.2,231.8 566.3,237 556,245.5 557.3,249.5 564.7,251.5 566.8,258.6 573.3,262.8 577,271.5 583.1,272.7 578.6,276.7 585,280.4 592.6,293.8 578.9,293.4 567.3,305.9 559.9,303.7 554.6,307.2 554.6,315.1 550.3,322.5 551.6,328.8 545.1,334.9 539.9,338 540.7,333.9 532.8,331.7 531,328.4 531.5,319.5 525.3,318.1 514.5,324.1 515.7,325.7 512.4,330.1 495.6,332.9 493.7,323.6 488.7,325.3 485.9,317.8 482.9,316.5 486.5,313.6 482.4,311.4 484.9,310.5 484.4,307.4 482.1,308.3 476.8,302.6 475.1,307.3 466.6,308.7 460.1,302.7 461.3,298.7 455.7,292 457.1,286.9 450,286.5 451.5,283.9 446.9,280.5 446.8,276 445.1,277.1 446.6,273.9 439.7,269.5 442.4,264.5 442.4,250 435.6,253.5 438.9,242.9 431.5,231.9 423.8,229.5 419.7,231.7 416.7,237.3 419.1,239.6 419,240.4 420.1,241 420.2,241.5 415.6,239.3 413.9,233.1 410.5,233.5 414,240.9 407.2,241.2 402.9,237.2 387.1,248.4 379.3,250.7 374.7,256.8 364.6,244.4 367.9,242.3 364.8,240.4 366.3,234.7 369,233.8 370.7,237.8 375.8,232.2 378.7,235 381.8,233.4 381.5,218.3 375,214.1 375.8,204.8 364.5,198.8 364.4,183.8 354.9,177.3 351.8,172.9 351.5,163.8 344.5,153.3 337.6,148.9 336.9,143.8 344.4,139.7 344.5,131.6 347.5,130 346.6,118.7 338.6,105.5 341.2,97.8 337.2,90.9 342.2,81.8 353.3,78.2 354.1,73.9 365.3,66.7 365.8,61.9 369.5,62.7 368.1,59.9 371.4,53.8 381.7,55 384.3,48.3 395.3,38.9 396.8,40.6 397.8,36.7 401.7,38.3 401.5,35.4 404.4,34.1 401.7,26.2 415.4,28.6 425.7,24.8 Z M 418.3,244.1 423.9,247.1 426.4,245.1 428.6,255.1 423.7,251.9 424.9,248.2 418.3,244.1 Z"
                          tabindex="0"
                          role="button"
                          aria-haspopup="dialog"
                          aria-label="{{ $mapaDatos['ES-CO']['name'] ?? 'Córdoba' }}: {{ $mapaDatos['ES-CO']['nodes'] ?? 0 }} nodos"
                          data-code="ES-CO" />
                    <g class="burbuja-grupo " transform="translate(460, 205)" pointer-events="none">
                        <circle r="22" class="burbuja-fondo" />
                        <text y="6" text-anchor="middle" class="burbuja-texto">{{ $mapaDatos['ES-CO']['nodes'] ?? '0' }}</text>
                    </g>
                </g>

                <!-- Granada (ES-GR) -->
                <g class="provincia-grupo" id="prov-ES-GR" data-code="ES-GR" data-name="Granada">
                    <path class="provincia-path nivel-{{ $mapaDatos['ES-GR']['level'] ?? 'nodata' }}"
                          d="M 826.8,155.5 847.1,165.5 860.3,167 867.3,171.9 875.6,185.6 882,189.3 867.9,197.4 868.8,206.6 866.8,211.3 870.8,217.8 863.3,234.5 865.4,247.5 857.4,249.2 852.3,247 856.1,255.3 855.1,262.2 858.1,264.3 857.6,269.3 841.8,272 841.6,275.7 832.5,278 811.9,296.7 812.6,305.7 809.8,306.2 808.1,317.5 809,332.4 797.2,327.2 793,329.4 789.8,322.1 779.1,318.7 774.3,328.4 773.3,337.4 765.5,343.6 762.4,355.8 754.1,356.1 749.3,352.4 753.2,370.7 758,379.9 755.7,383.9 749.4,386.1 749.1,390.7 742.5,391.8 749.4,407 731.5,418.5 733.4,426 713.8,425.3 699.7,428.5 686.4,437.6 676.2,436.9 669.3,431.5 664.8,433.3 656.9,427.3 648,426.9 635.6,432.4 633.3,428.7 628.7,428.7 630.2,415.6 620.7,404.8 614.1,405.7 604.1,399.2 585.4,395.2 576.7,385 565.9,384.1 555.4,372 548.8,372.4 546.5,351.1 539.9,338 551.6,328.8 550.3,322.5 554.6,315.1 554.6,307.2 559.9,303.7 567.3,305.9 578.9,293.4 592.6,293.8 600.7,298.6 618.2,297.1 627.4,275.7 628.9,278.6 652.9,268.1 667.6,257.5 667.6,254.1 672.4,250.1 689.7,260.9 706.3,255.9 711.8,245.6 719.3,244.6 726.4,244.9 739.9,254.5 745.9,249.8 757.6,253 757.7,246.7 772.8,237 770,235.4 769.6,222.9 773.4,221.1 774.6,214.7 788.9,189.1 802.1,178.1 807.4,180.2 817.7,172.8 819.3,161.5 817.6,159.8 826.8,155.5 Z M 691.3,434.7 691.2,434.6 691.2,434.6 691.3,434.7 691.3,434.7 Z"
                          tabindex="0"
                          role="button"
                          aria-haspopup="dialog"
                          aria-label="{{ $mapaDatos['ES-GR']['name'] ?? 'Granada' }}: {{ $mapaDatos['ES-GR']['nodes'] ?? 0 }} nodos"
                          data-code="ES-GR" />
                    <g class="burbuja-grupo " transform="translate(730, 310)" pointer-events="none">
                        <circle r="22" class="burbuja-fondo" />
                        <text y="6" text-anchor="middle" class="burbuja-texto">{{ $mapaDatos['ES-GR']['nodes'] ?? '0' }}</text>
                    </g>
                </g>

                <!-- Huelva (ES-H) -->
                <g class="provincia-grupo" id="prov-ES-H" data-code="ES-H" data-name="Huelva">
                    <path class="provincia-path nivel-{{ $mapaDatos['ES-H']['level'] ?? 'nodata' }}"
                          d="M 120.5,130.8 133.2,136.3 141.8,136.1 138.5,147.9 145.8,149.6 147.7,153.5 169.7,152.6 176.4,161.6 175.9,168 182,166.6 193.2,170.7 198.4,160.2 210.8,162.4 214.1,171.8 231.5,180.7 237.6,180 242.7,190.6 246.7,188.5 249.9,191.7 243.6,200.3 247.1,201.6 250.5,209.6 248.5,215.4 237.4,211.8 234.2,225.2 229.4,224.6 229.4,220.4 219.9,220.1 197.5,229.4 193.4,228.1 189.2,239.8 183.6,243.2 184.8,250.4 198.6,248.8 206.3,251.3 206.8,262.9 211,266.6 212.6,274.7 220.7,287.5 219.6,290.4 212.6,291.7 211.7,299.1 214.3,301.1 208,306.9 216.4,314.7 212,327.1 214.4,331.1 213.5,338.8 215.9,341 212.1,347.3 214.8,350.4 207.7,359.9 213,377.8 209,388.7 216.7,396 213.1,404.4 214.3,416.3 206,414.6 197.6,394.2 185.9,380.1 122.2,339.6 122.3,342.2 133,349.5 134.6,351.2 135.8,353.5 121,341.9 99.1,332.6 65,334.4 48,340.6 43,338.2 38.8,324.7 40.7,319.7 37.4,312.3 37.7,296.2 34.1,292.2 36.8,290.3 33.9,276.5 26.2,268.9 24.4,263 27,262.2 27.4,252.7 29.8,252.8 37.1,240.4 35.7,237.7 40.8,221.2 56.4,209.7 63,198.9 68.6,181.6 66.3,176.4 76.1,171.6 87.3,172.3 89.3,171.3 88.1,166.3 92.8,163.4 99.2,168.8 108.4,167.7 110.8,151.2 116.5,141.3 113.8,138.6 120.5,130.8 Z"
                          tabindex="0"
                          role="button"
                          aria-haspopup="dialog"
                          aria-label="{{ $mapaDatos['ES-H']['name'] ?? 'Huelva' }}: {{ $mapaDatos['ES-H']['nodes'] ?? 0 }} nodos"
                          data-code="ES-H" />
                    <g class="burbuja-grupo " transform="translate(150, 250)" pointer-events="none">
                        <circle r="22" class="burbuja-fondo" />
                        <text y="6" text-anchor="middle" class="burbuja-texto">{{ $mapaDatos['ES-H']['nodes'] ?? '0' }}</text>
                    </g>
                </g>

                <!-- Jaén (ES-J) -->
                <g class="provincia-grupo" id="prov-ES-J" data-code="ES-J" data-name="Jaén">
                    <path class="provincia-path nivel-{{ $mapaDatos['ES-J']['level'] ?? 'nodata' }}"
                          d="M 791.7,64.1 802.1,70.6 818.7,68.2 823.5,71.8 823.2,88.2 837.6,91.9 837.2,111.3 845,117.1 842.2,128.6 843.5,134.7 828.4,153.5 817.6,159.8 819.3,161.5 817.8,172.7 807.4,180.2 802.1,178.1 788.9,189.1 774.6,214.7 773.4,221.1 769.6,222.9 770,235.4 772.8,237 757.7,246.7 757.6,253 745.9,249.8 739.9,254.5 726.4,244.9 719.3,244.6 711.8,245.6 706.3,255.9 689.7,260.9 672.4,250.1 667.6,254.1 667.6,257.5 652.9,268.1 628.9,278.6 627.4,275.7 618.4,297 600.7,298.6 595.4,296.5 585,280.4 578.6,276.7 583.1,272.7 577,271.5 573.3,262.8 566.8,258.6 564.7,251.5 557.3,249.5 556,245.5 566.3,237 563.2,231.8 554.3,229.2 559.2,217 546.5,213.6 551.1,184.6 547.2,167.9 550.8,167.8 549.4,164.2 554,162.8 552.1,159.4 558.2,154.2 557.6,149.8 566.8,142.8 565.7,135.6 560.3,128.8 561.1,124 557.8,122.2 558.9,111.1 554.3,108.3 554.5,103 549.3,101.7 547.8,95.7 549.5,91.5 606.5,97.4 616.3,96.1 620.1,92.5 620.2,88.5 627.4,86 657.6,91.4 660,80.6 662.6,80.5 667.8,83.1 668.9,89.3 678.1,91.5 685.6,89.7 693.3,83 693.6,75.8 701.4,74.4 709,78.8 731.8,83.2 743.6,75.2 753.8,89.4 755.2,80.9 760,76.6 769.3,76.9 773.2,80.4 789.6,68.8 791.7,64.1 Z"
                          tabindex="0"
                          role="button"
                          aria-haspopup="dialog"
                          aria-label="{{ $mapaDatos['ES-J']['name'] ?? 'Jaén' }}: {{ $mapaDatos['ES-J']['nodes'] ?? 0 }} nodos"
                          data-code="ES-J" />
                    <g class="burbuja-grupo " transform="translate(675, 165)" pointer-events="none">
                        <circle r="22" class="burbuja-fondo" />
                        <text y="6" text-anchor="middle" class="burbuja-texto">{{ $mapaDatos['ES-J']['nodes'] ?? '0' }}</text>
                    </g>
                </g>

                <!-- Málaga (ES-MA) -->
                <g class="provincia-grupo" id="prov-ES-MA" data-code="ES-MA" data-name="Málaga">
                    <path class="provincia-path nivel-{{ $mapaDatos['ES-MA']['level'] ?? 'nodata' }}"
                          d="M 527.2,319.7 531.6,319.7 530.7,327.1 532.8,331.7 540.7,333.9 539.2,336.6 546.5,351.1 548.8,372.4 555.4,372 565.9,384.1 576.7,385 585.4,395.2 604.1,399.2 614.1,405.7 620.7,404.8 630.2,415.6 628.7,428.7 617.8,425.5 599.7,431.1 581.9,426.5 575.3,431.2 563.6,433.4 529.2,431.8 525.8,434.9 526.1,432.8 525.2,432.9 525.4,434.5 510.2,458.1 495.5,464.2 489.8,475.5 473.3,480.5 453.9,475.4 441.9,477 430.6,485.1 418.4,487 402.9,494.4 395.9,502.3 391.4,515.5 386.6,510.1 379.8,512.5 375.4,493.5 362.1,470.2 352,469.6 346.8,477.4 333.3,474.5 333.9,467 347,470.7 357.1,458.9 357.9,452.7 370,450.2 379.1,441.8 379.1,431.5 384.5,421.1 379.8,418.7 376.5,412.1 382.8,401.9 387.9,398.9 403.6,411.3 409.9,408.1 414.5,396.2 417.4,394.7 413.7,383.7 416,381.9 408.5,374.2 411.9,369.6 422.9,369.2 446.2,352.1 447,347.2 440.7,343.4 441.2,339.7 446.7,338.5 447.9,343.7 451.5,343.3 455,335.1 459.5,334.7 464.7,344.2 478.4,335.3 474.6,329.5 476.9,326.7 474.1,325.7 476.7,322.7 493.7,323.5 495.6,332.9 499.8,332.9 512.4,330.1 515.7,325.7 514.4,324.2 517.9,324.2 521,319 527.2,319.7 Z"
                          tabindex="0"
                          role="button"
                          aria-haspopup="dialog"
                          aria-label="{{ $mapaDatos['ES-MA']['name'] ?? 'Málaga' }}: {{ $mapaDatos['ES-MA']['nodes'] ?? 0 }} nodos"
                          data-code="ES-MA" />
                    <g class="burbuja-grupo " transform="translate(470, 400)" pointer-events="none">
                        <circle r="22" class="burbuja-fondo" />
                        <text y="6" text-anchor="middle" class="burbuja-texto">{{ $mapaDatos['ES-MA']['nodes'] ?? '0' }}</text>
                    </g>
                </g>

                <!-- Sevilla (ES-SE) -->
                <g class="provincia-grupo" id="prov-ES-SE" data-code="ES-SE" data-name="Sevilla">
                    <path class="provincia-path nivel-{{ $mapaDatos['ES-SE']['level'] ?? 'nodata' }}"
                          d="M 314.4,132.6 320.3,135.4 321.1,140.2 317.6,137.9 319.3,141.9 312.2,145.3 312.9,154.8 319.5,155.3 329.4,144.2 337.1,145.6 351.5,163.8 351.9,173.2 364.4,183.8 364.5,198.8 376.7,206.2 375,214 381.4,218.2 381.9,233.2 378.7,235 375.8,232.2 370.7,237.8 368.7,233.7 364.6,238.4 367.9,242.2 364.6,244.4 373.4,252.5 373.7,256.6 402.9,237.2 407.2,241.2 414,240.9 410.5,233.5 413.9,233.1 415.6,239.3 420.2,241.5 416.7,237.3 419.7,231.7 423.8,229.5 431.5,231.9 438.9,242.9 435.6,253.5 442.4,250 442.4,264.5 439.7,269.4 446.6,273.9 445.1,277.1 446.8,276 446.9,280.5 451.5,283.9 450,286.5 457.1,286.9 455.7,292 461.3,298.7 460.1,302.7 466.6,308.7 475.1,307.3 476.8,302.6 482.1,308.3 484.5,307.5 484.9,310.5 482.4,311.4 486.2,315.6 483.8,314.1 482.9,316.5 485.9,317.8 487.4,323.5 475,323.9 474.1,325.7 476.9,326.7 475.4,332.5 478.4,335.3 464.9,344.2 459.5,334.7 455,335.1 451.5,343.3 447.9,343.7 446.7,338.5 441.2,339.7 440.7,343.4 447,347.2 446.2,352.1 422.9,369.2 411.6,369.8 405.2,379.2 395,387.2 383,372.6 382.7,367.8 368.1,380.8 364.8,387.5 360.6,388.6 357.2,380.4 363.6,372.7 361.5,364.8 349,368.6 355.6,378.7 343.3,389.6 345.3,397.1 337.3,392.7 336.7,386.2 331.7,387.6 323.7,380.3 316.3,391.6 295,392.6 290,396.8 287.7,407.5 253.2,404.4 244.9,398.6 229.1,397 225.2,393.5 216.7,396 209,388.7 213,377.8 207.7,360.3 214.8,350.4 212.1,347.3 215.9,341 213.5,338.8 214.4,331.1 212,327.1 216.4,314.7 208,306.9 214.3,301.1 211.7,299.1 212.6,291.7 219.6,290.4 220.7,287.5 212.6,274.7 211,266.6 206.8,262.9 206.3,251.3 198.6,248.8 184.8,250.4 183.6,243.2 189.2,239.8 193.4,228.1 197.5,229.4 219.9,220.1 229.4,220.4 229.4,224.6 234.2,225.2 237.4,211.8 248.5,215.4 250.5,209.6 247.1,201.6 243.6,200.3 249.9,191.7 246.7,188.5 241.8,189.6 240.4,186.7 243,181.3 261.7,173 277.6,173.3 280.7,162.8 284.8,159.6 282,151 289.3,145.5 290.3,140.2 314.4,132.6 Z M 427.2,253.7 426.4,245.1 423.9,247.1 417.8,243.8 424.9,248.2 423.7,251.9 426.4,251.4 427.1,254.5 428.6,255.1 427.2,253.7 Z"
                          tabindex="0"
                          role="button"
                          aria-haspopup="dialog"
                          aria-label="{{ $mapaDatos['ES-SE']['name'] ?? 'Sevilla' }}: {{ $mapaDatos['ES-SE']['nodes'] ?? 0 }} nodos"
                          data-code="ES-SE" />
                    <g class="burbuja-grupo " transform="translate(350, 280)" pointer-events="none">
                        <circle r="22" class="burbuja-fondo" />
                        <text y="6" text-anchor="middle" class="burbuja-texto">{{ $mapaDatos['ES-SE']['nodes'] ?? '0' }}</text>
                    </g>
                </g>
            </g>
        </svg>

        <!-- Panel flotante (Tooltip) para detalles de provincia -->
        <div id="mapa-panel-detalle" 
             class="mapa-panel-detalle" 
             role="dialog" 
             aria-hidden="true" 
             style="display: none; position: absolute; pointer-events: none; z-index: 20;">
            <div class="tarjeta" style="padding: 1rem; width: 280px; box-shadow: var(--sombra-lg); border-color: var(--color-borde);">
                <div style="font-weight: 700; font-size: 1.15rem; color: var(--color-texto-1); margin-bottom: 0.25rem;" id="panel-provincia-nombre">
                    Cádiz
                </div>
                <div style="font-size: 0.95rem; color: var(--color-texto-2); margin-bottom: 0.75rem;" id="panel-provincia-recuento">
                    0 nodos (0% del total)
                </div>
                <div style="border-top: 1px solid var(--color-borde); padding-top: 0.5rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="color: var(--color-texto-2);">Saturación estimada:</span>
                        <strong id="panel-provincia-saturacion">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="color: var(--color-texto-2);">Carga máxima:</span>
                        <span id="panel-provincia-max">-</span>
                    </div>
                    <div style="color: var(--color-texto-3); font-size: 0.78rem; margin-top: 0.5rem;" id="panel-provincia-detalle-nodos">
                        Sin datos de telemetría recientes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leyenda de colores y cálculo de saturación -->
    <div class="mapa-leyenda" style="margin-top: 1.5rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
        <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem; color: var(--color-texto-1);">
            Leyenda de ocupación del espectro (saturación):
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; font-size: 0.88rem; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: var(--color-mapa-verde);"></span>
                <span>≤ 20 %: canal holgado</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: var(--color-mapa-naranja);"></span>
                <span>20 % – 40 %: canal cargado</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: var(--color-mapa-rojo);"></span>
                <span>≥ 40 %: canal saturado</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: var(--color-mapa-gris); border: 1px dashed var(--color-borde);"></span>
                <span>Sin datos en 12 h</span>
            </div>
        </div>
        <p style="font-size: 0.82rem; color: var(--color-texto-3); line-height: 1.5; margin-bottom: 0;">
            <strong>Cómo calculamos la saturación de cada provincia:</strong> con las coordenadas buscamos todos los nodos que están dentro de la provincia; sacamos la media de la ocupación del canal de los routers por un lado y la de todos los nodos CLIENT y CLIENT_BASE juntos por otro; y sumamos con pesos: routers 60 % y CLIENT con CLIENT_BASE 40 % (estos dos van juntos en una sola media). Los CLIENT_MUTE no cuentan porque suelen estar en interior o peor comunicados y su medida sale más baja de lo real. Se usa el último dato de cada nodo en las 12 últimas horas.
        </p>
    </div>

    <!-- Tabla accesible para lectores de pantalla y consulta alternativa -->
    <details class="tabla-accesible-contenedor" style="margin-top: 1.5rem;" open>
        <summary style="font-weight: 600; cursor: pointer; color: var(--color-texto-1); padding: 0.5rem 0;">
            Tabla de nodos y carga por provincia (alternativa accesible)
        </summary>
        <div style="overflow-x: auto; margin-top: 0.75rem;">
            <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                        <th style="padding: 0.6rem;">Provincia</th>
                        <th style="padding: 0.6rem; text-align: right;">Nodos</th>
                        <th style="padding: 0.6rem; text-align: right;">% Total</th>
                        <th style="padding: 0.6rem; text-align: right;">Saturación</th>
                        <th style="padding: 0.6rem;">Estado</th>
                    </tr>
                </thead>
                <tbody id="mapa-tabla-cuerpo">
                    @foreach($provinciasConfig as $codigo => $nombre)
                        @php
                            $pInfo = $mapaDatos[$codigo] ?? null;
                            $nodos = $pInfo['nodes'] ?? 0;
                            $pct = $totalAndalucia > 0 ? round(($nodos / $totalAndalucia) * 100, 1) : 0;
                            $avg = $pInfo['avg'] ?? null;
                            $level = $pInfo['level'] ?? 'nodata';
                            $estadoTexto = match($level) {
                                'green' => 'Holgado',
                                'orange' => 'Cargado',
                                'red' => 'Saturado',
                                default => 'Sin datos',
                            };
                            $chipTipo = match($level) {
                                'green' => 'correcto',
                                'orange' => 'aviso',
                                'red' => 'critico',
                                default => 'neutro',
                            };
                        @endphp
                        <tr style="border-bottom: 1px solid var(--color-borde);">
                            <td style="padding: 0.6rem; font-weight: 500;">
                                {{ $nombre }} {{ $codigo === 'ES-CA' ? '(Foco)' : '' }}
                            </td>
                            <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                {{ number_format((int) $nodos, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                {{ $pct }} %
                            </td>
                            <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                {{ $avg !== null ? $avg . ' %' : '—' }}
                            </td>
                            <td style="padding: 0.6rem;">
                                <x-chip-estado :tipo="$chipTipo" :texto="$estadoTexto" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($fueraAndalucia > 0)
            <div style="font-size: 0.8rem; color: var(--color-texto-3); margin-top: 0.5rem;">
                * Además, {{ $fueraAndalucia }} nodos oídos desde nuestros gateways se encuentran situados fuera de Andalucía.
            </div>
        @endif
    </details>
</div>