<?php

namespace App\Filament\Resources\Carretas\Pages;

use App\Filament\Resources\Carretas\CarretaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarretas extends ListRecords
{
    protected static string $resource = CarretaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
