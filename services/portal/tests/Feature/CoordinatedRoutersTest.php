<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CoordinatedRouter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del módulo de routers coordinados de infraestructura.
 */
class CoordinatedRoutersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comprueba la persistencia básica y métodos de ayuda del modelo CoordinatedRouter.
     */
    public function test_modelo_coordinated_router_persistencia_y_scopes(): void
    {
        $routerAndaluz = CoordinatedRouter::create([
            'node_id' => '!11223344',
            'short_name' => 'RTR1',
            'long_name' => 'Router Cerro del Viento',
            'role' => 'ROUTER',
            'province' => 'ES-CA',
            'approved' => true,
            'is_gateway' => false,
            'hw_model' => 'TLORA_V2_1_16',
            'notes' => 'Nodo coordinado en Cádiz.',
        ]);

        $routerNoAprobado = CoordinatedRouter::create([
            'node_id' => '!55667788',
            'short_name' => 'RTR2',
            'long_name' => 'Router Pendiente Sevilla',
            'role' => 'ROUTER',
            'province' => 'ES-SE',
            'approved' => false,
            'is_gateway' => true,
        ]);

        $this->assertTrue($routerAndaluz->isApproved());
        $this->assertTrue($routerAndaluz->isInAndalucia());
        $this->assertEquals('Cádiz', $routerAndaluz->province_name);

        $this->assertFalse($routerNoAprobado->isApproved());
        $this->assertTrue($routerNoAprobado->isInAndalucia());
        $this->assertEquals('Sevilla', $routerNoAprobado->province_name);

        $this->assertCount(1, CoordinatedRouter::approved()->get());
        $this->assertCount(1, CoordinatedRouter::pending()->get());
        $this->assertCount(1, CoordinatedRouter::inProvince('ES-CA')->get());
    }

    /**
     * Comprueba que un operador autenticado pueda acceder al listado de routers coordinados en Filament.
     */
    public function test_operador_puede_ver_listado_en_filament(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        CoordinatedRouter::create([
            'node_id' => '!aabbccdd',
            'short_name' => 'MAL1',
            'long_name' => 'Router Montes de Málaga',
            'role' => 'ROUTER',
            'province' => 'ES-MA',
            'approved' => true,
            'is_gateway' => false,
        ]);

        $response = $this->actingAs($operador)
            ->get('/admin/coordinated-routers');

        $response->assertStatus(200);
        $response->assertSee('Routers Coordinados');
        $response->assertSee('!aabbccdd');
        $response->assertSee('MAL1');
        $response->assertSee('Málaga');
    }
}
