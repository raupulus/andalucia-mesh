<?php

declare(strict_types=1);

namespace App\Filament\Resources\Webhooks\Tables;

use App\Models\WebhookDestination;
use App\Servicios\WebhooksClient;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de destinos de webhooks en Filament.
 */
class WebhookDestinationsTable
{
    /**
     * Define las columnas operativas y las acciones por fila (probar ping, reactivar, editar, eliminar).
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label(__('admin.webhooks.col_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('host')
                    ->label(__('admin.webhooks.col_host'))
                    ->searchable()
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('filtros_activos')
                    ->label(__('admin.webhooks.col_filters'))
                    ->state(function (WebhookDestination $record): array {
                        $badges = [];
                        if (! empty($record->riesgos)) {
                            foreach ($record->riesgos as $r) {
                                $badges[] = "riesgo: {$r}";
                            }
                        }
                        if (! empty($record->tipos)) {
                            foreach ($record->tipos as $t) {
                                $badges[] = "tipo: {$t}";
                            }
                        }
                        if (! empty($record->provincias)) {
                            foreach ($record->provincias as $p) {
                                $badges[] = "prov: {$p}";
                            }
                        }
                        if (! empty($record->nodos)) {
                            foreach ($record->nodos as $n) {
                                $badges[] = "nodo: {$n}";
                            }
                        }

                        return empty($badges) ? ['Sin filtros'] : $badges;
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Sin filtros' ? 'gray' : 'info')
                    ->wrap(),

                TextColumn::make('estado')
                    ->label(__('admin.webhooks.col_status'))
                    ->state(function (WebhookDestination $record): string {
                        if ($record->activo) {
                            return 'Activo';
                        }
                        $motivo = $record->motivo_baja ? " ({$record->motivo_baja})" : '';

                        return "Suspendido{$motivo}";
                    })
                    ->badge()
                    ->color(fn (WebhookDestination $record): string => $record->activo ? 'success' : 'danger'),

                TextColumn::make('fallos_seguidos')
                    ->label(__('admin.webhooks.col_failures'))
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('pendientes')
                    ->label(__('admin.webhooks.col_pending'))
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('ultimo_ok')
                    ->label(__('admin.webhooks.col_last_ok'))
                    ->dateTime('d/m/Y H:i:s')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('probar')
                    ->label(__('admin.webhooks.action_ping'))
                    ->icon(Heroicon::OutlinedBolt)
                    ->color('warning')
                    ->action(function (WebhookDestination $record, WebhooksClient $client): void {
                        try {
                            $res = $client->testDestination($record->nombre);
                            $code = $res['status_code'];
                            $ms = $res['duration_ms'];

                            if ($res['ok']) {
                                Notification::make()
                                    ->success()
                                    ->title("Ping exitoso (HTTP {$code}) en {$ms} ms")
                                    ->body('El receptor respondió correctamente y validó la firma HMAC-SHA256.')
                                    ->send();
                            } else {
                                $error = $res['error'] ?? 'Fallo en la comunicación con el destino';
                                $codeInfo = $code > 0 ? "Código: HTTP {$code}" : 'Sin respuesta del servicio';
                                $timing = $ms > 0 ? " ({$ms} ms)" : '';

                                Notification::make()
                                    ->danger()
                                    ->title('Fallo al probar destino (Ping)')
                                    ->body("{$codeInfo}{$timing}. {$error}")
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error al conectar con el servicio webhooks')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('reactivar')
                    ->label(__('admin.webhooks.action_reactivate'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('success')
                    ->visible(fn (WebhookDestination $record): bool => ! $record->activo)
                    ->requiresConfirmation()
                    ->action(function (WebhookDestination $record, WebhooksClient $client): void {
                        try {
                            $client->reactivateDestination($record->nombre);
                            $record->update([
                                'activo' => true,
                                'fallos_seguidos' => 0,
                                'motivo_baja' => null,
                            ]);
                            Notification::make()
                                ->success()
                                ->title("Destino '{$record->nombre}' reactivado con éxito")
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error al reactivar destino')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                EditAction::make()
                    ->label(__('admin.webhooks.action_edit')),

                DeleteAction::make()
                    ->label(__('admin.webhooks.action_delete'))
                    ->before(function (WebhookDestination $record, WebhooksClient $client): void {
                        try {
                            $client->deleteDestination($record->nombre);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->warning()
                                ->title('Aviso de baja en microservicio')
                                ->body('El destino se retiró localmente, pero el microservicio no respondió: '.$e->getMessage())
                                ->send();
                        }
                    }),
            ]);
    }
}
