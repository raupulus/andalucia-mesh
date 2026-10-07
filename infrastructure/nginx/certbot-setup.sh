#!/usr/bin/env bash
# ==============================================================================
# certbot-setup.sh (infrastructure/nginx/)
#
# Emite y configura certificados SSL con Certbot mediante reto HTTP-01 webroot
# para todos los subdominios públicos de Andalucía Mesh:
# - ${PROJECT_DOMAIN} (Portal Web, API, Chat WebSocket)
# - potato.${PROJECT_DOMAIN} (PotatoMesh)
# - ${MESHVIEW_DOMAIN} (MeshView)
# - mqtt.${PROJECT_DOMAIN} (Mosquitto TLS puerto 8883)
#
# Uso:
#   ./certbot-setup.sh [--staging] [--dry-run] [--force-renewal]
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(cd "${SCRIPT_DIR}/../.." && pwd)"

echo "=========================================================="
echo "Aprovisionamiento de Certificados SSL (Certbot ACME HTTP-01)"
echo "=========================================================="

if [[ "${EUID}" -ne 0 ]]; then
    echo "[ERROR] Este script debe ejecutarse como root o con sudo." >&2
    exit 1
fi

# Cargar variables de entorno
ENV_FILE="${BASE_DIR}/infrastructure/common/.env"
STORAGE_ENV="/var/www/storage/sur-nodos-en-mallas/common/.env"

if [[ -f "${ENV_FILE}" ]]; then
    # shellcheck disable=SC1090
    source "${ENV_FILE}"
elif [[ -f "${STORAGE_ENV}" ]]; then
    # shellcheck disable=SC1090
    source "${STORAGE_ENV}"
fi

PROJECT_DOMAIN="${PROJECT_DOMAIN:-mesh.example.org}"
MESHVIEW_DOMAIN="${MESHVIEW_DOMAIN:-meshview.${PROJECT_DOMAIN}}"
POTATO_DOMAIN="potato.${PROJECT_DOMAIN}"
MQTT_DOMAIN="mqtt.${PROJECT_DOMAIN}"
CONTACT_EMAIL="${PROJECT_CONTACT:-public@raupulus.dev}"

WEBROOT_DIR="/var/www/letsencrypt"
mkdir -p "${WEBROOT_DIR}"
chown -R www-data:www-data "${WEBROOT_DIR}"
chmod 755 "${WEBROOT_DIR}"

EXTRA_FLAGS=()
for arg in "$@"; do
    case "${arg}" in
        --staging|--test-cert)
            EXTRA_FLAGS+=("--staging")
            echo "[INFO] Modo STAGING activado (certificados de prueba Let's Encrypt)."
            ;;
        --dry-run)
            EXTRA_FLAGS+=("--dry-run")
            echo "[INFO] Modo DRY-RUN activado (simulación sin cambios reales)."
            ;;
        --force-renewal)
            EXTRA_FLAGS+=("--force-renewal")
            echo "[INFO] Forzando renovación inmediata."
            ;;
        *)
            echo "[AVISO] Parámetro no reconocido: ${arg}"
            ;;
    esac
done

if ! command -v certbot >/dev/null 2>&1; then
    echo "[INFO] Certbot no encontrado. Instalando certbot..."
    apt-get update -qq
    apt-get install -y -qq certbot
fi

DOMAINS=("${PROJECT_DOMAIN}" "${POTATO_DOMAIN}" "${MESHVIEW_DOMAIN}" "${MQTT_DOMAIN}")

for dom in "${DOMAINS[@]}"; do
    echo "----------------------------------------------------------"
    echo "[INFO] Solicitando certificado para: ${dom}"
    echo "----------------------------------------------------------"

    certbot certonly \
        --webroot \
        -w "${WEBROOT_DIR}" \
        --non-interactive \
        --agree-tos \
        --email "${CONTACT_EMAIL}" \
        -d "${dom}" \
        --deploy-hook "systemctl reload nginx" \
        "${EXTRA_FLAGS[@]}" || {
            echo "[ERROR] Falló la obtención del certificado para ${dom}." >&2
            echo "[PISTA] Verifica que el registro DNS A/AAAA apunta a este servidor y que el puerto 80/tcp está abierto." >&2
        }
done

echo "=========================================================="
echo "[INFO] Proceso de emisión finalizado. Recargando Nginx..."
if nginx -t >/dev/null 2>&1; then
    systemctl reload nginx
    echo "[OK] Nginx recargado con nuevos certificados."
else
    echo "[AVISO] No se pudo recargar Nginx tras certbot. Comprueba con 'nginx -t'." >&2
fi
echo "=========================================================="
