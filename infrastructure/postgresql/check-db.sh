#!/usr/bin/env bash
# ==============================================================================
# check-db.sh
# 
# Comprueba la salud, configuración y conectividad de PostgreSQL 17 + TimescaleDB
# para el proyecto Andalucía Mesh.
# ==============================================================================

set -euo pipefail

DB_HOST="172.30.0.1"
DB_PORT="5432"

echo "=========================================================="
echo "Diagnóstico de PostgreSQL 17 + TimescaleDB (Andalucía Mesh)"
echo "=========================================================="

# 1. Comprobación del servicio systemd
if command -v systemctl >/dev/null 2>&1; then
    if systemctl is-active --quiet postgresql; then
        echo "[OK] Servicio PostgreSQL 17: ACTIVO"
    else
        echo "[ERROR] Servicio PostgreSQL 17: INACTIVO" >&2
        exit 1
    fi
fi

# 2. Comprobación de disponibilidad del puerto
if command -v pg_isready >/dev/null 2>&1; then
    if pg_isready -h "127.0.0.1" -p "${DB_PORT}" -q; then
        echo "[OK] Puerto local 127.0.0.1:${DB_PORT}: ACEPTANDO CONEXIONES"
    else
        echo "[ERROR] Fallo al conectar a 127.0.0.1:${DB_PORT}" >&2
    fi

    if pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -q; then
        echo "[OK] Puerto Docker ${DB_HOST}:${DB_PORT}: ACEPTANDO CONEXIONES"
    else
        echo "[AVISO] No responde en ${DB_HOST}:${DB_PORT} (compruebe si la interfaz br-mesh está levantada)"
    fi
fi

# 3. Comprobación de TimescaleDB y configuración mediante sudo postgres si está disponible
if sudo -n -u postgres psql -c "SELECT 1;" >/dev/null 2>&1 || [[ "${EUID}" -eq 0 ]]; then
    echo "[INFO] Comprobando extensión TimescaleDB en base snm_ingest..."
    EXT_VER=$(sudo -u postgres psql -d snm_ingest -t -A -c "SELECT extversion FROM pg_extension WHERE extname = 'timescaledb';" 2>/dev/null || true)
    
    if [[ -n "${EXT_VER}" ]]; then
        echo "[OK] Extensión TimescaleDB activa en snm_ingest: v${EXT_VER}"
    else
        echo "[ERROR] La extensión TimescaleDB NO está instalada o activa en snm_ingest." >&2
    fi

    echo "[INFO] Comprobando bases de datos de servicio..."
    DATABASES=("snm_portal" "snm_meshview" "snm_ingest" "snm_detector" "snm_potato" "snm_chatws" "peersync" "snm_bot_telegram" "snm_bot_discord" "snm_webhooks")
    for DB in "${DATABASES[@]}"; do
        DB_EXISTS=$(sudo -u postgres psql -t -A -c "SELECT 1 FROM pg_database WHERE datname = '${DB}';" 2>/dev/null || true)
        if [[ "${DB_EXISTS}" == "1" ]]; then
            echo "  - Base de datos '${DB}': PRESENTE"
        else
            echo "  - [AVISO] Base de datos '${DB}': NO ENCONTRADA"
        fi
    done
fi

echo "=========================================================="
echo "[OK] Diagnóstico de base de datos finalizado."
