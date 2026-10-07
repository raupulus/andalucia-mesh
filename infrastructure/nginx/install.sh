#!/usr/bin/env bash
# ==============================================================================
# install.sh (infrastructure/nginx/)
#
# Instala y aprovisiona la configuración de Nginx para Andalucía Mesh:
# - Verifica e instala libnginx-mod-stream si es necesario.
# - Configura directorios y directiva stream en /etc/nginx/nginx.conf.
# - Prepara webroot compartido para Let's Encrypt (/var/www/letsencrypt).
# - Genera certificados autofirmados temporales si aún no existen los reales de Certbot.
# - Sustituye las variables de plantilla por los dominios de common/.env.
# - Instala snippets, sitios y streams.
# - Valida sintaxis con 'nginx -t' y recarga el servicio sin interrupción.
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(cd "${SCRIPT_DIR}/../.." && pwd)"

echo "=========================================================="
echo "Instalando configuración de Nginx (Andalucía Mesh)"
echo "=========================================================="

# 1. Comprobar permisos de superusuario
if [[ "${EUID}" -ne 0 ]]; then
    echo "[ERROR] Este script debe ejecutarse como root o con sudo." >&2
    exit 1
fi

# 2. Cargar variables de entorno comunes
ENV_FILE="${BASE_DIR}/infrastructure/common/.env"
STORAGE_ENV="/var/www/storage/sur-nodos-en-mallas/common/.env"

if [[ -f "${ENV_FILE}" ]]; then
    # shellcheck disable=SC1090
    source "${ENV_FILE}"
elif [[ -f "${STORAGE_ENV}" ]]; then
    # shellcheck disable=SC1090
    source "${STORAGE_ENV}"
else
    echo "[AVISO] No se encontró .env en ${ENV_FILE} ni en ${STORAGE_ENV}."
    echo "[INFO] Usando valores por defecto agnósticos de prueba."
fi

PROJECT_DOMAIN="${PROJECT_DOMAIN:-mesh.example.org}"
MESHVIEW_DOMAIN="${MESHVIEW_DOMAIN:-meshview.${PROJECT_DOMAIN}}"
POTATO_DOMAIN="potato.${PROJECT_DOMAIN}"
MQTT_DOMAIN="mqtt.${PROJECT_DOMAIN}"

echo "[INFO] Dominios configurados:"
echo "       - Portal:   ${PROJECT_DOMAIN}"
echo "       - Potato:   ${POTATO_DOMAIN}"
echo "       - MeshView: ${MESHVIEW_DOMAIN}"
echo "       - MQTT TLS: ${MQTT_DOMAIN}"

# 3. Verificar e instalar libnginx-mod-stream
if ! dpkg -s libnginx-mod-stream >/dev/null 2>&1; then
    echo "[INFO] Instalando paquete libnginx-mod-stream..."
    apt-get update -qq
    apt-get install -y -qq libnginx-mod-stream
fi

# 4. Asegurar directorios de Nginx y webroot
mkdir -p /etc/nginx/snippets
mkdir -p /etc/nginx/sites-available
mkdir -p /etc/nginx/sites-enabled
mkdir -p /etc/nginx/streams-available
mkdir -p /etc/nginx/streams-enabled

WEBROOT_DIR="/var/www/letsencrypt"
mkdir -p "${WEBROOT_DIR}"
chown -R www-data:www-data "${WEBROOT_DIR}"
chmod 755 "${WEBROOT_DIR}"

# 5. Asegurar inclusión de streams en /etc/nginx/nginx.conf
NGINX_CONF="/etc/nginx/nginx.conf"
STREAM_INCLUDE="include /etc/nginx/streams-enabled/*.conf;"
if ! grep -q "streams-enabled" "${NGINX_CONF}" 2>/dev/null; then
    echo "[INFO] Añadiendo bloque stream a ${NGINX_CONF}..."
    cat >> "${NGINX_CONF}" <<EOF

# Andalucía Mesh: Módulo Stream para TCP Proxy (MQTT TLS en 8883)
stream {
    ${STREAM_INCLUDE}
}
EOF
fi

# 6. Crear certificados temporales si no existen (evita error en nginx -t previo a Certbot)
for domain in "${PROJECT_DOMAIN}" "${POTATO_DOMAIN}" "${MESHVIEW_DOMAIN}" "${MQTT_DOMAIN}"; do
    CERT_DIR="/etc/letsencrypt/live/${domain}"
    if [[ ! -f "${CERT_DIR}/fullchain.pem" ]] || [[ ! -f "${CERT_DIR}/privkey.pem" ]]; then
        echo "[INFO] Generando certificado autofirmado provisional para ${domain}..."
        mkdir -p "${CERT_DIR}"
        openssl req -x509 -nodes -days 1 -newkey rsa:2048 \
            -keyout "${CERT_DIR}/privkey.pem" \
            -out "${CERT_DIR}/fullchain.pem" \
            -subj "/CN=${domain}" >/dev/null 2>&1
    fi
done

# 7. Copiar snippets
echo "[INFO] Instalando snippets en /etc/nginx/snippets/..."
install -m 0644 "${SCRIPT_DIR}/snippets/snm-proxy.conf" /etc/nginx/snippets/snm-proxy.conf
install -m 0644 "${SCRIPT_DIR}/snippets/snm-security.conf" /etc/nginx/snippets/snm-security.conf
install -m 0644 "${SCRIPT_DIR}/snippets/snm-ratelimit.conf" /etc/nginx/snippets/snm-ratelimit.conf

# 8. Renderizar e instalar sitios HTTP/HTTPS
echo "[INFO] Renderizando e instalando sitios virtuales..."

# Portal
sed -e "s/mesh\.example\.org/${PROJECT_DOMAIN}/g" \
    "${SCRIPT_DIR}/sites/snm-portal.conf" > /etc/nginx/sites-available/snm-portal.conf
ln -sf /etc/nginx/sites-available/snm-portal.conf /etc/nginx/sites-enabled/snm-portal.conf

# PotatoMesh
sed -e "s/potato\.mesh\.example\.org/${POTATO_DOMAIN}/g" \
    "${SCRIPT_DIR}/sites/snm-potatomesh.conf" > /etc/nginx/sites-available/snm-potatomesh.conf
ln -sf /etc/nginx/sites-available/snm-potatomesh.conf /etc/nginx/sites-enabled/snm-potatomesh.conf

# MeshView
sed -e "s/meshview\.mesh\.example\.org/${MESHVIEW_DOMAIN}/g" \
    "${SCRIPT_DIR}/sites/snm-meshview.conf" > /etc/nginx/sites-available/snm-meshview.conf
ln -sf /etc/nginx/sites-available/snm-meshview.conf /etc/nginx/sites-enabled/snm-meshview.conf

# 9. Renderizar e instalar stream TCP MQTT TLS
echo "[INFO] Renderizando e instalando stream TCP..."
sed -e "s/mqtt\.mesh\.example\.org/${MQTT_DOMAIN}/g" \
    "${SCRIPT_DIR}/streams/snm-mqtts.conf" > /etc/nginx/streams-available/snm-mqtts.conf
ln -sf /etc/nginx/streams-available/snm-mqtts.conf /etc/nginx/streams-enabled/snm-mqtts.conf

# 10. Validar configuración con nginx -t antes de aplicar
echo "[INFO] Validando sintaxis global de Nginx..."
if nginx -t; then
    echo "[OK] Sintaxis de Nginx válida. Recargando servicio..."
    systemctl reload nginx
    echo "=========================================================="
    echo "[OK] Nginx configurado y recargado con éxito."
    echo "=========================================================="
else
    echo "[ERROR] 'nginx -t' ha fallado. Revisa los errores anteriores." >&2
    echo "[ERROR] No se ha recargado Nginx para evitar caída del servicio." >&2
    exit 1
fi
