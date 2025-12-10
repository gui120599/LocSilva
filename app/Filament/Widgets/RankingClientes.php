<?php

namespace App\Filament\Widgets;

use App\Models\Aluguel;
use App\Models\Cliente;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RankingClientes extends ChartWidget
{
    protected ?string $heading = '⭐Top 10 Clientes — Aluguéis';

    protected int | string | array $columnSpan = '2';

    protected static ?int $sort = 3;

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hoje',
            'week'  => 'Últimos 7 dias',
            'month' => 'Últimos 30 dias',
            'year'  => 'Este ano',
            'all'   => 'Todo período',
        ];
    }

    protected function getData(): array
    {
        // Filtros dinâmicos
        $query = Aluguel::query();

        match ($this->filter) {
            'today' => $query->whereDate('alugueis.created_at', today()),
            'week'  => $query->whereBetween('alugueis.created_at', [now()->subDays(7), now()]),
            'month' => $query->whereBetween('alugueis.created_at', [now()->subDays(30), now()]),
            'year'  => $query->whereYear('alugueis.created_at', now()->year),
            default => null,
        };

        $ranking = $query
            ->select('clientes.nome', DB::raw('COUNT(alugueis.id) as total'))
            ->join('clientes', 'clientes.id', '=', 'alugueis.cliente_id')
            ->groupBy('clientes.id', 'clientes.nome')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Aluguéis',
                    'data' => $ranking->pluck('total'),
                    'backgroundColor' => $ranking->pluck('total')->map(function ($value) {
                        return $value > 5 ? '#0ea5e9' : '#93c5fd'; // Azul forte ou claro
                    }),
                ],
            ],
            'labels' => $ranking->pluck('nome'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',

            'animation' => [
                'duration' => 800,
                'easing' => 'easeOutQuart',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
