<?php

namespace App\Filament\Resources\Apify;

use App\Filament\Resources\Apify\JobResource\Pages;
use App\Filament\Resources\Apify\JobResource\RelationManagers;
use App\Models\Apify\Job;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class JobResource extends Resource
{
    protected static ?string $navigationGroup = 'Apify';
    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Задачи';

    protected static ?string $model = Job::class;

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
                TextColumn::make('job_id'),
                TextColumn::make('job_status'),
                TextColumn::make('created_at'),
                TextColumn::make('price'),
                TextColumn::make('items_count'),
                TextColumn::make('user.email'),
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
            'index' => Pages\ListJobs::route('/'),
            'view' => Pages\ViewJob::route('/{record}'),
        ];
    }
}
