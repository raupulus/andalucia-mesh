#!/usr/bin/env bash
# ==============================================================================
# install-host.sh
# 
# Orquesta la preparación e instalación base del host Debian 13 para Andalucía Mesh:
# - Crea los directorios de almacenamiento con permisos 2775 (SGID).
# - Aplica los parámetros sysctl del kernel.
# - Configura las reglas estrictas de cortafuegos UFW.
# - Aprovisiona la red Docker fija 'mesh' y el volumen 'alertas-socket'.
# - Configura Fail2ban y la vigilancia periódica de salud.
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(cd "${SCRIPT_DIR}/../.." && pwd)"

echo "=========================================================="
echo "Iniciando instalación base del host (Andalucía Mesh)"
echo "=========================================================="

# 1. Comprobación de superusuario
if [[ "${EUID}" -ne 0 ]]; then
    echo "[ERROR] Este script debe ejecutarse como root o mediante sudo." >&2
    exit 1
fi

# 2. Configuración de directorios y permisos en servidor
echo "[INFO] Creando estructura de directorios persistentes..."
STORAGE_DIR="/var/www/storage/sur-nodos-en-mallas"
mkdir -p "${STORAGE_DIR}/common"
mkdir -p "${STORAGE_DIR}/credenciales"
mkdir -p "${STORAGE_DIR}/logs"
mkdir -p "/etc/snm"

# Asignar grupo www-data y permisos 2775 (SGID) si existe el usuario fryntiz
TARGET_USER="fryntiz"
if id "${TARGET_USER}" >/dev/null 2>&1; then
    chown -R "${TARGET_USER}:www-data" "${STORAGE_DIR}"
else
    chown -R "${SUDO_USER:-root}:www-data" "${STORAGE_DIR}"
fi
chmod -R 2775 "${STORAGE_DIR}"
echo "[OK] Directorios creados en ${STORAGE_DIR} con permisos SGID 2775."

# 3. Aplicar parámetros sysctl
echo "[INFO] Paso 1/5: Parámetros sysctl del kernel..."
"${SCRIPT_DIR}/install-sysctl.sh"

# 4. Configurar cortafuegos UFW
echo "[INFO] Paso 2/5: Cortafuegos UFW..."
"${SCRIPT_DIR}/ufw-setup.sh"

# 5. Configurar red Docker y volúmenes
echo "[INFO] Paso 3/5: Redes Docker y volúmenes..."
"${SCRIPT_DIR}/redes.sh"

# 6. Configurar Fail2ban si está instalado
echo "[INFO] Paso 4/5: Configuración de Fail2ban..."
if command -v fail2ban-client >/dev/null 2>&1; then
    "${SCRIPT_DIR}/fail2ban/install-fail2ban.sh"
else
    echo "[AVISO] Fail2ban no instalado. Saltando configuración de cárcel."
fi

# 7. Configurar servicio y temporizador de vigilancia
echo "[INFO] Paso 5/5: Servicio de vigilancia del sistema..."
install -m 0644 "${SCRIPT_DIR}/snm-vigilancia.service" /etc/systemd/system/snm-vigilancia.service
install -m 0644 "${SCRIPT_DIR}/snm-vigilancia.timer" /etc/systemd/system/snm-vigilancia.timer
if [[ ! -f "/etc/snm/vigilancia.env" ]]; then
    install -m 0600 "${SCRIPT_DIR}/vigilancia.env.example" /etc/snm/vigilancia.env
fi
systemctl daemon-reload
systemctl enable --now snm-vigilancia.timer

echo "=========================================================="
echo "[OK] Instalación base del host finalizada exitosamente."
echo "=========================================================="
