#!/usr/bin/env bash
# ==============================================================================
# install-fail2ban.sh
# 
# Instala las reglas de Fail2ban para Andalucía Mesh en el host.
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "[INFO] Instalando reglas de Fail2ban para Andalucía Mesh..."

if [[ "${EUID}" -ne 0 ]]; then
    echo "[ERROR] Este script debe ejecutarse como root o mediante sudo." >&2
    exit 1
fi

# Copiar filtro y cárcel
install -m 0644 "${SCRIPT_DIR}/filter.d/snm-api-abuse.conf" /etc/fail2ban/filter.d/snm-api-abuse.conf
install -m 0644 "${SCRIPT_DIR}/jail.d/snm-api-abuse.conf" /etc/fail2ban/jail.d/snm-api-abuse.conf

echo "[INFO] Recargando configuración de Fail2ban..."
fail2ban-client reload

echo "[OK] Cárcel snm-api-abuse activa en Fail2ban."
