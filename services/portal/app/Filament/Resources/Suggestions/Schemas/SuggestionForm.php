<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suggestions\Schemas;

use App\Models\Suggestion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Esquema del formulario de gestión de sugerencias en Filament.
 */
class SuggestionForm
{
    /**
     * Configura los campos del formulario organizados en secciones legibles.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle de la Sugerencia')
                    ->description('Información y contenido enviado por el usuario.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('category')
                                ->label('Categoría')
                                ->options(Suggestion::CATEGORIES)
                                ->required(),

                            Select::make('status')
                                ->label('Estado')
                                ->options(Suggestion::STATUSES)
                                ->default(Suggestion::STATUS_PENDING)
                                ->required(),
                        ]),

                        Textarea::make('content')
                            ->label('Propuesta / Sugerencia')
                            ->rows(6)
                            ->required(),
                    ]),

                Section::make('Gestión Interna (Operador)')
                    ->description('Anotaciones y metadatos visibles exclusivamente para administradores.')
                    ->schema([
                        Textarea::make('operator_notes')
                            ->label('Notas del operador')
                            ->placeholder('Añade comentarios internos sobre la viabilidad, estado o respuesta de esta sugerencia...')
                            ->rows(4),

                        Grid::make(2)->schema([
                            TextInput::make('ip_hash')
                                ->label('Hash identificador (anonimizado)')
                                ->disabled()
                                ->dehydrated(false),

                            DateTimePicker::make('created_at')
                                ->label('Fecha de recepción')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),
            ]);
    }
}
