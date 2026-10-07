#!/usr/bin/env bash
# ==============================================================================
# install-sysctl.sh
# 
# Instala y aplica los parámetros optimizados de kernel en el sistema host.
# Requiere permisos de superusuario (sudo).
# ==============================================================================

set -euo pipefail

# Directorio base del script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_SOURCE="${SCRIPT_DIR}/sysctl.d/99-snm.conf"
CONFIG_TARGET="/etc/sysctl.d/99-snm.conf"

echo "[INFO] Instalando configuración sysctl para Andalucía Mesh..."

# Comprobar privilegios de root
if [[ "${EUID}" -ne 0 ]]; then
    echo "[ERROR] Este script debe ejecutarse como root o mediante sudo." >&2
    exit 1
fi

# Comprobar que el archivo de origen existe
if [[ ! -f "${CONFIG_SOURCE}" ]]; then
    echo "[ERROR] No se encuentra el archivo fuente: ${CONFIG_SOURCE}" >&2
    exit 1
fi

# Copiar configuración con permisos estrictos de root
install -m 0644 "${CONFIG_SOURCE}" "${CONFIG_TARGET}"
echo "[OK] Archivo copiado a ${CONFIG_TARGET}"

# Aplicar parámetros inmediatamente en el kernel
echo "[INFO] Aplicando parámetros en el kernel..."
sysctl --system > /dev/null

echo "[OK] Parámetros sysctl aplicados correctamente."
