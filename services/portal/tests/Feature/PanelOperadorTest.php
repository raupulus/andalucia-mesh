<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ComprobarServicios;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PanelOperadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_acceso_admin_no_autenticado_redirige_a_login(): void
    {
        $response = $this->get('/admin');
        $response->assertStatus(302);
        $this->assertStringContainsString('login', (string) $response->headers->get('Location'));
    }

    public function test_login_admin_es_accesible(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_operador_puede_iniciar_sesion_con_credenciales_correctas(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'activo' => true,
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'password123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect(filament()->getUrl());

        $this->assertAuthenticatedAs($user);
    }

    public function test_comando_operador_crear_valida_longitud_password(): void
    {
        $this->artisan('operador:crear', [
            'email' => 'admin@desdechipiona.es',
            'nombre' => 'Operador Principal',
            '--password' => 'corta',
        ])
            ->expectsOutputToContain('al menos 8 caracteres')
            ->assertFailed();

        $this->assertDatabaseMissing('users', [
            'email' => 'admin@desdechipiona.es',
        ]);
    }

    public function test_comando_operador_crear_genera_usuario_activo(): void
    {
        $this->artisan('operador:crear', [
            'email' => 'operador@desdechipiona.es',
            'nombre' => 'Operador Valido',
            '--password' => 'PasswordSegura2026!#',
        ])
            ->expectsOutputToContain('guardado con éxito')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'operador@desdechipiona.es',
            'activo' => true,
        ]);
    }

    public function test_comprobar_servicios_job_registra_estados_y_latido(): void
    {
        // En tests evitamos sockets de red externos para velocidad
        config([
            'servicios.lista.mosquitto.host' => '127.0.0.1',
            'servicios.lista.postgresql.conexiones' => ['sqlite'],
        ]);

        // Simulamos respuestas HTTP para microservicios
        Http::fake([
            'http://ingesta:8080/health' => Http::response(['ok' => true, 'version' => '1.0.0'], 200),
            'http://adaptador-potato:8080/health' => Http::response(['ok' => false, 'motivo' => 'Fallo token'], 503),
            'http://sync-peers:8080/health' => Http::response(['ok' => true, 'peers' => []], 200),
            'http://potatomesh:41447/version' => Http::response(['version' => '2.0.0'], 200),
            'http://meshview:8081/health' => Http::response(['ok' => true], 200),
            '*' => Http::response(['ok' => true], 200),
        ]);

        $job = new ComprobarServicios;
        $job->handle();

        // 1. Debe existir latido de la tarea
        $latido = DB::table('tareas_latido')->where('tarea', 'comprobar-servicios')->first();
        $this->assertNotNull($latido);

        // 2. Debe haber filas en estado_servicio
        $ingesta = DB::table('estado_servicio')->where('servicio', 'ingesta')->first();
        $this->assertNotNull($ingesta);
        $this->assertTrue((bool) $ingesta->ok);
        $this->assertEquals(0, $ingesta->fallos_seguidos);

        $adaptador = DB::table('estado_servicio')->where('servicio', 'adaptador-potato')->first();
        $this->assertNotNull($adaptador);
        $this->assertFalse((bool) $adaptador->ok);
        $this->assertEquals(1, $adaptador->fallos_seguidos);
        $this->assertEquals('Fallo token', $adaptador->motivo);

        // 3. PostgreSQL con conexión sqlite de prueba debe estar OK
        $db = DB::table('estado_servicio')->where('servicio', 'postgresql')->first();
        $this->assertNotNull($db);
        $this->assertTrue((bool) $db->ok);

        // 4. Debe haberse registrado la transición en estado_servicio_cambio
        $cambios = DB::table('estado_servicio_cambio')->where('servicio', 'adaptador-potato')->get();
        $this->assertNotEmpty($cambios);
    }

    public function test_operador_activo_puede_ver_dashboard_admin(): void
    {
        $user = User::factory()->create([
            'activo' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Estado de la Red y Microservicios');
    }
}
