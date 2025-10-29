<?php

namespace App\Filament\Resources\Aluguels\Pages;

use App\Filament\Resources\Aluguels\AluguelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAluguel extends EditRecord
{
    protected static string $resource = AluguelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
