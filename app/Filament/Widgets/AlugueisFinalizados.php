<?php

namespace App\Filament\Widgets;

use App\Models\Aluguel;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AlugueisFinalizados extends ChartWidget
{
    protected static ?int $sort = 5;
    
    protected ?string $heading = 'Alugueis por Mês';

    public ?string $filter = 'finalizado';

    protected int | string | array $columnSpan = 'full';

    protected function getFilters(): ?array
    {
        return [
            'ativo' => 'Ativos',
            'pendente' => 'Pendentes',
            'finalizado' => 'Finalizados',
            'cancoelado' => 'Cancelados'
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $data = Trend::query(
            Aluguel::query()
                ->where('status', $activeFilter)
        )
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => $activeFilter,
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => '0.3'
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),

        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

}
