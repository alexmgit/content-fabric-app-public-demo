<?php

namespace App\Filament\Resources\Apify\JobResource\Pages;

use App\Filament\Resources\Apify\JobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
