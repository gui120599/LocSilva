<?php

namespace App\Filament\Widgets;

use App\Models\Aluguel;
use App\Models\Carreta;
use App\Models\Cliente;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $carretas = Carreta::all()->count();
        $carretasAlugadas = Carreta::where('status', 'alugada')->count();
        $carretasDisponiveis = Carreta::where('status', 'disponivel')->count();
        $carretasManutencao = Carreta::where('status', 'manutencao')->count();
        $alugueis = Aluguel::all()->count();
        $alugueisAtivos = Aluguel::where('status', 'ativo')->count();
        $alugueisPendentes = Aluguel::where('status', 'pendente')->count();
        $alugueisFinalizados = Aluguel::where('status', 'finalizado')->count();
        $alugueisCancelados = Aluguel::where('status', 'cancelado')->count();
        $clientes = Cliente::all()->count();

        $clientesComAlugueisAtivos = Cliente::whereHas('alugueis', function ($query) {
            $query->where('status', 'ativo');
        })->count();

        $clientesComAlugueisPendentes = Cliente::whereHas('alugueis', function ($query) {
            $query->where('status', 'pendente');
        })->count();


        return [
            Grid::make()
                ->columnSpanFull()
                ->columns(4)
                ->schema([
                    Stat::make('Aluguéis', $alugueis)
                        ->columnSpan(4)
                        ->description($alugueisAtivos . ' Ativos - ' . $alugueisPendentes . ' Pendentes - ' . $alugueisFinalizados . ' Finalizados - ' . $alugueisCancelados . ' Cancelados')
                        ->icon('heroicon-o-banknotes'),

                    Stat::make('Carretas/Reboques', $carretas)
                        ->columnSpan(2)
                        ->description($carretasAlugadas . ' Alugadas - ' . $carretasDisponiveis . ' Disponíveis - ' . $carretasManutencao . ' em Manutenção')
                        ->icon('heroicon-o-truck'),

                    Stat::make('Clientes', $clientes)
                        ->columnSpan(2)
                        ->description($clientesComAlugueisAtivos . ' com Aluguéis Ativos - ' . $clientesComAlugueisPendentes . ' com ALugueis Pendentes')
                        ->icon('heroicon-o-user-group'),
                ]),


        ];
    }
}
