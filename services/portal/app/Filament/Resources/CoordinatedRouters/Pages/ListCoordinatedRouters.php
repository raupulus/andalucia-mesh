<?php

declare(strict_types=1);

namespace App\Filament\Resources\CoordinatedRouters\Pages;

use App\Filament\Resources\CoordinatedRouters\CoordinatedRouterResource;
use App\Models\CoordinatedRouter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Página de listado y sincronización de routers coordinados en Filament.
 */
class ListCoordinatedRouters extends ListRecords
{
    protected static string $resource = CoordinatedRouterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('admin.coordinated_routers.btn_create')),

            Action::make('sync_from_mesh')
                ->label(__('admin.coordinated_routers.btn_sync'))
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading(__('admin.coordinated_routers.btn_sync'))
                ->modalDescription('Se importarán y actualizarán los routers detectados en las 8 provincias de Andalucía desde la base de ingesta. Los nodos fuera de Andalucía serán descartados automáticamente.')
                ->action(function (): void {
                    try {
                        $provinciasAndalucia = array_keys(CoordinatedRouter::PROVINCES);

                        $nodes = DB::connection('ingesta')
                            ->table('api_routers')
                            ->whereIn('province', $provinciasAndalucia)
                            ->whereIn('role', ['ROUTER', 'ROUTER_LATE', 'REPEATER'])
                            ->get();

                        $count = 0;
                        foreach ($nodes as $n) {
                            $prov = strtoupper((string) ($n->province ?? ''));
                            // Descarte estricto de nodos sin provincia o fuera de Andalucía
                            if ($prov === '' || $prov === 'FUERA' || ! in_array($prov, $provinciasAndalucia, true)) {
                                continue;
                            }

                            CoordinatedRouter::updateOrCreate(
                                ['node_id' => (string) $n->id],
                                [
                                    'short_name' => $n->short_name,
                                    'long_name' => $n->long_name,
                                    'province' => $prov,
                                    'role' => (string) $n->role,
                                    'hw_model' => $n->hw_model,
                                    'is_gateway' => (bool) $n->is_gateway,
                                    'last_seen_at' => $n->last_seen ? Carbon::parse((string) $n->last_seen) : null,
                                ]
                            );
                            $count++;
                        }

                        Notification::make()
                            ->title(__('admin.coordinated_routers.sync_success', ['count' => $count]))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al sincronizar routers de la malla: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
