<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class UserResource extends Resource
{
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $model = User::class;

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
                TextColumn::make('id'),
                TextColumn::make('email')
                    ->html()
                    ->state(function ($record) {
                        return $record->email . '<br><span class="text-xs text-gray-500">' . $record->name . '</span>'  ;
                    }),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i'),
                TextColumn::make('last_login_at')->dateTime('Y-m-d H:i'),
                TextColumn::make('source_count')
                    ->label('Source / Search')
                    ->state(function ($record) {
                        return $record->source_count . ' / ' . $record->search_count;
                    }),
                TextColumn::make('balance'),
                TextColumn::make('spent_total'),
                TextColumn::make('spent_this_month'),
                TextColumn::make('plan_subscription')
                    ->label('Тариф')
                    ->state(function ($record) {
                        $planSubscription = $record->planSubscription('main');
                        if ($planSubscription && $planSubscription->active())
                        {
                            $name = $planSubscription->plan->name;
                            $ends_at = $planSubscription->ends_at;

                            if ($planSubscription->onTrial())
                            {
                                return 'Триал ' . ' до ' . $planSubscription->trial_ends_at->format('Y-m-d');
                            }
                            else
                            {
                                return $name . ' до ' . $ends_at->format('Y-m-d');
                            }
                        }
                        else
                        {
                            return 'Не подключен';
                        }
                    })
                    ->color(function ($record) {
                        $planSubscription = $record->planSubscription('main');
                        if ($planSubscription && $planSubscription->active())
                        {
                            if ($planSubscription->canceled())
                            {
                                return 'danger';
                            }
                            else
                            {
                                return 'success';
                            }
                        }
                        else
                        {
                            return 'secondary';
                        }
                    }),
                TextColumn::make('partner.email')
                    ->label('Партнер'),
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
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
