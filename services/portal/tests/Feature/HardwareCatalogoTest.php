<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\HardwareCategory;
use App\Models\HardwareItem;
use App\Models\User;
use Database\Seeders\HardwareSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas automatizadas de integración para el catálogo de hardware y sus recursos de administración.
 */
class HardwareCatalogoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comprueba que la página de hardware sea accesible, muestre artículos activos y cumpla la directiva de cero cookies (RN-06).
     */
    public function test_pagina_hardware_es_accesible_y_muestra_articulos_activos(): void
    {
        $categoria = HardwareCategory::create([
            'slug' => 'dispositivos-portatiles',
            'name' => [
                'es' => 'Dispositivos portátiles',
                'en' => 'Handheld devices',
            ],
            'description' => [
                'es' => 'Nodos ligeros con pantalla y batería para uso en mano o mochila.',
                'en' => 'Handheld nodes with screen and battery for portable use.',
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $articulo = HardwareItem::create([
            'category_id' => $categoria->id,
            'slug' => 'heltec-v3',
            'name' => 'Heltec WiFi LoRa 32 V3',
            'description' => [
                'es' => 'Placa muy popular con chip ESP32-S3 y pantalla OLED integrada. Ideal para iniciarse.',
                'en' => 'Popular board with ESP32-S3 and built-in OLED screen. Great starter device.',
            ],
            'image_path' => 'hardware/heltec-v3.webp',
            'buy_url' => 'https://es.aliexpress.com/item/heltec-v3.html',
            'guide_url' => 'https://meshtastic.org/docs/hardware/devices/heltec-v3/',
            'last_price' => 28.50,
            'currency' => 'EUR',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/hardware');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Catálogo de Hardware Recomendado');
        $response->assertSee('Dispositivos portátiles');
        $response->assertSee('Heltec WiFi LoRa 32 V3');
        $response->assertSee('Placa muy popular con chip ESP32-S3');
        $response->assertSee('28,50 €');
        $response->assertSee('https://es.aliexpress.com/item/heltec-v3.html', false);
        $response->assertSee('https://meshtastic.org/docs/hardware/devices/heltec-v3/', false);
        $response->assertSee('Recomendado');
    }

    /**
     * Comprueba que el filtro de categoría (?categoria=slug) filtre adecuadamente los elementos visibles.
     */
    public function test_filtro_por_categoria_funciona_correctamente(): void
    {
        $catPlacas = HardwareCategory::create([
            'slug' => 'placas-desarrollo',
            'name' => ['es' => 'Placas de desarrollo', 'en' => 'Development boards'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $catAntenas = HardwareCategory::create([
            'slug' => 'antenas',
            'name' => ['es' => 'Antenas y conectores', 'en' => 'Antennas & connectors'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $itemPlaca = HardwareItem::create([
            'category_id' => $catPlacas->id,
            'slug' => 'lilygo-t-beam',
            'name' => 'LilyGO T-Beam Supreme',
            'description' => ['es' => 'Placa con GPS y soporte para batería 18650.', 'en' => 'Board with GPS and 18650 slot.'],
            'image_path' => 'hardware/t-beam.webp',
            'buy_url' => 'https://example.com/tbeam',
            'last_price' => 45.00,
            'is_active' => true,
        ]);

        $itemAntena = HardwareItem::create([
            'category_id' => $catAntenas->id,
            'slug' => 'antena-fibra-868',
            'name' => 'Antena Fibra de Vidrio 868MHz 5.8dBi',
            'description' => ['es' => 'Antena omnidireccional para tejado y mastil exterior.', 'en' => 'Outdoor omni antenna for rooftops.'],
            'image_path' => 'hardware/antena.webp',
            'buy_url' => 'https://example.com/antena',
            'last_price' => 38.00,
            'is_active' => true,
        ]);

        // Consulta filtrando por antenas
        $response = $this->get('/hardware?categoria=antenas');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Antena Fibra de Vidrio 868MHz 5.8dBi');
        $response->assertDontSee('LilyGO T-Beam Supreme');

        // Consulta filtrando por placas
        $responsePlacas = $this->get('/hardware?categoria=placas-desarrollo');

        $responsePlacas->assertStatus(200);
        $responsePlacas->assertSee('LilyGO T-Beam Supreme');
        $responsePlacas->assertDontSee('Antena Fibra de Vidrio 868MHz 5.8dBi');
    }

    /**
     * Comprueba que los artículos marcados como inactivos (is_active=false) nunca se expongan en el frontend público.
     */
    public function test_articulos_inactivos_no_se_muestran_en_el_frontend(): void
    {
        $cat = HardwareCategory::create([
            'slug' => 'sensores',
            'name' => ['es' => 'Sensores ambientales', 'en' => 'Environmental sensors'],
            'is_active' => true,
        ]);

        $articuloActivo = HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'bme280',
            'name' => 'Sensor BME280 I2C',
            'description' => ['es' => 'Sensor de presión, temperatura y humedad.', 'en' => 'Temperature and humidity sensor.'],
            'image_path' => 'hardware/bme280.webp',
            'buy_url' => 'https://example.com/bme280',
            'is_active' => true,
        ]);

        $articuloInactivo = HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'sensor-obsoleto',
            'name' => 'Sensor Obsoleto DHT11',
            'description' => ['es' => 'Sensor no recomendado por baja precisión.', 'en' => 'Deprecated sensor.'],
            'image_path' => 'hardware/dht11.webp',
            'buy_url' => 'https://example.com/dht11',
            'is_active' => false,
        ]);

        $response = $this->get('/hardware');

        $response->assertStatus(200);
        $response->assertSee('Sensor BME280 I2C');
        $response->assertDontSee('Sensor Obsoleto DHT11');
    }

    /**
     * Comprueba que el panel de administración (/admin) restrinja el acceso a usuarios no autenticados y permita a operadores autorizados gestionar el catálogo.
     */
    public function test_admin_puede_acceder_a_recursos_de_hardware(): void
    {
        // 1. Visitante anónimo redirige al inicio de sesión
        $responseAnonimoCat = $this->get('/admin/hardware-categories');
        $responseAnonimoCat->assertRedirect('/admin/login');

        $responseAnonimoItem = $this->get('/admin/hardware-items');
        $responseAnonimoItem->assertRedirect('/admin/login');

        // 2. Operador autenticado y activo accede con 200 OK
        $operador = User::factory()->create([
            'email' => 'operador.hardware@andalucia.mesh',
            'activo' => true,
        ]);

        $cat = HardwareCategory::create([
            'slug' => 'estaciones-base',
            'name' => ['es' => 'Estaciones base', 'en' => 'Base stations'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $item = HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'station-g2',
            'name' => 'Station G2 Repeater',
            'description' => ['es' => 'Repetidor troncal estanco para mástil.', 'en' => 'Waterproof repeater for mast.'],
            'image_path' => 'hardware/station-g2.webp',
            'buy_url' => 'https://example.com/station-g2',
            'last_price' => 89.90,
            'is_active' => true,
        ]);

        $responseAdminCat = $this->actingAs($operador)->get('/admin/hardware-categories');
        $responseAdminCat->assertStatus(200);
        $responseAdminCat->assertSee('Estaciones base');

        $responseAdminItem = $this->actingAs($operador)->get('/admin/hardware-items');
        $responseAdminItem->assertStatus(200);
        $responseAdminItem->assertSee('Station G2 Repeater');
    }

    /**
     * Comprueba que la búsqueda textual por término (?q=...) filtre correctamente por nombre o descripción.
     */
    public function test_busqueda_textual_filtra_por_nombre_y_descripcion(): void
    {
        $cat = HardwareCategory::create([
            'slug' => 'general',
            'name' => ['es' => 'General', 'en' => 'General'],
            'is_active' => true,
        ]);

        HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'rak4631',
            'name' => 'WisBlock RAK4631',
            'description' => ['es' => 'Dispositivo basado en chip nRF52840 de bajísimo consumo energético.', 'en' => 'Ultra low power nRF52840 board.'],
            'image_path' => 'hardware/rak4631.webp',
            'buy_url' => 'https://example.com/rak',
            'is_active' => true,
        ]);

        HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'heltec-v2',
            'name' => 'Heltec V2 Legacy',
            'description' => ['es' => 'Placa ESP32 antigua con chip SX1276.', 'en' => 'Old board.'],
            'image_path' => 'hardware/v2.webp',
            'buy_url' => 'https://example.com/v2',
            'is_active' => true,
        ]);

        // Búsqueda por "WisBlock"
        $responseWisblock = $this->get('/hardware?q=WisBlock');
        $responseWisblock->assertStatus(200);
        $responseWisblock->assertSee('WisBlock RAK4631');
        $responseWisblock->assertDontSee('Heltec V2 Legacy');

        // Búsqueda por "nRF52840" en la descripción
        $responseNrf = $this->get('/hardware?q=nRF52840');
        $responseNrf->assertStatus(200);
        $responseNrf->assertSee('WisBlock RAK4631');
        $responseNrf->assertDontSee('Heltec V2 Legacy');

        // Búsqueda sin coincidencias muestra estado vacío
        $responseVacio = $this->get('/hardware?q=dispositivo_inexistente_xyz');
        $responseVacio->assertStatus(200);
        $responseVacio->assertSee('No se han encontrado dispositivos');
    }

    /**
     * Comprueba que la visualización localizada (ES vs EN) entregue los textos en el idioma solicitado.
     */
    public function test_articulos_multidioma_se_traducen_segun_locale(): void
    {
        $cat = HardwareCategory::create([
            'slug' => 'repetidores',
            'name' => ['es' => 'Repetidores de montaña', 'en' => 'Mountain repeaters'],
            'description' => ['es' => 'Equipos para cumbres.', 'en' => 'High peak equipment.'],
            'is_active' => true,
        ]);

        HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'solar-node',
            'name' => ['es' => 'Nodo Autónomo Solar', 'en' => 'Solar Autonomous Node'],
            'description' => [
                'es' => 'Incluye panel de 10W y batería LiFePO4 para funcionamiento ininterrumpido.',
                'en' => 'Includes 10W panel and LiFePO4 battery for 24/7 continuous operation.',
            ],
            'image_path' => 'hardware/solar.webp',
            'buy_url' => 'https://example.com/solar',
            'is_active' => true,
        ]);

        // Petición en español (por defecto)
        $responseEs = $this->get('/hardware');
        $responseEs->assertStatus(200);
        $responseEs->assertSee('Repetidores de montaña');
        $responseEs->assertSee('Nodo Autónomo Solar');
        $responseEs->assertSee('Incluye panel de 10W');

        // Petición en inglés (?lang=en)
        $responseEn = $this->get('/hardware?lang=en');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('Mountain repeaters');
        $responseEn->assertSee('Solar Autonomous Node');
        $responseEn->assertSee('Includes 10W panel and LiFePO4 battery');
        $responseEn->assertSee('Where to buy');
    }

    /**
     * Comprueba que HardwareSeeder genere las 4 categorías fijas requeridas por slug y los 10 artículos.
     */
    public function test_seeder_crea_las_cuatro_categorias_fijas_y_los_diez_articulos(): void
    {
        $this->seed(HardwareSeeder::class);

        // Comprueba las 4 categorías por su slug exacto
        $slugsEsperados = ['antenas', 'nodos-diy', 'nodos-prefabricados', 'placas-solares'];
        foreach ($slugsEsperados as $slug) {
            $cat = HardwareCategory::where('slug', $slug)->first();
            $this->assertNotNull($cat, "La categoría con slug '{$slug}' debe existir.");
            $this->assertTrue($cat->is_active);
            $this->assertGreaterThan(0, $cat->items()->count(), "La categoría '{$slug}' debe tener al menos un artículo.");
        }

        $this->assertEquals(4, HardwareCategory::count());
        $this->assertEquals(10, HardwareItem::count());

        // Comprueba idempotencia: ejecutar el seeder dos veces no duplica registros
        $this->seed(HardwareSeeder::class);
        $this->assertEquals(4, HardwareCategory::count());
        $this->assertEquals(10, HardwareItem::count());
    }

    /**
     * Comprueba que el menú lateral muestre todas las categorías con sus recuentos y el estado aria-current.
     */
    public function test_menu_lateral_muestra_categorias_con_conteo_de_articulos(): void
    {
        $this->seed(HardwareSeeder::class);

        $response = $this->get('/hardware');
        $response->assertStatus(200);

        // Menú lateral presente y con etiqueta accesible
        $response->assertSee('hardware-sidebar', false);
        $response->assertSee('Categorías de hardware');

        // Todas las categorías está activo inicialmente
        $response->assertSee('Todas las categorías');
        $response->assertSee('aria-current="page"', false);

        // Comprobar recuento total (10)
        $response->assertSee('10');

        // Cada una de las 4 categorías aparece en la navegación
        $response->assertSee('Antenas');
        $response->assertSee('Nodos DIY');
        $response->assertSee('Nodos Prefabricados');
        $response->assertSee('Placas Solares');

        // Al filtrar por una categoría específica (ej. antenas)
        $responseCat = $this->get('/hardware?categoria=antenas');
        $responseCat->assertStatus(200);
        $responseCat->assertSee('?categoria=antenas', false);
        $responseCat->assertSee('aria-current="page"', false);
    }

    /**
     * Comprueba que los formularios de Filament contengan el selector de idiomas superior y la distribución por bloques requerida.
     */
    public function test_formularios_filament_contienen_pestanas_multidioma_y_distribucion_ancho_completo(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador.i18n@andalucia.mesh',
            'activo' => true,
        ]);

        $cat = HardwareCategory::create([
            'slug' => 'test-cat',
            'name' => [
                'es' => 'Categoria Test ES',
                'en' => 'Category Test EN',
                'pt' => 'Categoria Test PT',
            ],
            'description' => [
                'es' => 'Descripción ES',
                'en' => 'Description EN',
                'pt' => 'Descrição PT',
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'test-item',
            'name' => [
                'es' => 'Dispositivo Test ES',
                'en' => 'Device Test EN',
                'pt' => 'Dispositivo Test PT',
            ],
            'description' => [
                'es' => 'Descripción artículo ES',
                'en' => 'Item description EN',
                'pt' => 'Descrição artigo PT',
            ],
            'image_path' => 'hardware/test.webp',
            'buy_url' => 'https://example.com/buy',
            'is_active' => true,
        ]);

        // 1. Acceso a edición de categoría: contiene los campos ES, EN y PT
        $responseEditCat = $this->actingAs($operador)->get("/admin/hardware-categories/{$cat->id}/edit");
        $responseEditCat->assertStatus(200);
        $responseEditCat->assertSee('name.es', false);
        $responseEditCat->assertSee('name.en', false);
        $responseEditCat->assertSee('name.pt', false);
        $responseEditCat->assertSee('Categoria Test ES');

        // 2. Acceso a edición de artículo: contiene los campos ES, EN y PT y los 4 bloques
        $responseEditItem = $this->actingAs($operador)->get("/admin/hardware-items/{$item->id}/edit");
        $responseEditItem->assertStatus(200);
        $responseEditItem->assertSee('name.es', false);
        $responseEditItem->assertSee('name.en', false);
        $responseEditItem->assertSee('name.pt', false);
        $responseEditItem->assertSee('image_path', false);
        $responseEditItem->assertSee('buy_url', false);
        $responseEditItem->assertSee('is_featured', false);
        $responseEditItem->assertSee('Dispositivo Test ES');
        // El campo sort_order se ha retirado del formulario de artículos para ordenarse solo desde la tabla
        $responseEditItem->assertDontSee('data.sort_order', false);
    }

    /**
     * Comprueba que los artículos de hardware autoasignen orden incremental al crearse si no se define.
     */
    public function test_articulo_hardware_autoasigna_orden_incremental_al_crear(): void
    {
        $cat = HardwareCategory::create([
            'slug' => 'cat-orden',
            'name' => ['es' => 'Cat Orden', 'en' => 'Order Cat'],
            'is_active' => true,
        ]);

        $item1 = HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'item-orden-1',
            'name' => 'Item 1',
            'description' => ['es' => 'Desc 1', 'en' => 'Desc 1'],
            'image_path' => 'hardware/1.webp',
            'buy_url' => 'https://example.com/1',
            'is_active' => true,
        ]);

        $item2 = HardwareItem::create([
            'category_id' => $cat->id,
            'slug' => 'item-orden-2',
            'name' => 'Item 2',
            'description' => ['es' => 'Desc 2', 'en' => 'Desc 2'],
            'image_path' => 'hardware/2.webp',
            'buy_url' => 'https://example.com/2',
            'is_active' => true,
        ]);

        $this->assertGreaterThan(0, $item1->sort_order);
        $this->assertGreaterThan($item1->sort_order, $item2->sort_order);
    }
}
