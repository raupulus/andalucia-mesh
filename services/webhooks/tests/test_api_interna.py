"""Pruebas unitarias y de integración para la API HTTP interna de gestión de webhooks."""

from typing import Any
from unittest.mock import AsyncMock, MagicMock, patch

import pytest
from aiohttp import web
from aiohttp.test_utils import TestClient, TestServer

from webhooks.api_interna import registrar_api_interna
from webhooks.configuracion import ConfiguracionWebhooks


class MockCursor:
    """Mock asíncrono para cursores de base de datos."""

    def __init__(self, fetchone_result: Any = None, fetchall_result: Any = None) -> None:
        self._fetchone_result = fetchone_result
        self._fetchall_result = fetchall_result or []
        self.executed_queries: list[tuple[str, Any]] = []

    async def __aenter__(self) -> "MockCursor":
        return self

    async def __aexit__(self, *args: Any) -> None:
        pass

    async def execute(self, query: str, params: Any = None) -> None:
        self.executed_queries.append((query, params))

    async def fetchone(self) -> Any:
        return self._fetchone_result

    async def fetchall(self) -> list[Any]:
        return self._fetchall_result


class MockConnection:
    """Mock asíncrono para conexiones de base de datos."""

    def __init__(self, cursor: MockCursor) -> None:
        self._cursor = cursor

    async def __aenter__(self) -> "MockConnection":
        return self

    async def __aexit__(self, *args: Any) -> None:
        pass

    def transaction(self) -> "MockConnection":
        return self

    def cursor(self) -> MockCursor:
        return self._cursor


class MockGestorBase:
    """Mock para el gestor de base de datos."""

    def __init__(self, cursor: MockCursor) -> None:
        self._conn = MockConnection(cursor)

    def conexion(self) -> MockConnection:
        return self._conn


@pytest.fixture
def config_test() -> ConfiguracionWebhooks:
    """Crea una configuración de prueba con secreto de API interna."""
    return ConfiguracionWebhooks(
        db_name="test_db",
        db_user="test_user",
        db_password="test_password",
        internal_api_secret="secreto_interno_super_seguro_123456",
    )


@pytest.fixture
def motor_mock() -> MagicMock:
    """Crea un mock del motor de entregas."""
    motor = MagicMock()
    motor.recargar_destinos_db = AsyncMock()
    motor.despertar = MagicMock()
    return motor


async def crear_cliente_test(
    config: ConfiguracionWebhooks,
    cursor: MockCursor,
    motor: MagicMock,
) -> TestClient[web.Request, web.Application]:
    """Instancia la aplicación web y devuelve un TestClient configurado."""
    app = web.Application()
    gestor = MockGestorBase(cursor)
    registrar_api_interna(app, config, gestor, motor)  # type: ignore[arg-type]
    server = TestServer(app)
    client = TestClient(server)
    await client.start_server()
    return client


async def test_auth_fallida_sin_cabecera(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba que una petición sin la cabecera X-Internal-Secret devuelve 401."""
    cursor = MockCursor()
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    try:
        resp = await client.get("/internal/destinos")
        assert resp.status == 401
        datos = await resp.json()
        assert datos["ok"] is False
    finally:
        await client.close()


async def test_auth_fallida_con_cabecera_incorrecta(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba que una petición con cabecera incorrecta devuelve 401."""
    cursor = MockCursor()
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    try:
        resp = await client.get("/internal/destinos", headers={"X-Internal-Secret": "secreto_invalido"})
        assert resp.status == 401
    finally:
        await client.close()


async def test_listar_destinos_ok(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba el listado de destinos con autenticación correcta."""
    cursor = MockCursor(
        fetchall_result=[
            (
                "cadiz-mesh",
                "https://alertas.cadiz.es/hook",
                "alertas.cadiz.es",
                True,
                None,
                0,
                None,
                ["alto"],
                ["infraestructura"],
                ["ES-CA"],
                ["!a1b2c3d4"],
                "secreto_64_caracteres_hex_ejemplo_12345678901234567890123456789012",
                2,
            )
        ]
    )
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    try:
        headers = {"X-Internal-Secret": config_test.internal_api_secret}
        resp = await client.get("/internal/destinos", headers=headers)
        assert resp.status == 200
        datos = await resp.json()
        assert datos["ok"] is True
        assert len(datos["destinos"]) == 1
        d = datos["destinos"][0]
        assert d["nombre"] == "cadiz-mesh"
        assert d["host"] == "alertas.cadiz.es"
        assert d["activo"] is True
        assert d["pendientes"] == 2
        assert d["provincias"] == ["ES-CA"]
    finally:
        await client.close()


async def test_crear_destino_validaciones(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba las validaciones al crear un destino (nombre, url, secreto)."""
    cursor = MockCursor()
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    headers = {"X-Internal-Secret": config_test.internal_api_secret}
    try:
        # Nombre inválido (mayúsculas)
        resp = await client.post(
            "/internal/destinos",
            headers=headers,
            json={"nombre": "Nombre_Invalido", "url": "https://example.org/hook", "secreto": "x" * 32},
        )
        assert resp.status == 400

        # URL no https
        resp = await client.post(
            "/internal/destinos",
            headers=headers,
            json={"nombre": "destino-valido", "url": "http://example.org/hook", "secreto": "x" * 32},
        )
        assert resp.status == 400

        # Secreto muy corto
        resp = await client.post(
            "/internal/destinos",
            headers=headers,
            json={"nombre": "destino-valido", "url": "https://example.org/hook", "secreto": "corto"},
        )
        assert resp.status == 400

        # Provincia no andaluza
        resp = await client.post(
            "/internal/destinos",
            headers=headers,
            json={
                "nombre": "destino-valido",
                "url": "https://example.org/hook",
                "secreto": "x" * 32,
                "provincias": ["ES-M"],
            },
        )
        assert resp.status == 400

        # Nodo con formato inválido
        resp = await client.post(
            "/internal/destinos",
            headers=headers,
            json={
                "nombre": "destino-valido",
                "url": "https://example.org/hook",
                "secreto": "x" * 32,
                "nodos": ["nodo_erroneo"],
            },
        )
        assert resp.status == 400
    finally:
        await client.close()


async def test_crear_destino_exitoso(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba la inserción de un nuevo destino válido y la invalidación de caché."""
    cursor = MockCursor(fetchone_result=None)
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    headers = {"X-Internal-Secret": config_test.internal_api_secret}
    try:
        payload = {
            "nombre": "nuevo-destino",
            "url": "https://hooks.example.org/alerts",
            "secreto": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef",
            "riesgos": ["alto", "medio"],
            "tipos": ["infraestructura"],
            "provincias": ["ES-CA", "ES-SE"],
            "nodos": ["!a1b2c3d4"],
        }
        resp = await client.post("/internal/destinos", headers=headers, json=payload)
        assert resp.status == 201
        datos = await resp.json()
        assert datos["ok"] is True
        assert datos["nombre"] == "nuevo-destino"
        motor_mock.recargar_destinos_db.assert_awaited_once()
    finally:
        await client.close()


async def test_crear_destino_duplicado_retorna_409(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba que intentar crear un destino existente devuelve 409 Conflict."""
    cursor = MockCursor(fetchone_result=(1,))  # Ya existe
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    headers = {"X-Internal-Secret": config_test.internal_api_secret}
    try:
        payload = {
            "nombre": "destino-duplicado",
            "url": "https://hooks.example.org/alerts",
            "secreto": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef",
        }
        resp = await client.post("/internal/destinos", headers=headers, json=payload)
        assert resp.status == 409
        datos = await resp.json()
        assert datos["ok"] is False
    finally:
        await client.close()


async def test_actualizar_destino_ok(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba la actualización de campos de un destino existente."""
    cursor = MockCursor(
        fetchone_result=(
            1,
            "https://vieja.example.org/hook",
            "vieja.example.org",
            "hash_viejo",
            "secreto_viejo_0123456789abcdef0123456789",
            None,
            None,
            None,
            None,
        )
    )
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    headers = {"X-Internal-Secret": config_test.internal_api_secret}
    try:
        payload = {
            "url": "https://nueva.example.org/hook",
            "riesgos": ["alto"],
        }
        resp = await client.put("/internal/destinos/mi-destino", headers=headers, json=payload)
        assert resp.status == 200
        datos = await resp.json()
        assert datos["ok"] is True
        motor_mock.recargar_destinos_db.assert_awaited_once()
    finally:
        await client.close()


async def test_eliminar_destino_marca_retirado(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba que eliminar un destino lo desactiva como retirado y purga entregas."""
    cursor = MockCursor(fetchone_result=(42,))
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    headers = {"X-Internal-Secret": config_test.internal_api_secret}
    try:
        resp = await client.delete("/internal/destinos/destino-a-borrar", headers=headers)
        assert resp.status == 200
        datos = await resp.json()
        assert datos["ok"] is True
        motor_mock.recargar_destinos_db.assert_awaited_once()
    finally:
        await client.close()


async def test_reactivar_destino(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba que reactivar un destino pone activo=true y despierta el motor."""
    cursor = MockCursor(fetchone_result=(10,))
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    headers = {"X-Internal-Secret": config_test.internal_api_secret}
    try:
        resp = await client.post("/internal/destinos/destino-caido/reactivar", headers=headers)
        assert resp.status == 200
        datos = await resp.json()
        assert datos["ok"] is True
        motor_mock.recargar_destinos_db.assert_awaited_once()
        motor_mock.despertar.assert_called_once()
    finally:
        await client.close()


async def test_probar_destino_simulado(config_test: ConfiguracionWebhooks, motor_mock: MagicMock) -> None:
    """Comprueba el envío de un ping de prueba simulando la respuesta HTTP externa."""
    cursor = MockCursor(
        fetchone_result=(
            "https://receptor.example.org/webhook",
            "receptor.example.org",
            "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef",
        )
    )
    client = await crear_cliente_test(config_test, cursor, motor_mock)
    headers = {"X-Internal-Secret": config_test.internal_api_secret}
    try:
        # Mock de resolución DNS segura
        with patch("webhooks.api_interna.resolver_y_validar_host", new=AsyncMock(return_value=["93.184.216.34"])):
            # Mock de respuesta HTTP de aiohttp
            mock_resp = MagicMock()
            mock_resp.status = 200
            mock_resp.reason = "OK"

            mock_session = MagicMock()
            mock_post_cm = MagicMock()
            mock_post_cm.__aenter__ = AsyncMock(return_value=mock_resp)
            mock_post_cm.__aexit__ = AsyncMock(return_value=None)
            mock_session.post.return_value = mock_post_cm
            mock_session.__aenter__ = AsyncMock(return_value=mock_session)
            mock_session.__aexit__ = AsyncMock(return_value=None)

            with patch("aiohttp.ClientSession", return_value=mock_session):
                resp = await client.post("/internal/destinos/destino-prueba/probar", headers=headers)
                assert resp.status == 200
                datos = await resp.json()
                assert datos["ok"] is True
                assert datos["status_code"] == 200
                assert datos["error"] is None
    finally:
        await client.close()
