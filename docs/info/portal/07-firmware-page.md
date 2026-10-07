# 06.7 · Página de firmware y apps

> `/firmware`: enlaces oficiales para gestionar y actualizar el nodo y qué versión elegir. Texto en `pages/15-firmware.md`.

## Objetivo

Que quien va a instalar o actualizar llegue al sitio oficial correcto y elija la versión adecuada sin dudar.

## Especificación

### Enlaces (texto, sin logos; excepción decidida)

| Texto | URL |
|---|---|
| Cliente web | https://client.meshtastic.org/ |
| App para iOS | https://msh.to/ios |
| App para Android | https://msh.to/android |
| Descargas oficiales | https://meshtastic.org/downloads/ |
| Versiones del firmware | https://github.com/meshtastic/firmware/releases |

- URLs en `config/proyecto.php` → `firmware.enlaces`; ninguna escrita en la vista.
- Los cinco visibles sin desplazarse en escritorio; en móvil, lista con área de toque completa.
- Abren en la misma pestaña con `rel="noopener"`.

### Qué versión elegir (texto obligatorio, junto al enlace de versiones)

- Solo hay dos tipos de versión: **Alpha** y **Beta**.
- **Alpha:** versiones de prueba; pueden ir bien o retirarse por fallos.
- **Beta:** la más probada por la comunidad y la más estable. **Ante la duda, la última beta.**

### Enlaces entrantes

Desde la navegación (pie), desde `/configura-tu-nodo` y desde `/revisa-tu-nodo` (hallazgo `reboots`).

## Contratos propios

- `config/proyecto.php` → `firmware.enlaces[]` (`texto`, `url`, `descripcion`).
- Texto en `resources/contenido/firmware.md`.

## Unidades de trabajo

- **UT-06.7.1 — Página.** *Aceptación:* los cinco enlaces y el texto Alpha/Beta visibles sin desplazamiento a 1280×800.
- **UT-06.7.2 — Verificación de enlaces.** Incluidos en `portal:enlaces` (`05-authorship.md`). *Aceptación:* un enlace roto aparece en el panel.

## Escenarios de prueba

1. **Dado** un escritorio de 1280×800, **cuando** se abre `/firmware`, **entonces** se ven los cinco enlaces y la explicación sin desplazarse.
2. **Dado** el hallazgo `reboots` en "Revisa tu nodo", **cuando** se pulsa su enlace, **entonces** se llega a `/firmware`.
3. **Dado** la página, **cuando** se inspecciona, **entonces** no hay logos ni imágenes de terceros, solo texto.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
