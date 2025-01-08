<?php

namespace App\Filament\Resources\Shop;

use App\Enums\ProductType;
use App\Filament\Resources\Shop\ProductResource\Pages;
use App\Filament\Resources\Shop\ProductResource\RelationManagers;
use App\Models\Shop\Product;
use App\Models\Telegram\Bot;
use Cknow\Money\Money;
use Filament\Forms;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

use function GuzzleHttp\default_ca_bundle;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function($state, $set) {
                                $newstr = preg_replace('/[^a-zA-Z0-9\']/', '', $state);
                                $newstr = str_replace("'", '', $newstr);
                                $set('code', Str::upper($newstr));
                            }),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\ToggleButtons::make('type')
                            ->inline()
                            ->options(ProductType::class)
                            ->required()
                            ->live()
                            ->default(static::getTypeDefault())
                            ->disabled(static::getTypeDisabled()),
                        Forms\Components\Select::make('bot')
                            ->relationship('bot', 'name')
                            ->visible(fn($get) => $get('type') == ProductType::Bot->value)
                            ->formatStateUsing(fn($record) => $record?->bot->id)
                            ->required()
                            ->saveRelationshipsUsing(function(Product $record, $state) {
                                $bot = Bot::find($state);
                                $bot->product()->associate($record->id);
                                $bot->save();
                            }),
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->live(),
                Forms\Components\Section::make('Product Detail')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->required()
                            ->default(true),
                        Forms\Components\TextInput::make('price')
                            ->hidden(static::getPriceHidden())
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Product Information')
                ->schema([
                    Infolists\Components\TextEntry::make('name')
                        ->columnSpan(1),
                    Infolists\Components\TextEntry::make('code')
                        ->columnSpan(1),
                    Infolists\Components\TextEntry::make('type'),
                    Infolists\Components\TextEntry::make('bot.name')
                        ->visible(fn($record) => $record->type == ProductType::Bot),
                    Infolists\Components\TextEntry::make('description')
                        ->html()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpan(2)
                ->grow(),
            Infolists\Components\Grid::make(1)
                    ->schema([
                        Infolists\Components\Section::make('Product Detail')
                            ->schema([
                                Infolists\Components\IconEntry::make('active')
                                    ->boolean(),
                                Infolists\Components\TextEntry::make('price')
                                    ->default('-'),
                            ]),
                        Infolists\Components\Section::make('Timestamp')
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->dateTime(),
                            ]),
                    ])
                        ->columnSpan(1)
        ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function(Builder $query) {
                $query
                    ->with(['bot', 'bot.options'])
                    ->withCount(['accounts', 'bundles'])
                    ->withMin('accounts', 'selling_price')
                    ->withMax('accounts', 'selling_price')
                    ;
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('amount')
                    ->getStateUsing(fn($record) => match($record->type) {
                        ProductType::Account => $record->accounts_count,
                        ProductType::Bundle => $record->bundles_count,
                        default => $record->amount
                    })
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->getStateUsing(function($record) {
                        $min = 0;
                        $max = 0;
                        if($record->type == ProductType::Bot){
                            $min = Money::IDR($record->bot->options->min(fn($op) => $op->price), true);
                            $max = Money::IDR($record->bot->options->max(fn($op) => $op->price), true);
                        }elseif($record->type == ProductType::Account) {
                            $min = Money::IDR($record->accounts_min_selling_price, true);
                            $max = Money::IDR($record->accounts_max_selling_price, true);
                        }
                        return "$min ~ $max";
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AccountsRelationManager::class,
            RelationManagers\BotOptionsRelationManager::class,
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ProductResource\Pages\ViewProduct::class,
            ProductResource\Pages\EditProduct::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // customs

    public static function getTypeDefault(){
        return function($operation) {
            return match($operation) {
                'createOptionAccount' => ProductType::Account,
                'createOptionBundle' => ProductType::Bundle,
                default => null,
            };
        };
    }

    public static function getTypeDisabled(){
        return function($operation) {
            return match($operation){
                'createOptionAccount', 'createOptionBundle' => true,
                default => false,
            };
        };
    }

    public static function getInfiniteDefault(){
        return function(Get $get){
            switch($get('type')){
                case 'account':
                    return;
            }
        };
    }

    public static function getInfiniteHidden(){
        return function(Get $get, $operation){
            return match($get('type')){
                'account', 'bundle' => false,
                default => true,
            };
        };
    }

    public static function getPriceHidden(){
        return function(Get $get) {
            return match($get('type')) {
                // 'account', 'bundle' => true,
                default => false,
            };
        };
    }
}
