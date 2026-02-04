<?php

namespace App\Filament\Resources\Search\EventResource\Pages;

use App\Filament\Resources\Search\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
