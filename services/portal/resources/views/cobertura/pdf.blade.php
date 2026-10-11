@php
    $rulerIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="7" viewBox="0 0 16 16"><g transform="rotate(45 8 8)"><rect x="1" y="6" width="14" height="4" rx="0.8" fill="#94A3B8" stroke="#CBD5E1" stroke-width="0.5"/><line x1="4" y1="6" x2="4" y2="7.8" stroke="#1F2029" stroke-width="0.6"/><line x1="6.5" y1="6" x2="6.5" y2="8.5" stroke="#1F2029" stroke-width="0.6"/><line x1="9" y1="9" x2="9" y2="7.8" stroke="#1F2029" stroke-width="0.6"/><line x1="11.5" y1="6" x2="11.5" y2="8.5" stroke="#1F2029" stroke-width="0.6"/></g></svg>';
    $rulerIconBase64 = 'data:image/svg+xml;base64,' . base64_encode($rulerIconSvg);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ ($datos['filtro'] ?? '') === 'todos' ? 'Matriz de Enlaces Completa (Todos)' : 'Matriz de Enlaces Positivos (SNR > 0 dB)' }} — Andalucía Mesh</title>
    <style>
        @page {
            margin: 16mm 7mm 14mm 7mm;
        }

        /* Fondo a sangre continua en todas las páginas para eliminar bordes blancos de Dompdf */
        .bg-fix {
            position: fixed;
            top: -16mm;
            bottom: -14mm;
            left: -7mm;
            right: -7mm;
            background-color: #1F2029;
            z-index: -1000;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            background-color: #1F2029;
            color: #F0F0F5;
            margin: 0;
            padding: 0;
            font-size: 6.8pt;
            line-height: 1.25;
        }

        /* Cabecera Fija en cada página */
        header {
            position: fixed;
            top: -12mm;
            left: 0;
            right: 0;
            height: 11mm;
            background-color: #1F2029;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo {
            width: 24px;
            height: 24px;
            vertical-align: middle;
            margin-right: 6px;
        }

        .header-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #FFFFFF;
            vertical-align: middle;
        }

        .header-subtitle {
            font-size: 5.8pt;
            color: #C7C8D4;
            display: block;
            margin-top: 1px;
        }

        .header-meta-date {
            font-size: 6.5pt;
            color: #C7C8D4;
            display: block;
        }

        .header-meta-filter {
            font-size: 6.8pt;
            font-weight: bold;
            color: #67EA94;
        }

        /* Franja Fina Bandera de Andalucía (Separador superior) */
        .separador-bandera-sup {
            width: 100%;
            height: 4.5px;
            margin-top: 3px;
        }
        .franja-v { height: 1.5px; background-color: #007A33; width: 100%; }
        .franja-b { height: 1.5px; background-color: #FFFFFF; width: 100%; }

        /* Pie de página Fijo en cada página */
        footer {
            position: fixed;
            bottom: -11mm;
            left: 0;
            right: 0;
            height: 9.5mm;
            text-align: center;
            background-color: #1F2029;
        }

        .separador-bandera-inf {
            width: 220px;
            height: 3px;
            margin: 0 auto 2px auto;
        }
        .franja-inf-v { height: 1px; background-color: #007A33; width: 100%; }
        .franja-inf-b { height: 1px; background-color: #FFFFFF; width: 100%; }

        .footer-url {
            font-size: 7pt;
            font-weight: bold;
            color: #67EA94;
            text-decoration: none;
            letter-spacing: 0.03em;
            display: block;
        }

        .footer-credito {
            font-size: 5.4pt;
            color: #94A3B8;
            margin-top: 1px;
        }

        /* Banner Resumen del Documento */
        .doc-banner {
            background-color: #2C2D3C;
            border: 1px solid #3D3E4D;
            border-radius: 6px;
            padding: 6px 10px;
            margin-top: 1mm;
            margin-bottom: 3mm;
        }

        .doc-pildora-red {
            display: inline-block;
            background-color: #181924;
            border: 1px solid #007A33;
            color: #67EA94;
            font-size: 5.6pt;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 9999px;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }

        .doc-titulo-matriz {
            font-size: 11pt;
            font-weight: bold;
            color: #FFFFFF;
            margin: 1px 0 2px 0;
            line-height: 1.15;
        }

        .doc-subtitulo {
            font-size: 6.6pt;
            color: #C7C8D4;
            margin: 0;
        }

        .doc-subtitulo strong {
            color: #67EA94;
        }

        .doc-aviso-directo {
            font-size: 5.8pt;
            color: #94A3B8;
            margin-top: 2px;
        }

        /* 4 Tarjetas KPI Superiores en Rejilla */
        .tabla-kpis {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
            margin-bottom: 3mm;
        }

        .celda-kpi {
            width: 25%;
            background-color: #2C2D3C;
            border: 1px solid #3D3E4D;
            border-radius: 6px;
            padding: 5px 6px;
            text-align: center;
            vertical-align: top;
        }

        .kpi-cifra {
            font-size: 12pt;
            font-weight: bold;
            color: #FFFFFF;
            line-height: 1.1;
            margin-bottom: 1px;
        }

        .kpi-titulo {
            font-size: 5.6pt;
            font-weight: bold;
            color: #67EA94;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 1px;
        }

        .kpi-subtitulo {
            font-size: 5pt;
            color: #94A3B8;
            line-height: 1.15;
        }

        /* Tabla de Cobertura */
        .tabla-matriz {
            width: 100%;
            border-collapse: collapse;
            background-color: #2C2D3C;
            border: 1px solid #3D3E4D;
            border-radius: 6px;
        }

        .tabla-matriz th {
            background-color: #181924;
            color: #C7C8D4;
            padding: 5px 6px;
            font-size: 5.8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1.5px solid #3D3E4D;
            text-align: left;
        }

        .tabla-matriz th.th-centro {
            text-align: center;
        }

        .tabla-matriz th.th-derecha {
            text-align: right;
        }

        /* Filas de División de Bloque */
        .fila-bloque td {
            background-color: #181924;
            color: #67EA94;
            font-size: 6.2pt;
            font-weight: bold;
            padding: 4px 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-top: 1.5px solid #007A33;
            border-bottom: 1px solid #3D3E4D;
        }

        /* Filas de Datos */
        .fila-item td {
            padding: 4px 6px;
            border-bottom: 1px solid #383948;
            vertical-align: middle;
        }

        .fila-par td {
            background-color: #242533;
        }

        .col-id {
            font-family: 'DejaVu Sans', monospace;
            font-size: 5.8pt;
            color: #94A3B8;
            white-space: nowrap;
        }

        .col-identidad strong {
            font-size: 6.6pt;
            font-weight: bold;
            color: #FFFFFF;
            display: block;
        }

        .col-identidad span {
            font-size: 5.4pt;
            color: #C7C8D4;
            display: block;
        }

        /* Badges */
        .badge-rol {
            display: inline-block;
            font-size: 5pt;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            background-color: #363748;
            color: #E2E8F0;
            border: 1px solid #475569;
            white-space: nowrap;
        }

        .badge-prov {
            display: inline-block;
            font-size: 5pt;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            background-color: #181924;
            color: #67EA94;
            border: 1px solid #3D3E4D;
            white-space: nowrap;
        }

        .distancia-km-pdf {
            font-size: 5.2pt;
            color: #CBD5E1;
            font-family: 'DejaVu Sans', monospace;
            margin-top: 1px;
            display: block;
            line-height: 1;
        }

        .icono-regla-pdf {
            width: 7px;
            height: 7px;
            vertical-align: -1px;
            margin-right: 1px;
        }

        .badge-cob {
            display: inline-block;
            font-size: 5pt;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            background-color: #181924;
            color: #94A3B8;
            border: 1px solid #3D3E4D;
            white-space: nowrap;
        }

        .badge-cob-ambos {
            background-color: #1C3A28;
            color: #67EA94;
            border: 1px solid #007A33;
        }

        .badge-cob-mutuo {
            background-color: #2D2545;
            color: #C4B5FD;
            border: 1px solid #7C3AED;
        }

        /* Cajas de SNR */
        .caja-snr {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 4px;
            text-align: center;
            min-width: 48px;
        }

        .caja-snr-valor {
            font-size: 6.6pt;
            font-weight: bold;
            display: block;
            line-height: 1.1;
        }

        .caja-snr-meta {
            font-size: 4.8pt;
            display: block;
            margin-top: 1px;
            opacity: 0.9;
        }

        .snr-excelente {
            background-color: #1C3A28;
            border: 1px solid #67EA94;
            color: #67EA94;
        }

        .snr-buena {
            background-color: #1C3325;
            border: 1px solid #4ADE80;
            color: #4ADE80;
        }

        .snr-limite {
            background-color: #3B331A;
            border: 1px solid #FACC15;
            color: #FACC15;
        }

        .snr-marginal {
            background-color: #3B1F23;
            border: 1px solid #F87171;
            color: #F87171;
        }

        /* Leyenda Inferior */
        .leyenda-snr {
            margin-top: 3mm;
            padding-top: 2mm;
            border-top: 1px solid #3D3E4D;
            font-size: 5.6pt;
            color: #94A3B8;
        }

        .leyenda-item {
            display: inline-block;
            margin-right: 8px;
        }

        .leyenda-dot {
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            margin-right: 2px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Fondo sólido continuo sin márgenes blancos -->
    <div class="bg-fix"></div>

    <!-- Cabecera fija en cada página -->
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 62%; vertical-align: middle;">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" class="header-logo" alt="Logo">
                    @endif
                    <span class="header-title">Andalucía Mesh</span>
                    <span class="header-subtitle">Red Ciudadana de Telecomunicaciones en Malla LoRa Meshtastic</span>
                </td>
                <td style="width: 38%; text-align: right; vertical-align: middle;">
                    <span class="header-meta-date">Fecha: {{ now()->format('d/m/Y H:i') }} h</span>
                    <span class="header-meta-filter">
                        Filtro: {{ ($datos['filtro'] ?? '') === 'todos' ? 'Todos (incluye marginales)' : 'Solo SNR positivo (> 0 dB)' }}
                    </span>
                </td>
            </tr>
        </table>
        <div class="separador-bandera-sup">
            <div class="franja-v"></div>
            <div class="franja-b"></div>
            <div class="franja-v"></div>
        </div>
    </header>

    <!-- Pie de página fijo en cada página -->
    <footer>
        <div class="footer-bloque-url">
            <div class="separador-bandera-inf">
                <div class="franja-inf-v"></div>
                <div class="franja-inf-b"></div>
                <div class="franja-inf-v"></div>
            </div>
            <span class="footer-url">{{ $projectDomain }}</span>
        </div>
        <div class="footer-credito">
            Andalucía Mesh · Generado desde telemetría y trazas RF reales de la red Meshtastic (100% Directos / 0 saltos RF)
        </div>
    </footer>

    <!-- Contenido Principal -->
    <main>
        <!-- Banner Informativo Superior -->
        <div class="doc-banner">
            <span class="doc-pildora-red">ANDALUCÍA MESH · RED MESHTASTIC ANDALUCÍA</span>
            <h1 class="doc-titulo-matriz">
                {{ ($datos['filtro'] ?? '') === 'todos' ? 'Matriz de Enlaces Completa (Todos)' : 'Matriz de Enlaces Positivos (SNR > 0 dB)' }}
            </h1>
            <p class="doc-subtitulo">
                @php
                    $nombresEvaluados = array_map(function ($n) {
                        return ($n['short_name'] ?? '') . ' (' . ($n['id'] ?? '') . ')';
                    }, $datos['nodos_evaluados'] ?? []);
                @endphp
                Nodos analizados: <strong>{{ implode(', ', $nombresEvaluados) }}</strong>
                · Ventana: {{ $datos['ventana_texto'] ?? 'Últimas 24 horas' }}
            </p>
            <div class="doc-aviso-directo">
                ⚡ Metodología: Enlaces directos 100% (0 saltos RF · Sin saltos multi-hop). Evaluamos la conectividad física directa con los vecinos de radiofrecuencia.
            </div>
        </div>

        <!-- 4 Tarjetas KPI Superiores -->
        <table class="tabla-kpis">
            <tr>
                @foreach($datos['kpis'] ?? [] as $kpi)
                    <td class="celda-kpi">
                        <div class="kpi-cifra">{{ $kpi['cifra'] }}</div>
                        <div class="kpi-titulo">{{ $kpi['titulo'] }}</div>
                        <div class="kpi-subtitulo">{{ $kpi['subtitulo'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>

        <!-- Tabla Estructurada con Bloques -->
        <table class="tabla-matriz">
            <thead>
                <tr>
                    <th style="width: 50px;">NODO ID</th>
                    <th>IDENTIDAD (ALIAS / NOMBRE)</th>
                    <th style="width: 48px;">ROL</th>
                    <th style="width: 55px;">PROV.</th>
                    <th style="width: 46px;">COBERTURA</th>
                    @foreach($datos['nodos_evaluados'] ?? [] as $ne)
                        <th class="th-centro" style="width: 60px;">
                            ENLACE {{ strtoupper($ne['short_name'] ?? ltrim($ne['id'], '!')) }}
                        </th>
                    @endforeach
                    <th class="th-derecha" style="width: 48px;">CONTACTO</th>
                </tr>
            </thead>
            <tbody>
                <!-- Bloque 1: Enlace Mutuo Inter-Router (si existe) -->
                @if(!empty($datos['fila_mutuo']))
                    @php $fm = $datos['fila_mutuo']; @endphp
                    <tr class="fila-bloque">
                        <td colspan="{{ 5 + count($datos['nodos_evaluados'] ?? []) + 1 }}">
                            <span style="color: #C084FC;">⇄</span> ENLACE DIRECTO INTER-NODO
                        </td>
                    </tr>
                    <tr class="fila-item">
                        <td class="col-id">{{ $fm['id'] }}</td>
                        <td class="col-identidad">
                            <strong>{{ $fm['identidad_titulo'] }}</strong>
                            <span>{{ $fm['identidad_subtitulo'] }}</span>
                        </td>
                        <td><span class="badge-rol {{ $fm['clase_rol'] ?? 'badge-rol-router' }}">{{ $fm['role'] ?? 'ROUTER' }}</span></td>
                        <td>
                            <span class="badge-prov {{ $fm['clase_provincia'] ?? 'badge-prov-cadiz' }}">{{ $fm['province'] ?? 'Cádiz' }}</span>
                            @if(!empty($fm['distancia_texto']))
                                <span class="distancia-km-pdf"><img src="{{ $rulerIconBase64 }}" class="icono-regla-pdf" alt=""> {{ $fm['distancia_texto'] }}</span>
                            @endif
                        </td>
                        <td><span class="badge-cob {{ $fm['cobertura_clase'] ?? 'badge-cob-mutuo' }}">{{ $fm['cobertura_label'] ?? 'Mutuo' }}</span></td>
                        @foreach($datos['nodos_evaluados'] ?? [] as $ne)
                            @php $col = $fm['enlaces'][$ne['id']] ?? null; @endphp
                            <td style="text-align: center;">
                                @if($col)
                                    <div class="caja-snr {{ $col['clase_calidad'] ?? 'snr-excelente' }}">
                                        <span class="caja-snr-valor">{{ $col['snr_formateado'] }}</span>
                                        <span class="caja-snr-meta">{{ $col['snr_avg_formateado'] ?? '' }}</span>
                                    </div>
                                @else
                                    <span style="color: #64748B;">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td style="text-align: right; color: #94A3B8;">{{ $fm['ultimo_contacto'] ?? 'Reciente' }}</td>
                    </tr>
                @endif

                <!-- Bloque 2: Cobertura Bilateral / Compartida / Individual -->
                @if(!empty($datos['bloque_bilateral']))
                    @php $cantBilat = count($datos['bloque_bilateral']); @endphp
                    <tr class="fila-bloque">
                        <td colspan="{{ 5 + count($datos['nodos_evaluados'] ?? []) + 1 }}">
                            <span style="color: #67EA94;">●</span> {{ $datos['titulo_bloque_comun'] ?? "NODOS CON COBERTURA BILATERAL ({$cantBilat} NODOS)" }}
                        </td>
                    </tr>
                    @foreach($datos['bloque_bilateral'] as $idx => $fila)
                        <tr class="fila-item {{ $idx % 2 === 1 ? 'fila-par' : '' }}">
                            <td class="col-id">{{ $fila['id'] }}</td>
                            <td class="col-identidad">
                                <strong>{{ $fila['short_name'] }}</strong>
                                <span>{{ $fila['long_name'] }}</span>
                            </td>
                            <td><span class="badge-rol {{ $fila['clase_rol'] }}">{{ $fila['role'] }}</span></td>
                            <td>
                                <span class="badge-prov {{ $fila['clase_provincia'] }}">{{ $fila['province'] }}</span>
                                @if(!empty($fila['distancia_texto']))
                                    <span class="distancia-km-pdf"><img src="{{ $rulerIconBase64 }}" class="icono-regla-pdf" alt=""> {{ $fila['distancia_texto'] }}</span>
                                @endif
                            </td>
                            <td><span class="badge-cob {{ $fila['cobertura_clase'] }}">{{ $fila['cobertura_label'] }}</span></td>
                            @foreach($datos['nodos_evaluados'] ?? [] as $ne)
                                @php $col = $fila['enlaces'][$ne['id']] ?? null; @endphp
                                <td style="text-align: center;">
                                    @if($col)
                                        <div class="caja-snr {{ $col['clase_calidad'] }}">
                                            <span class="caja-snr-valor">{{ $col['snr_formateado'] }}</span>
                                            <span class="caja-snr-meta">{{ $col['snr_avg_formateado'] }}</span>
                                        </div>
                                    @else
                                        <span style="color: #64748B;">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td style="text-align: right; color: #94A3B8;">{{ $fila['ultimo_contacto'] }}</td>
                        </tr>
                    @endforeach
                @endif

                <!-- Bloques Exclusivos -->
                @foreach($datos['bloques_exclusivos'] ?? [] as $nid => $filasExcl)
                    @if(!empty($filasExcl))
                        @php
                            $alias = '';
                            foreach ($datos['nodos_evaluados'] ?? [] as $ne) {
                                if ($ne['id'] === $nid) { $alias = $ne['short_name']; break; }
                            }
                            $cantExcl = count($filasExcl);
                            $subtitulo = $datos['subtitulos_bloques'][$nid] ?? '';
                            $sub = $subtitulo ? ' — ' . $subtitulo : '';
                        @endphp
                        <tr class="fila-bloque">
                            <td colspan="{{ 5 + count($datos['nodos_evaluados'] ?? []) + 1 }}">
                                COBERTURA EXCLUSIVA {{ $alias }}{{ $sub }} ({{ $cantExcl }} NODOS)
                            </td>
                        </tr>
                        @foreach($filasExcl as $idx => $fila)
                            <tr class="fila-item {{ $idx % 2 === 1 ? 'fila-par' : '' }}">
                                <td class="col-id">{{ $fila['id'] }}</td>
                                <td class="col-identidad">
                                    <strong>{{ $fila['short_name'] }}</strong>
                                    <span>{{ $fila['long_name'] }}</span>
                                </td>
                                <td><span class="badge-rol {{ $fila['clase_rol'] }}">{{ $fila['role'] }}</span></td>
                                <td>
                                    <span class="badge-prov {{ $fila['clase_provincia'] }}">{{ $fila['province'] }}</span>
                                    @if(!empty($fila['distancia_texto']))
                                        <span class="distancia-km-pdf"><img src="{{ $rulerIconBase64 }}" class="icono-regla-pdf" alt=""> {{ $fila['distancia_texto'] }}</span>
                                    @endif
                                </td>
                                <td><span class="badge-cob {{ $fila['cobertura_clase'] }}">{{ $fila['cobertura_label'] }}</span></td>
                                @foreach($datos['nodos_evaluados'] ?? [] as $ne)
                                    @php $col = $fila['enlaces'][$ne['id']] ?? null; @endphp
                                    <td style="text-align: center;">
                                        @if($col)
                                            <div class="caja-snr {{ $col['clase_calidad'] }}">
                                                <span class="caja-snr-valor">{{ $col['snr_formateado'] }}</span>
                                                <span class="caja-snr-meta">{{ $col['snr_avg_formateado'] }}</span>
                                            </div>
                                        @else
                                            <span style="color: #64748B;">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td style="text-align: right; color: #94A3B8;">{{ $fila['ultimo_contacto'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        <!-- Leyenda Inferior -->
        <div class="leyenda-snr">
            <div style="float: left;">
                <span class="leyenda-item" style="color: #FFFFFF; font-weight: bold;">Calidad SNR:</span>
                <span class="leyenda-item"><span class="leyenda-dot" style="background: #67EA94;"></span> ≥ +10 dB (Excelente)</span>
                <span class="leyenda-item"><span class="leyenda-dot" style="background: #4ADE80;"></span> +5 a +9.9 dB (Muy buena)</span>
                <span class="leyenda-item"><span class="leyenda-dot" style="background: #FACC15;"></span> 0 a +4.9 dB (Buena / Límite)</span>
                @if(($datos['filtro'] ?? '') === 'todos')
                    <span class="leyenda-item"><span class="leyenda-dot" style="background: #F87171;"></span> &lt; 0 dB (Límite / Marginal)</span>
                @endif
            </div>
            <div style="float: right; color: #94A3B8;">
                Generado el {{ $datos['generado_en'] ?? date('d/m/Y H:i') }}
            </div>
            <div style="clear: both;"></div>
        </div>
    </main>

    <!-- Script de Numeración de Páginas Dompdf Canvas para A4 Vertical (Portrait) -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 6.5;
            $color = array(0.58, 0.64, 0.72);
            $text = "Pág. " . $PAGE_NUM . " / " . $PAGE_COUNT;
            $pdf->page_text(525, 825, $text, $font, $size, $color);
        }
    </script>
</body>
</html>
