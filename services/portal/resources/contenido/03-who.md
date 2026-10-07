# 03 · Quién lo impulsa

> `/quien-lo-impulsa` · Decir quién está detrás del proyecto, cómo contactar y cómo echar una mano · `../05-authorship.md`

## SEO

- **Título:** `Quién lo impulsa · {PROJECT_NAME}` (38 caracteres)
- **Descripción:** `{PROJECT_NAME} es un proyecto personal y sin ánimo de lucro de Raúl Caro Pastorino (@raupulus). Quién es, cómo contactar y cómo echar una mano.` (148)
- `<meta name="author">` desde `config/autoria.php` (común a todo el portal).

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y entradilla | Tipografía H1 + Texto |
| 2 | Bloque de autoría: nombre, rol, presentación, webs y redes | Autoría y enlaces externos (sin logos de redes, solo texto con icono `open_in_new`) |
| 3 | Contacto | Texto corrido con enlace `mailto:` en `enlace` |
| 4 | Cómo echar una mano | Lista con enlaces |

Sin colaboradores que mencionar.

## Borrador del texto

Cada `###` es un H2 de la página.

**H1:** Quién lo impulsa

{PROJECT_NAME} es un proyecto personal y sin ánimo de lucro de Raúl Caro Pastorino. Lo diseña, lo desarrolla y lo mantiene él, con la ayuda de quienes conectan sus gateways para que la malla se vea entera.

**H3:** Raúl Caro Pastorino

Desarrollador web full stack especializado en backend · @raupulus

"Soy un desarrollador backend con amplia experiencia en PHP, Laravel, Javascript y PostgreSQL. A lo largo de mi carrera he trabajado en una variedad de proyectos, desde pequeños sitios web hasta grandes aplicaciones empresariales."

- **Webs:** [raupulus.dev](https://raupulus.dev) · [api.raupulus.dev](https://api.raupulus.dev)
- **Redes:** [LinkedIn](https://www.linkedin.com/in/raulcaropastorino) · [GitHub](https://github.com/raupulus) · [Instagram](https://www.instagram.com/raupulus/) · [Telegram (canal de difusión)](https://t.me/raupulus_diffusion)

### Contacto

Para cualquier cosa del proyecto (dar de alta un gateway, coordinar un router, avisar de un error o ejercer tus derechos sobre tus datos), escribe a [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### Cómo echar una mano

- **Configura bien tu nodo.** Es la mejor ayuda que puedes darle a la malla. [Configura tu nodo →](/configura-tu-nodo)
- **Conecta tu gateway.** Si tu nodo tiene internet, sube lo que oye y ayuda a que se vea toda la red. [Conecta tu gateway →](/conecta-tu-gateway)
- **Avísanos** si ves algo raro en los datos, en las alertas o en la web.

Por ahora no aceptamos donaciones; lo valoraremos cuando termine el desarrollo.

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Nombre, nick, rol, presentación, webs y redes | `config/autoria.php`. Los enlaces llevan `rel="me noopener"` |
| Contacto | `PROJECT_CONTACT` |
| Textos fijos | `resources/contenido/quien-lo-impulsa.md`. Sin datos de la API |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
