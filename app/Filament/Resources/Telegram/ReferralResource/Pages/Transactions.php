<?php

namespace App\Filament\Resources\Telegram\ReferralResource\Pages;

use App\Filament\Resources\Telegram\ReferralResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Transactions extends ManageRelatedRecords
{
    protected static string $resource = ReferralResource::class;

    protected static string $relationship = 'transactions';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Transactions';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('causer.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('causer.name')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime(),
                Tables\Columns\TextColumn::make('account.phone_number'),
                Tables\Columns\TextColumn::make('causer.name'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('referral.name'),
                Tables\Columns\TextColumn::make('amount')
                    ->color(fn($state) => $state <= 0 ? 'danger' : 'success')
                    ->money()
                    ->summarize(
                        Sum::make()
                            ->label('Profit')
                            ->money()
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
