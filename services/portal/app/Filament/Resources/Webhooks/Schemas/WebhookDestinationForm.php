<?php

declare(strict_types=1);

namespace App\Filament\Resources\Webhooks\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Esquema del formulario de creación y edición de destinos de webhooks en Filament.
 */
class WebhookDestinationForm
{
    /**
     * Configura los campos del formulario organizados en secciones operativas.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('admin.webhooks.section_general'))
                    ->description(__('admin.webhooks.section_general_desc'))
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nombre')
                            ->label(__('admin.webhooks.field_name'))
                            ->required()
                            ->regex('/^[a-z0-9-]{1,40}$/')
                            ->maxLength(40)
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->helperText('Identificador único en minúsculas, números y guiones (ej. radio-club-cadiz).'),

                        TextInput::make('url')
                            ->label(__('admin.webhooks.field_url'))
                            ->required()
                            ->url()
                            ->startsWith('https://')
                            ->maxLength(2048)
                            ->helperText('URL pública y segura de recepción (HTTPS obligatorio con validación SSRF).'),

                        TextInput::make('secreto')
                            ->label(__('admin.webhooks.field_secret'))
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(32)
                            ->maxLength(128)
                            ->helperText('Clave secreta compartida para cálculo de firma HMAC-SHA256 (mínimo 32 caracteres).')
                            ->suffixAction(
                                Action::make('generarSecreto')
                                    ->label(__('admin.webhooks.btn_generate_secret'))
                                    ->icon(Heroicon::OutlinedKey)
                                    ->tooltip(__('admin.webhooks.btn_generate_secret'))
                                    ->action(function ($set): void {
                                        $secretoAleatorio = bin2hex(random_bytes(32));
                                        $set('secreto', $secretoAleatorio);
                                        Notification::make()
                                            ->success()
                                            ->title('Secreto seguro generado')
                                            ->body('Se ha generado un secreto aleatorio de 64 caracteres hexadecimales.')
                                            ->send();
                                    })
                            ),
                    ])
                    ->columns(1),

                Section::make(__('admin.webhooks.section_filters'))
                    ->description(__('admin.webhooks.section_filters_desc'))
                    ->icon(Heroicon::OutlinedFunnel)
                    ->columnSpanFull()
                    ->schema([
                        CheckboxList::make('riesgos')
                            ->label(__('admin.webhooks.field_risks'))
                            ->options([
                                'bajo' => __('admin.webhooks.risk_low'),
                                'medio' => __('admin.webhooks.risk_medium'),
                                'alto' => __('admin.webhooks.risk_high'),
                            ])
                            ->descriptions([
                                'bajo' => __('admin.webhooks.risk_low_desc'),
                                'medio' => __('admin.webhooks.risk_medium_desc'),
                                'alto' => __('admin.webhooks.risk_high_desc'),
                            ])
                            ->helperText(__('admin.webhooks.field_risks_helper'))
                            ->columns(3),

                        CheckboxList::make('tipos')
                            ->label(__('admin.webhooks.field_types'))
                            ->options([
                                'infraestructura' => __('admin.webhooks.type_infra'),
                                'clientes' => __('admin.webhooks.type_clients'),
                            ])
                            ->descriptions([
                                'infraestructura' => __('admin.webhooks.type_infra_desc'),
                                'clientes' => __('admin.webhooks.type_clients_desc'),
                            ])
                            ->helperText(__('admin.webhooks.field_types_helper'))
                            ->columns(2),

                        Select::make('provincias')
                            ->label(__('admin.webhooks.field_provinces'))
                            ->multiple()
                            ->options([
                                'ES-AL' => 'Almería (ES-AL)',
                                'ES-CA' => 'Cádiz (ES-CA)',
                                'ES-CO' => 'Córdoba (ES-CO)',
                                'ES-GR' => 'Granada (ES-GR)',
                                'ES-H' => 'Huelva (ES-H)',
                                'ES-J' => 'Jaén (ES-J)',
                                'ES-MA' => 'Málaga (ES-MA)',
                                'ES-SE' => 'Sevilla (ES-SE)',
                                'FUERA' => 'Fuera de Andalucía (FUERA)',
                            ])
                            ->searchable()
                            ->helperText('Filtre por una o más provincias andaluzas, o incluya nodos exteriores (FUERA). Si no selecciona ninguna, recibe todas.'),

                        TagsInput::make('nodos')
                            ->label(__('admin.webhooks.field_nodes'))
                            ->placeholder('!a1b2c3d4')
                            ->nestedRecursiveRules([
                                'regex:/^![0-9a-f]{8}$/',
                            ])
                            ->helperText('Identificadores de nodos Meshtastic en formato hexadecimal con exclamación (ej. !a1b2c3d4). Deje vacío para recibir todos.'),
                    ])
                    ->columns(1),
            ]);
    }
}
