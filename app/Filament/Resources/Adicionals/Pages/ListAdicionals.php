<?php

namespace App\Filament\Resources\Adicionals\Pages;

use App\Filament\Resources\Adicionals\AdicionalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdicionals extends ListRecords
{
    protected static string $resource = AdicionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
