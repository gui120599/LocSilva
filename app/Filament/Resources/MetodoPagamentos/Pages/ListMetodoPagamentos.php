<?php

namespace App\Filament\Resources\MetodoPagamentos\Pages;

use App\Filament\Resources\MetodoPagamentos\MetodoPagamentoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMetodoPagamentos extends ListRecords
{
    protected static string $resource = MetodoPagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
