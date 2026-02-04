<?php

namespace App\Filament\Resources\Source\RunResource\Pages;

use App\Filament\Resources\Source\RunResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRuns extends ListRecords
{
    protected static string $resource = RunResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
