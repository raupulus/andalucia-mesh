#!/usr/bin/env bash
# ==============================================================================
# deploy.sh (infrastructure/)
#
# Script maestro de despliegue y actualización para Andalucía Mesh.
# Permite desplegar componentes de forma individual o el sistema completo:
#
# Uso:
#   ./deploy.sh <componente|all>
#
# Componentes nativos del host:
#   - mosquitto
#   - postgresql
#   - nginx
#
# Contenedores de integraciones:
#   - meshview
#   - potatomesh
#
# Contenedores de microservicios:
#   - adaptador-potato
#   - sync-peers
#   - ingesta
#   - portal
#   - chat-ws
#   - detector-alertas
#   - bot-telegram
#   - bot-discord
#   - webhooks
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

LOG_DIR="/var/www/storage/sur-nodos-en-mallas/logs"
mkdir -p "${LOG_DIR}" 2>/dev/null || LOG_DIR="/tmp"
LOG_FILE="${LOG_DIR}/deploy.log"

log() {
    local msg="[$(date '+%Y-%m-%d %H:%M:%S')] [USER:${USER:-unknown}] $1"
    echo -e "$1"
    echo "${msg}" >> "${LOG_FILE}" 2>/dev/null || true
}

show_help() {
    cat <<EOF
Uso: $0 <componente|all>

Componentes disponibles:
  Nativos:
    - mosquitto        Valida configuración y recarga Mosquitto
    - postgresql       Comprueba estado de conexión a TimescaleDB
    - nginx            Valida sintaxis y recarga Nginx

  Integraciones:
    - meshview         Despliega el visor técnico MeshView
    - potatomesh       Despliega la instancia PotatoMesh

  Servicios:
    - adaptador-potato Despliega el puente PotatoMesh <-> Mosquitto
    - sync-peers       Despliega el sincronizador de mallas vecinas
    - ingesta          Despliega el servicio de ingesta y TimescaleDB
    - portal           Despliega el portal web Laravel y API REST
    - chat-ws          Despliega el servidor de chat en tiempo real
    - detector-alertas Despliega el motor de reglas y alertas
    - bot-telegram     Despliega el bot de Telegram
    - bot-discord      Despliega el bot de Discord
    - webhooks         Despliega el servicio de webhooks salientes

  Especiales:
    - all              Despliega y verifica todos los componentes configurados

EOF
}

if [[ $# -lt 1 ]] || [[ "$1" == "-h" ]] || [[ "$1" == "--help" ]]; then
    show_help
    exit 0
fi

TARGET="$1"

# 1. Comprobación de seguridad Compose antes de desplegar
log "[INFO] Ejecutando validación estática de seguridad con check-compose.sh..."
"${SCRIPT_DIR}/check-compose.sh"

# Función para desplegar servicios nativos
deploy_native() {
    local name="$1"
    case "${name}" in
        mosquitto)
            log "[INFO] Comprobando configuración de Mosquitto nativo..."
            if command -v mosquitto >/dev/null 2>&1; then
                if [[ -f "/etc/mosquitto/conf.d/snm.conf" ]]; then
                    mosquitto -c /etc/mosquitto/conf.d/snm.conf -t
                fi
                if command -v systemctl >/dev/null 2>&1; then
                    systemctl reload mosquitto || systemctl restart mosquitto
                    log "[OK] Mosquitto recargado correctamente."
                fi
            else
                log "[AVISO] mosquitto no instalado en este entorno."
            fi
            ;;
        postgresql)
            log "[INFO] Comprobando conexión a PostgreSQL / TimescaleDB..."
            if command -v pg_isready >/dev/null 2>&1; then
                pg_isready -h 172.30.0.1 -p 5432 || pg_isready -h 127.0.0.1 -p 5432
                log "[OK] PostgreSQL responde correctamente."
            else
                log "[AVISO] Herramientas cliente PostgreSQL no disponibles."
            fi
            ;;
        nginx)
            log "[INFO] Validando y recargando Nginx nativo..."
            if command -v nginx >/dev/null 2>&1; then
                nginx -t
                if command -v systemctl >/dev/null 2>&1; then
                    systemctl reload nginx
                    log "[OK] Nginx recargado correctamente."
                fi
            else
                log "[AVISO] nginx no instalado en este entorno."
            fi
            ;;
        *)
            log "[ERROR] Servicio nativo desconocido: ${name}"
            return 1
            ;;
    esac
}

# Función para desplegar un contenedor Docker
deploy_container() {
    local dir_path="$1"
    local name="$2"

    if [[ ! -d "${dir_path}" ]]; then
        log "[AVISO] Directorio no encontrado para ${name}: ${dir_path}. Saltando."
        return 0
    fi

    local compose_file=""
    for candidate in "compose.yaml" "compose.yml" "docker-compose.yml" "docker-compose.yaml"; do
        if [[ -f "${dir_path}/${candidate}" ]]; then
            compose_file="${dir_path}/${candidate}"
            break
        fi
    done

    if [[ -z "${compose_file}" ]]; then
        log "[AVISO] No se encontró compose.yaml en ${dir_path} para ${name}. Saltando."
        return 0
    fi

    log "[INFO] ----------------------------------------------------"
    log "[INFO] Desplegando componente Docker: ${name}"
    log "[INFO] ----------------------------------------------------"

    local common_env="/var/www/storage/sur-nodos-en-mallas/common/.env"
    local local_env="${dir_path}/.env"
    local env_args=()

    if [[ -f "${common_env}" ]]; then
        env_args+=("--env-file" "${common_env}")
    fi
    if [[ -f "${local_env}" ]]; then
        env_args+=("--env-file" "${local_env}")
    fi

    # Comprobar si tiene Dockerfile (servicio propio -> build)
    if [[ -f "${dir_path}/Dockerfile" ]]; then
        log "[INFO] Construyendo imagen propia para ${name}..."
        docker compose "${env_args[@]}" -f "${compose_file}" build --pull
    else
        log "[INFO] Descargando imagen externa para ${name}..."
        docker compose "${env_args[@]}" -f "${compose_file}" pull --quiet || true
    fi

    log "[INFO] Iniciando contenedor(es) para ${name}..."
    docker compose "${env_args[@]}" -f "${compose_file}" up -d --remove-orphans

    # Espera breve y verificación de estado
    log "[INFO] Verificando salud de ${name}..."
    sleep 3
    docker compose "${env_args[@]}" -f "${compose_file}" ps

    log "[OK] Componente ${name} desplegado con éxito."
}

resolve_and_deploy() {
    local target="$1"
    case "${target}" in
        mosquitto|postgresql|nginx)
            deploy_native "${target}"
            ;;
        meshview|potatomesh)
            deploy_container "${BASE_DIR}/integrations/${target}" "${target}"
            ;;
        adaptador-potato|sync-peers|ingesta|portal|chat-ws|detector-alertas|bot-telegram|bot-discord|webhooks)
            deploy_container "${BASE_DIR}/services/${target}" "${target}"
            ;;
        all)
            log "[INFO] Desplegando todos los componentes del sistema..."
            deploy_native "mosquitto"
            deploy_native "postgresql"
            deploy_native "nginx"
            
            for item in meshview potatomesh; do
                deploy_container "${BASE_DIR}/integrations/${item}" "${item}"
            done

            for item in adaptador-potato sync-peers ingesta portal chat-ws detector-alertas bot-telegram bot-discord webhooks; do
                deploy_container "${BASE_DIR}/services/${item}" "${item}"
            done
            log "[OK] Despliegue global finalizado."
            ;;
        *)
            log "[ERROR] Componente desconocido: ${target}"
            show_help
            exit 1
            ;;
    esac
}

resolve_and_deploy "${TARGET}"
