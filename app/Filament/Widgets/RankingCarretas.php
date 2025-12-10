<?php

namespace App\Filament\Widgets;

use App\Models\Aluguel;
use App\Models\Carreta;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RankingCarretas extends ChartWidget
{
    protected ?string $heading = '⭐Top 10 Carretas — Aluguéis';

    protected static ?int $sort = 4;

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
        $query = Aluguel::query();

        match ($this->filter) {
            'today' => $query->whereDate('alugueis.created_at', today()),
            'week'  => $query->whereBetween('alugueis.created_at', [now()->subDays(7), now()]),
            'month' => $query->whereBetween('alugueis.created_at', [now()->subDays(30), now()]),
            'year'  => $query->whereYear('alugueis.created_at', now()->year),
            default => null,
        };

        $ranking = $query
            ->select('carretas.identificacao', DB::raw('COUNT(alugueis.id) as total'))
            ->join('carretas', 'carretas.id', '=', 'alugueis.carreta_id')
            ->groupBy('carretas.id', 'carretas.identificacao')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Aluguéis',
                    'data' => $ranking->pluck('total'),
                    'backgroundColor' => $ranking->pluck('total')->map(function ($value) {
                        return $value > 5 ? '#f97316' : '#fdba74'; // Laranja forte e claro
                    }),
                ],
            ],
            'labels' => $ranking->pluck('identificacao'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',

            'animation' => [
                'duration' => 800,
                'easing' => 'easeOutExpo',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
