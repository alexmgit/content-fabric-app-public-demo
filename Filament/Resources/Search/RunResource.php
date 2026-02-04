<?php

namespace App\Filament\Resources\Search;

use App\Filament\Resources\Search\RunResource\Pages;
use App\Filament\Resources\Search\RunResource\RelationManagers;
use App\Models\Search\Run;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RunResource extends Resource
{
    protected static ?string $navigationGroup = 'Поиск каналов';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Задачи поиска';

    protected static ?string $model = Run::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('created_at'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('searchJob.job_status')
                    ->url(fn (Run $record): string => $record->searchJob ? route('filament.admin.resources.apify.jobs.view', ['record' => $record->searchJob]) : 'null'),
                Tables\Columns\TextColumn::make('sourceJob.job_status')
                    ->url(fn (Run $record): string => $record->sourceJob ? route('filament.admin.resources.apify.jobs.view', ['record' => $record->sourceJob]) : 'null'),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->bulkActions([
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRuns::route('/'),
        ];
    }
}
