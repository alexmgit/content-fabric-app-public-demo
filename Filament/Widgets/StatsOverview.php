<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Team;
use App\Models\Endpoint;
use App\Models\Review;
use App\Models\EndpointVisit;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Enums\Billing\PaymentStatus;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $dataSourceSources = Trend::model(\App\Models\Source\Source::class)
            ->between(
                start: now()->startOfWeek(),
                end: now(),
            )
            ->perDay()
            ->count();

        $dataSourcePosts = Trend::model(\App\Models\Source\Post::class)
            ->between(
                start: now()->startOfWeek(),
                end: now(),
            )
            ->perDay()
            ->count();

        $dataSearchResults = Trend::model(\App\Models\Search\Search::class)
            ->between(
                start: now()->startOfWeek(),
                end: now(),
            )
            ->perDay()
            ->count();

        $dataSearchSources = Trend::model(\App\Models\Search\Source::class)
            ->between(
                start: now()->startOfWeek(),
                end: now(),
            )
            ->perDay()
            ->count();

        $dataUser = Trend::model(\App\Models\User::class)
            ->between(
                start: now()->startOfWeek(),
                end: now(),
            )
            ->perDay()
            ->count();

        $dataPayment = Trend::query(\App\Models\Billing\Payment::where('status', PaymentStatus::PAID->value))
            ->between(
                start: now()->startOfWeek(),
                end: now(),
            )
            ->perDay()
            ->sum('amount');

        return [
            Stat::make('Источников, за неделю', $dataSourceSources->reduce(fn ($memo, TrendValue $value) => $memo + $value->aggregate, 0))
                ->description($dataSourceSources->last()->aggregate . ' сегодня')
                ->color($dataSourceSources->last()->aggregate > 0 ? 'success' : 'danger'),

            Stat::make('Постов, за неделю', $dataSourcePosts->reduce(fn ($memo, TrendValue $value) => $memo + $value->aggregate, 0))
                ->description($dataSourcePosts->last()->aggregate . ' сегодня')            
                ->color($dataSourcePosts->last()->aggregate > 0 ? 'success' : 'danger'),

            Stat::make('Поисковых запросов, за неделю', $dataSearchResults->reduce(fn ($memo, TrendValue $value) => $memo + $value->aggregate, 0))
                ->description($dataSearchResults->last()->aggregate . ' сегодня')            
                ->color($dataSearchResults->last()->aggregate > 0 ? 'success' : 'danger'),

            Stat::make('Результатов поиска, за неделю', $dataSearchSources->reduce(fn ($memo, TrendValue $value) => $memo + $value->aggregate, 0))
                ->description($dataSearchSources->last()->aggregate . ' сегодня')
                ->color($dataSearchSources->last()->aggregate > 0 ? 'success' : 'danger'),
                
            Stat::make('Пользователей, за неделю', $dataUser->reduce(fn ($memo, TrendValue $value) => $memo + $value->aggregate, 0))
                ->description($dataUser->last()->aggregate . ' сегодня')
                ->color($dataUser->last()->aggregate > 0 ? 'success' : 'danger'),

            Stat::make('Платежей, за неделю', $dataPayment->reduce(fn ($memo, TrendValue $value) => $memo + $value->aggregate, 0))
                ->description($dataPayment->last()->aggregate . ' сегодня')
                ->color($dataPayment->last()->aggregate > 0 ? 'success' : 'danger'),
        ];
    }
}
