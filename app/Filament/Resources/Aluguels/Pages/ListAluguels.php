<?php

namespace App\Filament\Resources\Aluguels\Pages;

use App\Filament\Resources\Aluguels\AluguelResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAluguels extends ListRecords
{
    protected static string $resource = AluguelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('relatório')
                ->icon('heroicon-o-document-text')
                ->url(AluguelResource::getUrl('relatorio')),
        ];
    }
}
