<?php

declare(strict_types=1);

namespace App\Filament\Resources\CoordinatedRouters\Pages;

use App\Filament\Resources\CoordinatedRouters\CoordinatedRouterResource;
use App\Models\CoordinatedRouter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Página de listado y sincronización de routers coordinados en Filament.
 */
class ListCoordinatedRouters extends ListRecords
{
    protected static string $resource = CoordinatedRouterResource::class;

    public function getSubheading(): ?string
    {
        return __('admin.coordinated_routers.list_subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('admin.coordinated_routers.btn_create')),

            Action::make('sync_from_mesh')
                ->label(__('admin.coordinated_routers.btn_sync'))
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false))
                ->requiresConfirmation()
                ->modalHeading(__('admin.coordinated_routers.btn_sync'))
                ->modalDescription('Se importarán y actualizarán los routers detectados en las 8 provincias de Andalucía desde la base de ingesta. Los nodos no registrados se darán de alta como «Nuevos», mientras que los ya existentes mantendrán su estado de gestión y notas.')
                ->action(function (): void {
                    try {
                        self::ensureApiRoutersTableExists();

                        $provinciasAndalucia = array_keys(CoordinatedRouter::PROVINCES);

                        $nodes = DB::connection('ingesta')
                            ->table('api_routers')
                            ->whereIn('province', $provinciasAndalucia)
                            ->whereIn('role', ['ROUTER', 'ROUTER_LATE', 'REPEATER'])
                            ->get();

                        $countNew = 0;
                        $countUpdated = 0;

                        foreach ($nodes as $n) {
                            $prov = strtoupper((string) ($n->province ?? ''));
                            // Descarte estricto de nodos sin provincia o fuera de Andalucía
                            if ($prov === '' || $prov === 'FUERA' || ! in_array($prov, $provinciasAndalucia, true)) {
                                continue;
                            }

                            // Descarte estricto de routers sin coordenadas GPS válidas dentro del territorio andaluz
                            $lat = $n->latitude !== null ? (float) $n->latitude : null;
                            $lon = $n->longitude !== null ? (float) $n->longitude : null;
                            if (! CoordinatedRouter::isWithinAndaluciaBounds($lat, $lon)) {
                                continue;
                            }

                            // Descarte de nombres genéricos o ruido autogenerado
                            $lName = trim((string) ($n->long_name ?? ''));
                            if (empty($lName) || preg_match('/^(Router|Meshtastic)\s+[0-9a-fA-F]+$/i', $lName)) {
                                continue;
                            }

                            $nodeId = (string) $n->id;
                            $router = CoordinatedRouter::where('node_id', $nodeId)->first();

                            if ($router === null) {
                                // Router nuevo detectado en la malla: se crea con estado 'new'
                                CoordinatedRouter::create([
                                    'node_id' => $nodeId,
                                    'short_name' => $n->short_name,
                                    'long_name' => $n->long_name,
                                    'province' => $prov,
                                    'latitude' => $lat,
                                    'longitude' => $lon,
                                    'role' => (string) $n->role,
                                    'status' => CoordinatedRouter::STATUS_NEW,
                                    'hw_model' => $n->hw_model,
                                    'is_gateway' => (bool) $n->is_gateway,
                                    'last_seen_at' => $n->last_seen ? Carbon::parse((string) $n->last_seen) : null,
                                ]);
                                $countNew++;
                            } else {
                                // Router existente: actualizamos telemetría preservando su estado (managed/known) y notas
                                $router->update([
                                    'short_name' => $n->short_name,
                                    'long_name' => $n->long_name,
                                    'province' => $prov,
                                    'latitude' => $lat,
                                    'longitude' => $lon,
                                    'role' => (string) $n->role,
                                    'hw_model' => $n->hw_model,
                                    'is_gateway' => (bool) $n->is_gateway,
                                    'last_seen_at' => $n->last_seen ? Carbon::parse((string) $n->last_seen) : null,
                                ]);
                                $countUpdated++;
                            }
                        }

                        if ($countNew === 0 && $countUpdated === 0) {
                            Notification::make()
                                ->title(__('admin.coordinated_routers.sync_empty'))
                                ->info()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('admin.coordinated_routers.sync_summary', ['new' => $countNew, 'updated' => $countUpdated]))
                                ->success()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al sincronizar routers de la malla: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    /**
     * Asegura la existencia de la tabla o vista api_routers en la conexión de ingesta.
     * En entornos locales o pruebas con SQLite, crea la estructura y puebla con nodos de ejemplo.
     */
    public static function ensureApiRoutersTableExists(): void
    {
        $connection = DB::connection('ingesta');
        $driver = $connection->getDriverName();
        $schema = Schema::connection('ingesta');

        if (! $schema->hasTable('api_routers') && ! $schema->hasView('api_routers')) {
            if ($driver === 'sqlite') {
                $schema->create('api_routers', function (Blueprint $table) {
                    $table->string('id', 32)->primary();
                    $table->string('short_name', 32)->nullable();
                    $table->string('long_name', 128)->nullable();
                    $table->string('role', 32)->default('ROUTER');
                    $table->string('province', 10)->nullable();
                    $table->decimal('latitude', 10, 7)->nullable();
                    $table->decimal('longitude', 10, 7)->nullable();
                    $table->integer('battery_level')->nullable();
                    $table->decimal('voltage', 5, 2)->nullable();
                    $table->timestamp('battery_at')->nullable();
                    $table->decimal('channel_utilization', 5, 2)->nullable();
                    $table->decimal('air_util_tx', 5, 2)->nullable();
                    $table->timestamp('metrics_at')->nullable();
                    $table->timestamp('last_seen')->nullable();
                    $table->string('hw_model', 64)->nullable();
                    $table->boolean('powered')->default(false);
                    $table->boolean('is_gateway')->default(false);
                    $table->timestamp('last_reboot_at')->nullable();
                });
            } else {
                throw new \RuntimeException('La vista de contrato api_routers no existe en la base de datos de ingesta. Verifique las migraciones del servicio de ingesta.');
            }
        } elseif ($driver === 'sqlite' && $schema->hasTable('api_routers')) {
            if (! $schema->hasColumn('api_routers', 'latitude')) {
                $schema->table('api_routers', function (Blueprint $table) {
                    $table->decimal('latitude', 10, 7)->nullable();
                    $table->decimal('longitude', 10, 7)->nullable();
                });
            }
        }

        // Si estamos en SQLite y la tabla está vacía, poblamos con routers de ejemplo de la malla andaluza
        if ($driver === 'sqlite' && $connection->table('api_routers')->count() === 0) {
            $now = Carbon::now();
            $connection->table('api_routers')->insert([
                [
                    'id' => '!2a3b4c5d',
                    'short_name' => 'SE01',
                    'long_name' => 'Router Sevilla Aljarafe',
                    'role' => 'ROUTER',
                    'province' => 'ES-SE',
                    'latitude' => 37.3886,
                    'longitude' => -6.0024,
                    'battery_level' => 95,
                    'voltage' => 4.12,
                    'channel_utilization' => 14.5,
                    'air_util_tx' => 2.1,
                    'hw_model' => 'TLORA_V2_1_16',
                    'powered' => 0,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!3b4c5d6e',
                    'short_name' => 'CA01',
                    'long_name' => 'Router Cádiz Sierra Grazalema',
                    'role' => 'ROUTER',
                    'province' => 'ES-CA',
                    'latitude' => 36.7583,
                    'longitude' => -5.3672,
                    'battery_level' => 101,
                    'voltage' => 5.00,
                    'channel_utilization' => 8.2,
                    'air_util_tx' => 1.4,
                    'hw_model' => 'HELTEC_V3',
                    'powered' => 1,
                    'is_gateway' => 1,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!4c5d6e7f',
                    'short_name' => 'MA01',
                    'long_name' => 'Repetidor Málaga Gibralfaro',
                    'role' => 'REPEATER',
                    'province' => 'ES-MA',
                    'latitude' => 36.7213,
                    'longitude' => -4.4214,
                    'battery_level' => 88,
                    'voltage' => 3.98,
                    'channel_utilization' => 22.1,
                    'air_util_tx' => 4.8,
                    'hw_model' => 'RAK4631',
                    'powered' => 0,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!5d6e7f80',
                    'short_name' => 'GR01',
                    'long_name' => 'Router Granada Sierra Nevada',
                    'role' => 'ROUTER',
                    'province' => 'ES-GR',
                    'latitude' => 37.0542,
                    'longitude' => -3.3934,
                    'battery_level' => 76,
                    'voltage' => 3.85,
                    'channel_utilization' => 12.0,
                    'air_util_tx' => 1.9,
                    'hw_model' => 'STATION_G2',
                    'powered' => 0,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!6e7f8091',
                    'short_name' => 'CO01',
                    'long_name' => 'Router Córdoba Medina Azahara',
                    'role' => 'ROUTER_LATE',
                    'province' => 'ES-CO',
                    'latitude' => 37.8860,
                    'longitude' => -4.7794,
                    'battery_level' => 101,
                    'voltage' => 5.00,
                    'channel_utilization' => 16.3,
                    'air_util_tx' => 2.5,
                    'hw_model' => 'HELTEC_V3',
                    'powered' => 1,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!7f8091a2',
                    'short_name' => 'H01',
                    'long_name' => 'Router Huelva Punta Umbría',
                    'role' => 'ROUTER',
                    'province' => 'ES-H',
                    'latitude' => 37.1824,
                    'longitude' => -6.9660,
                    'battery_level' => 65,
                    'voltage' => 3.75,
                    'channel_utilization' => 9.8,
                    'air_util_tx' => 1.2,
                    'hw_model' => 'TLORA_V2_1_16',
                    'powered' => 0,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!8091a2b3',
                    'short_name' => 'J01',
                    'long_name' => 'Router Jaén Santa Catalina',
                    'role' => 'ROUTER',
                    'province' => 'ES-J',
                    'latitude' => 37.7692,
                    'longitude' => -3.7903,
                    'battery_level' => 90,
                    'voltage' => 4.05,
                    'channel_utilization' => 11.2,
                    'air_util_tx' => 1.8,
                    'hw_model' => 'RAK4631',
                    'powered' => 0,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!91a2b3c4',
                    'short_name' => 'AL01',
                    'long_name' => 'Router Almería Cabo de Gata',
                    'role' => 'ROUTER',
                    'province' => 'ES-AL',
                    'latitude' => 36.7823,
                    'longitude' => -2.2425,
                    'battery_level' => 82,
                    'voltage' => 3.92,
                    'channel_utilization' => 15.0,
                    'air_util_tx' => 2.2,
                    'hw_model' => 'HELTEC_V3',
                    'powered' => 0,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!a2b3c4d5',
                    'short_name' => 'EXT1',
                    'long_name' => 'Router Fuera Badajoz',
                    'role' => 'ROUTER',
                    'province' => 'FUERA',
                    'latitude' => 38.8794,
                    'longitude' => -6.9706,
                    'battery_level' => 80,
                    'voltage' => 3.90,
                    'channel_utilization' => 5.0,
                    'air_util_tx' => 0.5,
                    'hw_model' => 'HELTEC_V3',
                    'powered' => 0,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
                [
                    'id' => '!b3c4d5e6',
                    'short_name' => 'AB01',
                    'long_name' => 'Router Albacete Los Llanos',
                    'role' => 'ROUTER',
                    'province' => 'ES-AB',
                    'latitude' => 38.9943,
                    'longitude' => -1.8585,
                    'battery_level' => 88,
                    'voltage' => 4.02,
                    'channel_utilization' => 9.5,
                    'air_util_tx' => 1.1,
                    'hw_model' => 'HELTEC_V3',
                    'powered' => 0,
                    'is_gateway' => 0,
                    'last_seen' => $now->toDateTimeString(),
                ],
            ]);
        } elseif ($driver === 'sqlite') {
            $coords = [
                '!2a3b4c5d' => ['latitude' => 37.3886, 'longitude' => -6.0024],
                '!3b4c5d6e' => ['latitude' => 36.7583, 'longitude' => -5.3672],
                '!4c5d6e7f' => ['latitude' => 36.7213, 'longitude' => -4.4214],
                '!5d6e7f80' => ['latitude' => 37.0542, 'longitude' => -3.3934],
                '!6e7f8091' => ['latitude' => 37.8860, 'longitude' => -4.7794],
                '!7f8091a2' => ['latitude' => 37.1824, 'longitude' => -6.9660],
                '!8091a2b3' => ['latitude' => 37.7692, 'longitude' => -3.7903],
                '!91a2b3c4' => ['latitude' => 36.7823, 'longitude' => -2.2425],
                '!a2b3c4d5' => ['latitude' => 38.8794, 'longitude' => -6.9706],
                '!b3c4d5e6' => ['latitude' => 38.9943, 'longitude' => -1.8585],
            ];
            foreach ($coords as $nodeId => $c) {
                $connection->table('api_routers')->where('id', $nodeId)->whereNull('latitude')->update($c);
            }
        }
    }
}
