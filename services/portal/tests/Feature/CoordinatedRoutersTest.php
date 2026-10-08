<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CoordinatedRouters\Pages\ListCoordinatedRouters;
use App\Models\CoordinatedRouter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas del módulo de routers coordinados de infraestructura y gobernanza de malla.
 */
class CoordinatedRoutersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comprueba la persistencia básica, estados de gobernanza (managed, known, new) y scopes del modelo.
     */
    public function test_modelo_coordinated_router_estados_y_scopes(): void
    {
        $routerGestionado = CoordinatedRouter::create([
            'node_id' => '!11223344',
            'short_name' => 'RTR1',
            'long_name' => 'Router Cerro del Viento',
            'role' => 'ROUTER',
            'province' => 'ES-CA',
            'status' => CoordinatedRouter::STATUS_MANAGED,
            'is_gateway' => false,
            'hw_model' => 'TLORA_V2_1_16',
            'notes' => 'Nodo coordinado en Cádiz.',
        ]);

        $routerConocido = CoordinatedRouter::create([
            'node_id' => '!22334455',
            'short_name' => 'RTR2',
            'long_name' => 'Router Cerro de la Plata',
            'role' => 'ROUTER',
            'province' => 'ES-MA',
            'status' => CoordinatedRouter::STATUS_KNOWN,
            'is_gateway' => false,
        ]);

        $routerNuevo = CoordinatedRouter::create([
            'node_id' => '!55667788',
            'short_name' => 'RTR3',
            'long_name' => 'Router Pendiente Sevilla',
            'role' => 'ROUTER',
            'province' => 'ES-SE',
            'status' => CoordinatedRouter::STATUS_NEW,
            'is_gateway' => true,
        ]);

        // Verificación de métodos de ayuda del modelo
        $this->assertTrue($routerGestionado->isManaged());
        $this->assertTrue($routerGestionado->isApproved());
        $this->assertTrue($routerGestionado->approved);
        $this->assertEquals('Cádiz', $routerGestionado->province_name);
        $this->assertEquals('Gestionado', $routerGestionado->status_label);

        $this->assertTrue($routerConocido->isKnown());
        $this->assertFalse($routerConocido->isManaged());
        $this->assertFalse($routerConocido->approved);
        $this->assertEquals('Málaga', $routerConocido->province_name);
        $this->assertEquals('Conocido', $routerConocido->status_label);

        $this->assertTrue($routerNuevo->isNew());
        $this->assertFalse($routerNuevo->isManaged());
        $this->assertFalse($routerNuevo->approved);
        $this->assertEquals('Sevilla', $routerNuevo->province_name);
        $this->assertEquals('Nuevo', $routerNuevo->status_label);

        // Verificación de scopes
        $this->assertCount(1, CoordinatedRouter::managed()->get());
        $this->assertCount(1, CoordinatedRouter::known()->get());
        $this->assertCount(1, CoordinatedRouter::new()->get());
        $this->assertCount(1, CoordinatedRouter::approved()->get());
        $this->assertCount(2, CoordinatedRouter::pending()->get());
        $this->assertCount(1, CoordinatedRouter::inProvince('ES-CA')->get());
    }

    /**
     * Comprueba que la acción 'Sincronizar de la Malla' importa los routers andaluces como nuevos y descarta nodos exteriores.
     */
    public function test_sincronizacion_desde_la_malla_importa_routers_andaluces_y_descarta_fuera(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $this->actingAs($operador);

        // Inicializar tabla de malla y ejecutar acción de cabecera en Filament
        ListCoordinatedRouters::ensureApiRoutersTableExists();

        Livewire::test(ListCoordinatedRouters::class)
            ->callAction('sync_from_mesh')
            ->assertHasNoActionErrors();

        // Debe importar exactamente los 8 routers andaluces de ejemplo (descartando el nodo FUERA)
        $this->assertCount(8, CoordinatedRouter::all());

        // Comprobar que ningún nodo con provincia FUERA ha sido importado
        $this->assertDatabaseMissing('coordinated_routers', [
            'province' => 'FUERA',
        ]);

        // Comprobar que los nodos importados ingresan con estado 'new' (Nuevo)
        $this->assertCount(8, CoordinatedRouter::new()->get());
        $this->assertDatabaseHas('coordinated_routers', [
            'node_id' => '!2a3b4c5d',
            'short_name' => 'SE01',
            'province' => 'ES-SE',
            'status' => CoordinatedRouter::STATUS_NEW,
        ]);
        $this->assertDatabaseHas('coordinated_routers', [
            'node_id' => '!3b4c5d6e',
            'short_name' => 'CA01',
            'province' => 'ES-CA',
            'status' => CoordinatedRouter::STATUS_NEW,
        ]);
    }

    /**
     * Comprueba que sincronizar repetidamente actualiza datos pero PRESERVA el estado gestionado/conocido y las notas.
     */
    public function test_sincronizacion_preserva_estado_y_notas_de_routers_existentes(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $this->actingAs($operador);

        // Creamos de antemano un router marcado como 'managed' y con notas privadas
        CoordinatedRouter::create([
            'node_id' => '!2a3b4c5d',
            'short_name' => 'VIEJO_SE',
            'long_name' => 'Antiguo nombre',
            'province' => 'ES-SE',
            'role' => 'ROUTER',
            'status' => CoordinatedRouter::STATUS_MANAGED,
            'notes' => 'Custodiado en cerro Aljarafe por grupo EA7.',
        ]);

        ListCoordinatedRouters::ensureApiRoutersTableExists();

        Livewire::test(ListCoordinatedRouters::class)
            ->callAction('sync_from_mesh')
            ->assertHasNoActionErrors();

        $router = CoordinatedRouter::where('node_id', '!2a3b4c5d')->first();
        $this->assertNotNull($router);

        // Debe haber actualizado el nombre desde la malla
        $this->assertEquals('SE01', $router->short_name);
        $this->assertEquals('Router Sevilla Aljarafe', $router->long_name);

        // Pero DEBE mantener su estado 'managed' y sus notas intactas
        $this->assertEquals(CoordinatedRouter::STATUS_MANAGED, $router->status);
        $this->assertTrue($router->isManaged());
        $this->assertEquals('Custodiado en cerro Aljarafe por grupo EA7.', $router->notes);
    }

    /**
     * Comprueba que un operador puede ver el listado y cambiar el estado mediante las acciones rápidas de fila.
     */
    public function test_operador_puede_cambiar_estado_mediante_acciones_en_tabla(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $router = CoordinatedRouter::create([
            'node_id' => '!99887766',
            'short_name' => 'RTR_X',
            'long_name' => 'Router Sin Clasificar',
            'role' => 'ROUTER',
            'province' => 'ES-GR',
            'status' => CoordinatedRouter::STATUS_NEW,
        ]);

        $this->actingAs($operador);

        // 1. Marcar como Conocido
        Livewire::test(ListCoordinatedRouters::class)
            ->callTableAction('mark_known', $router)
            ->assertHasNoTableActionErrors();

        $router->refresh();
        $this->assertTrue($router->isKnown());
        $this->assertFalse($router->isManaged());

        // 2. Marcar como Gestionado
        Livewire::test(ListCoordinatedRouters::class)
            ->callTableAction('mark_managed', $router)
            ->assertHasNoTableActionErrors();

        $router->refresh();
        $this->assertTrue($router->isManaged());
        $this->assertTrue($router->isApproved());
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
            'status' => CoordinatedRouter::STATUS_MANAGED,
            'is_gateway' => false,
        ]);

        $response = $this->actingAs($operador)
            ->get('/admin/coordinated-routers');

        $response->assertStatus(200);
        $response->assertSee('Routers Coordinados');
        $response->assertSee('Supervisa los repetidores de la red');
        $response->assertSee('!aabbccdd');
        $response->assertSee('MAL1');
        $response->assertSee('Málaga');
        $response->assertSee('Gestionado');
    }
}
