<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CustomPages\Pages\CreateCustomPage;
use App\Models\CoordinatedRouter;
use App\Models\CustomPage;
use App\Models\Faq;
use App\Models\HardwareCategory;
use App\Models\HardwareItem;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\WebhookDestination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas exhaustivas de permisos y restricciones del rol Editor en el panel /admin.
 *
 * Verifica el cumplimiento estricto del principio de mínimo privilegio:
 * 1. Dashboard: acceso permitido y visualización de widgets.
 * 2. FAQs: listar, crear y editar permitido; eliminar denegado.
 * 3. Sugerencias: listar permitido; editar, moderar o eliminar denegado.
 * 4. Routers coordinados: listar permitido; crear, editar, sincronizar y eliminar denegado.
 * 5. Gestión de routers: acceso completo (/admin/gestion-routers).
 * 6. Páginas y artículos: crear y editar permitido (fuerza borrador is_active=false); publicar y eliminar denegado.
 * 7. Categorías de hardware: listar permitido; crear, editar y eliminar denegado.
 * 8. Artículos de hardware: listar, crear y editar permitido; eliminar denegado.
 * 9. Webhooks: recurso completamente oculto y denegado (403).
 */
class EditorRolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * El editor puede acceder al panel principal /admin (dashboard).
     */
    public function test_editor_puede_acceder_al_dashboard(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        $response = $this->actingAs($editor)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Panel de Operador');
    }

    /**
     * El editor puede listar, crear y editar FAQs, pero NO puede eliminarlas.
     */
    public function test_editor_permisos_faqs(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        $faq = Faq::create([
            'question' => '¿Pregunta de prueba?',
            'answer' => 'Respuesta explicativa.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Listar permitido
        $responseIndex = $this->actingAs($editor)->get('/admin/faqs');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('¿Pregunta de prueba?');

        // Formulario de creación permitido
        $responseCreate = $this->actingAs($editor)->get('/admin/faqs/create');
        $responseCreate->assertStatus(200);

        // Formulario de edición permitido
        $responseEdit = $this->actingAs($editor)->get("/admin/faqs/{$faq->id}/edit");
        $responseEdit->assertStatus(200);

        // Eliminar denegado por política
        $this->assertFalse(Gate::forUser($editor)->allows('delete', $faq));
        $this->assertFalse(Gate::forUser($editor)->allows('deleteAny', Faq::class));
    }

    /**
     * El editor puede ver sugerencias en modo solo lectura, pero NO editarlas, moderarlas ni eliminarlas.
     */
    public function test_editor_permisos_sugerencias(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        $sugerencia = Suggestion::create([
            'category' => 'potatomesh',
            'content' => 'Propuesta de mejora en mapas.',
            'status' => Suggestion::STATUS_PENDING,
            'ip_hash' => 'dummy_hash',
        ]);

        // Listar permitido
        $responseIndex = $this->actingAs($editor)->get('/admin/suggestions');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Propuesta de mejora en mapas.');

        // Editar denegado (403)
        $responseEdit = $this->actingAs($editor)->get("/admin/suggestions/{$sugerencia->id}/edit");
        $responseEdit->assertStatus(403);

        // Actualizar y eliminar denegados por política
        $this->assertFalse(Gate::forUser($editor)->allows('update', $sugerencia));
        $this->assertFalse(Gate::forUser($editor)->allows('delete', $sugerencia));
        $this->assertFalse(Gate::forUser($editor)->allows('deleteAny', Suggestion::class));
    }

    /**
     * El editor puede ver routers coordinados, pero NO darlos de alta, editarlos ni eliminarlos.
     */
    public function test_editor_permisos_routers_coordinados(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        $router = CoordinatedRouter::create([
            'node_id' => '!11223344',
            'short_name' => 'RTR1',
            'long_name' => 'Router Cerro del Viento',
            'role' => 'ROUTER',
            'province' => 'ES-CA',
            'status' => CoordinatedRouter::STATUS_MANAGED,
            'is_gateway' => false,
        ]);

        // Listar permitido
        $responseIndex = $this->actingAs($editor)->get('/admin/coordinated-routers');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Router Cerro del Viento');

        // Crear denegado (403)
        $responseCreate = $this->actingAs($editor)->get('/admin/coordinated-routers/create');
        $responseCreate->assertStatus(403);

        // Editar denegado (403)
        $responseEdit = $this->actingAs($editor)->get("/admin/coordinated-routers/{$router->id}/edit");
        $responseEdit->assertStatus(403);

        // Eliminar denegado por política
        $this->assertFalse(Gate::forUser($editor)->allows('create', CoordinatedRouter::class));
        $this->assertFalse(Gate::forUser($editor)->allows('update', $router));
        $this->assertFalse(Gate::forUser($editor)->allows('delete', $router));
        $this->assertFalse(Gate::forUser($editor)->allows('deleteAny', CoordinatedRouter::class));
    }

    /**
     * El editor tiene acceso completo a la herramienta de gestión técnica de routers.
     */
    public function test_editor_acceso_completo_a_gestion_routers(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        $response = $this->actingAs($editor)->get('/admin/gestion-routers');

        $response->assertStatus(200);
        $response->assertSee(__('admin.gestion_routers.title'));
    }

    /**
     * El editor puede crear y editar páginas, pero se crean siempre como borrador y NO puede eliminarlas.
     */
    public function test_editor_permisos_paginas(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        $pagina = CustomPage::create([
            'title' => 'Página Editor Existente',
            'slug' => 'pagina-editor-existente',
            'description' => 'Descripción previa.',
            'content' => 'Contenido redactado.',
            'is_active' => true,
        ]);

        // Listar permitido
        $responseIndex = $this->actingAs($editor)->get('/admin/custom-pages');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Página Editor Existente');

        // Formulario de creación permitido
        $responseCreate = $this->actingAs($editor)->get('/admin/custom-pages/create');
        $responseCreate->assertStatus(200);

        // Crear una nueva página como editor fuerza is_active = false
        Livewire::actingAs($editor)
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => 'Nuevo Artículo de Editor',
                'slug' => 'nuevo-articulo-editor',
                'description' => 'Descripción breve del artículo.',
                'content' => 'Contenido extenso del artículo.',
                'keywords' => ['LoRa', 'Guía'],
                'is_active' => true, // El editor intenta activarlo pero el backend debe forzar false
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $creada = CustomPage::where('slug', 'nuevo-articulo-editor')->first();
        $this->assertNotNull($creada);
        $this->assertFalse((bool) $creada->is_active);

        // Editar página existente permitido
        $responseEdit = $this->actingAs($editor)->get("/admin/custom-pages/{$pagina->id}/edit");
        $responseEdit->assertStatus(200);

        // Eliminar denegado por política
        $this->assertFalse(Gate::forUser($editor)->allows('delete', $pagina));
        $this->assertFalse(Gate::forUser($editor)->allows('deleteAny', CustomPage::class));
    }

    /**
     * El editor puede ver categorías de hardware pero NO crearlas, editarlas ni eliminarlas.
     */
    public function test_editor_permisos_categorias_hardware(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        $categoria = HardwareCategory::create([
            'slug' => 'dispositivos-moviles',
            'name' => ['es' => 'Dispositivos móviles', 'en' => 'Mobile devices'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Listar permitido
        $responseIndex = $this->actingAs($editor)->get('/admin/hardware-categories');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Dispositivos móviles');

        // Crear denegado (403)
        $responseCreate = $this->actingAs($editor)->get('/admin/hardware-categories/create');
        $responseCreate->assertStatus(403);

        // Editar denegado (403)
        $responseEdit = $this->actingAs($editor)->get("/admin/hardware-categories/{$categoria->id}/edit");
        $responseEdit->assertStatus(403);

        // Eliminar denegado por política
        $this->assertFalse(Gate::forUser($editor)->allows('create', HardwareCategory::class));
        $this->assertFalse(Gate::forUser($editor)->allows('update', $categoria));
        $this->assertFalse(Gate::forUser($editor)->allows('delete', $categoria));
        $this->assertFalse(Gate::forUser($editor)->allows('deleteAny', HardwareCategory::class));
    }

    /**
     * El editor puede listar, crear y editar artículos de hardware pero NO eliminarlos.
     */
    public function test_editor_permisos_articulos_hardware(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        $categoria = HardwareCategory::create([
            'slug' => 'antenas',
            'name' => ['es' => 'Antenas', 'en' => 'Antennas'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = HardwareItem::create([
            'category_id' => $categoria->id,
            'slug' => 'antena-fibra-868',
            'name' => 'Antena Fibra 868MHz',
            'image_path' => 'hardware/antena.webp',
            'buy_url' => 'https://example.com/antena',
            'description' => ['es' => 'Antena omnidireccional exterior.'],
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        // Listar permitido
        $responseIndex = $this->actingAs($editor)->get('/admin/hardware-items');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Antena Fibra 868MHz');

        // Crear permitido
        $responseCreate = $this->actingAs($editor)->get('/admin/hardware-items/create');
        $responseCreate->assertStatus(200);

        // Editar permitido
        $responseEdit = $this->actingAs($editor)->get("/admin/hardware-items/{$item->id}/edit");
        $responseEdit->assertStatus(200);

        // Eliminar denegado por política
        $this->assertFalse(Gate::forUser($editor)->allows('delete', $item));
        $this->assertFalse(Gate::forUser($editor)->allows('deleteAny', HardwareItem::class));
    }

    /**
     * El editor tiene el módulo de webhooks totalmente oculto y denegado (403).
     */
    public function test_editor_bloqueado_en_webhooks(): void
    {
        $editor = User::factory()->editor()->create(['activo' => true]);

        // Listar destinos denegado (403)
        $responseIndex = $this->actingAs($editor)->get('/admin/webhook-destinations');
        $responseIndex->assertStatus(403);

        // Crear destino denegado (403)
        $responseCreate = $this->actingAs($editor)->get('/admin/webhook-destinations/create');
        $responseCreate->assertStatus(403);

        // Política deniega completamente
        $this->assertFalse(Gate::forUser($editor)->allows('viewAny', WebhookDestination::class));
        $this->assertFalse(Gate::forUser($editor)->allows('create', WebhookDestination::class));
    }
}
