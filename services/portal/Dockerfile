# ==============================================================================
# Dockerfile (services/portal)
#
# Imagen de producción para el Portal Web Laravel y Daemon de Tareas.
# Basada en serversideup/php:8.4-fpm-nginx con compilación de assets en Node.js.
# ==============================================================================

# --- Etapa 1: Compilación de Assets de Frontend ---
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources resources

RUN npm run build

# --- Etapa 2: Imagen Final PHP-FPM + Nginx ---
FROM serversideup/php:8.4-fpm-nginx AS final

USER root

# Instalar extensión intl requerida para Filament e IDN
RUN install-php-extensions intl

# Optimización y variables del entorno de ejecución
ENV AUTORUN_ENABLED=true \
    SSL_MODE=off \
    PHP_OPCACHE_ENABLE=1 \
    NGINX_HTTP_PORT=8080

WORKDIR /var/www/html

# Copiar dependencias de Composer
COPY --chown=www-data:www-data composer.json composer.lock ./

USER www-data

# Instalar dependencias PHP de producción sin ejecutar scripts aún
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copiar código fuente
COPY --chown=www-data:www-data . .

# Copiar assets compilados desde la etapa de frontend
COPY --from=assets --chown=www-data:www-data /app/public/build public/build

# Optimizar autoloader final de Composer
RUN composer dump-autoload --optimize --no-dev

# Publicar assets de Filament (CSS, JS, fuentes) para que siempre existan en la imagen
RUN php artisan filament:assets

# Estructura de directorios storage y permisos
USER root
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www-data

EXPOSE 8080
