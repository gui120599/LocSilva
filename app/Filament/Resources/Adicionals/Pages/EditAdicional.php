<?php

namespace App\Filament\Resources\Adicionals\Pages;

use App\Filament\Resources\Adicionals\AdicionalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAdicional extends EditRecord
{
    protected static string $resource = AdicionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
