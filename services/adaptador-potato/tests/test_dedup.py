"""Tests unitarios para el módulo de deduplicación de adaptador-potato."""

from __future__ import annotations

from src.dedup import Deduplicator


def test_dedup_basic() -> None:
    """Verifica detección de duplicados en la ventana temporal."""
    dedup = Deduplicator(window_seconds=900.0, capacity=1000)

    # Primer paquete
    assert not dedup.is_duplicate(from_node=0xA1B2C3D4, packet_id=1001, current_time=100.0)
    assert dedup.seen_count == 1
    assert dedup.duplicate_count == 0

    # Mismo paquete 2 segundos después
    assert dedup.is_duplicate(from_node=0xA1B2C3D4, packet_id=1001, current_time=102.0)
    assert dedup.duplicate_count == 1

    # Otro paquete distinto del mismo nodo
    assert not dedup.is_duplicate(from_node=0xA1B2C3D4, packet_id=1002, current_time=103.0)
    assert dedup.duplicate_count == 1


def test_dedup_expiration() -> None:
    """Verifica que tras pasar la ventana temporal el paquete vuelve a ser aceptado."""
    dedup = Deduplicator(window_seconds=60.0, capacity=1000)

    assert not dedup.is_duplicate(from_node=0x1111, packet_id=50, current_time=100.0)
    assert dedup.is_duplicate(from_node=0x1111, packet_id=50, current_time=150.0)

    # Tras 61 segundos desde la inserción original
    assert not dedup.is_duplicate(from_node=0x1111, packet_id=50, current_time=211.0)


def test_dedup_capacity_eviction() -> None:
    """Verifica que se expulse el elemento más antiguo al alcanzar la capacidad máxima."""
    dedup = Deduplicator(window_seconds=900.0, capacity=3)

    assert not dedup.is_duplicate(1, 1, current_time=10.0)
    assert not dedup.is_duplicate(2, 2, current_time=11.0)
    assert not dedup.is_duplicate(3, 3, current_time=12.0)
    assert len(dedup) == 3

    # El elemento 4 desborda la capacidad y expulsa (1, 1)
    assert not dedup.is_duplicate(4, 4, current_time=13.0)
    assert len(dedup) == 3

    # El elemento (1, 1) ya no está en caché y por tanto no es detectado como duplicado
    assert not dedup.is_duplicate(1, 1, current_time=14.0)


def test_dedup_cleanup() -> None:
    """Verifica la purga manual de elementos caducados."""
    dedup = Deduplicator(window_seconds=30.0, capacity=1000)

    dedup.is_duplicate(1, 10, current_time=100.0)
    dedup.is_duplicate(2, 20, current_time=110.0)
    dedup.is_duplicate(3, 30, current_time=140.0)

    # A tiempo 135: (1, 10) ha caducado (135 - 100 > 30), pero (2, 20) y (3, 30) siguen vivos
    purged = dedup.cleanup(current_time=135.0)
    assert purged == 1
    assert len(dedup) == 2
