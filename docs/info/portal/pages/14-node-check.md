# 14 · Revisa tu nodo

> `/revisa-tu-nodo` · Diagnóstico de un nodo con lo que conviene mejorar · `../09-node-check.md`

## SEO

- **Título:** Revisa tu nodo · {PROJECT_NAME}
- **Descripción:** Escribe el id o el nombre de tu nodo y te decimos qué mejorar: telemetría, batería, reinicios, saltos y configuración.

## Estructura

1. Cabecera con título y buscador (Bloque de ajustes, campo + botón primario).
2. Resumen del nodo (Tarjeta sin imagen).
3. Puntos a mejorar (lista con chips de riesgo).
4. Alertas abiertas (Tabla).
5. Consumo de red (Tabla + barra de desglose).

## Borrador del texto

**Revisa tu nodo**
Escribe el id de tu nodo (por ejemplo `!a1b2c3d4`) o su nombre y te diremos qué puedes mejorar para que funcione mejor y no sature la malla.

**Puntos a mejorar**
Hemos revisado lo que tu nodo ha emitido en los últimos 7 días. Esto es lo que te recomendamos cambiar:
- *Ejemplo:* Tu nodo envía telemetría cada {intervalo} de media. Lo recomendado es {recomendado}. Súbelo en la configuración de telemetría del nodo. [Cómo hacerlo →](/configura-tu-nodo)
- *Ejemplo:* La batería ha estado por debajo del 40 % en {porcentaje} de las lecturas. Revisa el panel solar, la batería o baja los intervalos de emisión.

**Todo bien**
No vemos nada que mejorar. Gracias por cuidar la malla.

**Sin datos**
No tenemos datos de este nodo. Puede que no lo oiga ningún gateway o que tenga OK to MQTT desactivado. [Conecta tu gateway →](/conecta-tu-gateway)

## Datos dinámicos y configuración

- `GET /api/v1/nodes/{id}/diagnosis` (búsqueda por nombre: `GET /api/v1/nodes?search=`).
- Umbrales: `config/proyecto.php` y `/api/v1/alerts/catalog`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
