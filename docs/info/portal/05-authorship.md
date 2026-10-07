# 06.5 · Autoría

> Bloque "Quién está detrás" de la portada, página `/quien-lo-impulsa`, línea de autoría del pie y `<meta name="author">`. Datos de la página pública https://raupulus.dev/about/ (consultada 2026-10-02). Texto en `pages/03-who.md`.

## Objetivo

Decir con claridad quién impulsa el proyecto y cómo contactar, desde una única fuente de datos y sin cargar nada de terceros.

## Especificación

### Fuente única: `config/autoria.php`

| Campo | Valor |
|---|---|
| Nombre | Raúl Caro Pastorino |
| Nick | @raupulus |
| Rol | Desarrollador web full stack especializado en backend |
| Presentación | Texto de su página pública "about" (borrador en `pages/03-who.md`) |
| Contacto | `PROJECT_CONTACT` (`public@raupulus.dev`) |

Webs:

| Texto | URL |
|---|---|
| raupulus.dev | https://raupulus.dev |
| api.raupulus.dev | https://api.raupulus.dev |

Redes:

| Texto | URL |
|---|---|
| LinkedIn | https://www.linkedin.com/in/raulcaropastorino |
| GitHub | https://github.com/raupulus |
| Instagram | https://www.instagram.com/raupulus/ |
| Telegram (canal de difusión) | https://t.me/raupulus_diffusion |

Se usa siempre `raupulus.dev`. Ningún otro dato personal.

### Presentación

- **Portada:** último bloque antes del pie. Nombre (H3), rol en `texto-2`, presentación, listas "Webs" y "Redes".
- **Página `/quien-lo-impulsa`:** el mismo bloque, contacto y cómo echar una mano (sin colaboradores que mencionar).
- **Pie:** "Un proyecto de Raúl Caro Pastorino (@raupulus)" enlazado a https://raupulus.dev.
- Enlaces de texto con el nombre de la red e icono genérico de enlace externo. **Sin logos de redes**, sin widgets ni incrustados: solo `<a>`.
- `rel="me noopener"` en los perfiles propios; `target` por defecto (misma pestaña).
- Componente "Autoría y enlaces externos" de `DESIGN.md`.

## Contratos propios

- `config/autoria.php`: `nombre`, `nick`, `rol`, `presentacion`, `contacto` (= `PROJECT_CONTACT`), `webs[]` (`texto`, `url`), `redes[]` (`texto`, `url`).
- Componente Blade `x-autoria` (variantes `bloque` y `pie`).

## Unidades de trabajo

- **UT-06.5.1 — Configuración.** `config/autoria.php` y `x-autoria`. *Aceptación:* cambiar una URL la actualiza en portada, página, pie y `meta`.
- **UT-06.5.2 — Bloque y página.** *Aceptación:* 2 webs y 4 redes visibles; ninguna petición a terceros al cargar.
- **UT-06.5.3 — Verificación de enlaces.** Comando `php artisan portal:enlaces` que comprueba las 6 URL (se ejecuta a mano o desde el scheduler semanal y avisa en el panel). *Aceptación:* un enlace roto aparece en el panel; no bloquea el despliegue.

## Escenarios de prueba

1. **Dado** la portada, **cuando** se inspeccionan las peticiones, **entonces** el bloque de autoría no genera ninguna a otro dominio.
2. **Dado** la URL de Instagram cambiada en `config/autoria.php`, **cuando** se despliega, **entonces** cambia en portada, página y pie.
3. **Dado** un lector de pantalla, **cuando** recorre las redes, **entonces** cada enlace anuncia el nombre de la red y que es externo.
4. **Dado** el HTML de cualquier página, **cuando** se busca un correo distinto de `PROJECT_CONTACT`, **entonces** no aparece.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
