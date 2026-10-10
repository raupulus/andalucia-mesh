<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\GestionRouters;
use App\Models\CoordinatedRouter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas automatizadas de la página de Gestión Remota de Routers de Infraestructura.
 */
class GestionRoutersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Los usuarios no autenticados no pueden acceder a la página de gestión de routers.
     */
    public function test_invitados_son_redirigidos_a_login(): void
    {
        $response = $this->get('/admin/gestion-routers');

        $response->assertRedirect('/admin/login');
    }

    /**
     * Los operadores autenticados pueden cargar la vista de gestión de routers con éxito.
     */
    public function test_operador_autenticado_puede_acceder_a_gestion_routers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/gestion-routers');

        $response->assertSuccessful();
        $response->assertSee(__('admin.gestion_routers.title'));
        $response->assertSee(__('admin.gestion_routers.warning_browser_title'));
        $response->assertSee('/js/mesh-admin.bundle.js');
        $response->assertSee(__('admin.gestion_routers.local_connection_heading'));
        $response->assertSee(__('admin.gestion_routers.target_router_heading'));
    }

    /**
     * La página carga exclusivamente routers de Andalucía en el selector.
     */
    public function test_selector_incluye_routers_de_andalucia_y_excluye_otras_regiones(): void
    {
        $user = User::factory()->create();

        // Router en Cádiz (Andalucía)
        $routerAndaluz = CoordinatedRouter::create([
            'node_id' => '!11223344',
            'short_name' => 'CAD1',
            'long_name' => 'Router Sanlúcar de Barrameda',
            'role' => 'ROUTER',
            'province' => 'ES-CA',
            'status' => CoordinatedRouter::STATUS_MANAGED,
        ]);

        // Router en Madrid (Fuera de Andalucía)
        $routerMad = CoordinatedRouter::create([
            'node_id' => '!99887766',
            'short_name' => 'MAD1',
            'long_name' => 'Router Sierra de Guadarrama',
            'role' => 'ROUTER',
            'province' => 'ES-M',
            'status' => CoordinatedRouter::STATUS_KNOWN,
        ]);

        $response = $this->actingAs($user)->get('/admin/gestion-routers');

        $response->assertSuccessful();
        $response->assertSee('CAD1');
        $response->assertSee('!11223344');
        $response->assertDontSee('MAD1');
        $response->assertDontSee('!99887766');
    }

    /**
     * El componente Livewire de la página GestionRouters se renderiza con sus pestañas y opciones.
     */
    public function test_renderizado_livewire_de_gestion_routers(): void
    {
        $user = User::factory()->create();

        CoordinatedRouter::create([
            'node_id' => '!aabbccdd',
            'short_name' => 'SEV1',
            'long_name' => 'Router Aljarafe',
            'role' => 'ROUTER',
            'province' => 'ES-SE',
            'status' => CoordinatedRouter::STATUS_MANAGED,
        ]);

        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->assertSuccessful()
            ->assertSee(__('admin.gestion_routers.tab_roles'))
            ->assertSee(__('admin.gestion_routers.tab_favorites'))
            ->assertSee(__('admin.gestion_routers.tab_blocked'))
            ->assertSee(__('admin.gestion_routers.tab_poll'))
            ->assertSee(__('admin.gestion_routers.tab_unicast'))
            ->assertSee(__('admin.gestion_routers.tab_maintenance'))
            ->assertSee('SEV1')
            ->assertSee('!aabbccdd');
    }

    /**
     * El método Livewire updateRouterFavoriteNode añade y quita favoritos del router en base de datos.
     */
    public function test_actualizar_favorito_de_router_mediante_livewire(): void
    {
        $user = User::factory()->create();

        $router = CoordinatedRouter::create([
            'node_id' => '!63760c00',
            'short_name' => 'CC05',
            'long_name' => 'Candidato05',
            'role' => 'ROUTER',
            'province' => 'ES-CA',
            'status' => CoordinatedRouter::STATUS_MANAGED,
            'favorite_nodes' => [],
        ]);

        $testNodeData = [
            'short_name' => 'TST1',
            'long_name' => 'Nodo Test',
            'role' => 'ROUTER',
        ];

        // Añadir favorito
        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->call('updateRouterFavoriteNode', '!63760c00', '!5f3a3a29', true, $testNodeData);

        $router->refresh();
        $this->assertCount(1, $router->favorite_nodes);
        $this->assertSame('!5f3a3a29', $router->favorite_nodes[0]['hex']);
        $this->assertSame('TST1', $router->favorite_nodes[0]['short_name']);

        // Eliminar favorito
        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->call('updateRouterFavoriteNode', '!63760c00', '!5f3a3a29', false);

        $router->refresh();
        $this->assertCount(0, $router->favorite_nodes);
    }

    /**
     * El método Livewire updateRouterBlockedNode añade y quita nodos bloqueados del router en base de datos.
     */
    public function test_actualizar_bloqueado_de_router_mediante_livewire(): void
    {
        $user = User::factory()->create();

        $router = CoordinatedRouter::create([
            'node_id' => '!63760c00',
            'short_name' => 'CC05',
            'long_name' => 'Candidato05',
            'role' => 'ROUTER',
            'province' => 'ES-CA',
            'status' => CoordinatedRouter::STATUS_MANAGED,
            'blocked_nodes' => [],
        ]);

        $testNodeData = [
            'short_name' => 'SPAM',
            'long_name' => 'Spam Node',
            'role' => 'CLIENT',
        ];

        // Añadir nodo bloqueado
        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->call('updateRouterBlockedNode', '!63760c00', '!bad0cafe', true, $testNodeData);

        $router->refresh();
        $this->assertCount(1, $router->blocked_nodes);
        $this->assertSame('!bad0cafe', $router->blocked_nodes[0]['hex']);
        $this->assertSame('SPAM', $router->blocked_nodes[0]['short_name']);

        // Desbloquear nodo
        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->call('updateRouterBlockedNode', '!63760c00', '!bad0cafe', false);

        $router->refresh();
        $this->assertCount(0, $router->blocked_nodes);
    }

    /**
     * El método updateRouterFavoriteNode acepta payload con camelCase (shortName, longName) y normaliza ambos.
     */
    public function test_actualizar_favorito_con_payload_camelcase_y_autoresolucion(): void
    {
        $user = User::factory()->create();

        // Router que se gestiona
        $router = CoordinatedRouter::create([
            'node_id' => '!63760c00',
            'short_name' => 'CA05',
            'long_name' => 'EA7-CA-05',
            'role' => 'ROUTER',
            'province' => 'ES-CA',
            'status' => CoordinatedRouter::STATUS_MANAGED,
            'favorite_nodes' => [],
        ]);

        // Nodo conocido en base de datos para probar auto-resolución
        CoordinatedRouter::create([
            'node_id' => '!4abdfee8',
            'short_name' => 'BA01',
            'long_name' => 'BA01ZSuarezTentudia',
            'role' => 'ROUTER',
            'province' => 'FUERA',
            'status' => CoordinatedRouter::STATUS_KNOWN,
        ]);

        // 1. Añadir con camelCase desde JS (shortName y longName)
        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->call('updateRouterFavoriteNode', '!63760c00', '!5f3a3a29', true, [
                'shortName' => 'Rau0',
                'longName' => 'Raupulus Base',
                'role' => 'CLIENT',
            ]);

        $router->refresh();
        $this->assertCount(1, $router->favorite_nodes);
        $this->assertSame('Rau0', $router->favorite_nodes[0]['short_name']);
        $this->assertSame('Rau0', $router->favorite_nodes[0]['shortName']);
        $this->assertSame('Raupulus Base', $router->favorite_nodes[0]['long_name']);
        $this->assertSame('Raupulus Base', $router->favorite_nodes[0]['longName']);

        // 2. Añadir nodo pasando nombres vacíos para comprobar auto-resolución en base de datos
        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->call('updateRouterFavoriteNode', '!63760c00', '!4abdfee8', true, []);

        $router->refresh();
        $this->assertCount(2, $router->favorite_nodes);
        $this->assertSame('BA01', $router->favorite_nodes[1]['short_name']);
        $this->assertSame('BA01', $router->favorite_nodes[1]['shortName']);
        $this->assertSame('BA01ZSuarezTentudia', $router->favorite_nodes[1]['long_name']);
    }

    /**
     * Comprueba que los métodos de limpieza y sus alias funcionan con mayúsculas/minúsculas.
     */
    public function test_limpiar_listas_con_alias_y_case_insensitive(): void
    {
        $user = User::factory()->create();

        $router = CoordinatedRouter::create([
            'node_id' => '!63760c00',
            'short_name' => 'CA05',
            'long_name' => 'EA7-CA-05',
            'role' => 'ROUTER',
            'province' => 'ES-CA',
            'status' => CoordinatedRouter::STATUS_MANAGED,
            'favorite_nodes' => [['hex' => '!30618df9', 'num' => 811699705, 'short_name' => 'CA01']],
            'blocked_nodes' => [['hex' => '!deadbeef', 'num' => 3735928559, 'short_name' => 'DEAD']],
        ]);

        // Probar clearRouterFavoriteNodes con mayúsculas
        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->call('clearRouterFavoriteNodes', '!63760C00');

        $router->refresh();
        $this->assertNull($router->favorite_nodes);

        // Probar clearRouterBlockedNodes con minúsculas
        Livewire::actingAs($user)
            ->test(GestionRouters::class)
            ->call('clearRouterBlockedNodes', '!63760c00');

        $router->refresh();
        $this->assertNull($router->blocked_nodes);
    }
}

