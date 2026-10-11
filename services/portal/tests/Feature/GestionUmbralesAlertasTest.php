<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\GestionUmbralesAlertas;
use App\Jobs\ComprobarServicios;
use App\Models\User;
use App\Services\AlertThresholdService;
use Database\Seeders\AlertThresholdSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas de integración para la página Filament GestionUmbralesAlertas en el panel /admin.
 */
class GestionUmbralesAlertasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AlertThresholdSeeder::class);
    }

    /**
     * Un operador autenticado puede acceder a la página de calibración de umbrales.
     */
    public function test_operador_puede_acceder_a_pagina_umbrales(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/gestion-umbrales-alertas');
        $response->assertStatus(200);
        $response->assertSee(__('admin.alert_thresholds.title'));
    }

    /**
     * El componente Livewire monta los umbrales de base de datos correctamente.
     */
    public function test_livewire_monta_umbrales_correctamente(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->assertSet('activeTab', 'router')
            ->assertSet('umbrales.router.hop_limit_recommended', 3.0)
            ->assertSet('umbrales.client.battery_warning_pct', 35.0);
    }

    /**
     * Guardar umbrales actualiza los datos en base de datos.
     */
    public function test_guardar_umbrales_actualiza_db(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->set('umbrales.router.hop_limit_warning', 4.0)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('alert_thresholds', [
            'role_group' => 'router',
            'parameter' => 'hop_limit_warning',
            'value_numeric' => 4.0,
        ]);
    }

    /**
     * La validación cruzada rechaza umbrales incoherentes (warning mayor que critical).
     */
    public function test_validacion_cruzada_rechaza_umbrales_incoherentes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->set('umbrales.router.hop_limit_warning', 8.0) // Mayor que critical (7.0)
            ->call('save')
            ->assertHasErrors(['umbrales.router.hop_limit_warning']);
    }

    /**
     * La acción resetDefaults restablece los valores de fábrica.
     */
    public function test_restablecer_valores_de_fabrica(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        $service = app(AlertThresholdService::class);
        $matrix = $service->getAllThresholds();
        $matrix['router']['hop_limit_warning'] = 2.0;
        $service->saveThresholds($matrix);

        $this->assertDatabaseHas('alert_thresholds', [
            'role_group' => 'router',
            'parameter' => 'hop_limit_warning',
            'value_numeric' => 2.0,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->call('resetDefaults')
            ->assertHasNoErrors()
            ->assertSet('umbrales.router.hop_limit_warning', 5.0);

        $this->assertDatabaseHas('alert_thresholds', [
            'role_group' => 'router',
            'parameter' => 'hop_limit_warning',
            'value_numeric' => 5.0,
        ]);
    }

    /**
     * La validación cruzada rechaza valores numéricos negativos.
     */
    public function test_validacion_cruzada_rechaza_valores_negativos(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->set('umbrales.router.battery_warning_pct', -5.0)
            ->call('save')
            ->assertHasErrors(['umbrales.router.battery_warning_pct']);
    }

    /**
     * La validación cruzada rechaza saltos recomendados mayores que el umbral de aviso.
     */
    public function test_validacion_cruzada_rechaza_saltos_recomendados_mayores_que_aviso(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->set('umbrales.router.hop_limit_recommended', 6.0) // Aviso está en 5.0
            ->call('save')
            ->assertHasErrors(['umbrales.router.hop_limit_recommended']);
    }

    /**
     * La validación cruzada rechaza saltos LoRa fuera del rango [1, 7].
     */
    public function test_validacion_cruzada_rechaza_saltos_fuera_de_rango(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->set('umbrales.router.hop_limit_recommended', 0.0) // Menor que 1
            ->call('save')
            ->assertHasErrors(['umbrales.router.hop_limit_recommended']);
    }

    /**
     * La validación cruzada rechaza orden de silencios incoherente (warning > critical).
     */
    public function test_validacion_cruzada_rechaza_orden_silencios_incoherente(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->set('umbrales.router.silence_warning_h', 30.0) // Critical está en 24.0
            ->call('save')
            ->assertHasErrors(['umbrales.router.silence_warning_h']);
    }

    /**
     * Cuando detector-alertas no responde, la notificación de aviso utiliza la traducción saved_local.
     */
    public function test_guardar_umbrales_con_detector_caido_emite_notificacion_warning_traducida(): void
    {
        Http::fake([
            '*/internal/recargar-umbrales' => Http::response(null, 500),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'activo' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(GestionUmbralesAlertas::class)
            ->call('save')
            ->assertHasNoErrors()
            ->assertNotified(__('admin.alert_thresholds.saved_local'));
    }

    /**
     * ComprobarServicios sincroniza automáticamente los umbrales hacia detector-alertas si su versión es 0.
     */
    public function test_comprobar_servicios_auto_sincroniza_umbrales_cuando_detector_esta_en_version_cero(): void
    {
        Http::fake([
            '*/health' => Http::response([
                'ok' => true,
                'servicio' => 'detector-alertas',
                'umbrales' => [
                    'version' => 0,
                    'ultima_recarga' => '1970-01-01T00:00:00Z',
                ],
            ], 200),
            '*/internal/recargar-umbrales' => Http::response([
                'ok' => true,
                'status' => 'reloaded',
                'version' => 1791715000,
            ], 200),
            '*' => Http::response(['ok' => true], 200),
        ]);

        config(['servicios.lista' => ['detector-alertas' => config('servicios.lista.detector-alertas')]]);

        $job = new ComprobarServicios;
        $job->handle();

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), '/internal/recargar-umbrales')
                && isset($request['umbrales']['router']);
        });
    }
}
