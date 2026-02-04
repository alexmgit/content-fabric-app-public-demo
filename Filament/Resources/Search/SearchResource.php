<?php

namespace App\Filament\Resources\Search;

use App\Filament\Resources\Search\SearchResource\Pages;
use App\Filament\Resources\Search\SearchResource\RelationManagers;
use App\Models\Search\Search;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SearchResource extends Resource
{
    protected static ?string $navigationGroup = 'Поиск каналов';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Поисковые запросы';

    protected static ?string $model = Search::class;

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
                Tables\Columns\TextColumn::make('query'),
                Tables\Columns\TextColumn::make('search_type'),
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
            'index' => Pages\ListSearches::route('/'),
        ];
    }
}
