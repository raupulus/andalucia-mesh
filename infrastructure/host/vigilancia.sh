#!/usr/bin/env bash
# ==============================================================================
# vigilancia.sh
# 
# Comprueba la salud general del host Debian 13 para Andalucía Mesh:
# - Uso de espacio en disco (raíz).
# - Uso de memoria RAM física.
# - Carga media de la CPU a 5 minutos.
# - Estado de servicios críticos (PostgreSQL y Mosquitto).
# - Días restantes para caducidad de certificados SSL.
# Emite avisos a Telegram si se configuran credenciales en /etc/snm/vigilancia.env.
# ==============================================================================

set -euo pipefail

ENV_FILE="/etc/snm/vigilancia.env"
if [[ -f "${ENV_FILE}" ]]; then
    # shellcheck disable=SC1090
    source "${ENV_FILE}"
fi

# Umbrales por defecto
VIG_DISCO_PCT="${VIG_DISCO_PCT:-70}"
VIG_RAM_PCT="${VIG_RAM_PCT:-80}"
VIG_CARGA5="${VIG_CARGA5:-2.5}"
VIG_CERT_DIAS="${VIG_CERT_DIAS:-14}"
AVISO_TELEGRAM_TOKEN="${AVISO_TELEGRAM_TOKEN:-}"
AVISO_TELEGRAM_CHAT_ID="${AVISO_TELEGRAM_CHAT_ID:-}"

ALERT_MESSAGES=()

# 1. Comprobación de disco en la raíz (/)
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | tr -d '%')
if [[ "${DISK_USAGE}" -ge "${VIG_DISCO_PCT}" ]]; then
    ALERT_MESSAGES+=("⚠️ Disco al ${DISK_USAGE}% (umbral: ${VIG_DISCO_PCT}%)")
fi

# 2. Comprobación de memoria RAM
RAM_TOTAL=$(free -m | awk '/Mem:/ {print $2}')
RAM_USED=$(free -m | awk '/Mem:/ {print $3}')
RAM_USAGE_PCT=$(( (RAM_USED * 100) / RAM_TOTAL ))
if [[ "${RAM_USAGE_PCT}" -ge "${VIG_RAM_PCT}" ]]; then
    ALERT_MESSAGES+=("⚠️ RAM al ${RAM_USAGE_PCT}% (${RAM_USED}MB / ${RAM_TOTAL}MB, umbral: ${VIG_RAM_PCT}%)")
fi

# 3. Comprobación de carga media (5 min)
LOAD5=$(awk '{print $2}' /proc/loadavg)
# Comparar flotantes con awk
IS_LOAD_HIGH=$(awk -v l5="${LOAD5}" -v th="${VIG_CARGA5}" 'BEGIN {print (l5 >= th) ? "1" : "0"}')
if [[ "${IS_LOAD_HIGH}" -eq 1 ]]; then
    ALERT_MESSAGES+=("⚠️ Carga media (5 min) alta: ${LOAD5} (umbral: ${VIG_CARGA5})")
fi

# 4. Comprobación de servicios críticos del host
if command -v systemctl >/dev/null 2>&1; then
    if ! systemctl is-active --quiet postgresql; then
        ALERT_MESSAGES+=("🚨 Servicio PostgreSQL inactivo en el host")
    fi
    if systemctl list-unit-files | grep -q "mosquitto.service"; then
        if ! systemctl is-active --quiet mosquitto; then
            ALERT_MESSAGES+=("🚨 Servicio Mosquitto inactivo en el host")
        fi
    fi
fi

# 5. Comprobación de certificados SSL si existen
CERT_DIR="/etc/letsencrypt/live"
if [[ -d "${CERT_DIR}" ]]; then
    for CERT_FILE in "${CERT_DIR}"/*/fullchain.pem; do
        if [[ -f "${CERT_FILE}" ]]; then
            DOMAIN_NAME=$(basename "$(dirname "${CERT_FILE}")")
            EXPIRY_DATE=$(openssl x509 -enddate -noout -in "${CERT_FILE}" | cut -d= -f2)
            EXPIRY_EPOCH=$(date -d "${EXPIRY_DATE}" +%s 2>/dev/null || date -j -f "%b %d %T %Y %Z" "${EXPIRY_DATE}" +%s)
            CURRENT_EPOCH=$(date +%s)
            DAYS_LEFT=$(( (EXPIRY_EPOCH - CURRENT_EPOCH) / 86400 ))

            if [[ "${DAYS_LEFT}" -le "${VIG_CERT_DIAS}" ]]; then
                ALERT_MESSAGES+=("⚠️ Certificado para ${DOMAIN_NAME} caduca en ${DAYS_LEFT} días")
            fi
        fi
    done
fi

# Si hay alertas, registrar y notificar
if [[ "${#ALERT_MESSAGES[@]}" -gt 0 ]]; then
    echo "[AVISO] $(date --iso-8601=seconds) Se detectaron alertas de vigilancia:"
    for MSG in "${ALERT_MESSAGES[@]}"; do
        echo " - ${MSG}"
    done

    # Notificar a Telegram si está configurado
    if [[ -n "${AVISO_TELEGRAM_TOKEN}" && -n "${AVISO_TELEGRAM_CHAT_ID}" ]]; then
        TEXT="🚨 *Alerta de Host (Andalucía Mesh)*%0A$(printf "%s%%0A" "${ALERT_MESSAGES[@]}")"
        curl -s -X POST "https://api.telegram.org/bot${AVISO_TELEGRAM_TOKEN}/sendMessage" \
            -d "chat_id=${AVISO_TELEGRAM_CHAT_ID}" \
            -d "text=${TEXT}" \
            -d "parse_mode=Markdown" >/dev/null || true
    fi
else
    echo "[OK] $(date --iso-8601=seconds) Todos los parámetros del host dentro de los umbrales normales."
fi
