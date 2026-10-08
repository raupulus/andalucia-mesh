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
            ->assertSee(__('admin.gestion_routers.tab_poll'))
            ->assertSee(__('admin.gestion_routers.tab_unicast'))
            ->assertSee(__('admin.gestion_routers.tab_maintenance'))
            ->assertSee('SEV1')
            ->assertSee('!aabbccdd');
    }
}
