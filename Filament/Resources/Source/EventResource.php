<?php

namespace App\Filament\Resources\Source;

use App\Filament\Resources\Source\EventResource\Pages;
use App\Filament\Resources\Source\EventResource\RelationManagers;
use App\Models\Source\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $navigationGroup = 'Тренд мониторинг';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'События';

    protected static ?string $model = Event::class;

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
                Tables\Columns\TextColumn::make('source.url'),
                Tables\Columns\TextColumn::make('source.type'),
                Tables\Columns\TextColumn::make('source.tags')
                    ->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->markdown()
                    ->wrap(true),
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
            'index' => Pages\ListEvents::route('/'),
        ];
    }
}
