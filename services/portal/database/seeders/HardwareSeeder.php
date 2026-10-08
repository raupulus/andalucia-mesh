<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HardwareCategory;
use App\Models\HardwareItem;
use Illuminate\Database\Seeder;

/**
 * Seeder para el catálogo de hardware recomendado para la red comunitaria.
 *
 * Crea las 4 categorías fijas por slug exigidas por el sistema y puebla
 * 10 artículos realistas para la previsualización local y despliegue inicial.
 */
class HardwareSeeder extends Seeder
{
    /**
     * Ejecuta la inserción o actualización idempotente de categorías y artículos de hardware.
     */
    public function run(): void
    {
        // 1. Categorías obligatorias por slug
        $categoriesData = [
            'antenas' => [
                'name' => [
                    'es' => 'Antenas',
                    'en' => 'Antennas',
                ],
                'description' => [
                    'es' => 'Antenas de alta ganancia, directivas y fibra de vidrio calibradas para la frecuencia europea ISM 868 MHz.',
                    'en' => 'High-gain, directional and fiberglass antennas calibrated for the European ISM 868 MHz band.',
                ],
                'sort_order' => 10,
                'is_active' => true,
            ],
            'nodos-diy' => [
                'name' => [
                    'es' => 'Nodos DIY',
                    'en' => 'DIY Nodes',
                ],
                'description' => [
                    'es' => 'Placas de desarrollo ESP32 y nRF52 para montaje personalizado, nodos solares caseros o estaciones meteorológicas.',
                    'en' => 'ESP32 and nRF52 development boards for custom builds, home solar nodes or weather stations.',
                ],
                'sort_order' => 20,
                'is_active' => true,
            ],
            'nodos-prefabricados' => [
                'name' => [
                    'es' => 'Nodos Prefabricados',
                    'en' => 'Prefab Nodes',
                ],
                'description' => [
                    'es' => 'Dispositivos listos para usar con caja estanca o pantalla, ideales para llevar consigo o desplegar de inmediato sin soldar.',
                    'en' => 'Ready-to-use devices with enclosure or display, ideal for handheld carry or plug-and-play field setup.',
                ],
                'sort_order' => 30,
                'is_active' => true,
            ],
            'placas-solares' => [
                'name' => [
                    'es' => 'Placas Solares',
                    'en' => 'Solar Panels',
                ],
                'description' => [
                    'es' => 'Paneles solares fotovoltaicos y módulos de gestión de carga MPPT para alimentar repetidores autónomos e infraestructuras aisladas.',
                    'en' => 'Photovoltaic solar panels and MPPT charge controllers to power off-grid autonomous repeaters.',
                ],
                'sort_order' => 40,
                'is_active' => true,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $slug => $data) {
            $categories[$slug] = HardwareCategory::query()->updateOrCreate(
                ['slug' => $slug],
                $data
            );
        }

        // 2. Artículos de demostración y recomendados
        $itemsData = [
            // Nodos Prefabricados
            [
                'category_slug' => 'nodos-prefabricados',
                'slug' => 'lilygo-t-echo',
                'name' => 'LilyGO T-Echo BME280 (868 MHz)',
                'description' => [
                    'es' => 'Nodo portátil autónomo con pantalla de tinta electrónica (E-Paper) de 1,54", SoC nRF52840 de ultra bajo consumo, chip LoRa SX1262, GPS integrado y sensor ambiental BME280. Autonomía de varios días en uso continuo.',
                    'en' => 'Autonomous portable node featuring a 1.54" E-Paper display, ultra-low power nRF52840 SoC, SX1262 LoRa transceiver, built-in GPS, and BME280 environmental sensor. Multi-day continuous battery life.',
                ],
                'image_path' => 'img/hardware/lilygo-t-echo.svg',
                'buy_url' => 'https://lilygo.cc/products/t-echo',
                'guide_url' => 'https://meshtastic.org/docs/hardware/devices/t-echo/',
                'last_price' => 58.00,
                'currency' => 'EUR',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'category_slug' => 'nodos-prefabricados',
                'slug' => 'station-g2',
                'name' => 'B&Q Station G2 Outdoor Repeater',
                'description' => [
                    'es' => 'Estación repetidora de exterior con caja estanca de aluminio fundido IP67, microcontrolador ESP32 y amplificador RF. Diseñado para montaje permanente en mástiles o tejados comunitarios con conector N hembra.',
                    'en' => 'Outdoor base repeater in an IP67 die-cast aluminum enclosure, powered by ESP32 with RF power amplifier. Built for permanent mast or community roof installation with an N-female connector.',
                ],
                'image_path' => 'img/hardware/station-g2.svg',
                'buy_url' => 'https://uniteng.com/index.php/product/station-g2/',
                'guide_url' => 'https://meshtastic.org/docs/hardware/devices/station-g2/',
                'last_price' => 89.00,
                'currency' => 'EUR',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'category_slug' => 'nodos-prefabricados',
                'slug' => 'sensecap-t1000-e',
                'name' => 'Seeed SenseCAP Card Tracker T1000-E',
                'description' => [
                    'es' => 'Rastreador ultradelgado tamaño tarjeta de crédito con GPS integrado, acelerómetro, botón SOS y chip nRF52840 con soporte Meshtastic oficial. Protección IP65 contra agua y polvo y carga magnética rápida.',
                    'en' => 'Ultra-slim credit-card-sized tracker with built-in GPS, accelerometer, SOS button, and nRF52840 MCU running official Meshtastic firmware. Features IP65 weather resistance and magnetic charging.',
                ],
                'image_path' => 'img/hardware/sensecap-t1000e.svg',
                'buy_url' => 'https://www.seeedstudio.com/SenseCAP-Card-Tracker-T1000-E-for-Meshtastic-p-5913.html',
                'guide_url' => 'https://meshtastic.org/docs/hardware/devices/seeed/sensecap-card-tracker/',
                'last_price' => 39.90,
                'currency' => 'EUR',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 30,
            ],

            // Nodos DIY
            [
                'category_slug' => 'nodos-diy',
                'slug' => 'heltec-wifi-lora-32-v3',
                'name' => 'Heltec WiFi LoRa 32 V3',
                'description' => [
                    'es' => 'Placa de desarrollo de referencia económica basada en ESP32-S3 de doble núcleo y chip LoRa SX1262. Incluye pantalla OLED monocolor de 0,96" y circuito de carga Li-Po. Ideal para iniciarse o actuar como gateway local conectado a red doméstica.',
                    'en' => 'Budget-friendly reference development board based on dual-core ESP32-S3 and SX1262 LoRa chip. Includes a 0.96" monochrome OLED display and Li-Po charger circuit. Ideal for beginners or local home gateways.',
                ],
                'image_path' => 'img/hardware/heltec-v3.svg',
                'buy_url' => 'https://heltec.org/project/wifi-lora-32-v3/',
                'guide_url' => 'https://meshtastic.org/docs/hardware/devices/heltec/',
                'last_price' => 24.50,
                'currency' => 'EUR',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'category_slug' => 'nodos-diy',
                'slug' => 'rak4631-starter-kit',
                'name' => 'WisBlock RAK4631 Meshtastic Starter Kit',
                'description' => [
                    'es' => 'La elección recomendada para repetidores autónomos solares. Placa base modular RAK19007 con módulo RAK4631 (nRF52840 + SX1262). Consumo en reposo mínimo (~12 mA), conector JST para batería Li-Ion y conector directo de panel solar.',
                    'en' => 'The top recommendation for autonomous solar repeaters. Modular RAK19007 baseboard with RAK4631 core (nRF52840 + SX1262). Minimal idle power consumption (~12 mA), JST battery connector, and direct solar panel input.',
                ],
                'image_path' => 'img/hardware/rak4631-kit.svg',
                'buy_url' => 'https://store.rakwireless.com/products/wisblock-meshtastic-starter-kit',
                'guide_url' => 'https://meshtastic.org/docs/hardware/devices/rak/',
                'last_price' => 35.00,
                'currency' => 'EUR',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'category_slug' => 'nodos-diy',
                'slug' => 'lilygo-t-beam',
                'name' => 'LilyGO T-Beam ESP32 LoRa & GPS',
                'description' => [
                    'es' => 'Placa clásica para nodos móviles y vehiculares. Incorpora zócalo 18650 en la parte posterior, receptor satelital GNSS NEO-6M/NEO-M8N y chip LoRa SX1262. Excelente para emitir telemetría de posición en rutas y senderos.',
                    'en' => 'Classic board for mobile and vehicular nodes. Features an integrated rear 18650 battery holder, NEO-6M/M8N GNSS satellite receiver, and SX1262 LoRa module. Great for broadcasting GPS coordinates while trekking.',
                ],
                'image_path' => 'img/hardware/lilygo-t-beam.svg',
                'buy_url' => 'https://lilygo.cc/products/t-beam',
                'guide_url' => 'https://meshtastic.org/docs/hardware/devices/t-beam/',
                'last_price' => 44.90,
                'currency' => 'EUR',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 30,
            ],

            // Antenas
            [
                'category_slug' => 'antenas',
                'slug' => 'antena-colineal-fibra-5-8dbi',
                'name' => 'Antena Fibra de Vidrio 5.8 dBi (868 MHz)',
                'description' => [
                    'es' => 'Antena omnidireccional colineal en encapsulado de fibra de vidrio de alta resistencia con protección UV. Ajustada específicamente a 868 MHz con conector industrial tipo N. Proporciona ganancia uniforme en el plano horizontal para nodos base.',
                    'en' => 'Collinear omnidirectional antenna encased in heavy-duty UV-resistant fiberglass. Tuned for 868 MHz with an industrial N-type connector. Delivers uniform horizon coverage for base station nodes.',
                ],
                'image_path' => 'img/hardware/antena-fibra-868.svg',
                'buy_url' => 'https://store.rakwireless.com/products/fiber-glass-antenna',
                'guide_url' => 'https://meshtastic.org/docs/hardware/antennas/',
                'last_price' => 39.00,
                'currency' => 'EUR',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'category_slug' => 'antenas',
                'slug' => 'alfa-network-aoa-868-5ac',
                'name' => 'Alfa Network AOA-868-5AC 5 dBi Omni',
                'description' => [
                    'es' => 'Antena profesional exterior de alta calidad para telecomunicaciones LoRa. Muy baja ROE (VSWR inferior a 1,5), conector N hembra y herrajes para mástil incluidos. Óptima tanto para entornos urbanos como rurales.',
                    'en' => 'High-grade commercial outdoor antenna for LoRa communications. Low VSWR (< 1.5), integrated N-female connector, and stainless mast mounting kit included. Ideal for urban and rural settings.',
                ],
                'image_path' => 'img/hardware/alfa-aoa-868.svg',
                'buy_url' => 'https://alfa-network.eu/',
                'guide_url' => null,
                'last_price' => 32.50,
                'currency' => 'EUR',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 20,
            ],

            // Placas Solares
            [
                'category_slug' => 'placas-solares',
                'slug' => 'panel-solar-monocristalino-10w',
                'name' => 'Panel Solar Monocristalino 10W 6V',
                'description' => [
                    'es' => 'Panel fotovoltaico monocristalino compacto de 10W con vidrio templado de bajo contenido en hierro y marco de aluminio anodizado. Diseñado para soportar granizo, viento y exposición continua al sol en repetidores de montaña.',
                    'en' => 'Compact 10W monocrystalline PV panel with low-iron tempered glass and anodized aluminum frame. Engineered to endure hail, wind, and harsh sun in hilltop solar repeaters.',
                ],
                'image_path' => 'img/hardware/panel-solar-10w.svg',
                'buy_url' => 'https://www.voltaicsystems.com/',
                'guide_url' => 'https://meshtastic.org/docs/hardware/solar/',
                'last_price' => 28.00,
                'currency' => 'EUR',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'category_slug' => 'placas-solares',
                'slug' => 'modulo-mppt-cn3791',
                'name' => 'Controlador de Carga Solar MPPT CN3791',
                'description' => [
                    'es' => 'Módulo de carga de batería Li-Ion/LiFePO4 con seguimiento de punto de máxima potencia basado en el integrado CN3791. Optimiza la transferencia de energía solar incluso con baja irradiación en días nublados.',
                    'en' => 'Li-Ion/LiFePO4 battery charging board with Maximum Power Point Tracking (MPPT) based on the CN3791 chip. Maximizes solar harvest even under cloudy and low-irradiance conditions.',
                ],
                'image_path' => 'img/hardware/mppt-cn3791.svg',
                'buy_url' => 'https://aliexpress.com/',
                'guide_url' => 'https://meshtastic.org/docs/hardware/solar/',
                'last_price' => 6.50,
                'currency' => 'EUR',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 20,
            ],
        ];

        foreach ($itemsData as $item) {
            $catSlug = $item['category_slug'];
            unset($item['category_slug']);

            if (! isset($categories[$catSlug])) {
                continue;
            }

            $item['category_id'] = $categories[$catSlug]->id;

            HardwareItem::query()->updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
