<?php

namespace App\Filament\Widgets\Search;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Enums\Search\SourceInterestLevel;

class SourceChart extends ChartWidget
{
    protected static ?string $heading = 'Результаты поиска | Поиск каналов';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = Trend::model(\App\Models\Search\Source::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        $dataExcellent = Trend::query(\App\Models\Search\Source::where('interest_level', SourceInterestLevel::EXCELLENT->value))
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        $dataVeryHigh = Trend::query(\App\Models\Search\Source::where('interest_level', SourceInterestLevel::VERY_HIGH->value))
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();


        return [
            'datasets' => [
                [
                    'label' => 'Результатов поиска',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
                [
                    'label' => 'Отличных',
                    'data' => $dataExcellent->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
                [
                    'label' => 'Очень высоких',
                    'data' => $dataVeryHigh->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FF6384',
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
