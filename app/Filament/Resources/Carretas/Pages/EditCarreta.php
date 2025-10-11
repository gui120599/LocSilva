<?php

namespace App\Filament\Resources\Carretas\Pages;

use App\Filament\Resources\Carretas\CarretaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarreta extends EditRecord
{
    protected static string $resource = CarretaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
