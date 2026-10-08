<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomPages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Esquema del formulario de gestión de páginas en Filament.
 */
class CustomPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.custom_pages.section_general'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('admin.custom_pages.field_title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, mixed $state, callable $set): void {
                                        if ($operation === 'create' && is_string($state) && $state !== '') {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->label(__('admin.custom_pages.field_slug'))
                                    ->helperText(__('admin.custom_pages.field_slug_helper'))
                                    ->required()
                                    ->alphaDash()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ]),

                        Textarea::make('description')
                            ->label(__('admin.custom_pages.field_description'))
                            ->helperText(__('admin.custom_pages.field_description_helper'))
                            ->required()
                            ->rows(3)
                            ->maxLength(500),

                        MarkdownEditor::make('content')
                            ->label(__('admin.custom_pages.field_content'))
                            ->required()
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
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.custom_pages.section_media_seo'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->label(__('admin.custom_pages.field_featured_image'))
                                    ->disk('public')
                                    ->directory('paginas')
                                    ->image()
                                    ->imageEditor()
                                    ->nullable(),

                                TagsInput::make('keywords')
                                    ->label(__('admin.custom_pages.field_keywords'))
                                    ->helperText(__('admin.custom_pages.field_keywords_helper'))
                                    ->placeholder('Añadir keyword...'),
                            ]),

                        Toggle::make('is_active')
                            ->label(__('admin.custom_pages.field_is_active'))
                            ->default(true),
                    ]),
            ]);
    }
}
