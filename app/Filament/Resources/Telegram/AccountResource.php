<?php

namespace App\Filament\Resources\Telegram;

use App\Enums\AccountStatus;
use App\Enums\AccountTransactionType;
use App\Enums\TransactionType;
use App\Filament\Clusters\Referrals\Resources\AccountTransactionResource\Pages\ManageAccountTransactions;
use App\Filament\Resources\Telegram\AccountResource\Pages;
use App\Filament\Resources\Telegram\AccountResource\RelationManagers;
use App\Filament\Tables\Actions;
use App\Models\Shop\Cart;
use App\Models\Shop\CartProduct;
use App\Models\Telegram\Account;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Tapp\FilamentCountryCodeField\Tables\Columns\CountryCodeColumn;
use Tapp\FilamentCountryCodeField\Tables\Filters\CountryCodeSelectFilter;

class AccountResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = "Telegram";

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getPermissionPrefixes(): array
    {
        return array_merge(config('filament-shield.permission_prefixes.resource'), [
            'import',
            'download',
            'retrieve',
            'bundle',
            'add_referral',
            'add_referral_any',
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('country_code')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('path')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('selling_price')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('downloader_id')
                    ->required()
                    ->maxLength(36),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Account Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('country_code'),
                        Infolists\Components\TextEntry::make('phone_number')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('status')->badge(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(fn($query) => $query->where('transactions.type', 'referral'))
            ->columns([
                CountryCodeColumn::make('country_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('transactions.referral.name')
                    ->getStateUsing(function($record, $livewire) {
                        $filtered = $record->transactions
                            ->filter(function ($transaction) {
                                return $transaction->type === AccountTransactionType::Referral;
                            })
                            ->pluck('referral.name');
                        if(isset($livewire->ownerRecord))
                            $filtered = $filtered->sortBy(function ($item) use ($livewire) {
                                return $item === $livewire->ownerRecord->name ? 0 : 1; // Assign lower weight to the specific key
                            });
                        return $filtered;
                    })
                    ->badge()
                    ->limitList(1),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Listed On'),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transactions_sum_amount')
                    ->label('Profit')
                    ->sum('transactions', 'amount')
                    ->money()
                    ->getStateUsing(fn($record) => $record->transactions_sum_amount <= 0 ? 0 : $record->transactions_sum_amount),
                Tables\Columns\TextColumn::make('downloader.name')
                    ->label('Downloaded by')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                CountryCodeSelectFilter::make('country_code'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(AccountStatus::class),
                Tables\Filters\Filter::make('listed')
                    ->label('Listed')
                    ->indicator('Listed')
                    ->indicateUsing(function($data) {
                        if(!$data['value'])
                            return;
                        return 'Listed Status: '. $data['value'];
                    })
                    ->form([
                        Forms\Components\Select::make('value')
                            ->label('Listed Status')
                            ->options([
                                null => 'Show All',
                                'Listed' => 'Show only listed',
                                'Unlisted' => 'Show only unlisted',
                            ])
                            ->default('Listed')
                            ->selectablePlaceholder(false)
                    ])
                    ->query(function(Builder $query, array $data) {
                        switch($data['value']){
                            case 'Listed':
                                return $query->whereHas('product');
                            case 'Unlisted':
                                return $query->whereDoesntHave('product');
                        }
                        return $query;
                    }),
                Tables\Filters\SelectFilter::make('with_referrals')
                    ->relationship('transactions.referral', 'name')
                    ->label('With Referrals')
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('without_referrals')
                    ->relationship('transactions.referral', 'name')
                    ->query(function(Builder $query, $data) {
                        return $query->when(
                            count($data['values']),
                            function(Builder $query) use($data) {
                                $query->whereDoesntHave('transactions', fn($query) => $query->where('type', TransactionType::AccountReferral)->whereIn('referral_id', $data['values']));
                            }
                        );
                    })
                    ->label('Without Referrals')
                    ->multiple()
                    ->preload(),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\Action::make('add_to_cart')
                    ->action(function($record){
                        $cart = Auth::user()->cart ?? Auth::user()->cart()->create();
                        $cart->addItem($record);
                    })
                    ->visible(fn($record) => $record->status == AccountStatus::Available),
                Tables\Actions\ActionGroup::make([
                    Actions\DownloadAction::make(),
                    Actions\RetrieveAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Actions\DownloadBulkAction::make()->authorize('download'),
                    Actions\RetrieveBulkAction::make()->authorize('retrieve'),
                    Actions\BundleBulkAction::make()->authorize('bundle'),
                    Actions\AddReferralBulkAction::make()->authorize('addReferralAny'),
                    Actions\AttachToProductBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AccountResource\RelationManagers\TransactionsRelationManager::class
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            AccountResource\Pages\ViewAccount::class,
            AccountResource\Pages\EditAccount::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'view' => Pages\ViewAccount::route('/{record}'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
