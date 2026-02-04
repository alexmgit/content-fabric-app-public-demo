<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Enums\Billing\PaymentStatus;

class PaymentChart extends ChartWidget
{
    protected static ?string $heading = 'Платежи';

    protected static ?int $sort = 11;

    protected function getData(): array
    {
        $data = Trend::query(\App\Models\Billing\Payment::where('status', PaymentStatus::PAID->value))
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Платежи',
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
