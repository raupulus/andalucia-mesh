# syntax=docker/dockerfile:1
# ==============================================================================
# Dockerfile (services/adaptador-potato/Dockerfile)
# Multi-stage build para el microservicio adaptador-potato con uv y Python 3.13.
# ==============================================================================

FROM ghcr.io/astral-sh/uv:latest AS uv_bin

FROM python:3.13-slim AS builder

WORKDIR /app
COPY --from=uv_bin /uv /uvx /bin/

COPY pyproject.toml uv.lock ./
RUN uv sync --frozen --no-dev --no-install-project

COPY README.md ./
COPY src/ ./src/
RUN uv sync --frozen --no-dev

# ------------------------------------------------------------------------------
# Imagen final de ejecución (sin herramientas de compilación ni uv)
# ------------------------------------------------------------------------------
FROM python:3.13-slim AS runtime

RUN useradd -u 10500 -m -s /bin/false snm

WORKDIR /app
COPY --from=builder /app/.venv /app/.venv
COPY --from=builder /app/src /app/src

ENV PATH="/app/.venv/bin:$PATH" \
    PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1

USER snm

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD python3 -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8080/health', timeout=4)" || exit 1

CMD ["python", "-m", "src.main"]
