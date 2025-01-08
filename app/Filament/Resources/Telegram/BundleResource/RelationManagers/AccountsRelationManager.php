<?php

namespace App\Filament\Resources\Telegram\BundleResource\RelationManagers;

use App\Filament\Resources\Telegram\AccountResource;
use App\Models\Telegram\Account;
use App\Filament\Tables\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'accounts';

    protected static ?string $inverseRelationship = 'bundle';

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
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelectOptionsQuery(fn($query) => $query->doesntHave('bundle'))
                    ->after(function(AttachAction $action) {
                        Account::whereIn('id', $action->getFormData()['recordId'])->update(['status' => 'bundled']);
                    })
                    ->preloadRecordSelect()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DetachAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->after(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'available']);
                            });
                        }),
                    Actions\DownloadBulkAction::make()->authorize('download'),
                    Actions\RetrieveBulkAction::make()->authorize('retrieve'),
                    Actions\AddReferralBulkAction::make()->authorize('addReferralAny'),
                ]),
            ]);
    }
}
