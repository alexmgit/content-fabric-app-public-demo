<?php

namespace App\Filament\Resources\Source;

use App\Filament\Resources\Source\RunResource\Pages;
use App\Filament\Resources\Source\RunResource\RelationManagers;
use App\Models\Source\Run;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RunResource extends Resource
{
    protected static ?string $navigationGroup = 'Тренд мониторинг';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Задачи';

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
                Tables\Columns\TextColumn::make('profileJob.job_status')
                    ->url(fn (Run $record): string => $record->profileJob ? route('filament.admin.resources.apify.jobs.view', ['record' => $record->profileJob]) : ''),
                Tables\Columns\TextColumn::make('postJob.job_status')
                    ->url(fn (Run $record): string => route('filament.admin.resources.apify.jobs.view', ['record' => $record->postJob])),
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
