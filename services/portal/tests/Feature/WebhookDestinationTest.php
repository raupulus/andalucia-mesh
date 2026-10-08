<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Webhooks\Pages\CreateWebhookDestination;
use App\Filament\Resources\Webhooks\Pages\ListWebhookDestinations;
use App\Models\User;
use App\Models\WebhookDestination;
use App\Servicios\WebhooksClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas de integración para la gestión de destinos de webhooks desde el panel Filament.
 */
class WebhookDestinationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'servicios.webhooks_api.url' => 'http://webhooks:8080',
            'servicios.webhooks_api.secret' => 'secreto_interno_para_pruebas_1234567890',
        ]);
    }

    /**
     * Comprueba que WebhooksClient realiza las peticiones HTTP con las cabeceras requeridas.
     */
    public function test_cliente_webhooks_realiza_llamadas_autenticadas(): void
    {
        Http::fake([
            'http://webhooks:8080/internal/destinos' => Http::response([
                'ok' => true,
                'destinos' => [
                    [
                        'nombre' => 'cadiz-alertas',
                        'url' => 'https://alertas.cadiz.es/hook',
                        'host' => 'alertas.cadiz.es',
                        'activo' => true,
                        'motivo_baja' => null,
                        'fallos_seguidos' => 0,
                        'ultimo_ok' => '2026-10-08T10:00:00Z',
                        'pendientes' => 1,
                        'riesgos' => ['alto'],
                        'tipos' => ['infraestructura'],
                        'provincias' => ['ES-CA'],
                        'nodos' => ['!a1b2c3d4'],
                        'secreto' => 'secreto_ejemplo_de_mas_de_32_caracteres_hex',
                    ],
                ],
            ], 200),
            'http://webhooks:8080/internal/destinos/cadiz-alertas/probar' => Http::response([
                'ok' => true,
                'status_code' => 200,
                'duration_ms' => 35,
                'error' => null,
            ], 200),
            'http://webhooks:8080/internal/destinos/cadiz-alertas/reactivar' => Http::response([
                'ok' => true,
                'mensaje' => 'Destino reactivado con éxito',
            ], 200),
            'http://webhooks:8080/internal/destinos/cadiz-alertas' => Http::response([
                'ok' => true,
            ], 200),
        ]);

        $client = new WebhooksClient;

        // 1. Listar
        $destinos = $client->getDestinations();
        $this->assertCount(1, $destinos);
        $this->assertSame('cadiz-alertas', $destinos[0]['nombre']);

        // 2. Probar Ping
        $resPing = $client->testDestination('cadiz-alertas');
        $this->assertTrue($resPing['ok']);
        $this->assertSame(200, $resPing['status_code']);
        $this->assertSame(35, $resPing['duration_ms']);

        // 3. Reactivar
        $resReactivar = $client->reactivateDestination('cadiz-alertas');
        $this->assertTrue($resReactivar['ok']);

        // 4. Actualizar
        $resUpdate = $client->updateDestination('cadiz-alertas', ['url' => 'https://alertas.cadiz.es/nuevo-hook']);
        $this->assertTrue($resUpdate['ok']);

        // 5. Eliminar
        $resDelete = $client->deleteDestination('cadiz-alertas');
        $this->assertTrue($resDelete['ok']);

        // Verificar cabecera X-Internal-Secret en todas las peticiones salientes
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Internal-Secret', 'secreto_interno_para_pruebas_1234567890');
        });
    }

    /**
     * Comprueba la sincronización de destinos remotos en la tabla local de Filament.
     */
    public function test_cliente_sincroniza_destinos_locales(): void
    {
        Http::fake([
            'http://webhooks:8080/internal/destinos' => Http::response([
                'ok' => true,
                'destinos' => [
                    [
                        'nombre' => 'sevilla-mesh',
                        'url' => 'https://hooks.sevilla.es/in',
                        'host' => 'hooks.sevilla.es',
                        'activo' => true,
                        'motivo_baja' => null,
                        'fallos_seguidos' => 0,
                        'ultimo_ok' => '2026-10-08T11:00:00Z',
                        'pendientes' => 3,
                        'riesgos' => ['medio', 'alto'],
                        'tipos' => ['clientes'],
                        'provincias' => ['ES-SE'],
                        'nodos' => [],
                        'secreto' => 'secreto_ejemplo_de_mas_de_32_caracteres_hex',
                    ],
                ],
            ], 200),
        ]);

        $client = new WebhooksClient;
        $destinos = $client->syncLocalDestinations();

        $this->assertCount(1, $destinos);
        $this->assertDatabaseHas('webhook_destinations', [
            'nombre' => 'sevilla-mesh',
            'host' => 'hooks.sevilla.es',
            'activo' => true,
            'pendientes' => 3,
        ]);
    }

    /**
     * Comprueba que un operador autenticado pueda visualizar la pantalla de webhooks en Filament.
     */
    public function test_operador_puede_ver_listado_webhooks_en_filament(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        Http::fake([
            'http://webhooks:8080/internal/destinos' => Http::response([
                'ok' => true,
                'destinos' => [
                    [
                        'nombre' => 'huelva-alertas',
                        'url' => 'https://huelva.example.org/hook',
                        'host' => 'huelva.example.org',
                        'activo' => true,
                        'motivo_baja' => null,
                        'fallos_seguidos' => 0,
                        'ultimo_ok' => null,
                        'pendientes' => 0,
                        'riesgos' => ['alto'],
                        'tipos' => ['infraestructura'],
                        'provincias' => ['ES-H'],
                        'nodos' => ['!12345678'],
                        'secreto' => 'secreto_ejemplo_de_mas_de_32_caracteres_hex',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($operador)
            ->get('/admin/webhook-destinations');

        $response->assertStatus(200);
        $response->assertSee('huelva-alertas');
        $response->assertSee('huelva.example.org');
        $response->assertSee('Activo');
    }

    /**
     * Comprueba la creación de un nuevo destino a través del formulario Filament.
     */
    public function test_crear_destino_desde_filament_invoca_api_y_guarda_localmente(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        Http::fake([
            'http://webhooks:8080/internal/destinos' => Http::response([
                'ok' => true,
                'nombre' => 'almeria-mesh',
            ], 201),
        ]);

        $this->actingAs($operador);

        Livewire::test(CreateWebhookDestination::class)
            ->fillForm([
                'nombre' => 'almeria-mesh',
                'url' => 'https://almeria.example.org/webhook',
                'secreto' => 'secreto_generado_de_64_caracteres_hex_1234567890123456789012345678',
                'riesgos' => ['alto'],
                'tipos' => ['infraestructura'],
                'provincias' => ['ES-AL'],
                'nodos' => ['!cafe0001'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('webhook_destinations', [
            'nombre' => 'almeria-mesh',
            'host' => 'almeria.example.org',
            'activo' => true,
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://webhooks:8080/internal/destinos' &&
                   $request->method() === 'POST' &&
                   $request['nombre'] === 'almeria-mesh';
        });
    }

    /**
     * Comprueba la acción de ping y notificación en la tabla de Filament.
     */
    public function test_accion_probar_ping_en_tabla_filament(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $destino = WebhookDestination::create([
            'nombre' => 'granada-hook',
            'url' => 'https://granada.example.org/in',
            'host' => 'granada.example.org',
            'activo' => true,
            'secreto' => 'secreto_64_caracteres_ejemplo_123456789012345678901234567890123456',
        ]);

        Http::fake([
            'http://webhooks:8080/internal/destinos' => Http::response(['ok' => true, 'destinos' => []], 200),
            'http://webhooks:8080/internal/destinos/granada-hook/probar' => Http::response([
                'ok' => true,
                'status_code' => 200,
                'duration_ms' => 24,
                'error' => null,
            ], 200),
        ]);

        $this->actingAs($operador);

        Livewire::test(ListWebhookDestinations::class)
            ->callTableAction('probar', $destino)
            ->assertHasNoTableActionErrors();

        Http::assertSent(function ($request) {
            return $request->url() === 'http://webhooks:8080/internal/destinos/granada-hook/probar';
        });
    }

    /**
     * Comprueba que si el ping falla por error de conexión o host no resuelto,
     * se captura la excepción y se emite notificación de peligro sin error 500.
     */
    public function test_accion_probar_ping_captura_excepcion_conexion(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $destino = WebhookDestination::create([
            'nombre' => 'malaga-hook',
            'url' => 'https://malaga.example.org/in',
            'host' => 'malaga.example.org',
            'activo' => true,
            'secreto' => 'secreto_64_caracteres_ejemplo_123456789012345678901234567890123456',
        ]);

        Http::fake([
            'http://webhooks:8080/internal/destinos' => Http::response(['ok' => true, 'destinos' => []], 200),
            'http://webhooks:8080/internal/destinos/malaga-hook/probar' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('cURL error 6: Could not resolve host: webhooks');
            },
        ]);

        $this->actingAs($operador);

        Livewire::test(ListWebhookDestinations::class)
            ->callTableAction('probar', $destino)
            ->assertHasNoTableActionErrors()
            ->assertNotified('Fallo al probar destino (Ping)');
    }

    /**
     * Comprueba la acción de reactivación de un destino suspendido.
     */
    public function test_accion_reactivar_en_tabla_filament(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $destino = WebhookDestination::create([
            'nombre' => 'jaen-hook',
            'url' => 'https://jaen.example.org/in',
            'host' => 'jaen.example.org',
            'activo' => false,
            'motivo_baja' => 'fallos',
            'fallos_seguidos' => 20,
            'secreto' => 'secreto_64_caracteres_ejemplo_123456789012345678901234567890123456',
        ]);

        Http::fake([
            'http://webhooks:8080/internal/destinos' => Http::response(['ok' => true, 'destinos' => []], 200),
            'http://webhooks:8080/internal/destinos/jaen-hook/reactivar' => Http::response([
                'ok' => true,
                'mensaje' => 'Destino reactivado con éxito',
            ], 200),
        ]);

        $this->actingAs($operador);

        Livewire::test(ListWebhookDestinations::class)
            ->callTableAction('reactivar', $destino)
            ->assertHasNoTableActionErrors();

        $destino->refresh();
        $this->assertTrue($destino->activo);
        $this->assertSame(0, $destino->fallos_seguidos);
        $this->assertNull($destino->motivo_baja);
    }

    /**
     * Comprueba que el formulario de webhook muestre la guía explicativa y descripciones de filtros.
     */
    public function test_formulario_crear_muestra_guia_y_descripciones_de_filtros(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $this->actingAs($operador);

        Livewire::test(CreateWebhookDestination::class)
            ->assertSee(__('admin.webhooks.guide_engine_badge'))
            ->assertSee(__('admin.webhooks.guide_risks_title'))
            ->assertSee(__('admin.webhooks.guide_types_title'))
            ->assertSee(__('admin.webhooks.guide_wildcard_notice'));
    }
}
