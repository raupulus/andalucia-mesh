# Catálogo de Componentes

Catálogo de componentes reutilizables de interfaz de usuario de Andalucía Mesh, con sus propiedades, variantes y contratos de uso según [DESIGN.md](DESIGN.md).

## Componentes Registrados

### `x-cabecera` (`cabecera.blade.php`)
- **Propósito:** Barra superior fija con logotipo oficial SVG, navegación principal con indicador visual de ruta activa, conmutador de contraste (modo claro / modo oscuro persistente en `localStorage`) y franja inferior tricolor representativa de Andalucía (verde `#007A33` / blanco `#FFFFFF` / verde).
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

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
