<?php

namespace App\Filament\Widgets\Source;

use App\Enums\Source\ViralLevel;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class PostChart extends ChartWidget
{
    protected static ?string $heading = 'Посты | Тренд мониторинг';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::model(\App\Models\Source\Post::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        $dataViral = Trend::query(\App\Models\Source\Post::where('viral_level', ViralLevel::VIRAL->value))
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        $dataHigh = Trend::query(\App\Models\Source\Post::where('viral_level', ViralLevel::HIGH->value))
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Постов',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
                [
                    'label' => 'Вирусных',
                    'data' => $dataViral->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
                [
                    'label' => 'Высоких',
                    'data' => $dataHigh->map(fn (TrendValue $value) => $value->aggregate),
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
