<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de pregunta frecuente en el panel Filament.
 */
class CreateFaq extends CreateRecord
{
    protected static string $resource = FaqResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
