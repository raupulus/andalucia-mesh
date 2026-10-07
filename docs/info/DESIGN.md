# DESIGN.md — Sistema visual del portal

> Paleta, tipografía, espaciado y componentes del portal de **Sur Nodos en Mallas** (`${PROJECT_NAME}`). Estética Material (tarjetas, elevación suave, iconos Material, rejilla de 8 px) pero **cuidada y oxigenada**: mucho aire, pocos colores, un solo acento. **Modo claro y modo oscuro con el mismo nivel de contraste.** Es la referencia obligatoria para quien implemente el portal: lo que no está aquí no se añade sin pasar por `dudas/`.

## 1. Principios

1. **Aire antes que adornos.** El espacio en blanco separa; no hacen falta líneas, cajas ni fondos de color para estructurar.
2. **Dos colores base, un acento.** Navy para texto y estructura, verde como único acento. Todo lo demás son neutros.
3. **Material, no plantilla.** Tarjetas, elevación y estados de Material, sin el aspecto por defecto de ningún framework. Si se usa uno, se sobrescriben sus tokens con los de este documento.
4. **Contraste AAA en texto, en los dos modos.** Todo texto ≥ 7:1 (WCAG 2.1 AAA), salvo texto deshabilitado o de marcador (≥ 4,5:1). Controles y elementos gráficos ≥ 3:1. El color nunca es la única forma de transmitir información.
5. **Ligero.** Sin vídeo, sin carruseles, sin animaciones en bucle; el QR se abre casi siempre en un móvil con mala cobertura.
6. **Sin marcas de terceros.** El portal no muestra nombres, logos ni insignias de otros proyectos. Logo propio del proyecto.

## 2. Colores base

Salen del logo de referencia: **Navy `#2C2D3C`** y **Verde `#67EA94`**. El resto de la paleta se deriva de ellos (mismo tono, distinta luminosidad). El verde base tiene un contraste de 1,52:1 sobre blanco: **nunca es texto sobre fondo claro**; en modo oscuro sí lo es (8,90:1).

## 3. Tokens semánticos (claro / oscuro)

La implementación usa **solo estos tokens**, nunca hexadecimales sueltos. Cada token tiene valor para los dos modos. Contrastes calculados con la fórmula de luminancia relativa de WCAG 2.1 contra **todos** los fondos donde se usa el token.

### Superficies

| Token | Claro | Oscuro | Uso |
|---|---|---|---|
| `fondo` | `#FFFFFF` | `#1F2029` | Fondo de página |
| `superficie` | `#FFFFFF` (+ borde) | `#2C2D3C` | Tarjetas, panel de provincia |
| `superficie-elevada` | `#FFFFFF` (nivel 2) | `#363748` | Hover de tarjeta, menús, paneles flotantes |
| `superficie-sutil` | `#F7F8FA` | `#17181F` | Pie, bandas secundarias |
| `borde` | `#E0E1EB` | `#3D3E4D` | Separadores y borde de tarjetas (decorativo) |
| `borde-control` | `#8D8EA6` | `#7E819B` | Borde de campos y botón secundario: ≥ 3,02:1 (claro) / ≥ 3,06:1 (oscuro) |

### Texto

| Token | Claro | Contraste (peor caso) | Oscuro | Contraste (peor caso) | Uso |
|---|---|---|---|---|---|
| `texto` | `#2C2D3C` | 12,77:1 | `#F0F0F5` | 10,29:1 | Texto principal, títulos |
| `texto-2` | `#515267` | 7,18:1 | `#C7C8D4` | 7,03:1 | Descripciones, fechas, cabeceras de tabla |
| `texto-3` | `#6E708C` | 4,53:1 | `#9FA0B4` | 4,54:1 | Solo deshabilitado y marcador de posición |
| `enlace` | `#15612F` | 7,10:1 | `#67EA94` | 7,67:1 | Enlaces, flecha de tarjeta, texto verde |
| `foco` | `#15612F` | 7,10:1 | `#67EA94` | 7,67:1 | Anillo de foco de 2 px con 2 px de separación |

"Peor caso" = el fondo más desfavorable de los que admite el token (`fondo`, `superficie`, `superficie-elevada` y `superficie-sutil`).

### Acento

| Token | Claro | Oscuro | Notas |
|---|---|---|---|
| `acento` | `#67EA94` | `#67EA94` | Relleno de botón primario, indicador activo, zona de imagen destacada |
| `sobre-acento` | `#2C2D3C` | `#2C2D3C` | Texto e iconos sobre `acento`: 8,90:1 en ambos modos |
| `acento-suave` | `#E9FCEF` | `#1C3A28` | Fondo de la zona de imagen de tarjetas |

### Estados y severidades (chips)

Texto ≥ 7:1 sobre su propio fondo en los dos modos. Siempre icono + palabra ("Crítico", "Aviso"…), nunca solo color.

| Token | Claro (texto / fondo) | Contraste | Oscuro (texto / fondo) | Contraste |
|---|---|---|---|---|
| `critico` | `#9B1E15` / `#FDECEC` | 7,10:1 | `#FFB4AE` / `#4A2326` | 7,95:1 |
| `aviso` | `#794700` / `#FEF4E2` | 7,09:1 | `#FFD48A` / `#47360F` | 8,34:1 |
| `info` | `#18509B` / `#EAF2FD` | 7,00:1 | `#A9C9FF` / `#1E3150` | 7,75:1 |
| `correcto` | `#15612F` / `#E9FCEF` | 7,06:1 | `#9CF1BA` / `#1C3A28` | 9,32:1 |
| `neutro` | `#6E708C` / `#F7F8FA` (borde `#E0E1EB`) | 4,82:1 | `#9FA0B4` / `#17181F` (borde `#3D3E4D`) | 5,27:1 |


### Mapa de provincias

| Elemento | Claro | Oscuro | Contraste relevante |
|---|---|---|---|
| Provincia verde (≤ 20 %) | `#82EEA7` | `#82EEA7` | — |
| Provincia naranja (> 20 % y < 40 %) | `#F59E0B` | `#F59E0B` | — |
| Provincia roja (≥ 40 %) | `#E5484D` | `#E5484D` | — |
| Provincia sin datos | `#E0E1EB` + trama diagonal | `#3D3E4D` + trama diagonal | La trama la distingue de "verde" |
| Trazo entre provincias | `#FFFFFF` (1,5 px) | `#1F2029` (1,5 px) | Igual al fondo: separa sin añadir líneas |
| Contorno exterior de Andalucía | `#515267` (1,5 px) | `#C7C8D4` (1,5 px) | 7,63:1 / 9,75:1 sobre el fondo: el mapa se lee aunque una provincia sea clara |
| Burbuja: fondo | `#FFFFFF` | `#1F2029` | Oscuro: ≥ 4,14:1 contra rellenos de color |
| Burbuja: borde (2 px) | `#2C2D3C` | `#F0F0F5` | Claro: ≥ 3,47:1 contra cualquier relleno. Oscuro: 9,27:1 contra "sin datos" |
| Burbuja: número | `#2C2D3C` | `#F0F0F5` | 13,57:1 / 14,26:1 |
| Burbuja de Cádiz: borde (3 px) | `#15612F` | `#67EA94` | Foco del proyecto |

Los rellenos no cambian entre modos para que el código de colores sea el mismo para todo el mundo. La luminosidad baja de forma ordenada (verde 0,69 → naranja 0,44 → rojo 0,22), así que los niveles se distinguen también en escala de grises y con daltonismo rojo-verde. Panel y tabla repiten el nivel en texto. Hover o selección de provincia: trazo de 2 px en `texto`.

### Gráficos (barra de desglose por tipo de paquete)

| Categoría | Claro | vs `superficie` | Oscuro | vs `superficie` | Ayuda extra |
|---|---|---|---|---|---|
| NodeInfo | `#2C2D3C` | 13,57:1 | `#F0F0F5` | 11,95:1 | — |
| Posición | `#6E708C` | 4,82:1 | `#9FA0B4` | 5,27:1 | — |
| Telemetría | `#1D8641` | 4,63:1 | `#24A851` | 4,39:1 | — |
| Texto | `#67EA94` | 1,52:1 | `#67EA94` | 8,90:1 | Claro: borde de 1 px `#1D8641` |
| Otros | `#CCCCD7` | 1,59:1 | `#55576D` | 1,92:1 | Trama + borde de 1 px `borde-control` |

Segmentos separados por un hueco de 2 px del color de la superficie. Leyenda siempre visible y valores también en la tabla.

## 4. Modo claro y oscuro

- **Por defecto**, el que tenga el sistema (`prefers-color-scheme`).
- **Conmutador** en la cabecera (icono sol/luna con texto accesible "Cambiar a modo oscuro/claro"). La elección se recuerda en el navegador como preferencia técnica (no requiere aviso de cookies; ver `10-legal-privacy.md`).
- Sin parpadeo al cargar: el modo se aplica antes de pintar la página.
- Ningún componente puede tener colores fijos: todo pasa por los tokens de la sección 3.
- Las ilustraciones tienen versión para cada modo (o usan solo colores de tokens).
- Revisión obligatoria: cada pantalla se comprueba en los dos modos antes de publicarla.

## 5. Tipografía

| Rol | Familia | Peso | Tamaño / interlineado (escritorio → móvil) |
|---|---|---|---|
| Portada (total de nodos) | Ubuntu | 500 | 40/48 → 32/40 |
| H1 | Ubuntu | 500 | 32/40 → 28/36 |
| H2 | Ubuntu | 500 | 24/32 |
| H3 (títulos de tarjeta) | Ubuntu | 500 | 20/28 |
| Texto | Inter | 400 | 16/26 |
| Texto secundario | Inter | 400 | 14/22 |
| Etiquetas y chips | Inter | 500 | 12/16, mayúsculas solo en chips |
| Números (burbujas, rankings, tablas) | Inter con cifras tabulares | 600 | Según contexto |
| Ids de nodo (`!a1b2c3d4`) | Ubuntu Mono | 400 | 14/22 |

- Ubuntu en títulos (redondeada, combina con el trazo del logo); Inter para texto largo por legibilidad.
- Fuentes **autoalojadas** (nada de cargarlas desde servidores de terceros), con subconjunto latino y `font-display: swap`.
- Longitud de línea máxima: 68 caracteres. En modo oscuro no se usa peso inferior a 400 (el texto fino se "come" sobre fondo oscuro).

## 6. Espaciado y rejilla

- Unidad base **8 px**. Escala: 4 · 8 · 12 · 16 · 24 · 32 · 48 · 64 · 96.
- Ancho máximo de contenido: **1120 px**, centrado; márgenes laterales 16 px en móvil, 32 px en tableta.
- Separación entre secciones: **96 px** en escritorio, **64 px** en móvil. Es lo que da el aire; no se reduce para "meter más cosas".
- Relleno interior de tarjetas: 24 px. Separación entre tarjetas: 24 px (móvil) / 32 px (escritorio).
- Rejilla de portada: 3 columnas en escritorio (≥ 960 px), 1 columna en móvil.

## 7. Forma, elevación y movimiento

| Elemento | Radio |
|---|---|
| Tarjetas, paneles | 16 px |
| Botones, campos | 8 px |
| Chips, burbujas, indicadores | Totalmente redondeado |

| Nivel | Uso | Claro | Oscuro |
|---|---|---|---|
| 0 | Superficies planas | Sin sombra, borde 1 px `borde` | Sin sombra, borde 1 px `borde` |
| 1 | Tarjetas en reposo, burbujas | Sombra muy suave teñida de navy (desplazamiento 1–2 px, desenfoque 8 px, opacidad ≈ 6 %) | Sin sombra (no se ve en oscuro): la elevación la da `superficie` sobre `fondo` |
| 2 | Hover de tarjeta, panel de provincia | Desplazamiento 4 px, desenfoque 16 px, opacidad ≈ 10 % | `superficie-elevada` + borde 1 px `borde` |

**Máximo dos niveles de elevación.** En claro, sombras teñidas de navy, nunca negras puras; en oscuro la elevación se expresa con superficies más claras, no con sombras.

Movimiento: 150 ms en hover, 200 ms en apariciones, curva de desaceleración estándar de Material. La tarjeta en hover sube 2 px y pasa a nivel 2; nada de rebotes ni escalados. Con "reducir movimiento" activado, sin animaciones. El cambio de modo claro/oscuro no se anima.

## 8. Iconos e imágenes

- **Iconos:** Material Symbols Rounded, contorno (sin relleno), grosor 400, 24 px; autoalojados en subconjunto. Color `texto` o `texto-2` según jerarquía.
- **Ilustraciones de tarjetas:** propias, planas, máximo 3 colores tomados de los tokens (`texto`, `acento` y un neutro), formas geométricas simples (nodos, enlaces, ondas), trazos de 2 px redondeados. Fondo de la zona de imagen `acento-suave`. Versión clara y oscura. Formato 16:9, WebP/AVIF < 40 KB.
- Sin fotos de stock ni capturas de pantalla.

## 9. Componentes del portal

### Tarjeta de servicio (portada)

- `superficie` con borde 1 px `borde`, radio 16 px, nivel 1; toda la tarjeta es el enlace.
- Zona de imagen 16:9 arriba (radio solo en esquinas superiores), fondo `acento-suave`.
- Título H3 en `texto`; descripción en `texto-2`, **máximo 2 líneas** con recorte.
- Indicador de enlace: flecha `→` en `enlace` junto al título.
- Estado opcional: punto de 8 px (`correcto` / `critico`, color de texto del token) con texto oculto para lectores de pantalla.
- Hover: nivel 2 y subida de 2 px. Foco: anillo `foco`.

### Mapa de provincias

- Total encima: estilo "Portada" en `texto`, con "nodos en Andalucía" en `texto-2` debajo.
- Colores, trazos y burbujas según la tabla del mapa (sección 3).
- Burbuja: círculo de 44 px (36 px en móvil), número en Inter 600 tabular, nivel 1.
- Leyenda bajo el mapa: muestras cuadradas de 12 px (radio 3 px, con borde 1 px `borde-control`) + texto en `texto-2`; en móvil, dos líneas.
- Panel de provincia: `superficie` / `superficie-elevada`, radio 16 px.

### Chips de severidad

Píldora de 24 px de alto, icono 16 px + texto, colores del token correspondiente (sección 3).

### Tablas (rankings, nodos en peligro)

- Filas de 48 px, separadores de 1 px `borde`, sin cebreado; hover de fila `superficie-sutil` (claro) / `superficie-elevada` (oscuro).
- Números a la derecha con cifras tabulares; ids de nodo en Ubuntu Mono.
- Cabecera en `texto-2`, 14 px, peso 500.

### Botones

| Tipo | Claro | Oscuro |
|---|---|---|
| Primario | Fondo `acento`, texto `sobre-acento` (8,90:1) | Igual |
| Secundario | Transparente, borde 1 px `borde-control`, texto `texto` | Igual con tokens oscuros |
| Texto/enlace | `enlace`, subrayado en hover | Igual con tokens oscuros |

Alto 40 px, radio 8 px. Un solo botón primario por pantalla.

### Bloque de ajustes (configura tu nodo, conexión MQTT)

- Tabla clave-valor de 2 columnas: ajuste en `texto-2`, valor en Ubuntu Mono `texto`; filas de 44 px, separadores 1 px `borde`, sin cebreado.
- Valores que se copian (servidor, root topic): botón de icono `content_copy` de 40 px, estilo secundario, con texto oculto "Copiar" y confirmación "Copiado" en `texto-2` durante 2 s (sin animación).
- Ajustes críticos (downlink desactivado): chip de **aviso** junto al valor; nunca solo color.
- Pasos numerados: número en círculo de 28 px, borde 1 px `borde-control`, cifra en Inter 600.
- En portada, cada bloque cabe en una tarjeta `superficie` sin imagen, con enlace de texto "Guía completa →" al final; no es tarjeta de servicio y no cuenta para el límite de tres.

### Autoría y enlaces externos

- Nombre en H3 `texto`, rol en `texto-2`, presentación en cuerpo normal (máx. 65 caracteres por línea).
- Dos listas en línea ("Webs", "Redes") con enlaces en `enlace`, cada uno con el icono `open_in_new` de 16 px detrás del texto.
- **Sin logos de redes sociales** ni widgets incrustados: solo el nombre de la red en texto.
- En móvil, las listas pasan a una columna con áreas de toque de 44 px.

### Bloque de código (ejemplos de API, mensajes de bot)

- Ubuntu Mono 14/22 sobre `superficie-sutil`, borde 1 px `borde`, radio 8 px, relleno 16 px; desplazamiento horizontal propio si no cabe (nunca en la página).
- Botón de copiar igual que en el "Bloque de ajustes".
- Mensajes de bot de ejemplo: mismo bloque, con el emoji de riesgo tal cual lo envía el bot.

### Panel `/admin` (Filament)

- Se usa el tema de Filament, no CSS propio: color primario `#15612F` (`enlace`; Filament genera la escala y mantiene el contraste de sus botones), grises de Filament, tipografía Inter.
- Modo claro y oscuro nativos de Filament, con su conmutador.
- Logo propio y `PROJECT_NAME` en la cabecera del panel.
- Este documento manda en las páginas públicas; en el panel solo se exige legibilidad AA de Filament y el color primario indicado.

### Cabecera y pie

- Cabecera: `fondo`, logo propio a la izquierda, navegación en `texto` y conmutador claro/oscuro a la derecha; en móvil, menú desplegable. Borde inferior rematado con las tres líneas representativas de la bandera de Andalucía (verde, blanca, verde finitas de 1,5 px).
- Pie: `superficie-sutil`, texto y enlaces en `texto-2` (subrayados, no verdes); enlaces legales, aviso de proyecto independiente y línea de autoría "Un proyecto de Raúl Caro Pastorino (@raupulus)".

## 10. Prohibido (para que nadie se pase)

1. Colores fuera de estos tokens, aunque sea "un azul más bonito". Nada de hexadecimales sueltos en componentes.
2. Verde `#67EA94` como texto sobre fondo claro. El texto verde en claro es `enlace` (`#15612F`).
3. Texto por debajo de 7:1 (salvo `texto-3` para deshabilitado/marcador, ≥ 4,5:1) en cualquiera de los dos modos.
4. Un componente que solo se haya revisado en un modo.
5. Más de un acento por pantalla.
6. Degradados, neones, brillos, glassmorphism, sombras negras o duras; sombras en modo oscuro.
7. Más de dos niveles de elevación.
8. Carruseles, vídeo de fondo, parallax, animaciones en bucle, contadores animados.
9. Fotos de stock y capturas de pantalla en las tarjetas.
10. Fuentes, iconos o scripts servidos desde terceros.
11. Más de tres tarjetas en la portada.
12. El color como único portador de información.
13. Nombres, logos o insignias de terceros en el portal.
14. Personalizar el CSS de PotatoMesh o MeshView (sería un fork que mantener). Solo se tocan sus opciones de configuración; la coherencia visual se garantiza en el portal.
15. Reducir la separación entre secciones para meter más contenido: si no cabe, sobra contenido.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
