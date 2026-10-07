#!/usr/bin/env bash
# ==============================================================================
# set-role-password.sh
# 
# Genera y asigna una contraseña criptográficamente segura (32 caracteres hex)
# a un rol de PostgreSQL, actualizando de forma atómica su archivo .env.
# Uso: sudo ./set-role-password.sh <rol> [ruta_archivo_env]
# ==============================================================================

set -euo pipefail

if [[ $# -lt 1 ]]; then
    echo "Uso: $0 <nombre_rol> [ruta_archivo_env]" >&2
    exit 1
fi

ROLE_NAME="$1"
ENV_FILE="${2:-}"

# Comprobar privilegios de superusuario para interactuar con postgres
if [[ "${EUID}" -ne 0 ]]; then
    echo "[ERROR] Este script debe ejecutarse como root o mediante sudo." >&2
    exit 1
fi

# 1. Generar contraseña criptosegura de 32 caracteres (16 bytes en hexadecimal)
NEW_PASSWORD=$(openssl rand -hex 16)

# 2. Aplicar contraseña en PostgreSQL mediante el usuario postgres local
echo "[INFO] Actualizando contraseña para el rol '${ROLE_NAME}' en PostgreSQL..."
sudo -u postgres psql -v ON_ERROR_STOP=1 -c "ALTER ROLE \"${ROLE_NAME}\" WITH ENCRYPTED PASSWORD '${NEW_PASSWORD}';" > /dev/null

echo "[OK] Contraseña actualizada en PostgreSQL para el rol '${ROLE_NAME}'."

# 3. Si se especificó archivo .env, actualizarlo o crearlo de forma segura
if [[ -n "${ENV_FILE}" ]]; then
    echo "[INFO] Guardando contraseña en ${ENV_FILE}..."
    mkdir -p "$(dirname "${ENV_FILE}")"
    
    if [[ -f "${ENV_FILE}" ]]; then
        if grep -q "^DB_PASSWORD=" "${ENV_FILE}"; then
            sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=\"${NEW_PASSWORD}\"|" "${ENV_FILE}"
        else
            echo "DB_PASSWORD=\"${NEW_PASSWORD}\"" >> "${ENV_FILE}"
        fi
    else
        echo "DB_PASSWORD=\"${NEW_PASSWORD}\"" > "${ENV_FILE}"
    fi

    # Permisos estrictos: solo lectura/escritura para el dueño
    chmod 0600 "${ENV_FILE}"
    echo "[OK] Archivo ${ENV_FILE} actualizado con permisos 0600."
fi
