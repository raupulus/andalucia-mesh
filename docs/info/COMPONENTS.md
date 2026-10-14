# Catálogo de Componentes

Catálogo de componentes reutilizables de interfaz de usuario de Andalucía Mesh, con sus propiedades, variantes y contratos de uso según [DESIGN.md](DESIGN.md).

## Componentes Registrados

### `x-cabecera` (`cabecera.blade.php`)
- **Propósito:** Barra superior fija con logotipo oficial, navegación principal agrupada (enlaces directos `Mapa`, `Herramientas`, `Páginas` y submenús desplegables con ordenación alfabética estricta: `Tu Nodo`, `Estadísticas`, `Documentación`, `Extras`) con clases unificadas `.nav-enlace` y `.dropdown-nav-toggle` que garantizan alineación horizontal y vertical milimétrica entre todos los elementos de menú, menú móvil modular estructurado en bloques temáticos, selector de idioma redondo con banderas y conmutador de contraste accesible (modo claro / modo oscuro persistente en `localStorage`) sobre franja tricolor andaluza.
- **Ranuras:** Ninguna.

### `x-pie` (`pie.blade.php`)
- **Propósito:** Pie de página común con enlaces institucionales, técnicos y legales, autoría del proyecto y aviso de exclusión de servicio de emergencias.
- **Ranuras:** Ninguna.

### `x-chip-estado` (`chip-estado.blade.php`)
- **Propósito:** Indicador visual accesible de severidad o estado de un nodo, alerta, regla o provincia. Cumple WCAG 2.1 AAA combinando icono textual, color semántico de fondo y texto descriptivo explícito (nunca solo color).
- **Propiedades:**
  - `nivel` / `tipo` (string, opcional, por defecto `'info'`): `'critico'` (`✕`, rojo), `'aviso'` (`▲`, naranja/ámbar), `'correcto'` (`✓`, verde), `'info'` (`ℹ`, azul) o `'neutro'` (`—`, fondo sutil y texto atenuado). Admite indistintamente `nivel` o `tipo` como alias compatibles.
  - `texto` (string, opcional): etiqueta textual del chip. Si se omite, se utiliza el `$slot`.
- **Atributos:** Soporta paso directo de atributos HTML y clases adicionales (`$attributes->merge(...)`).

### `x-tarjeta-servicio` (`tarjeta-servicio.blade.php`)
- **Propósito:** Tarjeta de presentación de herramientas clave en portada (MeshView, PotatoMesh, Rankings). Proporción de imagen 16:9 con fondo temático suave, título H3, descripción resumida y flecha indicadora.
- **Propiedades:**
  - `id` (string): identificador del servicio.
  - `titulo` (string): nombre de la plataforma.
  - `descripcion` (string): texto resumen de hasta dos líneas.
  - `url` (string): enlace de destino.
  - `imagen` (string): ruta a la ilustración WebP optimizada.

### `x-mapa-provincias` (`mapa-provincias.blade.php`)
- **Propósito:** Visualización cartográfica vectorial SVG de Andalucía por provincias coloreadas según saturación ponderada de canales LoRa, burbujas con recuento de nodos, conmutador de ventana temporal (7 días / 24 horas) y panel de detalle accesible con foco por teclado y ratón.
- **Subcomponentes integrados:** Tabla accesible desplegable con orden provincial, recuento de nodos, % relativo, porcentaje de saturación media y chips de estado accesibles (`x-chip-estado`).
- **Propiedades:**
  - `provinciasConfig` (array): mapa de códigos y nombres oficiales.
  - `mapaDatos` (array): datos consolidados de nodos, saturación media y nivel de severidad.
  - `totalAndalucia` (int): total agregado de nodos detectados en la comunidad.
  - `fueraAndalucia` (int): recuento de nodos oídos fuera de las 8 provincias.
  - `ventana` (string): `'7d'` o `'24h'`.

### `x-aviso-emergencias` (`aviso-emergencias.blade.php`)
- **Propósito:** Banner de llamada de atención y seguridad advirtiendo que la red ciudadana no constituye un servicio de emergencias ni sustituye al 112.
- **Ranuras:** Personalizable o con texto por defecto.

## Patrones y Componentes de Páginas Dinámicas

### `.tarjeta-pagina-horizontal` / `.tarjeta-pagina-compacta`
- **Propósito:** Presentación de artículos y páginas en formato horizontal ocupando el ancho completo (catálogo `/paginas`) o compacto en cuadrícula 2x2 (portada `/`).
- **Composición:**
  - Contenedor de imagen izquierda con `object-fit: cover` y efecto zoom suave al hover (`transform: scale(1.04)`).
  - Separador `.franja-andalucia-vertical`: franja verde/blanca/verde de 9px (se transforma a horizontal de 9px en pantallas móviles <= 680px).
  - Borde dual verde y blanco con contraste accesible en modo claro (`border: 1.5px solid #007A33; box-shadow: 0 0 0 1.5px #FFFFFF, 0 0 0 3px rgba(0, 122, 51, 0.25)`) y modo oscuro (`box-shadow: 0 0 0 1.5px rgba(255, 255, 255, 0.2), 0 0 0 3px rgba(0, 122, 51, 0.4)`).
  - Cuerpo con título H2/H3, fecha en formato europeo legible, descripción resumida y badges verdes de palabras clave alineados en la esquina inferior derecha.

### `.badge-keyword`
- **Propósito:** Píldora visual para palabras clave y temas de páginas (`#007A33` sobre texto blanco `#FFFFFF`, ratio de contraste 7.36:1, superando WCAG AAA).

### `.btn-verde`
- **Propósito:** Botón de acción principal verde Andalucía (`#007A33`) con texto blanco y realce interactivo para llamadas a la acción directas como el botón de catálogo en la portada.

### `.btn-configurador-destacado`
- **Propósito:** Botón de llamada a la acción de alta visibilidad para el configurador automático de nodos. Emplea un degradado Verde Andalucía (`#008F3E` a `#007A33`), borde verde nítido (`#00B348`), texto blanco nítido con sombra y elevación reactiva en hover, garantizando máximo protagonismo visual frente a botones secundarios en portada y guías.

### `.tarjeta-tabla-andalucia` y `.prose table`
- **Propósito:** Contenedores y tablas estilizadas a ancho completo para todas las tablas y bloques de configuración clave: tanto en la portada como en las páginas específicas de contenido Markdown (`/configura-tu-nodo` para las tablas de Radio, Rol e Intervalos; y `/conecta-tu-gateway` para la tabla de Ajustes MQTT).
- **Diseño visual y variantes:**
  - Ocupan el 100% del ancho disponible en el contenedor de la página (`display: table; width: 100%; border-collapse: separate; border-spacing: 0;`), eliminando restricciones artificiales de anchura para evitar encajonamientos y lucir a gran tamaño en cualquier pantalla.
  - Borde tricolor Verde/Blanco/Verde de inspiración andaluza (`border: 2px solid #007A33; box-shadow: 0 0 0 1.5px #FFFFFF, 0 0 0 3px rgba(0, 122, 51, 0.25)` en modo claro y halo exterior verde en modo oscuro), replicando el acabado de las tarjetas del listado de páginas.
  - Fondo con efecto de reflejo cromado sutil verde/blanco mediante gradiente diagonal en ángulo de 135º, aportando un acabado brillante y distinguido sin perjudicar en ningún momento la legibilidad ni el contraste WCAG 2.1 AAA de los textos, cifras y bloques `<code>`.
  - Cabeceras (`thead th`) con degradado translúcido verde/blanco, tipografía destacada y borde divisor nítido.
  - Celdas (`tbody td`) amplias y desahogadas con hover interactivo translúcido y códigos `<code>` con realce verde andaluz.
  - Elementos complementarios: `.tarjeta-tabla-andalucia-cabecera` con fondo translúcido y borde separador, `.tarjeta-tabla-andalucia-cuerpo`, y tarjetas de parámetro `.param-box-andalucia`, `.param-box-andalucia-aviso` y `.param-box-andalucia-destacado` con fondos translúcidos que dejan traslucir el reflejo cromado.
  - Adaptabilidad móvil con desplazamiento horizontal fluido (`overflow-x: auto`) en dispositivos de pantalla reducida.
  - Elevación reactiva suave en hover con realce del anillo tricolor.

### `x-aviso-pruebas` (`aviso-pruebas.blade.php`)
- **Propósito:** Aviso flotante global y persistente ubicado en la esquina inferior derecha de la pantalla (o barra inferior adaptable en móviles), indicando a los visitantes que la plataforma se encuentra en fase activa de desarrollo y pruebas, y que determinados datos o nodos pueden no estar 100% verificados.
- **Comportamiento interactivo:**
  - Botón de cierre (`✕`) y botón «Entendido» que colapsan la tarjeta en una píldora flotante compacta (`⚠️ Entorno en pruebas`).
  - Al pulsar la píldora, el aviso vuelve a expandirse.
  - El estado minimizado se persiste en `sessionStorage` (`snm_aviso_pruebas_minimizado = '1'`) para mantener la preferencia durante la sesión de navegación sin emitir cookies de seguimiento (cumpliendo estrictamente RN-06).
- **Accesibilidad y diseño:**
  - Rol ARIA semántico `role="status"` y `aria-live="polite"`.
  - Estilo de advertencia/ámbar según `DESIGN.md` con borde semántico, icono de alerta, botón accesible y soporte nativo para temas claro y oscuro.

### `x-separador-bandera` (`separador-bandera.blade.php`)
- **Propósito:** Separador horizontal decorativo con la bandera tricolor de Andalucía (verde `#007A33` / blanco `#FFFFFF` / verde `#007A33`) para delimitar secciones, cabeceras de página y bloques temáticos.
- **Propiedades / Atributos:** Admite paso directo de atributos HTML y clases adicionales mediante `$attributes->merge(...)`. Utiliza la clase de utilidad `.borde-bandera-andalucia`.

### `x-botones-compartir` (`botones-compartir.blade.php`)
- **Propósito:** Barra reutilizable y parametrizable de botones para compartir contenido o fichas en redes y canales directos: WhatsApp, Telegram, Correo Electrónico y Copiar Enlace permanente con portapapeles.
- **Propiedades:**
  - `url` (string, opcional, por defecto `url()->current()`): enlace de la página o recurso a compartir.
  - `titulo` (string, opcional, por defecto nombre del proyecto): asunto o título descriptivo.
  - `texto` (string, opcional): cuerpo de texto sugerido para la difusión.
  - `whatsappUrl` / `telegramUrl` / `emailUrl` (string, opcional): enlaces de acción directos personalizados si la vista requiere un formateo previo específico (ej. `/mi-cobertura`).
  - `align` (string, `'start'` | `'center'` | `'end'`, por defecto `'start'`): alineación del contenedor flex.
- **Estilos y comportamiento:**
  - Colores distintivos de marca por plataforma (WhatsApp verde `#25D366`, Telegram azul `#229ED9`, Email coral `#EA4335`, Copiar enlace con acento verde Andalucía).
  - Copiado universal asíncrono (`navigator.clipboard.writeText` con fallback a `document.execCommand('copy')`).
  - Feedback visual inmediato e interactivo tras copiar (`✓ Copiado` con transición y reversión automática tras 2.2 segundos).
  - Accesibilidad WCAG AAA: contraste de textos, atributos `title` y `aria-label` localizados con i18n (`portal.share.*`).

### `x-tabla-routers` (`tabla-routers.blade.php`)
- **Propósito:** Componente unificado y reactivo de supervisión de repetidores y nodos de infraestructura de la red, utilizado de forma idéntica en la vista pública `/routers` y en el widget del cuadro de mandos de administración (`RoutersInfraestructuraWidget` en `/admin`).
- **Composición y características:**
  - Cabecera con título, insignia de total según ámbito (`andalucia`, `espana`, `ambos`), y píldora de atención si existen alertas activas.
  - Barra de herramientas con píldoras de navegación rápida por provincia (`all_provinces`, provincias individuales) con recuentos dinámicos.
  - Banner informativo de filtrado activo con botón de restablecimiento rápido.
  - Tabla completa agrupada por provincias con filas de cabecera provincial (recuentos, nodos online, alertas) y filas de repetidor (estado online/offline, nombre corto/largo/ID, modelo hardware y rol, alimentación/batería con barras de progreso cromáticas, presión de canal ChUtil, saturación TX, último reporte con marca temporal local y botón de inspección diagnóstica `/revisa-tu-nodo/{id}`).
  - Delegación de eventos en `document` para filtrado reactivo compatible tanto con renderizado estático/SPA como con ciclos de vida Livewire (`wire:poll.30s`, `morph.updated`).
  - Actualización limpia de URL mediante `history.replaceState` preservando cero cookies (cumplimiento estricto RN-06).
- **Propiedades:**
  - `routers` (array, opcional): colección plana ordenada de routers.
  - `routersPorProvincia` (array, opcional): mapa indexado de routers agrupados por provincia.
  - `total` (int): total de repetidores.
  - `enAlerta` (int): número de routers con parámetros en nivel de atención.
  - `errorFuente` (string|null): mensaje de indisponibilidad de datos si procede.
  - `ambito` (string): `'andalucia'`, `'espana'` o `'ambos'`.
  - `filtroProvincia` (string, opcional): provincia seleccionada inicialmente.
  - `langQuerySolo` (string, opcional): parámetro de idioma para el enlace de inspección.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-14

