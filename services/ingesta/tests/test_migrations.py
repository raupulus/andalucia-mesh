"""Pruebas unitarias para el gestor y parser de migraciones SQL (migrations.py)."""

from src.migrations import (
    split_sql_statements,
    compute_sha256,
    MigrationRunner,
)


def test_split_sql_statements_simple() -> None:
    """Verifica la separación de sentencias SQL elementales."""
    sql = "SELECT 1; SELECT 2; SELECT 3;"
    stmts = split_sql_statements(sql)
    assert len(stmts) == 3
    assert stmts == ["SELECT 1", "SELECT 2", "SELECT 3"]


def test_split_sql_statements_with_quotes() -> None:
    """Verifica que los puntos y coma dentro de comillas simples no dividan la sentencia."""
    sql = "INSERT INTO test (val) VALUES ('texto; con punto y coma'); SELECT 'otro;' AS col;"
    stmts = split_sql_statements(sql)
    assert len(stmts) == 2
    assert stmts[0] == "INSERT INTO test (val) VALUES ('texto; con punto y coma')"
    assert stmts[1] == "SELECT 'otro;' AS col"


def test_split_sql_statements_with_dollar_quoting() -> None:
    """Verifica que los bloques de funciones PL/pgSQL ($$ o $tag$) se preserven intactos."""
    sql = """
    CREATE OR REPLACE PROCEDURE test_proc()
    LANGUAGE plpgsql
    AS $$
    BEGIN
        SELECT 1;
        DELETE FROM t WHERE id = 2;
    END;
    $$;

    CREATE TABLE t2 (id int);
    """
    stmts = split_sql_statements(sql)
    assert len(stmts) == 2
    assert "CREATE OR REPLACE PROCEDURE test_proc()" in stmts[0]
    assert "SELECT 1;" in stmts[0]
    assert "DELETE FROM t WHERE id = 2;" in stmts[0]
    assert stmts[1] == "CREATE TABLE t2 (id int)"


def test_compute_sha256() -> None:
    """Comprueba el cálculo de suma de verificación SHA-256."""
    hash1 = compute_sha256("test-content")
    hash2 = compute_sha256("test-content")
    hash3 = compute_sha256("different-content")

    assert hash1 == hash2
    assert hash1 != hash3
    assert len(hash1) == 64


def test_template_rendering() -> None:
    """Verifica la sustitución de variables de entorno en plantillas SQL."""
    raw_sql = "SELECT '{{TZ}}' AS tz WHERE role IN ({{INFRA_ROLES}});"
    runner = MigrationRunner(
        db_pool=None,  # type: ignore
        migrations_dir="/tmp",
        tz="Europe/Madrid",
        infra_roles=["ROUTER", "ROUTER_LATE", "REPEATER"],
    )

    rendered = runner._render_template(raw_sql)
    assert "Europe/Madrid" in rendered
    assert "'ROUTER', 'ROUTER_LATE', 'REPEATER'" in rendered
    assert "{{TZ}}" not in rendered
    assert "{{INFRA_ROLES}}" not in rendered
