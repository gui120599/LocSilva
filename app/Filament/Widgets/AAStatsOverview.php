<?php

namespace App\Filament\Widgets;

use App\Models\Aluguel;
use App\Models\Carreta;
use App\Models\Cliente;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AAStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $carretas = Carreta::all()->count();
        $alugueis = Aluguel::all()->count();
        $clientes = Cliente::all()->count();


        return [
            Stat::make('Carretas/Reboques', $carretas)->icon('heroicon-o-truck'),
            Stat::make('Alugueis', $alugueis)->icon('heroicon-o-banknotes'),
            Stat::make('Clientes', $clientes)->icon('heroicon-o-user-group'),
        ];
    }
}
