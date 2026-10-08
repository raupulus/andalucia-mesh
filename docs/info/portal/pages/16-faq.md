# 16 · FAQ (Preguntas Frecuentes)

> `/faq` · Centro de ayuda, dudas y resolución de incidencias comunes de la red · `../14-operator-panel.md`

## SEO

- **Título:** Preguntas Frecuentes (FAQ) · {PROJECT_NAME}
- **Descripción:** Respuestas claras y directas a las dudas y problemas más habituales sobre la red Meshtastic Andalucía Mesh.

## Estructura

1. Enlace superior de retorno al inicio (`/`).
2. Cabecera oxigenada con badge pill temático, H1 y texto de introducción lead.
3. Buscador interactivo en tiempo real con icono, botón de limpieza rápida y atajo de teclado Escape.
4. Barra de estado con contador de preguntas coincidentes y botones de control "Expandir todas" / "Contraer todas".
5. Acordeón interactivo accesible (`<details>` / `<summary>`) con números correlativos, chevrons animados y soporte de respuestas formateadas en Markdown (listas, enlaces, negritas, bloques de código).
6. Botón de copia de enlace permanente por cada pregunta (`#faq-{id}`).
7. Soporte para enlaces directos con hash URL: apertura automática y scroll suave hacia la pregunta indicada.
8. Estado sin resultados con opción de restablecer búsqueda.
9. Estado vacío cuando aún no existen preguntas dadas de alta.
10. Tarjeta inferior de soporte comunitario enlazando al diagnóstico de nodo y buzón de sugerencias.

## Datos dinámicos y configuración

- Consulta directa a base de datos de preguntas activas: `Faq::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')`.
- Traducción dinámica según el locale activo de la aplicación (`es`, `en`, `pt`) con fallback transparente a español (`getTranslatedQuestion()`, `getTranslatedAnswer()`).
- Renderizado de respuestas con `ContenidoMarkdown::convertText()` para conversión segura a HTML y reemplazo de variables globales (`{PROJECT_NAME}`, `{PROJECT_DOMAIN}`, `{TELEGRAM_BOT_USERNAME}`, etc.).
- Gestión interna desde Filament en `/admin/faqs` (`FaqResource`) con selector superior de idiomas por pestañas (`Tabs`), visibilidad a la derecha de la pregunta, editor Markdown a ancho completo y reordenación interactiva arrastrando filas en la tabla.

---
> Creado: 2026-10-08 · Última revisión: 2026-10-08
