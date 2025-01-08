<?php

namespace App\Filament\Resources\Telegram\AccountResource\RelationManagers;

use App\Enums\AccountTransactionType;
use App\Filament\Tables\Actions\AddReferralAction;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class TransactionsRelationManager extends RelationManager implements HasShieldPermissions
{
    protected static string $relationship = 'transactions';

    public static function getPermissionPrefixes(): array {
        return [
            'add_referral'
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('referral_id')
                    ->relationship('referral', 'name')
                    ->multiple()
                    ->preload()
                    ->dehydrated()
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('id')
                ->label('TransactionID'),
            Infolists\Components\TextEntry::make('created_at')
                ->label('Transaction Date'),
            Infolists\Components\TextEntry::make('causer.name'),
            Infolists\Components\TextEntry::make('type')->badge(),
            Infolists\Components\TextEntry::make('referral.name')->default('-'),
            Infolists\Components\TextEntry::make('amount')->default(0)->money()->color(fn($state) => $state <= 0 ? 'danger' : 'success'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Account Transaction')
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime(),
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
                Tables\Filters\SelectFilter::make('type')
                    ->options(AccountTransactionType::class)
            ])
            ->poll('10s')
            ->headerActions([
                AddReferralAction::make(),
                // Tables\Actions\Action::make('add_referral')
                //     ->label('New Referral')
                //     ->form([
                //         Forms\Components\Select::make('referral_id')
                //             ->relationship('referral', 'name', function($query, $livewire) {
                //                 $ownerId = $livewire->ownerRecord->id;
                //                 $query->whereDoesntHave('accounts', function ($query) use ($ownerId) {
                //                     $query->where('account_id', $ownerId);
                //                 });
                //             })
                //             ->multiple()
                //             ->preload()
                //             ->dehydrated()
                //     ])
                //     ->action(function($data, $livewire){

                //     }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    // Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
}
