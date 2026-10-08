<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Jobs\ComprobarServicios;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
        $response->assertSee('Presión del Aire (ChUtil)');
        $response->assertSee('Saturación TX Repetidores');
        $response->assertSee('Repetidores y Nodos de Infraestructura');
        $response->assertSee('Estado de la Red y Microservicios');
        $response->assertSee('Panel de Operador');
        $response->assertSee('Ingesta de paquetes');
    }

    public function test_operador_activo_ve_dashboard_y_widgets_traducidos_a_ingles(): void
    {
        $user = User::factory()->create([
            'activo' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin?lang=en');
        $response->assertStatus(200);
        $response->assertSee('Operations Control Center');
        $response->assertSee('Air Pressure (ChUtil)');
        $response->assertSee('Repeater TX Saturation');
        $response->assertSee('Repeaters &amp; Infrastructure Nodes', false);
        $response->assertSee('Network and Microservices Status');
        $response->assertSee('Operator Panel');
        $response->assertSee('Packet Ingestion');
    }

    public function test_operador_activo_ve_dashboard_y_widgets_traducidos_a_portugues(): void
    {
        $user = User::factory()->create([
            'activo' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin?lang=pt');
        $response->assertStatus(200);
        $response->assertSee('Centro de Controlo Operativo');
        $response->assertSee('Pressão do Ar (ChUtil)');
        $response->assertSee('Saturação TX Repetidores');
        $response->assertSee('Repetidores e Nós de Infraestrutura');
        $response->assertSee('Estado da Rede e Microsserviços');
        $response->assertSee('Painel de Operador');
        $response->assertSee('Ingestão de pacotes');
    }

    public function test_operador_activo_puede_ver_su_perfil(): void
    {
        $user = User::factory()->create([
            'activo' => true,
            'name' => 'Operador Test',
        ]);

        $response = $this->actingAs($user)->get('/admin/profile');
        $response->assertStatus(200);
        $response->assertSee('Mi perfil de operador');
        $response->assertSee('Identidad del Operador');
        $response->assertSee('Seguridad y Contraseña');
        $response->assertSee('Eliminar cuenta');
        $response->assertSee('fi-width-4xl', false);
        $response->assertSee('fi-align-center', false);
        $response->assertSee('fi-operator-avatar-wrapper', false);
        $response->assertSee('fi-operator-avatar-upload', false);
    }

    public function test_operador_puede_modificar_su_nombre_desde_perfil(): void
    {
        $user = User::factory()->create([
            'activo' => true,
            'name' => 'Nombre Antiguo',
        ]);

        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => 'Nombre Renovado',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nombre Renovado',
        ]);
    }

    public function test_operador_puede_actualizar_su_password_con_confirmacion(): void
    {
        $user = User::factory()->create([
            'activo' => true,
            'password' => bcrypt('ClaveAntigua2026!'),
        ]);

        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'currentPassword' => 'ClaveAntigua2026!',
                'password' => 'NuevaClaveSegura2026!',
                'passwordConfirmation' => 'NuevaClaveSegura2026!',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('NuevaClaveSegura2026!', $user->password));
    }

    public function test_operador_puede_eliminar_su_cuenta_con_confirmacion(): void
    {
        $user = User::factory()->create([
            'activo' => true,
            'password' => bcrypt('PasswordBorrado123!'),
        ]);

        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->callAction('deleteAccount', data: [
                'delete_confirm_password' => 'PasswordBorrado123!',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_operador_puede_actualizar_avatar_desde_perfil(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'activo' => true,
            'avatar_url' => null,
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.png', 200, 200);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'avatar_url' => $file,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar_url);
        Storage::disk('public')->assertExists($user->avatar_url);
    }
}
