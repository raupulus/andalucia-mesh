#!/usr/bin/env bash
# ==============================================================================
# ufw-setup.sh
# 
# Configura las reglas de cortafuegos UFW para el proyecto Andalucía Mesh.
# Reglas obligatorias de seguridad:
# - NUNCA ejecutar 'ufw reset'.
# - NUNCA cerrar ni restringir el puerto 22/tcp (SSH a fuego).
# - Cada regla debe incorporar su comentario descriptivo explicito.
# ==============================================================================

set -euo pipefail

echo "=========================================================="
echo "Configuración de Cortafuegos UFW para Andalucía Mesh"
echo "=========================================================="

# Comprobar privilegios de root
if [[ "${EUID}" -ne 0 ]]; then
    echo "[ERROR] Este script debe ejecutarse como root o mediante sudo." >&2
    exit 1
fi

# 1. Comprobación crítica de SSH (Regla de oro de seguridad)
echo "[INFO] Verificando protección del puerto SSH (22/tcp)..."
if ! ufw status | grep -q "22/tcp"; then
    echo "[AVISO] Asegurando regla explícita de SSH antes de continuar..."
    ufw allow 22/tcp comment 'SSH acceso remoto seguro'
fi
echo "[OK] Puerto SSH 22/tcp verificado y protegido."

# 2. Servicios Web (Nginx en el host)
echo "[INFO] Configurando puertos web de Nginx..."
ufw allow 80/tcp comment 'HTTP Nginx'
ufw allow 443/tcp comment 'HTTPS Nginx'

# 3. Servicios MQTT públicos (Mosquitto plano y TLS Stream en Nginx)
echo "[INFO] Configurando puertos MQTT públicos..."
ufw allow 1883/tcp comment 'MQTT Mosquitto plano'
ufw allow 8883/tcp comment 'MQTTS Nginx TLS stream'

# 4. Servicios internos accesibles únicamente desde la red Docker mesh (br-mesh)
# Subred: 172.30.0.0/24 -> IP Gateway del host: 172.30.0.1
echo "[INFO] Configurando reglas internas para el puente br-mesh..."
ufw allow in on br-mesh from 172.30.0.0/24 to 172.30.0.1 port 5432 proto tcp comment 'PostgreSQL desde Docker mesh'
ufw allow in on br-mesh from 172.30.0.0/24 to 172.30.0.1 port 1884 proto tcp comment 'Mosquitto interno desde Docker mesh'

# 5. Habilitar cortafuegos si estuviera inactivo
if ufw status | grep -qi "inactive"; then
    echo "[INFO] Habilitando UFW..."
    ufw --force enable
fi

echo "=========================================================="
echo "Estado actual del Cortafuegos UFW:"
echo "=========================================================="
ufw status numbered
echo "=========================================================="
echo "[OK] Reglas UFW configuradas correctamente."
