#!/usr/bin/env bash
# ==============================================================================
# test-acl.sh (integrations/mosquitto/tools/)
#
# Suite de pruebas automatizada de ACL y aislamiento de listeners en Mosquitto.
# Ejecuta los escenarios formales descritos en docs/info/mosquitto/README.md §10.
#
# Uso:
#   ./test-acl.sh
# ==============================================================================

set -uo pipefail

export PATH="${PATH}:/usr/sbin:/sbin"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(cd "${SCRIPT_DIR}/../../.." && pwd)"

CRED_DIR="${MOSQUITTO_CRED_DIR:-/srv/mosquitto/credenciales}"
PASSWD_GATEWAYS="${CRED_DIR}/passwd-gateways"
PASSWD_SERVICES="${CRED_DIR}/passwd-servicios"

GW1="!ffff0001"
GW2="!ffff0002"
GW_PASS="TestPassAcl123456789012"

TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0
SKIPPED_TESTS=0

# Colores para terminal
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_test() {
    local id="$1"
    local desc="$2"
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo -e "${BLUE}[TEST ${id}]${NC} ${desc}..."
}

pass() {
    PASSED_TESTS=$((PASSED_TESTS + 1))
    echo -e "       ${GREEN}--> PASS${NC}"
}

fail() {
    local reason="$1"
    FAILED_TESTS=$((FAILED_TESTS + 1))
    echo -e "       ${RED}--> FAIL: ${reason}${NC}"
}

skip() {
    local reason="$1"
    SKIPPED_TESTS=$((SKIPPED_TESTS + 1))
    echo -e "       ${YELLOW}--> OMITIDA: ${reason}${NC}"
}

cleanup() {
    echo ""
    echo "[INFO] Limpiando gateways de prueba (${GW1}, ${GW2})..."
    if [[ -f "${PASSWD_GATEWAYS}" ]]; then
        mosquitto_passwd -D "${PASSWD_GATEWAYS}" "${GW1}" 2>/dev/null || sudo mosquitto_passwd -D "${PASSWD_GATEWAYS}" "${GW1}" 2>/dev/null || true
        mosquitto_passwd -D "${PASSWD_GATEWAYS}" "${GW2}" 2>/dev/null || sudo mosquitto_passwd -D "${PASSWD_GATEWAYS}" "${GW2}" 2>/dev/null || true
    fi
    if [[ -f "${CRED_DIR}/gateways.tsv" ]]; then
        grep -v -E "^(!ffff0001|!ffff0002)" "${CRED_DIR}/gateways.tsv" > "${CRED_DIR}/gateways.tsv.clean" 2>/dev/null || \
            sudo grep -v -E "^(!ffff0001|!ffff0002)" "${CRED_DIR}/gateways.tsv" > "${CRED_DIR}/gateways.tsv.clean" 2>/dev/null || true
        if [[ -f "${CRED_DIR}/gateways.tsv.clean" ]]; then
            mv -f "${CRED_DIR}/gateways.tsv.clean" "${CRED_DIR}/gateways.tsv" 2>/dev/null || \
                sudo mv -f "${CRED_DIR}/gateways.tsv.clean" "${CRED_DIR}/gateways.tsv" 2>/dev/null || true
        fi
    fi
    if command -v systemctl >/dev/null 2>&1 && systemctl is-active --quiet mosquitto 2>/dev/null; then
        sudo systemctl reload mosquitto 2>/dev/null || systemctl reload mosquitto 2>/dev/null || true
    fi
}
trap cleanup EXIT

# 0. Verificaciones previas
if ! command -v mosquitto_pub >/dev/null 2>&1 || ! command -v mosquitto_sub >/dev/null 2>&1; then
    echo "[ERROR] Las herramientas mosquitto_pub y mosquitto_sub (paquete mosquitto-clients) son requeridas." >&2
    exit 1
fi

if ! systemctl is-active --quiet mosquitto 2>/dev/null; then
    echo "[ERROR] El servicio mosquitto no está activo en systemd." >&2
    exit 1
fi

echo "================================================================================"
echo " Iniciando Batería de Pruebas de ACL y Seguridad (Andalucía Mesh)"
echo "================================================================================"

# Dar de alta gateways de prueba
echo "[INFO] Creando credenciales de prueba para ${GW1} y ${GW2}..."
mosquitto_passwd -b "${PASSWD_GATEWAYS}" "${GW1}" "${GW_PASS}" 2>/dev/null || sudo mosquitto_passwd -b "${PASSWD_GATEWAYS}" "${GW1}" "${GW_PASS}"
mosquitto_passwd -b "${PASSWD_GATEWAYS}" "${GW2}" "${GW_PASS}" 2>/dev/null || sudo mosquitto_passwd -b "${PASSWD_GATEWAYS}" "${GW2}" "${GW_PASS}"
sudo systemctl reload mosquitto 2>/dev/null || systemctl reload mosquitto 2>/dev/null || true
sleep 1

# ------------------------------------------------------------------------------
# TC-01: Publicación legítima de un gateway en su propio topic
# ------------------------------------------------------------------------------
log_test "TC-01" "${GW1} publica en su topic autorizado (msh/EU_868/2/e/SFNarrow/${GW1})"
SUB_OUT="/tmp/snm_sub_tc01_$$"
rm -f "${SUB_OUT}"
mosquitto_sub -h 127.0.0.1 -p 1885 -t "msh/EU_868/2/e/SFNarrow/${GW1}" -C 1 -W 3 > "${SUB_OUT}" 2>/dev/null &
SUB_PID=$!
sleep 0.5
mosquitto_pub -h 127.0.0.1 -p 1883 -u "${GW1}" -P "${GW_PASS}" -t "msh/EU_868/2/e/SFNarrow/${GW1}" -m "payload_test_01"
wait ${SUB_PID} 2>/dev/null || true

if [[ -f "${SUB_OUT}" ]] && grep -q "payload_test_01" "${SUB_OUT}"; then
    pass
else
    fail "El mensaje legítimo no fue recibido por el observador local en 1885."
fi
rm -f "${SUB_OUT}"

# ------------------------------------------------------------------------------
# TC-02: Publicación del usuario público compartido meshdev en canal autorizado
# ------------------------------------------------------------------------------
log_test "TC-02" "Usuario público meshdev publica en canal autorizado (msh/EU_868/2/e/SFNarrow/!gwpublic)"
SUB_OUT="/tmp/snm_sub_tc02_$$"
rm -f "${SUB_OUT}"
mosquitto_sub -h 127.0.0.1 -p 1885 -t "msh/EU_868/2/e/SFNarrow/!gwpublic" -C 1 -W 3 > "${SUB_OUT}" 2>/dev/null &
SUB_PID=$!
sleep 0.5
mosquitto_pub -h 127.0.0.1 -p 1883 -u "meshdev" -P "large4cats" -t "msh/EU_868/2/e/SFNarrow/!gwpublic" -m "meshdev_payload" 2>/dev/null || true
wait ${SUB_PID} 2>/dev/null || true

if [[ -f "${SUB_OUT}" ]] && grep -q "meshdev_payload" "${SUB_OUT}"; then
    pass
else
    fail "El mensaje del usuario público meshdev no fue recibido por el observador local en 1885."
fi
rm -f "${SUB_OUT}"

# ------------------------------------------------------------------------------
# TC-03: Publicación en canal no autorizado fuera de la lista blanca (Madrid)
# ------------------------------------------------------------------------------
log_test "TC-03" "${GW1} intenta publicar en canal no autorizado (msh/EU_868/2/e/Madrid/${GW1})"
SUB_OUT="/tmp/snm_sub_tc03_$$"
rm -f "${SUB_OUT}"
mosquitto_sub -h 127.0.0.1 -p 1885 -t "msh/EU_868/2/e/Madrid/${GW1}" -C 1 -W 2 > "${SUB_OUT}" 2>/dev/null &
SUB_PID=$!
sleep 0.5
mosquitto_pub -h 127.0.0.1 -p 1883 -u "${GW1}" -P "${GW_PASS}" -t "msh/EU_868/2/e/Madrid/${GW1}" -m "canal_invalido" 2>/dev/null || true
wait ${SUB_PID} 2>/dev/null || true

if [[ -f "${SUB_OUT}" ]] && grep -q "canal_invalido" "${SUB_OUT}"; then
    fail "Vulnerabilidad: Publicación autorizada en canal fuera de lista blanca."
else
    pass
fi
rm -f "${SUB_OUT}"

# ------------------------------------------------------------------------------
# TC-04: Publicación en canal con tilde (Cádiz)
# ------------------------------------------------------------------------------
log_test "TC-04" "${GW1} intenta publicar con tilde (msh/EU_868/2/e/Cádiz/${GW1})"
SUB_OUT="/tmp/snm_sub_tc04_$$"
rm -f "${SUB_OUT}"
mosquitto_sub -h 127.0.0.1 -p 1885 -t "msh/EU_868/2/e/Cádiz/${GW1}" -C 1 -W 2 > "${SUB_OUT}" 2>/dev/null &
SUB_PID=$!
sleep 0.5
mosquitto_pub -h 127.0.0.1 -p 1883 -u "${GW1}" -P "${GW_PASS}" -t "msh/EU_868/2/e/Cádiz/${GW1}" -m "canal_tilde" 2>/dev/null || true
wait ${SUB_PID} 2>/dev/null || true

if [[ -f "${SUB_OUT}" ]] && grep -q "canal_tilde" "${SUB_OUT}"; then
    fail "Vulnerabilidad: Publicación autorizada en canal no canónico con tilde."
else
    pass
fi
rm -f "${SUB_OUT}"

# ------------------------------------------------------------------------------
# TC-05: Intento de subscripción y recepción por gateway (Bloqueo de Downlink)
# ------------------------------------------------------------------------------
log_test "TC-05" "${GW1} intenta suscribirse a '#' y recibir tráfico de ${GW2}"
GW_SUB_OUT="/tmp/snm_gw_sub_$$"
rm -f "${GW_SUB_OUT}"
mosquitto_sub -h 127.0.0.1 -p 1883 -u "${GW1}" -P "${GW_PASS}" -t "#" -C 1 -W 2 > "${GW_SUB_OUT}" 2>/dev/null &
SUB_PID=$!
sleep 0.5
mosquitto_pub -h 127.0.0.1 -p 1883 -u "${GW2}" -P "${GW_PASS}" -t "msh/EU_868/2/e/SFNarrow/${GW2}" -m "test_downlink"
wait ${SUB_PID} 2>/dev/null || true

if [[ -f "${GW_SUB_OUT}" ]] && grep -q "test_downlink" "${GW_SUB_OUT}"; then
    fail "Vulnerabilidad crítica: Un gateway pudo recibir paquetes vía subscripción (vector downlink activo)."
else
    pass
fi
rm -f "${GW_SUB_OUT}"

# ------------------------------------------------------------------------------
# TC-06: Aislamiento de listener - Gateway intenta entrar en puerto 1884 (servicios)
# ------------------------------------------------------------------------------
log_test "TC-06" "${GW1} intenta conectar al listener interno 1884"
if mosquitto_pub -h 172.30.0.1 -p 1884 -u "${GW1}" -P "${GW_PASS}" -t "msh/EU_868/2/e/SFNarrow/${GW1}" -m "bypass_listener" 2>/dev/null; then
    fail "Vulnerabilidad: Gateway autenticado en listener interno 1884 de servicios."
else
    pass
fi

# ------------------------------------------------------------------------------
# TC-07: Aislamiento de listener - Intento anónimo en puertos 1883 y 1884
# ------------------------------------------------------------------------------
log_test "TC-07" "Conexión anónima rechazada en listeners 1883 y 1884"
ANON_83=0
ANON_84=0
if mosquitto_pub -h 127.0.0.1 -p 1883 -t "msh/EU_868/2/e/SFNarrow/anon" -m "anon" 2>/dev/null; then
    ANON_83=1
fi
if mosquitto_pub -h 172.30.0.1 -p 1884 -t "msh/EU_868/2/e/SFNarrow/anon" -m "anon" 2>/dev/null; then
    ANON_84=1
fi

if [[ ${ANON_83} -eq 0 ]] && [[ ${ANON_84} -eq 0 ]]; then
    pass
else
    fail "Vulnerabilidad: Se permitió publicación anónima (1883:${ANON_83}, 1884:${ANON_84})."
fi

# ------------------------------------------------------------------------------
# TC-08: Límite de tamaño de paquete (>16 KB debe ser rechazado)
# ------------------------------------------------------------------------------
log_test "TC-08" "Rechazo y desconexión ante payload superior a 16 KB"
LARGE_PAYLOAD="$(head -c 20480 < /dev/zero | tr '\0' 'A')"
SUB_OUT="/tmp/snm_sub_tc08_$$"
rm -f "${SUB_OUT}"
mosquitto_sub -h 127.0.0.1 -p 1885 -t "msh/EU_868/2/e/SFNarrow/${GW1}" -C 1 -W 2 > "${SUB_OUT}" 2>/dev/null &
SUB_PID=$!
sleep 0.5
mosquitto_pub -h 127.0.0.1 -p 1883 -u "${GW1}" -P "${GW_PASS}" -t "msh/EU_868/2/e/SFNarrow/${GW1}" -m "${LARGE_PAYLOAD}" 2>/dev/null || true
wait ${SUB_PID} 2>/dev/null || true

if [[ -f "${SUB_OUT}" ]] && [[ -s "${SUB_OUT}" ]]; then
    fail "Vulnerabilidad: El broker aceptó un paquete superior al límite de 16 KB."
else
    pass
fi
rm -f "${SUB_OUT}"

# ------------------------------------------------------------------------------
# TC-09: Comprobación de mensajes retenidos en msh/#
# ------------------------------------------------------------------------------
log_test "TC-09" "Auditoría de mensajes retenidos en topics de radio (msh/#)"
RETAINED_OUT="$(mosquitto_sub -h 127.0.0.1 -p 1885 --retained-only -t 'msh/#' -W 2 2>/dev/null || true)"
if [[ -z "${RETAINED_OUT}" ]]; then
    pass
else
    fail "Se detectaron mensajes retenidos en msh/#."
fi

# ------------------------------------------------------------------------------
# TC-10: Healthcheck local en 1885 ($SYS/broker/uptime)
# ------------------------------------------------------------------------------
log_test "TC-10" "Lectura anónima de métricas en listener local 1885 (\$SYS/broker/uptime)"
UPTIME_OUT="$(mosquitto_sub -h 127.0.0.1 -p 1885 -t '$SYS/broker/uptime' -C 1 -W 3 2>/dev/null || true)"
if [[ -n "${UPTIME_OUT}" ]]; then
    pass
else
    fail "El listener local 1885 no devolvió la métrica \$SYS/broker/uptime."
fi

# ------------------------------------------------------------------------------
# TC-11 & TC-12: Pruebas de microservicios (si existen ficheros .env)
# ------------------------------------------------------------------------------
log_test "TC-11" "Aislamiento de microservicio svc-ingest (si configurado en .env)"
INGEST_ENV="/srv/ingesta/.env"
if [[ -f "${INGEST_ENV}" ]] && grep -q "MQTT_PASSWORD=" "${INGEST_ENV}"; then
    INGEST_PASS="$(grep -E "^MQTT_PASSWORD=" "${INGEST_ENV}" | cut -d'=' -f2- | tr -d '"'\''')"
    # 1. svc-ingest no debe conectar a 1883
    if mosquitto_pub -h 127.0.0.1 -p 1883 -u "svc-ingest" -P "${INGEST_PASS}" -t "test" -m "x" 2>/dev/null; then
        fail "svc-ingest pudo conectar al listener público 1883."
    else
        # 2. svc-ingest debe poder publicar en snm/v1/decoded/text en 1884
        if mosquitto_pub -h 172.30.0.1 -p 1884 -u "svc-ingest" -P "${INGEST_PASS}" -t "snm/v1/decoded/text" -m "test_ingest" 2>/dev/null; then
            # 3. svc-ingest no debe poder publicar en msh/#
            SUB_OUT="/tmp/snm_sub_tc11_$$"
            rm -f "${SUB_OUT}"
            mosquitto_sub -h 127.0.0.1 -p 1885 -t "msh/EU_868/2/e/SFNarrow/svc-ingest" -C 1 -W 2 > "${SUB_OUT}" 2>/dev/null &
            SUB_PID=$!
            sleep 0.5
            mosquitto_pub -h 172.30.0.1 -p 1884 -u "svc-ingest" -P "${INGEST_PASS}" -t "msh/EU_868/2/e/SFNarrow/svc-ingest" -m "x" 2>/dev/null || true
            wait ${SUB_PID} 2>/dev/null || true
            if [[ -f "${SUB_OUT}" ]] && grep -q "x" "${SUB_OUT}"; then
                fail "svc-ingest pudo publicar en msh/#."
            else
                pass
            fi
            rm -f "${SUB_OUT}"
        else
            fail "svc-ingest no pudo publicar en snm/v1/decoded/text en el puerto 1884."
        fi
    fi
else
    skip "No existe ${INGEST_ENV} con credenciales de svc-ingest (se verificará en Fase 4)."
fi

log_test "TC-12" "Conexión de salud de svc-panel (si configurado en /srv/portal/.env)"
PORTAL_ENV="/srv/portal/.env"
if [[ -f "${PORTAL_ENV}" ]] && grep -q "MQTT_PASSWORD=" "${PORTAL_ENV}"; then
    PANEL_PASS="$(grep -E "^MQTT_PASSWORD=" "${PORTAL_ENV}" | cut -d'=' -f2- | tr -d '"'\''')"
    # svc-panel conecta sin permisos de lectura en #
    SUB_OUT="/tmp/snm_sub_panel_$$"
    rm -f "${SUB_OUT}"
    mosquitto_sub -h 172.30.0.1 -p 1884 -u "svc-panel" -P "${PANEL_PASS}" -t "#" -C 1 -W 2 > "${SUB_OUT}" 2>/dev/null &
    SUB_PID=$!
    sleep 0.5
    mosquitto_pub -h 127.0.0.1 -p 1885 -t "msh/EU_868/test" -m "ping" 2>/dev/null || true
    wait ${SUB_PID} 2>/dev/null || true
    if [[ -f "${SUB_OUT}" ]] && [[ -s "${SUB_OUT}" ]]; then
        fail "svc-panel tiene permisos de lectura indebidos sobre topics."
    else
        pass
    fi
    rm -f "${SUB_OUT}"
else
    skip "No existe ${PORTAL_ENV} con credenciales de svc-panel (se verificará en Fase 5)."
fi

echo "================================================================================"
echo " Resumen de Resultados"
echo "================================================================================"
echo " Total ejecutados: ${TOTAL_TESTS}"
echo -e " Aprobados:        ${GREEN}${PASSED_TESTS}${NC}"
echo -e " Fallidos:         ${RED}${FAILED_TESTS}${NC}"
echo -e " Omitidos:         ${YELLOW}${SKIPPED_TESTS}${NC}"
echo "================================================================================"

if [[ ${FAILED_TESTS} -gt 0 ]]; then
    echo -e "${RED}[RESULTADO FINAL] Se detectaron fallos de seguridad o configuración en Mosquitto.${NC}" >&2
    exit 1
else
    echo -e "${GREEN}[RESULTADO FINAL] Todos los tests de aislamiento y seguridad han pasado con éxito.${NC}"
    exit 0
fi
