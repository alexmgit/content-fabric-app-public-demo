<?php

namespace App\Filament\Widgets\Source;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SourceChart extends ChartWidget
{
    protected static ?string $heading = 'Источники | Тренд мониторинг';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = Trend::model(\App\Models\Source\Source::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Источников',
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
