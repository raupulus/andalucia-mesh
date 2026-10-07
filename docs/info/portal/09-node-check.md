# 06.9 · Revisa tu nodo

> `/revisa-tu-nodo` y `/revisa-tu-nodo/{id}`: la persona busca su nodo y la página le dice qué mejorar, con el dato que lo demuestra. Texto en `pages/14-node-check.md`. Datos de `13-nodes-alerts-api.md` (`nodes?search` y `nodes/{id}/diagnosis`).

## Objetivo

Que cualquiera sepa en un minuto si su nodo perjudica a la malla y qué cambiar, sin interpretar gráficas.

## Especificación

### Flujo

1. `/revisa-tu-nodo`: campo de búsqueda (id `!a1b2c3d4` o nombre corto/largo). Formulario `GET` (sin sesión ni CSRF) a `/revisa-tu-nodo?q=…`.
2. Una coincidencia → redirección 302 a `/revisa-tu-nodo/{id}`. Varias → lista para elegir (nombre, id, rol, provincia, último visto). Ninguna → mensaje con enlaces a `/conecta-tu-gateway` ("¿tu nodo sube con OK to MQTT?") y a `/configura-tu-nodo`.
3. `/revisa-tu-nodo/{id}`: resultado renderizado en servidor; enlazable desde fichas de alerta, nodos en peligro y bots.

### Resultado

| Bloque | Contenido |
|---|---|
| Resumen | Nombre corto y largo, id, rol, hardware, provincia, último visto, gateways que lo oyen (`heard_by`) |
| Puntos a mejorar | `findings` en el orden de la API: problema (`text`), dato que lo demuestra (`observed`), recomendación (`recommended`) y enlace "Cómo cambiarlo" a `guide` (`/configura-tu-nodo#…` o `/firmware`) |
| Alertas abiertas | `open_alerts` con chip de riesgo y enlace a cada ficha |
| Consumo de red | Puesto en `network-usage` (7 días) "de N", tiempo de aire y barra por tipo |
| Todo bien | `findings` vacío y sin alertas: "Tu nodo sigue las recomendaciones. Gracias por cuidar la malla." |

- Comprobaciones y umbrales: los de `13-nodes-alerts-api.md` (`config/proyecto.php` → `recomendaciones`). La página no calcula nada: pinta lo que devuelve la API.
- Ventana fija de 7 días, indicada en la página.
- Nodo sin datos en 7 días: "No hemos recibido datos de este nodo en 7 días" + enlaces de ayuda.
- Si `alertas` no responde: bloque de alertas con "no disponible ahora"; el resto se muestra.
- `noindex` en `/revisa-tu-nodo/{id}` (son diagnósticos individuales; no aportan en buscadores).

### Textos de los hallazgos

`text` llega de la API ya redactado en español con los valores (por ejemplo, "Telemetría cada 12 min de media; lo recomendado es 4 h o más"). Duraciones formateadas en la API (min, h, días). La página solo añade el título por `check` desde `lang/es/diagnostico.php`.

## Contratos propios

- Rutas `revisa-tu-nodo` (`GET`, opcional `q`) y `revisa-tu-nodo/{id}` (id normalizado como en la API; uno no válido → `404`).
- `lang/es/diagnostico.php`: título por `check` (`telemetry-interval`, `environment-interval`, `nodeinfo-interval`, `position-interval`, `battery-low-sustained`, `reboots`, `hops`, `role`, `channel-busy`).
- Anclas de `/configura-tu-nodo` (`03-node-setup-guide.md`).

## Unidades de trabajo

- **UT-06.9.1 — Búsqueda.** Formulario sin sesión, redirección con una coincidencia, lista con varias. *Aceptación:* `!A1B2C3D4`, `a1b2c3d4` y el nombre corto llevan al mismo nodo.
- **UT-06.9.2 — Resultado.** Bloques, orden, enlaces de guía. *Aceptación:* cada hallazgo muestra problema, dato y enlace.
- **UT-06.9.3 — Estados.** Sin datos, desconocido, `alertas` caída, todo bien. *Aceptación:* los cuatro con datos simulados.
- **UT-06.9.4 — Títulos de comprobaciones.** *Aceptación:* prueba que falla si la API devuelve un `check` sin título en `lang/es/diagnostico.php`.

## Escenarios de prueba

1. **Dado** un nodo con telemetría cada 12 min, **cuando** se abre su diagnóstico, **entonces** el primer punto dice el intervalo observado y enlaza a `/configura-tu-nodo#intervalos`.
2. **Dado** un nodo que sigue todas las recomendaciones, **cuando** se abre, **entonces** aparece "Tu nodo sigue las recomendaciones".
3. **Dado** una búsqueda "CAD" con 3 coincidencias, **cuando** se envía, **entonces** se listan las 3 y no se redirige.
4. **Dado** un id que nunca ha subido datos, **cuando** se busca, **entonces** "no lo encontramos" con los enlaces de ayuda.
5. **Dado** un nodo con 4 reinicios en 7 días, **cuando** se abre, **entonces** sale el punto de reinicios con enlace a `/firmware`.
6. **Dado** la página de resultado, **cuando** se inspeccionan cabeceras, **entonces** `X-Robots-Tag: noindex` y ninguna cookie.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
