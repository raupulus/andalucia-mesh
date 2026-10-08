<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

/**
 * Esquema del formulario de gestión de preguntas frecuentes (FAQ) en Filament con soporte multi-idioma (ES/EN/PT).
 */
class FaqForm
{
    /**
     * Configura los campos del formulario con selector de idiomas en la cabecera,
     * visibilidad a la derecha de la pregunta y editor de Markdown a ancho completo.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Idiomas')
                    ->tabs([
                        Tab::make(__('admin.faqs.tab_es'))
                            ->badge('ES')
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 12])
                                    ->schema([
                                        TextInput::make('question.es')
                                            ->label(__('admin.faqs.field_question_es'))
                                            ->placeholder(__('admin.faqs.placeholder_question'))
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(['default' => 1, 'md' => 9]),

                                        Toggle::make('is_active')
                                            ->label(__('admin.faqs.field_is_active'))
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(['default' => 1, 'md' => 3]),
                                    ]),

                                MarkdownEditor::make('answer.es')
                                    ->label(__('admin.faqs.field_answer_es'))
                                    ->placeholder(__('admin.faqs.placeholder_answer'))
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'strike',
                                        'link',
                                        'heading',
                                        'bulletList',
                                        'orderedList',
                                        'codeBlock',
                                        'blockquote',
                                        'undo',
                                        'redo',
                                    ])
                                    ->maxLength(1024)
                                    ->required()
                                    ->helperText(__('admin.faqs.helper_answer_markdown'))
                                    ->columnSpanFull(),
                            ]),

                        Tab::make(__('admin.faqs.tab_en'))
                            ->badge('EN')
                            ->schema([
                                TextInput::make('question.en')
                                    ->label(__('admin.faqs.field_question_en'))
                                    ->placeholder(__('admin.faqs.placeholder_question_en'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                MarkdownEditor::make('answer.en')
                                    ->label(__('admin.faqs.field_answer_en'))
                                    ->placeholder(__('admin.faqs.placeholder_answer_en'))
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'strike',
                                        'link',
                                        'heading',
                                        'bulletList',
                                        'orderedList',
                                        'codeBlock',
                                        'blockquote',
                                        'undo',
                                        'redo',
                                    ])
                                    ->maxLength(1024)
                                    ->helperText(__('admin.faqs.helper_answer_markdown'))
                                    ->columnSpanFull(),
                            ]),

                        Tab::make(__('admin.faqs.tab_pt'))
                            ->badge('PT')
                            ->schema([
                                TextInput::make('question.pt')
                                    ->label(__('admin.faqs.field_question_pt'))
                                    ->placeholder(__('admin.faqs.placeholder_question_pt'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                MarkdownEditor::make('answer.pt')
                                    ->label(__('admin.faqs.field_answer_pt'))
                                    ->placeholder(__('admin.faqs.placeholder_answer_pt'))
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'strike',
                                        'link',
                                        'heading',
                                        'bulletList',
                                        'orderedList',
                                        'codeBlock',
                                        'blockquote',
                                        'undo',
                                        'redo',
                                    ])
                                    ->maxLength(1024)
                                    ->helperText(__('admin.faqs.helper_answer_markdown'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
