<?php

namespace App\Filament\Resources\Search\RunResource\Pages;

use App\Filament\Resources\Search\RunResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRun extends EditRecord
{
    protected static string $resource = RunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
