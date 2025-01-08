<?php

namespace App\Filament\Resources\Shop\ProductResource\RelationManagers;

use App\Enums\ProductType;
use App\Filament\Resources\Telegram\AccountResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'accounts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('phone_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return AccountResource::infolist($infolist);
    }

    public function table(Table $table): Table
    {
        return AccountResource::table($table)
            ->recordTitleAttribute('phone_number')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type == ProductType::Account;
    }
}
