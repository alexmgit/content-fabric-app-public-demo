<?php

namespace App\Filament\Resources\Search\EventResource\Pages;

use App\Filament\Resources\Search\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;
}
