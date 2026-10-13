<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Data\Ingest\Diagnostico;
use App\Models\AlertThreshold;
use App\Services\AlertThresholdService;
use Database\Seeders\AlertThresholdSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pruebas unitarias para el modelo AlertThreshold y el servicio AlertThresholdService.
 */
class AlertThresholdTest extends TestCase
{
    use RefreshDatabase;

    protected AlertThresholdService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AlertThresholdService::class);
    }

    /**
     * Verifica la resolución estricta de roles Meshtastic a los 3 grupos canónicos.
     */
    public function test_resolucion_grupos_de_roles(): void
    {
        // Grupo 1: router (infraestructura)
        $this->assertSame(AlertThreshold::GROUP_ROUTER, $this->service->resolveRoleGroup('ROUTER'));
        $this->assertSame(AlertThreshold::GROUP_ROUTER, $this->service->resolveRoleGroup('router'));
        $this->assertSame(AlertThreshold::GROUP_ROUTER, $this->service->resolveRoleGroup('ROUTER_LATE'));
        $this->assertSame(AlertThreshold::GROUP_ROUTER, $this->service->resolveRoleGroup('REPEATER'));

        // Grupo 2: client (clientes fijos y estándar)
        $this->assertSame(AlertThreshold::GROUP_CLIENT, $this->service->resolveRoleGroup('CLIENT'));
        $this->assertSame(AlertThreshold::GROUP_CLIENT, $this->service->resolveRoleGroup('client_hidden'));
        $this->assertSame(AlertThreshold::GROUP_CLIENT, $this->service->resolveRoleGroup('CLIENT_BASE'));

        // Grupo 3: client_mute (personales y resto de roles auxiliares)
        $this->assertSame(AlertThreshold::GROUP_CLIENT_MUTE, $this->service->resolveRoleGroup('CLIENT_MUTE'));
        $this->assertSame(AlertThreshold::GROUP_CLIENT_MUTE, $this->service->resolveRoleGroup('TRACKER'));
        $this->assertSame(AlertThreshold::GROUP_CLIENT_MUTE, $this->service->resolveRoleGroup('LOST_AND_FOUND'));
        $this->assertSame(AlertThreshold::GROUP_CLIENT_MUTE, $this->service->resolveRoleGroup('TAK'));
        $this->assertSame(AlertThreshold::GROUP_CLIENT_MUTE, $this->service->resolveRoleGroup(null));
        $this->assertSame(AlertThreshold::GROUP_CLIENT_MUTE, $this->service->resolveRoleGroup('DESCONOCIDO'));
    }

    /**
     * Verifica que el seeder inserta la totalidad de los 45 parámetros de la matriz calibrada.
     */
    public function test_seeder_matriz_calibrada_completa(): void
    {
        $this->seed(AlertThresholdSeeder::class);

        $this->assertDatabaseCount('alert_thresholds', 45);

        // Comprobar parámetros del grupo router
        $this->assertDatabaseHas('alert_thresholds', [
            'role_group' => 'router',
            'parameter' => 'hop_limit_warning',
            'value_numeric' => 5.0,
        ]);
        $this->assertDatabaseHas('alert_thresholds', [
            'role_group' => 'router',
            'parameter' => 'battery_warning_pct',
            'value_numeric' => 60.0,
        ]);

        // Comprobar parámetros del grupo client
        $this->assertDatabaseHas('alert_thresholds', [
            'role_group' => 'client',
            'parameter' => 'hop_limit_warning',
            'value_numeric' => 6.0,
        ]);
        $this->assertDatabaseHas('alert_thresholds', [
            'role_group' => 'client',
            'parameter' => 'battery_warning_pct',
            'value_numeric' => 35.0,
        ]);
    }

    /**
     * Verifica la lectura en memoria RAM mediante Cache y su invalidación al guardar cambios.
     */
    public function test_cache_ram_e_invalidacion_al_guardar(): void
    {
        $this->seed(AlertThresholdSeeder::class);

        // Primera llamada: puebla la caché
        Cache::forget(AlertThresholdService::CACHE_KEY);
        $this->assertFalse(Cache::has(AlertThresholdService::CACHE_KEY));

        $thresholds = $this->service->getAllThresholds();
        $this->assertTrue(Cache::has(AlertThresholdService::CACHE_KEY));
        $this->assertSame(5.0, $thresholds['router']['hop_limit_warning']);

        // Modificar umbral y guardar
        $nuevos = $thresholds;
        $nuevos['router']['hop_limit_warning'] = 4.0;

        $this->service->saveThresholds($nuevos);

        // La caché debe contener el nuevo valor
        $fresh = $this->service->getAllThresholds();
        $this->assertSame(4.0, $fresh['router']['hop_limit_warning']);
        $this->assertSame(4.0, $this->service->getForRole('ROUTER', 'hop_limit_warning'));
    }

    /**
     * Verifica que el diagnóstico de nodo evalúa de acuerdo con los umbrales dinámicos.
     */
    public function test_diagnostico_nodo_utiliza_umbrales_dinamicos(): void
    {
        $this->seed(AlertThresholdSeeder::class);

        // Crear tablas temporales de ingesta en SQLite
        Schema::connection('ingesta')->dropIfExists('api_nodes');
        Schema::connection('ingesta')->create('api_nodes', function ($table) {
            $table->string('id')->primary();
            $table->string('short_name')->nullable();
            $table->string('long_name')->nullable();
            $table->string('role')->nullable();
            $table->string('hw_model')->nullable();
            $table->string('firmware')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('last_seen')->nullable();
            $table->integer('hop_start_last')->nullable();
            $table->boolean('is_gateway')->default(false);
            $table->boolean('is_router')->default(false);
        });

        Schema::connection('ingesta')->dropIfExists('api_node_intervals');
        Schema::connection('ingesta')->create('api_node_intervals', function ($table) {
            $table->string('node_id');
            $table->string('portnum');
            $table->string('variant')->nullable();
            $table->double('median_interval_s')->nullable();
        });

        Schema::connection('ingesta')->dropIfExists('api_node_battery_daily');
        Schema::connection('ingesta')->create('api_node_battery_daily', function ($table) {
            $table->string('node_id');
            $table->date('day');
            $table->integer('min_level')->nullable();
        });

        Schema::connection('ingesta')->dropIfExists('api_node_reboots_daily');
        Schema::connection('ingesta')->create('api_node_reboots_daily', function ($table) {
            $table->string('node_id');
            $table->date('day');
            $table->integer('reboots')->default(0);
        });

        // Simulamos un nodo cliente en ingesta
        DB::connection('ingesta')->table('api_nodes')->insert([
            'id' => '!testnode1',
            'short_name' => 'NODE',
            'long_name' => 'Test Node',
            'role' => 'CLIENT',
            'hw_model' => 'T-Beam',
            'firmware' => '2.5.0',
            'province' => 'ES-CA',
            'last_seen' => now()->toIso8601String(),
            'hop_start_last' => 6,
            'is_gateway' => false,
            'is_router' => false,
        ]);

        $diagnostico = new Diagnostico($this->service);

        // Con los umbrales por defecto para CLIENT (hop_limit_warning = 6, hop_limit_critical = 7):
        // hop_start_last = 6 debe generar aviso (saltos_altos)
        $res = $diagnostico->obtenerDiagnostico('!testnode1');
        $this->assertNotNull($res->datos);
        $hallazgos = collect($res->datos['findings']);
        $this->assertTrue($hallazgos->contains('clave', 'saltos_altos'));

        // Modificamos el umbral de aviso para CLIENT a 7 (para que 6 ya no sea aviso)
        $matrix = $this->service->getAllThresholds();
        $matrix['client']['hop_limit_warning'] = 7.0;
        $this->service->saveThresholds($matrix);

        // Limpiar caché de Fuente::recordar
        Cache::flush();

        $res2 = $diagnostico->obtenerDiagnostico('!testnode1');
        $this->assertNotNull($res2->datos);
        $hallazgos2 = collect($res2->datos['findings']);
        $this->assertFalse($hallazgos2->contains('clave', 'saltos_altos'));
    }
}
