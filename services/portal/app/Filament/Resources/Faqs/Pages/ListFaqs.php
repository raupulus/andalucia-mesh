<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listado de preguntas frecuentes en el panel Filament.
 */
class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('admin.faqs.btn_create')),
        ];
    }
}
