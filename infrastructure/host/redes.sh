#!/usr/bin/env bash
# ==============================================================================
# redes.sh
# 
# Crea y aprovisiona la red Docker fija 'mesh' y el volumen 'alertas-socket'
# para los servicios de Andalucía Mesh.
# ==============================================================================

set -euo pipefail

NETWORK_NAME="mesh"
SUBNET="172.30.0.0/24"
GATEWAY="172.30.0.1"
BRIDGE_NAME="br-mesh"
VOLUME_NAME="alertas-socket"
VOLUME_DIR="/var/lib/docker/volumes/${VOLUME_NAME}/_data"

echo "=========================================================="
echo "Aprovisionamiento de Red Docker y Volúmenes Compartidos"
echo "=========================================================="

# 1. Crear la red Docker 'mesh' si no existe
if docker network inspect "${NETWORK_NAME}" >/dev/null 2>&1; then
    echo "[INFO] La red Docker '${NETWORK_NAME}' ya existe."
else
    echo "[INFO] Creando red Docker '${NETWORK_NAME}' (${SUBNET}, puerta ${GATEWAY}, puente ${BRIDGE_NAME})..."
    docker network create \
        --driver bridge \
        --subnet "${SUBNET}" \
        --gateway "${GATEWAY}" \
        --opt "com.docker.network.bridge.name=${BRIDGE_NAME}" \
        "${NETWORK_NAME}"
    echo "[OK] Red Docker '${NETWORK_NAME}' creada exitosamente."
fi

# 2. Crear el volumen externo 'alertas-socket' si no existe
if docker volume inspect "${VOLUME_NAME}" >/dev/null 2>&1; then
    echo "[INFO] El volumen Docker '${VOLUME_NAME}' ya existe."
else
    echo "[INFO] Creando volumen Docker '${VOLUME_NAME}'..."
    docker volume create "${VOLUME_NAME}"
    echo "[OK] Volumen Docker '${VOLUME_NAME}' creado exitosamente."
fi

# 3. Ajustar permisos del volumen para el socket Unix compartido
# Usuario detector-alertas (10501), grupo compartido snm-socket (10500), modo SGID 2770
if [[ -d "${VOLUME_DIR}" ]]; then
    echo "[INFO] Aplicando permisos de seguridad al directorio del socket (${VOLUME_DIR})..."
    chown -R 10501:10500 "${VOLUME_DIR}" || true
    chmod 2770 "${VOLUME_DIR}" || true
    echo "[OK] Permisos 2770 (10501:10500) aplicados al socket."
fi

echo "=========================================================="
echo "Verificación final:"
docker network inspect "${NETWORK_NAME}" --format 'Red: {{.Name}} | Subred: {{range .IPAM.Config}}{{.Subnet}}{{end}} | Puerta: {{range .IPAM.Config}}{{.Gateway}}{{end}}'
echo "[OK] Configuración de redes completada."
