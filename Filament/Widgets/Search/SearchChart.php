<?php

namespace App\Filament\Widgets\Search;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SearchChart extends ChartWidget
{
    protected static ?string $heading = 'Поисковые запросы | Поиск каналов';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Trend::model(\App\Models\Search\Search::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Поисковых запросов',
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
