<?php

namespace App\Filament\Resources\Aluguels\Pages;

use App\Filament\Resources\Aluguels\AluguelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAluguel extends CreateRecord
{
    protected static string $resource = AluguelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
