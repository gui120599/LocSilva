<?php

namespace App\Filament\Resources\MovimentoCaixas\Pages;

use App\Filament\Resources\MovimentoCaixas\MovimentoCaixaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMovimentoCaixas extends ManageRecords
{
    protected static string $resource = MovimentoCaixaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
