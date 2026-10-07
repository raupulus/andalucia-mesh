#!/usr/bin/env bash
# ==============================================================================
# install-timescale.sh
# 
# Instala y configura la extensión TimescaleDB en PostgreSQL 17 nativo para Debian 13.
# Configura pg_hba.conf para permitir acceso a la red Docker mesh (172.30.0.0/24).
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_SOURCE="${SCRIPT_DIR}/conf.d/99-snm.conf"
PG_CONF_DIR="/etc/postgresql/17/main/conf.d"
PG_HBA_FILE="/etc/postgresql/17/main/pg_hba.conf"

echo "=========================================================="
echo "Instalación y Configuración de TimescaleDB (PostgreSQL 17)"
echo "=========================================================="

if [[ "${EUID}" -ne 0 ]]; then
    echo "[ERROR] Este script debe ejecutarse como root o mediante sudo." >&2
    exit 1
fi

# 1. Instalar dependencias previas
echo "[INFO] Verificando dependencias necesarias (curl, gpg)..."
apt-get update -qq
apt-get install -y -qq curl gnupg lsb-release

# 2. Agregar repositorio oficial de TimescaleDB si no está presente
if [[ ! -f "/etc/apt/sources.list.d/timescaledb.list" ]]; then
    echo "[INFO] Añadiendo repositorio oficial de TimescaleDB..."
    curl -fsSL https://packagecloud.io/timescale/timescaledb/gpgkey | gpg --dearmor -o /etc/apt/trusted.gpg.d/timescaledb.gpg
    echo "deb https://packagecloud.io/timescale/timescaledb/debian/ $(lsb_release -c -s) main" > /etc/apt/sources.list.d/timescaledb.list
    apt-get update -qq
fi

# 3. Instalar TimescaleDB para PostgreSQL 17
echo "[INFO] Instalando paquete timescaledb-2-postgresql-17..."
apt-get install -y -qq timescaledb-2-postgresql-17
apt-mark hold timescaledb-2-postgresql-17 >/dev/null

# 4. Copiar configuración de optimización
echo "[INFO] Aplicando archivo de configuración 99-snm.conf..."
mkdir -p "${PG_CONF_DIR}"
install -m 0644 "${CONFIG_SOURCE}" "${PG_CONF_DIR}/99-snm.conf"

# 5. Configurar pg_hba.conf para la red Docker mesh
echo "[INFO] Configurando autenticación en pg_hba.conf..."
RULE_DOCKER="host    all             all             172.30.0.0/24           scram-sha-256"
if ! grep -q "172.30.0.0/24" "${PG_HBA_FILE}"; then
    echo "" >> "${PG_HBA_FILE}"
    echo "# Red Docker mesh para Andalucía Mesh" >> "${PG_HBA_FILE}"
    echo "${RULE_DOCKER}" >> "${PG_HBA_FILE}"
    echo "[OK] Regla de red Docker añadida a ${PG_HBA_FILE}."
else
    echo "[INFO] Regla de red Docker ya presente en ${PG_HBA_FILE}."
fi

# 6. Reiniciar PostgreSQL para cargar shared_preload_libraries y nuevas IPs
echo "[INFO] Reiniciando servicio PostgreSQL 17..."
systemctl restart postgresql

# 7. Comprobación
echo "=========================================================="
if systemctl is-active --quiet postgresql; then
    echo "[OK] PostgreSQL 17 reiniciado y activo con TimescaleDB cargado."
else
    echo "[ERROR] El servicio PostgreSQL falló al reiniciar. Revise /var/log/postgresql/." >&2
    exit 1
fi
