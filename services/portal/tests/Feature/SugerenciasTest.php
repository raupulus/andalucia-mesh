<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Pruebas de integración para el buzón de sugerencias público y su gestión en Filament.
 */
class SugerenciasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comprueba que la página de sugerencias devuelva 200 OK y cumpla la directiva de cero cookies.
     */
    public function test_pagina_sugerencias_es_accesible_y_devuelve_200_con_cero_cookies(): void
    {
        $response = $this->get('/sugerencias');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Buzón de sugerencias');
        $response->assertSee('Bot Telegram');
        $response->assertSee('Potato Mesh');
        $response->assertSee('Nueva Funcionalidad');
        $response->assertSee('campo-content', false);
    }

    /**
     * Comprueba que una sugerencia válida se registre en la base de datos cuando Turnstile está desactivado.
     */
    public function test_envio_sugerencia_valida_con_turnstile_desactivado(): void
    {
        config(['services.turnstile.secret_key' => null]);

        $payload = [
            'category' => 'bot_telegram',
            'content' => 'Sería genial añadir un comando /cobertura para ver los repetidores activos.',
        ];

        $response = $this->post('/sugerencias', $payload);

        $response->assertStatus(200);
        $response->assertSee('¡Muchas gracias por tu aportación!');
        $response->assertHeaderMissing('Set-Cookie');

        $this->assertDatabaseHas('suggestions', [
            'category' => 'bot_telegram',
            'content' => 'Sería genial añadir un comando /cobertura para ver los repetidores activos.',
            'status' => 'pending',
            'operator_notes' => null,
        ]);
    }

    /**
     * Comprueba que una petición AJAX/JSON devuelva respuesta JSON correcta.
     */
    public function test_envio_sugerencia_por_ajax_devuelve_json(): void
    {
        config(['services.turnstile.secret_key' => null]);

        $response = $this->postJson('/sugerencias', [
            'category' => 'web',
            'content' => 'Añadir un modo de alto contraste para exteriores en el mapa.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
        ]);

        $this->assertDatabaseCount('suggestions', 1);
    }

    /**
     * Valida que se rechacen categorías desconocidas o sugerencias con menos de 10 caracteres.
     */
    public function test_validacion_rechaza_campos_invalidos(): void
    {
        // 1. Categoría inválida
        $respCat = $this->postJson('/sugerencias', [
            'category' => 'categoria_inventada',
            'content' => 'Texto suficientemente largo para pasar la prueba.',
        ]);
        $respCat->assertStatus(422);
        $respCat->assertJsonFragment(['ok' => false]);

        // 2. Contenido muy corto
        $respShort = $this->postJson('/sugerencias', [
            'category' => 'web',
            'content' => 'Hola',
        ]);
        $respShort->assertStatus(422);

        $this->assertDatabaseCount('suggestions', 0);
    }

    /**
     * Comprueba que Cloudflare Turnstile rechaza tokens inválidos cuando está activo.
     */
    public function test_verificacion_turnstile_activa_rechaza_token_invalido(): void
    {
        config([
            'services.turnstile.site_key' => '0x4AAAAAAAsitekey',
            'services.turnstile.secret_key' => '0x4AAAAAAAsecretkey',
        ]);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $respFail = $this->postJson('/sugerencias', [
            'category' => 'meshview',
            'content' => 'Mejorar el filtro de paquetes por provincia en MeshView.',
            'cf-turnstile-response' => 'token-invalido',
        ]);

        $respFail->assertStatus(422);
        $this->assertDatabaseCount('suggestions', 0);
    }

    /**
     * Comprueba que Cloudflare Turnstile aprueba tokens válidos cuando está activo.
     */
    public function test_verificacion_turnstile_activa_acepta_token_valido(): void
    {
        config([
            'services.turnstile.site_key' => '0x4AAAAAAAsitekey',
            'services.turnstile.secret_key' => '0x4AAAAAAAsecretkey',
        ]);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $respSuccess = $this->postJson('/sugerencias', [
            'category' => 'meshview',
            'content' => 'Mejorar el filtro de paquetes por provincia en MeshView.',
            'cf-turnstile-response' => 'token-valido',
        ]);

        $respSuccess->assertStatus(200);
        $respSuccess->assertJson(['ok' => true]);
        $this->assertDatabaseCount('suggestions', 1);
    }

    /**
     * Comprueba que el control de frecuencia límite abusos por IP.
     */
    public function test_rate_limiting_mitiga_envios_masivos(): void
    {
        config(['services.turnstile.secret_key' => null]);

        for ($i = 0; $i < 10; $i++) {
            $resp = $this->postJson('/sugerencias', [
                'category' => 'otros',
                'content' => "Propuesta de prueba número {$i} con texto descriptivo.",
            ]);
            $resp->assertStatus(200);
        }

        // El intento número 11 debe ser bloqueado con 429
        $respExcess = $this->postJson('/sugerencias', [
            'category' => 'otros',
            'content' => 'Propuesta excedente que debe ser bloqueada.',
        ]);

        $respExcess->assertStatus(429);
        $this->assertDatabaseCount('suggestions', 10);
    }

    /**
     * Comprueba que los operadores puedan consultar y gestionar sugerencias en Filament.
     */
    public function test_operador_puede_ver_y_gestionar_sugerencias(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $sugerencia = Suggestion::create([
            'category' => 'potatomesh',
            'content' => 'Sería muy útil permitir guardar waypoints offline en PotatoMesh.',
            'status' => Suggestion::STATUS_PENDING,
            'operator_notes' => null,
            'ip_hash' => 'dummy_hash',
        ]);

        // Simular operador autenticado en el panel admin
        $response = $this->actingAs($operador)
            ->get('/admin/suggestions');

        $response->assertStatus(200);
        $response->assertSee('Potato Mesh');
        $response->assertSee('waypoints offline');

        // Modificar estado y notas internas
        $sugerencia->update([
            'status' => Suggestion::STATUS_APPROVED,
            'operator_notes' => 'Aprobado para la próxima versión de PotatoMesh.',
        ]);

        $this->assertEquals(Suggestion::STATUS_APPROVED, $sugerencia->fresh()->status);
        $this->assertEquals('Aprobado para la próxima versión de PotatoMesh.', $sugerencia->fresh()->operator_notes);
    }

    /**
     * Comprueba que claves con marcadores dummy/placeholder desactiven Turnstile de forma segura.
     */
    public function test_turnstile_ignora_claves_placeholder_y_opera_degradado(): void
    {
        config([
            'services.turnstile.site_key' => '0x4AAAAAAAxxxxxxxxxxxxxx',
            'services.turnstile.secret_key' => '0x4AAAAAAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        ]);

        $service = app(\App\Servicios\TurnstileService::class);
        $this->assertFalse($service->isEnabled());
        $this->assertNull($service->getSiteKey());
        $this->assertTrue($service->verify(null));
    }

    /**
     * Comprueba que la verificación no envíe remoteip cuando la IP sea privada o loopback.
     */
    public function test_turnstile_no_envia_remoteip_en_ips_privadas(): void
    {
        config([
            'services.turnstile.site_key' => '0x4AAAAAAA_real_site_key',
            'services.turnstile.secret_key' => '0x4AAAAAAA_real_secret_key',
        ]);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => function (\Illuminate\Http\Client\Request $request) {
                // Comprobar que en el cuerpo del form no se incluye remoteip para 127.0.0.1
                $body = $request->data();
                if (isset($body['remoteip'])) {
                    return Http::response(['success' => false], 400);
                }

                return Http::response(['success' => true], 200);
            },
        ]);

        $service = app(\App\Servicios\TurnstileService::class);
        $this->assertTrue($service->verify('token-valido', '127.0.0.1'));
        $this->assertTrue($service->verify('token-valido', '172.18.0.2'));
    }
}
