#!/usr/bin/env bash
# ==============================================================================
# install.sh (integrations/mosquitto/)
#
# Script de instalación, configuración y arranque de Mosquitto nativo en Debian.
#
# Uso:
#   ./install.sh
# ==============================================================================

set -euo pipefail

export PATH="${PATH}:/usr/sbin:/sbin"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(cd "${SCRIPT_DIR}/../.." && pwd)"

log() {
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

log "[INFO] Iniciando instalación y configuración de Mosquitto para Andalucía Mesh..."

# 1. Instalar paquetes necesarios si no están instalados
if ! command -v mosquitto >/dev/null 2>&1 || ! command -v mosquitto_pub >/dev/null 2>&1; then
    log "[INFO] Instalando mosquitto y mosquitto-clients..."
    sudo apt-get update -qq
    sudo apt-get install -y -qq mosquitto mosquitto-clients
fi

# 2. Asegurar directorios de configuración y persistencia
log "[INFO] Asegurando directorios de credenciales y configuración..."
sudo install -d -o mosquitto -g mosquitto -m 0750 /srv/mosquitto/credenciales
sudo install -d -o mosquitto -g mosquitto -m 0755 /srv/mosquitto/config
sudo install -d -o mosquitto -g mosquitto -m 0755 /var/lib/mosquitto

# 3. Inicializar credenciales y ACLs mediante generate-acl.sh
log "[INFO] Inicializando ACLs y archivos de contraseñas..."
"${SCRIPT_DIR}/tools/generate-acl.sh" --init

# 4. Enlazar configuración a /etc/mosquitto/conf.d/snm.conf
log "[INFO] Configurando /etc/mosquitto/conf.d/snm.conf..."
sudo cp -f "${SCRIPT_DIR}/config/mosquitto.conf" /etc/mosquitto/conf.d/snm.conf
sudo chmod 0644 /etc/mosquitto/conf.d/snm.conf
sudo chown root:root /etc/mosquitto/conf.d/snm.conf

# 5. Habilitar y reiniciar servicio
log "[INFO] Reiniciando y habilitando servicio systemd mosquitto..."
sudo systemctl enable mosquitto
sudo systemctl restart mosquitto

# 7. Comprobar estado
if systemctl is-active --quiet mosquitto; then
    log "[OK] Servicio mosquitto activo y en ejecución."
else
    log "[ERROR] El servicio mosquitto no pudo iniciar. Revisa 'journalctl -u mosquitto'." >&2
    exit 1
fi

# 8. Comprobación rápida de sockets
log "[INFO] Verificando listeners:"
ss -tlnp | grep mosquitto || true

log "[OK] Instalación de Mosquitto completada con éxito."
