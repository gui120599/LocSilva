<?php

namespace App\Filament\Widgets;

use App\Models\Aluguel;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AlugueisFinalizados extends ChartWidget
{
    protected ?string $heading = 'Alugueis Finalizados';

    protected function getData(): array
    {
       $data = Trend::model(Aluguel::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->count();

    return [
        'datasets' => [
            [
                'label' => 'Finalizados',
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
            ],
        ],
        'labels' => $data->map(fn (TrendValue $value) => $value->date),
    ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
