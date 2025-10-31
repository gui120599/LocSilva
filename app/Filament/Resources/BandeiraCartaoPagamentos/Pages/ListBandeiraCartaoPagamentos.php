<?php

namespace App\Filament\Resources\BandeiraCartaoPagamentos\Pages;

use App\Filament\Resources\BandeiraCartaoPagamentos\BandeiraCartaoPagamentoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBandeiraCartaoPagamentos extends ListRecords
{
    protected static string $resource = BandeiraCartaoPagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
