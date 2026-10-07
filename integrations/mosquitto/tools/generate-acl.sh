#!/usr/bin/env bash
# ==============================================================================
# generate-acl.sh (integrations/mosquitto/tools/)
#
# Generador determinista y atómico de listas de control de acceso (ACL)
# para el broker Mosquitto de Andalucía Mesh.
#
# Uso:
#   ./generate-acl.sh [--init]
#
# Opciones:
#   --init    Crea el directorio de credenciales y archivos vacíos iniciales
#             (passwd-gateways, passwd-servicios, gateways.tsv) con permisos 0640.
#
# Variables requeridas (cargadas de /srv/comun/.env o valores por defecto):
#   - MQTT_TOPIC_ROOT   (por defecto: msh/EU_868)
#   - MQTT_TOPIC_PREFIX (por defecto: snm)
#   - ALLOWED_CHANNELS  (lista separada por comas)
#   - PRIMARY_CHANNEL   (por defecto: SFNarrow)
# ==============================================================================

set -euo pipefail

export PATH="${PATH}:/usr/sbin:/sbin"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(cd "${SCRIPT_DIR}/../../.." && pwd)"

CRED_DIR="${MOSQUITTO_CRED_DIR:-/srv/mosquitto/credenciales}"

# Cargar variables de entorno si existen
if [[ -f "/srv/comun/.env" ]]; then
    # shellcheck disable=SC1091
    source "/srv/comun/.env"
elif [[ -f "${BASE_DIR}/infrastructure/common/.env.example" ]]; then
    # Cargar valores por defecto para pruebas
    set -a
    # shellcheck disable=SC1091
    source <(grep -E '^(MQTT_TOPIC_ROOT|MQTT_TOPIC_PREFIX|ALLOWED_CHANNELS|PRIMARY_CHANNEL)=' "${BASE_DIR}/infrastructure/common/.env.example" || true)
    set +a
fi

# Valores por defecto seguros
MQTT_TOPIC_ROOT="${MQTT_TOPIC_ROOT:-msh/EU_868}"
MQTT_TOPIC_PREFIX="${MQTT_TOPIC_PREFIX:-snm}"
PRIMARY_CHANNEL="${PRIMARY_CHANNEL:-SFNarrow}"
ALLOWED_CHANNELS="${ALLOWED_CHANNELS:-SFNarrow,Iberia,Andalucia,Cadiz,Huelva,Almeria,Granada,Jaen,Sevilla,Cordoba,Malaga,Ceuta,Melilla,sos}"

# 1. Validaciones estrictas de seguridad sintáctica
if [[ "${MQTT_TOPIC_ROOT}" =~ [\+\#\ ] ]] || [[ -z "${MQTT_TOPIC_ROOT}" ]]; then
    echo "[ERROR] MQTT_TOPIC_ROOT inválido ('${MQTT_TOPIC_ROOT}'). No puede contener comodines (+, #) ni espacios." >&2
    exit 1
fi

if [[ "${MQTT_TOPIC_PREFIX}" =~ [\+\#\ ] ]] || [[ -z "${MQTT_TOPIC_PREFIX}" ]]; then
    echo "[ERROR] MQTT_TOPIC_PREFIX inválido ('${MQTT_TOPIC_PREFIX}'). No puede contener comodines (+, #) ni espacios." >&2
    exit 1
fi

if [[ -z "${ALLOWED_CHANNELS}" ]]; then
    echo "[ERROR] ALLOWED_CHANNELS no puede estar vacío." >&2
    exit 1
fi

# Validar canales
IFS=',' read -ra CHANNELS <<< "${ALLOWED_CHANNELS}"
declare -A SEEN_CHANNELS=()
PRIMARY_FOUND=0

for ch in "${CHANNELS[@]}"; do
    ch="$(echo "${ch}" | xargs)" # Trim
    if [[ ! "${ch}" =~ ^[A-Za-z0-9_-]{1,11}$ ]]; then
        echo "[ERROR] Canal inválido: '${ch}'. Solo se admiten de 1 a 11 caracteres [A-Za-z0-9_-]." >&2
        exit 1
    fi
    if [[ -n "${SEEN_CHANNELS[${ch}]:-}" ]]; then
        echo "[ERROR] Canal duplicado en ALLOWED_CHANNELS: '${ch}'." >&2
        exit 1
    fi
    SEEN_CHANNELS["${ch}"]=1
    if [[ "${ch}" == "${PRIMARY_CHANNEL}" ]]; then
        PRIMARY_FOUND=1
    fi
done

if [[ ${PRIMARY_FOUND} -eq 0 ]]; then
    echo "[ERROR] El canal primario '${PRIMARY_CHANNEL}' no está presente en ALLOWED_CHANNELS." >&2
    exit 1
fi

INIT_MODE=0
if [[ $# -gt 0 ]] && [[ "$1" == "--init" ]]; then
    INIT_MODE=1
fi

# Función para ajustar propiedad mosquitto si el usuario existe
set_ownership() {
    local target="$1"
    if id mosquitto >/dev/null 2>&1; then
        chown mosquitto:mosquitto "${target}" 2>/dev/null || sudo chown mosquitto:mosquitto "${target}" 2>/dev/null || true
    fi
}

# Inicialización si se solicita
if [[ ${INIT_MODE} -eq 1 ]]; then
    echo "[INFO] Inicializando directorio y archivos de credenciales en ${CRED_DIR}..."
    mkdir -p "${CRED_DIR}" 2>/dev/null || sudo mkdir -p "${CRED_DIR}"
    chmod 0750 "${CRED_DIR}" 2>/dev/null || sudo chmod 0750 "${CRED_DIR}"
    set_ownership "${CRED_DIR}"

    for f in "passwd-gateways" "passwd-servicios"; do
        if [[ ! -f "${CRED_DIR}/${f}" ]]; then
            touch "${CRED_DIR}/${f}" 2>/dev/null || sudo touch "${CRED_DIR}/${f}"
            chmod 0640 "${CRED_DIR}/${f}" 2>/dev/null || sudo chmod 0640 "${CRED_DIR}/${f}"
            set_ownership "${CRED_DIR}/${f}"
            echo "[OK] Creado fichero vacío ${CRED_DIR}/${f}"
        fi
    done

    if [[ ! -f "${CRED_DIR}/gateways.tsv" ]]; then
        cat <<EOF > "${CRED_DIR}/gateways.tsv.tmp"
# NODE_ID	CREATED_AT	STATUS	NOTE
EOF
        mv -f "${CRED_DIR}/gateways.tsv.tmp" "${CRED_DIR}/gateways.tsv" 2>/dev/null || sudo mv -f "${CRED_DIR}/gateways.tsv.tmp" "${CRED_DIR}/gateways.tsv"
        chmod 0640 "${CRED_DIR}/gateways.tsv" 2>/dev/null || sudo chmod 0640 "${CRED_DIR}/gateways.tsv"
        set_ownership "${CRED_DIR}/gateways.tsv"
        echo "[OK] Creado fichero inicial ${CRED_DIR}/gateways.tsv"
    fi
fi

if [[ ! -d "${CRED_DIR}" ]]; then
    echo "[ERROR] El directorio ${CRED_DIR} no existe. Ejecuta con --init primero." >&2
    exit 1
fi

TMP_PREFIX="${CRED_DIR}/.acl_tmp_$$"

# 2. Generación atómica de acl-gateways
echo "[INFO] Generando ${CRED_DIR}/acl-gateways..."
{
    echo "# =============================================================================="
    echo "# acl-gateways"
    echo "# Generado automáticamente por generate-acl.sh. NO EDITAR DIRECTAMENTE."
    echo "# Solo escritura en los 14 canales autorizados y map reports. SIN LECTURA."
    echo "# =============================================================================="
    for ch in "${CHANNELS[@]}"; do
        ch="$(echo "${ch}" | xargs)"
        echo "pattern write ${MQTT_TOPIC_ROOT}/2/e/${ch}/%u"
    done
    echo "topic write ${MQTT_TOPIC_ROOT}/2/map/#"
} > "${TMP_PREFIX}.gateways"

chmod 0640 "${TMP_PREFIX}.gateways" 2>/dev/null || sudo chmod 0640 "${TMP_PREFIX}.gateways"
set_ownership "${TMP_PREFIX}.gateways"
mv -f "${TMP_PREFIX}.gateways" "${CRED_DIR}/acl-gateways" 2>/dev/null || sudo mv -f "${TMP_PREFIX}.gateways" "${CRED_DIR}/acl-gateways"

# 3. Generación atómica de acl-servicios
echo "[INFO] Generando ${CRED_DIR}/acl-servicios..."
{
    echo "# =============================================================================="
    echo "# acl-servicios"
    echo "# Generado automáticamente por generate-acl.sh. NO EDITAR DIRECTAMENTE."
    echo "# Permisos de mínimo privilegio para microservicios internos en listener 1884."
    echo "# =============================================================================="
    echo ""
    echo "user svc-meshview"
    echo "topic read ${MQTT_TOPIC_ROOT}/#"
    echo ""
    echo "user svc-potato"
    echo "topic read ${MQTT_TOPIC_ROOT}/#"
    echo "topic write ${MQTT_TOPIC_PREFIX}/v1/peer/#"
    echo ""
    echo "user svc-ingest"
    echo "topic read ${MQTT_TOPIC_ROOT}/#"
    echo "topic read ${MQTT_TOPIC_PREFIX}/v1/peer/#"
    echo "topic write ${MQTT_TOPIC_PREFIX}/v1/decoded/#"
    echo ""
    echo "user svc-detector"
    echo "topic read ${MQTT_TOPIC_PREFIX}/v1/decoded/#"
    echo ""
    echo "user svc-chatws"
    echo "topic read ${MQTT_TOPIC_PREFIX}/v1/decoded/text"
    echo ""
    echo "# user svc-panel: sin reglas de topic; solo conexión para healthcheck de salud"
} > "${TMP_PREFIX}.servicios"

chmod 0640 "${TMP_PREFIX}.servicios" 2>/dev/null || sudo chmod 0640 "${TMP_PREFIX}.servicios"
set_ownership "${TMP_PREFIX}.servicios"
mv -f "${TMP_PREFIX}.servicios" "${CRED_DIR}/acl-servicios" 2>/dev/null || sudo mv -f "${TMP_PREFIX}.servicios" "${CRED_DIR}/acl-servicios"

# 4. Generación atómica de acl-local (loopback 1885)
echo "[INFO] Generando ${CRED_DIR}/acl-local..."
{
    echo "# =============================================================================="
    echo "# acl-local"
    echo "# Generado automáticamente por generate-acl.sh. NO EDITAR DIRECTAMENTE."
    echo "# Listener 1885 solo en loopback (127.0.0.1) para diagnóstico y vigilancia."
    echo "# =============================================================================="
    echo "topic read \$SYS/#"
    echo "topic read ${MQTT_TOPIC_ROOT}/#"
    echo "topic read ${MQTT_TOPIC_PREFIX}/v1/decoded/#"
} > "${TMP_PREFIX}.local"

chmod 0640 "${TMP_PREFIX}.local" 2>/dev/null || sudo chmod 0640 "${TMP_PREFIX}.local"
set_ownership "${TMP_PREFIX}.local"
mv -f "${TMP_PREFIX}.local" "${CRED_DIR}/acl-local" 2>/dev/null || sudo mv -f "${TMP_PREFIX}.local" "${CRED_DIR}/acl-local"

echo "[OK] Las tres listas ACL han sido generadas y actualizadas de forma atómica."

# 5. Recarga en caliente si el servicio está en ejecución
if command -v systemctl >/dev/null 2>&1; then
    if systemctl is-active --quiet mosquitto 2>/dev/null; then
        echo "[INFO] Recargando Mosquitto (SIGHUP)..."
        sudo systemctl reload mosquitto 2>/dev/null || systemctl reload mosquitto 2>/dev/null || true
        echo "[OK] Mosquitto recargado correctamente."
    fi
fi
