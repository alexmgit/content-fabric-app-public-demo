<?php

namespace App\Filament\Resources\Source\SourceResource\Pages;

use App\Filament\Resources\Source\SourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSources extends ListRecords
{
    protected static string $resource = SourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
