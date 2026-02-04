<?php

namespace App\Filament\Resources\Apify\JobResource\Pages;

use App\Filament\Resources\Apify\JobResource;
use App\Infolists\Components\Json;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists;

class ViewJob extends ViewRecord
{
    protected static string $resource = JobResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('actor'),
                Infolists\Components\TextEntry::make('job_id'),
                Infolists\Components\TextEntry::make('job_status'),
                Infolists\Components\TextEntry::make('job_error'),

                

                Json::make('job_options'),
                    Json::make('job_data'),

                Infolists\Components\Split::make([
                    Json::make('job_result'),
                ]),
            ]);
    }
}
