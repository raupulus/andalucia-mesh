# syntax=docker/dockerfile:1
# ==============================================================================
# Dockerfile (services/detector-alertas/Dockerfile)
# Multi-stage build para el microservicio snm-detector-alertas con uv y Python 3.13.
# ==============================================================================

FROM ghcr.io/astral-sh/uv:latest AS uv_bin

FROM python:3.13-slim AS builder

WORKDIR /app
COPY --from=uv_bin /uv /uvx /bin/

COPY pyproject.toml uv.lock ./
RUN uv sync --frozen --no-dev --no-install-project

COPY README.md ./
COPY detector/ ./detector/
COPY config/ ./config/
COPY migrations/ ./migrations/
RUN uv sync --frozen --no-dev

# ------------------------------------------------------------------------------
# Imagen final de ejecución (sin compiladores ni uv)
# ------------------------------------------------------------------------------
FROM python:3.13-slim AS runtime

RUN groupadd -g 10500 snm && useradd -u 10501 -g 10500 -m -s /bin/false snm

WORKDIR /app
COPY --from=builder /app/.venv /app/.venv
COPY --from=builder /app/detector /app/detector
COPY --from=builder /app/config /app/config
COPY --from=builder /app/migrations /app/migrations

ENV PATH="/app/.venv/bin:$PATH" \
    PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1

USER 10501:10500

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD python3 -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8080/health', timeout=4)" || exit 1

CMD ["python", "-m", "detector"]
