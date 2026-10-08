<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Suggestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder con 10 sugerencias ciudadanas realistas de prueba para Andalucía Mesh.
 */
class SuggestionSeeder extends Seeder
{
    /**
     * Ejecuta las inserciones de sugerencias de prueba en la base de datos.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $suggestions = [
            [
                'category' => Suggestion::CATEGORY_WEB,
                'content' => '¿Podríais añadir un mapa de cobertura estimado o capas de relieve topográfico en la sección del mapa de nodos? Facilitaría mucho ver si un nodo en la sierra tiene línea de visión directa (LOS).',
                'status' => Suggestion::STATUS_PENDING,
                'operator_notes' => null,
                'ip_hash' => hash('sha256', '192.168.1.10_seed_suggestion_1'),
                'created_at' => $now->copy()->subDays(2)->subHours(3),
                'updated_at' => $now->copy()->subDays(2)->subHours(3),
            ],
            [
                'category' => Suggestion::CATEGORY_BOT_TELEGRAM,
                'content' => 'Estaría genial que el bot de Telegram avisara cuando un repetidor troncal o router solar se quede sin batería o caiga en desconexión tras varias horas sin telemetría.',
                'status' => Suggestion::STATUS_APPROVED,
                'operator_notes' => 'Buena propuesta. Se vinculará con las alertas automáticas del detector de anomalías para nodos marcados como infraestructura.',
                'ip_hash' => hash('sha256', '192.168.1.11_seed_suggestion_2'),
                'created_at' => $now->copy()->subDays(5)->subHours(1),
                'updated_at' => $now->copy()->subDays(4)->subHours(6),
            ],
            [
                'category' => Suggestion::CATEGORY_NEW_FEATURE,
                'content' => 'Sería muy útil que en la web hubiera un conversor o validador de coordenadas GPS a formato Maidenhead locator / QTH locator para los radioaficionados de la malla.',
                'status' => Suggestion::STATUS_PENDING,
                'operator_notes' => null,
                'ip_hash' => hash('sha256', '192.168.1.12_seed_suggestion_3'),
                'created_at' => $now->copy()->subDays(1)->subHours(5),
                'updated_at' => $now->copy()->subDays(1)->subHours(5),
            ],
            [
                'category' => Suggestion::CATEGORY_MESHVIEW,
                'content' => 'En MeshView a veces se solapan los enlaces MQTT de nodos que están muy juntos en el centro de Sevilla y cuesta distinguirlos con el zoom medio. ¿Se podría configurar agrupación (clustering) de marcadores?',
                'status' => Suggestion::STATUS_PENDING,
                'operator_notes' => null,
                'ip_hash' => hash('sha256', '192.168.1.13_seed_suggestion_4'),
                'created_at' => $now->copy()->subDays(3)->subHours(8),
                'updated_at' => $now->copy()->subDays(3)->subHours(8),
            ],
            [
                'category' => Suggestion::CATEGORY_POTATO_MESH,
                'content' => 'En PotatoMesh para dispositivos móviles vendría bien que los canales provinciales vengan preconfigurados en la lista de favoritos para no tener que escribirlos a mano cada vez.',
                'status' => Suggestion::STATUS_APPROVED,
                'operator_notes' => 'Se incluye en la documentación de bienvenida y en el archivo de configuración inicial para la app.',
                'ip_hash' => hash('sha256', '192.168.1.14_seed_suggestion_5'),
                'created_at' => $now->copy()->subDays(7)->subHours(2),
                'updated_at' => $now->copy()->subDays(6)->subHours(10),
            ],
            [
                'category' => Suggestion::CATEGORY_OTHER,
                'content' => '¿Podemos cambiar la frecuencia general a 433 MHz para tener más alcance en interiores en vez de los 868 MHz habituales?',
                'status' => Suggestion::STATUS_REJECTED,
                'operator_notes' => 'Descartado por normativa y consenso comunitario: 868 MHz es la frecuencia estándar adoptada para toda la malla en España e interoperabilidad con Europa.',
                'ip_hash' => hash('sha256', '192.168.1.15_seed_suggestion_6'),
                'created_at' => $now->copy()->subDays(14)->subHours(4),
                'updated_at' => $now->copy()->subDays(13)->subHours(14),
            ],
            [
                'category' => Suggestion::CATEGORY_WEB,
                'content' => 'En la sección de hardware vendría bien poner enlaces o recomendaciones de cajas estancas IP67 impresas en 3D (archivos STL/STEP) con soporte para panel solar y conector N o SMA.',
                'status' => Suggestion::STATUS_PENDING,
                'operator_notes' => null,
                'ip_hash' => hash('sha256', '192.168.1.16_seed_suggestion_7'),
                'created_at' => $now->copy()->subHours(12),
                'updated_at' => $now->copy()->subHours(12),
            ],
            [
                'category' => Suggestion::CATEGORY_BOT_TELEGRAM,
                'content' => '¿El comando /nodos en Telegram podría filtrar por provincia? Por ejemplo /nodos cadiz o /nodos malaga para ver directamente los nodos activos en nuestra zona.',
                'status' => Suggestion::STATUS_PENDING,
                'operator_notes' => null,
                'ip_hash' => hash('sha256', '192.168.1.17_seed_suggestion_8'),
                'created_at' => $now->copy()->subDays(4)->subHours(7),
                'updated_at' => $now->copy()->subDays(4)->subHours(7),
            ],
            [
                'category' => Suggestion::CATEGORY_NEW_FEATURE,
                'content' => 'Proponemos añadir un generador de tarjetas QR para imprimir y pegar en las cajas de los repetidores con la clave y canal de emergencia del pueblo o comarca.',
                'status' => Suggestion::STATUS_APPROVED,
                'operator_notes' => 'Integrado en el nuevo configurador web con exportación de código QR y canal provincial directo.',
                'ip_hash' => hash('sha256', '192.168.1.18_seed_suggestion_9'),
                'created_at' => $now->copy()->subDays(6)->subHours(11),
                'updated_at' => $now->copy()->subDays(5)->subHours(9),
            ],
            [
                'category' => Suggestion::CATEGORY_OTHER,
                'content' => '¿Hay algún grupo de compras conjuntas o repositorio de impresiones 3D para nodos de exterior en la Bahía de Cádiz? Me gustaría colaborar aportando filamento ASA.',
                'status' => Suggestion::STATUS_PENDING,
                'operator_notes' => null,
                'ip_hash' => hash('sha256', '192.168.1.19_seed_suggestion_10'),
                'created_at' => $now->copy()->subHours(6),
                'updated_at' => $now->copy()->subHours(6),
            ],
        ];

        foreach ($suggestions as $data) {
            Suggestion::create($data);
        }
    }
}
