<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Esquema del formulario de gestión de preguntas frecuentes (FAQ) en Filament.
 */
class FaqForm
{
    /**
     * Configura los campos del formulario organizados en dos bloques principales: pregunta y respuesta.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.faqs.section_question'))
                    ->description(__('admin.faqs.section_question_desc'))
                    ->schema([
                        TextInput::make('question')
                            ->label(__('admin.faqs.field_question'))
                            ->placeholder(__('admin.faqs.placeholder_question'))
                            ->required()
                            ->maxLength(255),
                    ]),

                Section::make(__('admin.faqs.section_answer'))
                    ->description(__('admin.faqs.section_answer_desc'))
                    ->schema([
                        Textarea::make('answer')
                            ->label(__('admin.faqs.field_answer'))
                            ->placeholder(__('admin.faqs.placeholder_answer'))
                            ->rows(8)
                            ->required()
                            ->helperText(__('admin.faqs.helper_answer_markdown')),
                    ]),

                Section::make(__('admin.faqs.section_settings'))
                    ->description(__('admin.faqs.section_settings_desc'))
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')
                                ->label(__('admin.faqs.field_is_active'))
                                ->default(true)
                                ->required(),

                            TextInput::make('sort_order')
                                ->label(__('admin.faqs.field_sort_order'))
                                ->numeric()
                                ->default(0)
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
