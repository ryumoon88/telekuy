<?php

namespace App\Filament\Resources\Telegram\BotResource\RelationManagers;

use App\Enums\BotDurationOption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('duration_num')
                        ->hiddenLabel()
                        ->default(1)
                        ->minValue(1)
                        ->numeric()
                        ->formatStateUsing(fn($record) => $record?->duration_num),
                    Forms\Components\Select::make('duration_modifier')
                        ->hiddenLabel()
                        ->options(BotDurationOption::class)
                        ->formatStateUsing(fn($record) => $record?->duration_modifier),
                ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->afterStateUpdated(function($state, $set) {
                        $set('duration', $state['duration_num'].' '.Str::plural($state['duration_modifier'], $state['duration_num']));
                    }),
                Forms\Components\Hidden::make('duration'),
                Forms\Components\TextInput::make('price')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('duration')
            ->columns([
                Tables\Columns\TextColumn::make('duration'),
                Tables\Columns\TextColumn::make('price')
                    ->money(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // Combine `duration_num` and `duration_modifier` into `duration`
        $data['duration'] = "{$data['duration_num']} {$data['duration_modifier']}";

        // Optional: Remove original fields if not needed
        unset($data['duration_num'], $data['duration_modifier']);

        return $data;
    }
}
