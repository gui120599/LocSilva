<?php

namespace App\Filament\Resources\Aluguels\Pages;

use App\Filament\Resources\Aluguels\AluguelResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class Aluguel extends Page
{
    protected static string $resource = AluguelResource::class;

    public $record;
    public $aluguel;

    public function mount($record)
    {
        $this->record = $record;
        $this->aluguel = \App\Models\Aluguel::find($record);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('print')
            ->label('Print')
            ->icon('heroicon-s-printer')
            ->requiresConfirmation(true)
            ->url(route('print-aluguel',['id' => $this->record]))
        ];
    }

    protected string $view = 'filament.resources.aluguels.pages.aluguel';
}
