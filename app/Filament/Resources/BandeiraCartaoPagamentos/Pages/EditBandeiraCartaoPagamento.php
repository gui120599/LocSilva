<?php

namespace App\Filament\Resources\BandeiraCartaoPagamentos\Pages;

use App\Filament\Resources\BandeiraCartaoPagamentos\BandeiraCartaoPagamentoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBandeiraCartaoPagamento extends EditRecord
{
    protected static string $resource = BandeiraCartaoPagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
