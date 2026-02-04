<?php

namespace App\Filament\Resources\Source;

use App\Filament\Resources\Source\SourceResource\Pages;
use App\Filament\Resources\Source\SourceResource\RelationManagers;
use App\Models\Source\Source;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SourceResource extends Resource
{
    protected static ?string $navigationGroup = 'Тренд мониторинг';
    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Источники';

    protected static ?string $model = Source::class;

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
                Tables\Columns\TextColumn::make('url'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('tags')
                    ->badge(),
                Tables\Columns\TextColumn::make('last_parsed_at'),
                Tables\Columns\TextColumn::make('next_parsed_at'),
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
            'index' => Pages\ListSources::route('/'),
        ];
    }
}
