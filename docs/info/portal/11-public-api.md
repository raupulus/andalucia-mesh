# 06.11 · API pública

> Base común de la API JSON de solo lectura del portal en `https://${PROJECT_DOMAIN}/api/v1`: rutas, conexiones de lectura, formato, errores, paginación, caché, límite, CORS, cabeceras, versionado y la página `/api`. Los endpoints concretos están en [12](12-stats-api.md) y [13](13-nodes-alerts-api.md).

## Objetivo

Que las páginas del portal, los bots y cualquier integrador lean los datos públicos con un contrato estable, en < 200 ms con caché caliente, sin cookies, sin poder escribir en ninguna base y sirviendo la última copia buena cuando `ingest` o `alertas` no responden.

## Especificación

### Rutas y middleware

- `bootstrap/app.php`: `->withRouting(web: …, api: __DIR__.'/../routes/api.php', apiPrefix: 'api/v1', commands: …)`. Sin Sanctum (no hay autenticación).
- Todas las rutas son `Route::get` (Laravel responde `HEAD` solo); cualquier otro método → `405`. `OPTIONS` lo atiende `HandleCors` (global) antes del enrutado.
- Grupo `api` de Laravel (sin sesión, cookies ni CSRF) + `throttle:api-publica` + `api.respuesta` (`App\Http\Middleware\RespuestaApi`: cabeceras, ETag, codificación JSON).
- Restricciones de parámetros de ruta: `{id}` de nodo `!?[0-9A-Fa-f]{8}` (acepta `!a1b2c3d4`, `%21a1b2c3d4` y `a1b2c3d4`; se normaliza a `!` + minúsculas); `{id}` de alerta ULID `[0-9A-HJKMNP-TV-Za-hjkmnp-tv-z]{26}` (se pasa a mayúsculas); `{id}` de ranking, lista blanca de [12](12-stats-api.md). `alerts/catalog` se registra antes que `alerts/{id}`.
- IP real del cliente: `trustProxies(at: ['172.30.0.1'])` con `X-Forwarded-For/Proto/Host/Port`. Nginx sustituye esas cabeceras (no reenvía las del cliente), así que no se pueden falsear.

| Ruta (`/api/v1/…`) | Módulo | TTL caché | Consumidores |
|---|---|---|---|
| `` (índice) | este | 1 h | Integradores |
| `stats/summary` | 12 | 60 s | Portada, `/status` de los bots |
| `stats/provinces?window=` | 12 | 60 s | Mapa |
| `stats/rankings`, `stats/rankings/{id}?period=&which=&limit=` | 12 | 1 h; 60 s – 15 min (periodo cerrado 1 h) | Rankings |
| `stats/traffic-mix?period=&which=` | 12 | 5 min (cerrado 1 h) | Rankings |
| `routers?province=&sort=` | 12 | 60 s | `/battery`, `/routers` de los bots |
| `nodes?search=&limit=` | 13 | 60 s | Revisa tu nodo |
| `nodes/{id}/diagnosis` | 13 | 5 min | Revisa tu nodo |
| `nodes/at-risk?province=&min_risk=&type=&limit=` | 13 | 30 s | Rankings, portada |
| `alerts?state=&risk=&min_risk=&type=&rule=&province=&node=&since=&sort=&limit=&cursor=` | 13 | 30 s | Página de alertas |
| `alerts/{id}` | 13 | 30 s | Ficha de alerta |
| `alerts/catalog` | 13 | 1 h | Bots, página de bots, filtros |

### Conexiones de base de datos

`config/database.php` (los nombres de variables son contrato del portal):

```php
'ingesta' => [
    'driver' => 'pgsql', 'host' => env('DB_HOST'), 'port' => env('DB_PORT', '5432'),
    'database' => env('DB_INGESTA_DATABASE', 'ingest'),
    'username' => env('DB_INGESTA_USERNAME', 'portal_lector_ingesta'),
    'password' => env('DB_INGESTA_PASSWORD'),
    'charset' => 'utf8', 'prefix' => '', 'search_path' => 'public', 'sslmode' => 'prefer',
],
'alertas' => [ /* igual con DB_ALERTAS_DATABASE (alertas), DB_ALERTAS_USERNAME (portal_lector_alertas), DB_ALERTAS_PASSWORD */ ],
```

- `pgsql` (base `portal`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) es la única con escritura; la API no la usa salvo para caché y límite.
- Listener de `Illuminate\Database\Events\ConnectionEstablished` para `ingesta` y `alertas`: `SET TIME ZONE 'UTC'; SET statement_timeout = '5s'; SET default_transaction_read_only = on;`. Las vistas calculan días locales con `TZ` explícito, no con la zona de la sesión.
- Acceso: Query Builder en `App\Datos\Ingesta\*` y `App\Datos\Alertas\*` (un método por consulta). Los nombres de vista salen de constantes o listas blancas, nunca de la petición. Las páginas web usan las mismas clases (misma caché).
- `APP_TIMEZONE=UTC`; los periodos en hora local usan `config('proyecto.zona_horaria')` = `TZ` (`Europe/Madrid`).

### Formato JSON

- Objeto en la raíz, sin envoltorio `data` (`JsonResource::withoutWrapping()`). Claves en inglés `snake_case`. Valores de riesgo, tipo, regla y fase tal como el catálogo (`bajo`, `infraestructura`, `reboot-loop`…). El contenido de `data` de una alerta va tal cual lo emite el detector (claves en español: es carga opaca de cada regla).
- Campos comunes en toda respuesta `200`: `generated_at` (cuándo se calcularon los datos, no cuándo se sirven) y `stale` (`true` si es la copia de respaldo). Opcional `notes` (array de textos en español con advertencias de interpretación).
- Fechas ISO 8601 UTC con segundos y `Z` (`2026-10-01T15:00:00Z`); días locales `YYYY-MM-DD`. Porcentajes y SNR con 1 decimal, voltaje con 2, segundos de aire con 1. `JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION`.
- Claves siempre presentes; dato desconocido = `null`. Listas: `items` (+ `count` cuando no hay paginación).
- Nunca se exponen coordenadas, claves públicas ni IP: solo ids, nombres públicos, rol, hardware, provincia y recuentos.

### Parámetros, validación y paginación

- Validación con `FormRequest` por endpoint; listas en un solo valor separado por comas (`risk=medio,alto`); un array (`risk[]=`) o un valor fuera de rango → `400`. Parámetros desconocidos se ignoran.
- **Top N** (rankings, búsqueda, nodos en peligro): `limit` con defecto y máximo propios de cada endpoint.
- **Cursor** (solo `/alerts`): `limit` (50 por defecto, 500 máximo) y `cursor` opaco (`cursorPaginate` de Laravel sobre el orden del endpoint). Respuesta: `items`, `limit`, `next_cursor` (`null` al final). El cursor solo vale con los mismos filtros y orden; uno manipulado → `400`.

### Errores

```json
{"error": {"status": 400, "code": "invalid_parameter", "message": "Valor no admitido en window: usa 24h, 7d o 30d.", "parameters": {"window": ["Valor no admitido: usa 24h, 7d o 30d."]}}}
```

| HTTP | `code` | Cuándo | `Cache-Control` |
|---|---|---|---|
| 400 | `invalid_parameter` | Validación, cursor inválido (`ValidationException` se convierte a 400, no 422) | `no-store` |
| 404 | `not_found` | Ruta desconocida bajo `/api/v1`, nodo, alerta o ranking inexistente | `public, max-age=60` |
| 405 | `method_not_allowed` | Método distinto de `GET`/`HEAD`/`OPTIONS` (cabecera `Allow: GET, HEAD`) | `no-store` |
| 429 | `rate_limited` | Más de 60 peticiones/min (cabecera `Retry-After`) | `no-store` |
| 500 | `internal_error` | Excepción no controlada; sin traza (`APP_DEBUG=false`) | `no-store` |
| 503 | `source_unavailable` | La vista no responde y no hay copia de respaldo | `no-store` |

Un único `render` en `->withExceptions()` para `api/v1` y `api/v1/*` (`shouldRenderJsonWhen`) aplica la tabla. `message` en español.

### Caché y copia de respaldo

`App\Datos\Fuente::recordar(string $consulta, array $params, int $ttl, Closure $leer): Resultado` (`datos`, `generado_en`, `stale`):

1. Clave `api1:<consulta>:<sha1(json de params normalizados y ordenados)>`. Almacén `database` sobre `pgsql` (tablas `cache` y `cache_locks` de la base `portal`).
2. Acierto en `…:fresco` (TTL del endpoint) → se devuelve.
3. Fallo → `Cache::lock('…:bloqueo', 15)->block(5)`: una sola petición consulta; las demás esperan y leen lo que deje. Si el bloqueo expira, consulta igualmente.
4. Consulta correcta → guarda `…:fresco` (TTL) y `…:ultimo` (24 h).
5. `QueryException`/`PDOException` de `ingesta` o `alertas` → marca `…:fallo` 10 s (no se martillea una base caída) y devuelve `…:ultimo` con `stale: true`; sin copia → `FuenteNoDisponible` → `503`.
6. Tarea horaria en `portal-tareas`: `DELETE FROM cache WHERE expiration < extract(epoch FROM now())` (el almacén `database` no purga solo; las búsquedas crean claves efímeras).

### Límite y CORS

```php
RateLimiter::for('api-publica', fn (Request $r) => IpUtils::checkIp($r->ip(), config('proyecto.api.exentas'))
    ? Limit::none()
    : Limit::perMinute(config('proyecto.api.limite_por_minuto'))->by($r->ip())
        ->response(fn (Request $r, array $h) => ErrorApi::respuesta(429, 'rate_limited', 'Demasiadas peticiones: espera un minuto.', $h)));
```

- 60/min por IP (`API_RATE_LIMIT_PER_MINUTE`). La clave se guarda con hash (comportamiento de Laravel) y expira en 60 s: no queda la IP en claro.
- `API_RATE_LIMIT_EXEMPT` (CIDR separados por comas, vacío por defecto): en producción, las IP públicas del servidor y `172.30.0.0/24`, porque los dos bots salen por la misma IP del servidor. Peor caso sin exención: 11 URL distintas por bot cada 30 s ≈ 44/min entre los dos (cabe, pero sin margen).
- `config/cors.php`: `paths => ['api/v1', 'api/v1/*']`, `allowed_methods => ['GET', 'HEAD', 'OPTIONS']`, `allowed_origins => ['*']`, `allowed_headers => ['Accept', 'If-None-Match']`, `exposed_headers => ['ETag', 'X-Cache', 'X-RateLimit-Limit', 'X-RateLimit-Remaining', 'Retry-After']`, `max_age => 86400`, `supports_credentials => false`.

### Cabeceras

| Cabecera | Valor |
|---|---|
| `Content-Type` | `application/json; charset=utf-8` |
| `Cache-Control` | `public, max-age=<TTL del endpoint>`; respuesta `stale`: `public, max-age=15`; errores según la tabla |
| `ETag` | `W/"<sha1 del cuerpo>"`; `If-None-Match` coincidente → `304` sin cuerpo |
| `X-Cache` | `HIT`, `MISS` o `STALE` |
| `X-RateLimit-Limit`, `X-RateLimit-Remaining` | Las pone `throttle` (no en IP exentas) |
| `Access-Control-Allow-Origin` | `*` |
| `X-Content-Type-Options` | `nosniff` |
| `Set-Cookie` | Nunca |
| `Deprecation`, `Sunset`, `Link: <…/api>; rel="deprecation"` | Solo cuando `v1` esté en retirada |

### Versionado

- Cambios compatibles dentro de `v1`: claves, endpoints, parámetros o valores de catálogo nuevos (riesgos, tipos y reglas son ampliables). Los clientes deben ignorar claves desconocidas.
- Incompatible (quitar o renombrar una clave, cambiar tipo o significado) → `/api/v2` en paralelo; `v1` se mantiene al menos 6 meses con `Deprecation` y `Sunset`, anunciado en `/api`.

### Índice y documentación pública

- `App\Api\V1\CatalogoEndpoints`: lista única de endpoints (ruta, descripción, parámetros con valores admitidos, TTL, URL de ejemplo). Alimenta `GET /api/v1` y la tabla de `/api`.
- `GET /api/v1` → `{"name": PROJECT_NAME, "version": "v1", "docs": "https://${PROJECT_DOMAIN}/api", "rate_limit_per_minute": 60, "endpoints": [{"path", "description", "parameters"}], "generated_at", "stale": false}`.
- Página `/api` (grupo público sin sesión, estilos de `DESIGN.md`): texto de su borrador en `pages/` + tabla generada + un ejemplo de respuesta por endpoint + límites, CORS, errores, versionado, aviso de que los recuentos son un mínimo (solo lo que llega con OK to MQTT) y contacto `PROJECT_CONTACT`. Enlazada desde el pie y la página de bots.

## Contratos propios

Campos comunes (`generated_at`, `stale`, `notes`, `items`, `count`, `limit`, `next_cursor`), objeto `error` y sus códigos, cabeceras, política de versionado, variables `DB_INGESTA_*`, `DB_ALERTAS_*`, `API_RATE_LIMIT_PER_MINUTE`, `API_RATE_LIMIT_EXEMPT` y la sección `api` de `config/proyecto.php` (`limite_por_minuto`, `exentas`).

## Unidades de trabajo

- **UT-06.11.1 — Rutas y grupo sin estado.** `withRouting` con `apiPrefix`, `routes/api.php`, restricciones de `{id}`, `RespuestaApi`. *Bordes:* `HEAD`, `POST`, barra final, `%21` en el id. *Aceptación:* `php artisan route:list --path=api/v1` muestra solo `GET|HEAD`; ninguna respuesta lleva `Set-Cookie`.
- **UT-06.11.2 — Conexiones de lectura.** `ingesta`, `alertas`, listener de sesión. *Bordes:* contraseña ausente (la web sigue; la API da `503`). *Aceptación:* `DB::connection('ingesta')->insert(…)` falla (`read-only transaction` o `permission denied`); `SHOW statement_timeout` = `5s`.
- **UT-06.11.3 — Formato y errores.** Serialización común, `ErrorApi`, conversión de excepciones. *Bordes:* 404 bajo `/api/v1` en JSON y fuera de él en HTML. *Aceptación:* cada código de la tabla tiene una prueba.
- **UT-06.11.4 — Caché con respaldo.** `Fuente::recordar`, bloqueo, marca de fallo, purga horaria. *Bordes:* base caída con y sin copia; 100 peticiones simultáneas en frío. *Aceptación:* escenarios 3 y 4; en frío, una sola consulta a la vista.
- **UT-06.11.5 — Límite, IP real y CORS.** *Bordes:* IP exenta, IPv6, `X-Forwarded-For` falso desde internet. *Aceptación:* escenarios 1 y 2.
- **UT-06.11.6 — Cabeceras y ETag.** *Aceptación:* segunda petición con `If-None-Match` → `304`; `X-Cache` correcto en los tres casos.
- **UT-06.11.7 — Índice y página `/api`.** `CatalogoEndpoints`, `GET /api/v1`, vista Blade. *Aceptación:* una prueba recorre `Route::getRoutes()` y falla si una ruta de `api/v1` no está en el catálogo.
- **UT-06.11.8 — Contrato de vistas.** `App\Datos\ContratoVistas` (columnas que lee el portal por vista) y comando `php artisan portal:vistas` que compara con `information_schema.columns` de `ingest` y `alertas`. Pruebas con bases de prueba donde `tests/Fixtures/vistas/*.sql` crea tablas con el nombre y las columnas de cada vista. *Aceptación:* contra una vista sin `riesgo_orden`, el comando lo lista y sale con código 1.

## Escenarios de prueba

1. **Dado** una IP no exenta, **cuando** hace 61 peticiones en un minuto, **entonces** la 61.ª recibe `429`, `Retry-After` y `{"error": {"code": "rate_limited", …}}`.
2. **Dado** una página de otro dominio, **cuando** hace `fetch` a `/api/v1/stats/summary`, **entonces** recibe `Access-Control-Allow-Origin: *` y ninguna cookie; un `POST` recibe `405` en JSON.
3. **Dado** `summary` en caché y `ingest` parada, **cuando** caduca el TTL y llega una petición, **entonces** responde `200` con los datos anteriores, `stale: true`, `X-Cache: STALE` y el `generated_at` original.
4. **Dado** un endpoint sin copia y `alertas` sin desplegar, **cuando** se pide `/api/v1/alerts`, **entonces** `503` `source_unavailable` y el resto de endpoints de `ingest` siguen respondiendo.
5. **Dado** una respuesta con `ETag`, **cuando** el cliente repite con `If-None-Match`, **entonces** `304` sin cuerpo.
6. **Dado** el rol `portal_lector_ingesta`, **cuando** el portal intenta leer la tabla `packet`, **entonces** `permission denied` y el error se registra sin exponer SQL en la respuesta (`500` `internal_error`).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
