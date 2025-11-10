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
                ->label('Imprimir Contrato')
                ->icon('heroicon-s-printer')
                ->color('primary')
                // A CHAVE: Passar o registro ($record) para a rota dentro do closure
                ->url(fn(): string => route('print-aluguel', ['id' => $this->record]))
                ->openUrlInNewTab()
            // REMOVIDO: requiresConfirmation(true)
        ];
    }

    protected string $view = 'filament.resources.aluguels.pages.aluguel';
}
