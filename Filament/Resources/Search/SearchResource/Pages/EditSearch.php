<?php

namespace App\Filament\Resources\Search\SearchResource\Pages;

use App\Filament\Resources\Search\SearchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSearch extends EditRecord
{
    protected static string $resource = SearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
