#!/usr/bin/env bash
# ==============================================================================
# mqtt-users.sh (integrations/mosquitto/tools/)
#
# Herramienta de gestión de usuarios y credenciales para el broker Mosquitto.
#
# Uso:
#   ./mqtt-users.sh add <node_id> [nota]   - Da de alta un gateway Meshtastic
#   ./mqtt-users.sh remove <node_id>      - Revoca acceso y corta sesiones
#   ./mqtt-users.sh rotate <node_id>      - Rota contraseña y reinicia broker
#   ./mqtt-users.sh service <svc_name>    - Gestiona usuario para un microservicio
#   ./mqtt-users.sh list [gateways|services] - Muestra usuarios registrados
# ==============================================================================

set -euo pipefail

export PATH="${PATH}:/usr/sbin:/sbin"

CRED_DIR="${MOSQUITTO_CRED_DIR:-/srv/mosquitto/credenciales}"
PASSWD_GATEWAYS="${CRED_DIR}/passwd-gateways"
PASSWD_SERVICES="${CRED_DIR}/passwd-servicios"
TSV_FILE="${CRED_DIR}/gateways.tsv"

ALLOWED_SERVICES=("svc-meshview" "svc-potato" "svc-ingest" "svc-detector" "svc-chatws" "svc-panel")

set_ownership() {
    local target="$1"
    if id mosquitto >/dev/null 2>&1; then
        chown mosquitto:mosquitto "${target}" 2>/dev/null || sudo chown mosquitto:mosquitto "${target}" 2>/dev/null || true
    fi
}

reload_mosquitto() {
    if command -v systemctl >/dev/null 2>&1 && systemctl is-active --quiet mosquitto 2>/dev/null; then
        echo "[INFO] Recargando Mosquitto (SIGHUP)..."
        sudo systemctl reload mosquitto 2>/dev/null || systemctl reload mosquitto 2>/dev/null || true
    fi
}

restart_mosquitto() {
    if command -v systemctl >/dev/null 2>&1 && systemctl is-active --quiet mosquitto 2>/dev/null; then
        echo "[INFO] Reiniciando Mosquitto para cortar sesiones activas..."
        sudo systemctl restart mosquitto 2>/dev/null || systemctl restart mosquitto 2>/dev/null || true
    fi
}

check_prerequisites() {
    if ! command -v mosquitto_passwd >/dev/null 2>&1; then
        echo "[ERROR] La herramienta 'mosquitto_passwd' (paquete mosquitto) no está disponible en el sistema." >&2
        exit 1
    fi
}

generate_password() {
    local length="${1:-24}"
    # Genera contraseña segura alfanumérica sin activar SIGPIPE en pipelines con pipefail
    openssl rand -base64 64 | LC_ALL=C tr -dc 'A-Za-z0-9' | head -c "${length}"
}

validate_node_id() {
    local id="$1"
    if [[ ! "${id}" =~ ^![0-9a-f]{8}$ ]]; then
        if [[ "${id}" =~ ^![0-9A-Fa-f]{8}$ ]]; then
            echo "[ERROR] El ID de nodo '${id}' contiene mayúsculas. Debe estar completamente en minúsculas (ej: '!2c3d4e5f')." >&2
        elif [[ ! "${id}" =~ ^! ]]; then
            echo "[ERROR] El ID de nodo '${id}' debe comenzar por el prefijo '!' (ej: '!${id}')." >&2
        else
            echo "[ERROR] Formato de ID de nodo inválido: '${id}'. Debe ser '!<8_hex_minusculas>' (ej: '!1a2b3c4d')." >&2
        fi
        exit 1
    fi
}

show_help() {
    cat <<EOF
Uso: $0 <comando> [argumentos...]

Comandos disponibles:
  add <node_id> [nota]   Crea un nuevo usuario de gateway (!<8_hex_minusculas>).
                         Genera contraseña de 24 caracteres y recarga Mosquitto sin corte.
  remove <node_id>       Elimina el usuario de gateway y reinicia el broker para cortar la sesión.
  rotate <node_id>       Genera una nueva contraseña y reinicia el broker forzando reconexión.
  service <svc_name>     Crea o actualiza las credenciales para un microservicio autorizado:
                         (${ALLOWED_SERVICES[*]})
  list [gateways|services]
                         Muestra los gateways o microservicios registrados.

Ejemplos:
  $0 add '!2a3b4c5d' "Nodo repetidor Sierra"
  $0 remove '!2a3b4c5d'
  $0 rotate '!2a3b4c5d'
  $0 service svc-ingest
  $0 list gateways
EOF
}

if [[ $# -lt 1 ]] || [[ "$1" == "-h" ]] || [[ "$1" == "--help" ]]; then
    show_help
    exit 0
fi

COMMAND="$1"
shift

case "${COMMAND}" in
    add)
        check_prerequisites
        if [[ $# -lt 1 ]]; then
            echo "[ERROR] Faltan argumentos. Uso: $0 add <node_id> [nota]" >&2
            exit 1
        fi
        NODE_ID="$1"
        NOTE="${2:-Sin descripcion}"
        validate_node_id "${NODE_ID}"

        if [[ ! -f "${PASSWD_GATEWAYS}" ]]; then
            touch "${PASSWD_GATEWAYS}" 2>/dev/null || sudo touch "${PASSWD_GATEWAYS}"
            chmod 0640 "${PASSWD_GATEWAYS}" 2>/dev/null || sudo chmod 0640 "${PASSWD_GATEWAYS}"
            set_ownership "${PASSWD_GATEWAYS}"
        fi

        # Comprobar si ya existe
        if grep -q "^${NODE_ID}:" "${PASSWD_GATEWAYS}" 2>/dev/null; then
            echo "[ERROR] El usuario '${NODE_ID}' ya existe en ${PASSWD_GATEWAYS}. Usa 'rotate' para cambiar contraseña." >&2
            exit 1
        fi

        PASSWORD="$(generate_password 24)"
        echo "[INFO] Generando usuario ${NODE_ID}..."
        mosquitto_passwd -b "${PASSWD_GATEWAYS}" "${NODE_ID}" "${PASSWORD}" 2>/dev/null || sudo mosquitto_passwd -b "${PASSWD_GATEWAYS}" "${NODE_ID}" "${PASSWORD}"
        chmod 0640 "${PASSWD_GATEWAYS}" 2>/dev/null || sudo chmod 0640 "${PASSWD_GATEWAYS}"
        set_ownership "${PASSWD_GATEWAYS}"

        # Registrar en gateways.tsv
        if [[ ! -f "${TSV_FILE}" ]]; then
            echo -e "# NODE_ID\tCREATED_AT\tSTATUS\tNOTE" > "${TSV_FILE}.tmp"
            mv -f "${TSV_FILE}.tmp" "${TSV_FILE}" 2>/dev/null || sudo mv -f "${TSV_FILE}.tmp" "${TSV_FILE}"
            chmod 0640 "${TSV_FILE}" 2>/dev/null || sudo chmod 0640 "${TSV_FILE}"
            set_ownership "${TSV_FILE}"
        fi

        NOW="$(date -u '+%Y-%m-%dT%H:%M:%SZ')"
        # Eliminar cualquier registro previo del mismo nodo
        if grep -v "^${NODE_ID}\s" "${TSV_FILE}" > "${TSV_FILE}.tmp" 2>/dev/null || sudo grep -v "^${NODE_ID}\s" "${TSV_FILE}" > "${TSV_FILE}.tmp"; then
            echo -e "${NODE_ID}\t${NOW}\tactive\t${NOTE}" >> "${TSV_FILE}.tmp"
            mv -f "${TSV_FILE}.tmp" "${TSV_FILE}" 2>/dev/null || sudo mv -f "${TSV_FILE}.tmp" "${TSV_FILE}"
            chmod 0640 "${TSV_FILE}" 2>/dev/null || sudo chmod 0640 "${TSV_FILE}"
            set_ownership "${TSV_FILE}"
        fi

        reload_mosquitto

        echo "================================================================================"
        echo " Gateway registrado con éxito"
        echo "================================================================================"
        echo " Usuario:     ${NODE_ID}"
        echo " Contraseña:  ${PASSWORD}"
        echo " Servidor:    mqtt.<PROJECT_DOMAIN> (puerto 8883 con TLS obligatorio)"
        echo " Canales:     Solo canales autorizados de Andalucía Mesh"
        echo "================================================================================"
        echo " NOTA: La contraseña no se almacena en texto plano y no se volverá a mostrar."
        ;;

    remove)
        check_prerequisites
        if [[ $# -lt 1 ]]; then
            echo "[ERROR] Faltan argumentos. Uso: $0 remove <node_id>" >&2
            exit 1
        fi
        NODE_ID="$1"
        validate_node_id "${NODE_ID}"

        if [[ ! -f "${PASSWD_GATEWAYS}" ]] || ! grep -q "^${NODE_ID}:" "${PASSWD_GATEWAYS}" 2>/dev/null; then
            echo "[ERROR] El usuario '${NODE_ID}' no existe en ${PASSWD_GATEWAYS}." >&2
            exit 1
        fi

        echo "[INFO] Eliminando usuario ${NODE_ID} de ${PASSWD_GATEWAYS}..."
        mosquitto_passwd -D "${PASSWD_GATEWAYS}" "${NODE_ID}" 2>/dev/null || sudo mosquitto_passwd -D "${PASSWD_GATEWAYS}" "${NODE_ID}"

        # Actualizar estado en gateways.tsv
        if [[ -f "${TSV_FILE}" ]]; then
            NOW="$(date -u '+%Y-%m-%dT%H:%M:%SZ')"
            awk -v id="${NODE_ID}" -v d="${NOW}" 'BEGIN{FS=OFS="\t"} $1==id {$3="revoked"; $4=$4 " [Revocado: " d "]"} {print}' "${TSV_FILE}" > "${TSV_FILE}.tmp" 2>/dev/null || \
                sudo awk -v id="${NODE_ID}" -v d="${NOW}" 'BEGIN{FS=OFS="\t"} $1==id {$3="revoked"; $4=$4 " [Revocado: " d "]"} {print}' "${TSV_FILE}" > "${TSV_FILE}.tmp"
            mv -f "${TSV_FILE}.tmp" "${TSV_FILE}" 2>/dev/null || sudo mv -f "${TSV_FILE}.tmp" "${TSV_FILE}"
            chmod 0640 "${TSV_FILE}" 2>/dev/null || sudo chmod 0640 "${TSV_FILE}"
            set_ownership "${TSV_FILE}"
        fi

        restart_mosquitto
        echo "[OK] Usuario ${NODE_ID} eliminado y conexiones cortadas."
        ;;

    rotate)
        check_prerequisites
        if [[ $# -lt 1 ]]; then
            echo "[ERROR] Faltan argumentos. Uso: $0 rotate <node_id>" >&2
            exit 1
        fi
        NODE_ID="$1"
        validate_node_id "${NODE_ID}"

        if [[ ! -f "${PASSWD_GATEWAYS}" ]] || ! grep -q "^${NODE_ID}:" "${PASSWD_GATEWAYS}" 2>/dev/null; then
            echo "[ERROR] El usuario '${NODE_ID}' no existe en ${PASSWD_GATEWAYS}." >&2
            exit 1
        fi

        PASSWORD="$(generate_password 24)"
        echo "[INFO] Rotando credencial para ${NODE_ID}..."
        mosquitto_passwd -b "${PASSWD_GATEWAYS}" "${NODE_ID}" "${PASSWORD}" 2>/dev/null || sudo mosquitto_passwd -b "${PASSWD_GATEWAYS}" "${NODE_ID}" "${PASSWORD}"
        chmod 0640 "${PASSWD_GATEWAYS}" 2>/dev/null || sudo chmod 0640 "${PASSWD_GATEWAYS}"
        set_ownership "${PASSWD_GATEWAYS}"

        if [[ -f "${TSV_FILE}" ]]; then
            NOW="$(date -u '+%Y-%m-%dT%H:%M:%SZ')"
            awk -v id="${NODE_ID}" -v d="${NOW}" 'BEGIN{FS=OFS="\t"} $1==id {$2=d; $3="active"; $4=$4 " [Rotado: " d "]"} {print}' "${TSV_FILE}" > "${TSV_FILE}.tmp" 2>/dev/null || \
                sudo awk -v id="${NODE_ID}" -v d="${NOW}" 'BEGIN{FS=OFS="\t"} $1==id {$2=d; $3="active"; $4=$4 " [Rotado: " d "]"} {print}' "${TSV_FILE}" > "${TSV_FILE}.tmp"
            mv -f "${TSV_FILE}.tmp" "${TSV_FILE}" 2>/dev/null || sudo mv -f "${TSV_FILE}.tmp" "${TSV_FILE}"
            chmod 0640 "${TSV_FILE}" 2>/dev/null || sudo chmod 0640 "${TSV_FILE}"
            set_ownership "${TSV_FILE}"
        fi

        restart_mosquitto

        echo "================================================================================"
        echo " Credencial rotada para ${NODE_ID}"
        echo "================================================================================"
        echo " Usuario:     ${NODE_ID}"
        echo " Nueva clave: ${PASSWORD}"
        echo "================================================================================"
        ;;

    service)
        check_prerequisites
        if [[ $# -lt 1 ]]; then
            echo "[ERROR] Faltan argumentos. Uso: $0 service <svc_name>" >&2
            echo "Servicios autorizados: ${ALLOWED_SERVICES[*]}" >&2
            exit 1
        fi
        SVC_NAME="$1"

        FOUND=0
        for s in "${ALLOWED_SERVICES[@]}"; do
            if [[ "${s}" == "${SVC_NAME}" ]]; then
                FOUND=1
                break
            fi
        done

        if [[ ${FOUND} -eq 0 ]]; then
            echo "[ERROR] Servicio '${SVC_NAME}' no autorizado. Debe ser uno de: ${ALLOWED_SERVICES[*]}" >&2
            exit 1
        fi

        if [[ ! -f "${PASSWD_SERVICES}" ]]; then
            touch "${PASSWD_SERVICES}" 2>/dev/null || sudo touch "${PASSWD_SERVICES}"
            chmod 0640 "${PASSWD_SERVICES}" 2>/dev/null || sudo chmod 0640 "${PASSWD_SERVICES}"
            set_ownership "${PASSWD_SERVICES}"
        fi

        PASSWORD="$(generate_password 32)"
        echo "[INFO] Configurando credencial para servicio ${SVC_NAME}..."
        mosquitto_passwd -b "${PASSWD_SERVICES}" "${SVC_NAME}" "${PASSWORD}" 2>/dev/null || sudo mosquitto_passwd -b "${PASSWD_SERVICES}" "${SVC_NAME}" "${PASSWORD}"
        chmod 0640 "${PASSWD_SERVICES}" 2>/dev/null || sudo chmod 0640 "${PASSWD_SERVICES}"
        set_ownership "${PASSWD_SERVICES}"

        reload_mosquitto

        echo "================================================================================"
        echo " Credencial generada para servicio interno ${SVC_NAME}"
        echo "================================================================================"
        echo " Usuario:       ${SVC_NAME}"
        echo " Contraseña:    ${PASSWORD}"
        echo " Host interno:  172.30.0.1:1884 (red mesh)"
        echo "================================================================================"
        echo " Añade esta clave en el archivo .env correspondiente del microservicio."
        ;;

    list)
        TARGET="${1:-gateways}"
        case "${TARGET}" in
            gateways)
                if [[ -f "${TSV_FILE}" ]]; then
                    echo "--- Gateways registrados (${TSV_FILE}) ---"
                    column -t -s $'\t' "${TSV_FILE}"
                else
                    echo "[AVISO] No existe el archivo ${TSV_FILE}"
                fi
                ;;
            services)
                if [[ -f "${PASSWD_SERVICES}" ]]; then
                    echo "--- Microservicios configurados (${PASSWD_SERVICES}) ---"
                    awk -F':' '{print "- " $1}' "${PASSWD_SERVICES}"
                else
                    echo "[AVISO] No existe el archivo ${PASSWD_SERVICES}"
                fi
                ;;
            *)
                echo "[ERROR] Objetivo de lista inválido: '${TARGET}'. Usa 'gateways' o 'services'." >&2
                exit 1
                ;;
        esac
        ;;

    *)
        echo "[ERROR] Comando desconocido: '${COMMAND}'" >&2
        show_help
        exit 1
        ;;
esac
