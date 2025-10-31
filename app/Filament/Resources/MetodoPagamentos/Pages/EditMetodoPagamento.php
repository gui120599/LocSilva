<?php

namespace App\Filament\Resources\MetodoPagamentos\Pages;

use App\Filament\Resources\MetodoPagamentos\MetodoPagamentoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMetodoPagamento extends EditRecord
{
    protected static string $resource = MetodoPagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
