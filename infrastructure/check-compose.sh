#!/usr/bin/env bash
# ==============================================================================
# check-compose.sh (infrastructure/)
#
# Auditor estático de seguridad para archivos Docker Compose en Andalucía Mesh:
# - Verifica que la directiva 'ports:' SOLO exponga en '127.0.0.1:'.
# - Rechaza cualquier binding a '0.0.0.0' o puertos huérfanos sin IP.
# - Verifica la conexión a la red externa 'mesh'.
# - Verifica política de reinicio 'restart: unless-stopped'.
# - Valida sintaxis con 'docker compose config -q' si Docker está disponible.
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}==========================================================${NC}"
echo -e "${BLUE}Auditoría de Seguridad de Docker Compose (Andalucía Mesh)${NC}"
echo -e "${BLUE}==========================================================${NC}"

SCAN_DIRS=("${BASE_DIR}/services" "${BASE_DIR}/integrations")
COMPOSE_FILES=()

for d in "${SCAN_DIRS[@]}"; do
    if [[ -d "${d}" ]]; then
        while IFS= read -r -d '' file; do
            COMPOSE_FILES+=("${file}")
        done < <(find "${d}" -type f \( -name "compose.yaml" -o -name "compose.yml" -o -name "docker-compose.yml" -o -name "docker-compose.yaml" \) -print0)
    fi
done

if [[ ${#COMPOSE_FILES[@]} -eq 0 ]]; then
    echo -e "${GREEN}[OK] No se encontraron archivos Compose en services/ ni integrations/.${NC}"
    echo -e "${GREEN}[OK] Validación estática superada (sin componentes activos que auditar).${NC}"
    exit 0
fi

TOTAL_ERRORS=0

for file in "${COMPOSE_FILES[@]}"; do
    REL_PATH="${file#${BASE_DIR}/}"
    echo -e "\n${BLUE}Verificando: ${REL_PATH}${NC}"
    FILE_ERRORS=0

    # 1. Comprobar que ningún puerto se exponga sin 127.0.0.1
    # Buscar líneas de puertos bajo 'ports:'
    # Coincide con patrones como: - "8080:8080", - 8080:8080, - "0.0.0.0:8080:8080"
    while IFS= read -r line; do
        # Limpiar espacios
        clean_line=$(echo "${line}" | sed -e 's/^[[:space:]]*-[[:space:]]*//' -e 's/["'\'']//g')
        # Si la línea contiene ':' y parece un mapeo de puertos pero no empieza por 127.0.0.1:
        if [[ "${clean_line}" =~ ^[0-9]+:[0-9]+ ]] || [[ "${clean_line}" =~ ^0\.0\.0\.0: ]]; then
            echo -e "  ${RED}[FALLO] Exposición no segura de puertos detectada: '${clean_line}'${NC}"
            echo -e "          Todos los puertos DEBEN publicarse explícitamente en 127.0.0.1 (ej. '127.0.0.1:8080:8080')."
            FILE_ERRORS=$((FILE_ERRORS + 1))
        fi
    done < <(awk '/ports:/ {flag=1; next} /^[a-zA-Z]/ {flag=0} flag && /^[[:space:]]*-/ {print $0}' "${file}")

    # 2. Comprobar que use la red 'mesh'
    if ! grep -q "mesh:" "${file}"; then
        echo -e "  ${RED}[FALLO] El archivo no define ni referencia la red 'mesh'.${NC}"
        FILE_ERRORS=$((FILE_ERRORS + 1))
    fi

    # 3. Comprobar red mesh externa
    if grep -q "networks:" "${file}"; then
        if ! grep -A 3 "mesh:" "${file}" | grep -q "external:[[:space:]]*true"; then
            echo -e "  ${YELLOW}[AVISO] La red 'mesh' debería estar definida con 'external: true'.${NC}"
        fi
    fi

    # 4. Comprobar directiva restart: unless-stopped
    if ! grep -q "restart:[[:space:]]*unless-stopped" "${file}"; then
        echo -e "  ${YELLOW}[AVISO] Se recomienda la directiva 'restart: unless-stopped'.${NC}"
    fi

    # 5. Comprobación de sintaxis con docker compose si está disponible
    if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
        if ! docker compose -f "${file}" config -q 2>/dev/null; then
            echo -e "  ${RED}[FALLO] 'docker compose config' falló para ${REL_PATH}.${NC}"
            FILE_ERRORS=$((FILE_ERRORS + 1))
        fi
    fi

    if [[ ${FILE_ERRORS} -eq 0 ]]; then
        echo -e "  ${GREEN}[OK] Configuración segura y conforme a las normas del proyecto.${NC}"
    else
        TOTAL_ERRORS=$((TOTAL_ERRORS + FILE_ERRORS))
    fi
done

echo -e "\n${BLUE}==========================================================${NC}"
if [[ ${TOTAL_ERRORS} -eq 0 ]]; then
    echo -e "${GREEN}[OK] Todos los archivos Compose (${#COMPOSE_FILES[@]}) son seguros y válidos.${NC}"
    exit 0
else
    echo -e "${RED}[ERROR] Se encontraron ${TOTAL_ERRORS} problemas de seguridad o sintaxis.${NC}"
    exit 1
fi
