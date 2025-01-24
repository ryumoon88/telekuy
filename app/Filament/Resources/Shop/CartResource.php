<?php

namespace App\Filament\Resources\Shop;

use App\Enums\AccountStatus;
use App\Enums\ProductType;
use App\Filament\Resources\Shop\CartResource\Pages;
use App\Filament\Resources\Shop\CartResource\RelationManagers;
use App\Forms\Components\TableRepeaterCustom;
use App\Models\Shop\Cart;
use App\Models\Shop\CartProductItem;
use App\Models\Shop\Product;
use App\Models\Telegram\Account;
use App\Models\Telegram\BotOption;
use App\Models\Telegram\Referral;
use Awcodes\TableRepeater\Header;
use Cknow\Money\Money;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function GuzzleHttp\default_ca_bundle;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('owner', 'name'),
                Forms\Components\Repeater::make('cartProducts')
                    ->relationship('cartProducts')
                    ->defaultItems(1)
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function($set, $record, $state){
                                $product = Product::find($state);
                                $set('cartProductItems', []);
                                $set('type', $state ? $product->type : null);
                            })
                            ->afterStateHydrated(fn($set, $record) => $record ? $set('type', $record->product->type) : null)
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->type->name." | ".$record->name)
                            ->preload(),
                        Forms\Components\TextInput::make('type')
                            ->live()
                            ->hidden(),
                        TableRepeaterCustom::make('cartProductItems')
                            ->label('Durations')
                            ->relationship('cartProductItems')
                            ->headers([
                                Header::make('duration')
                            ])
                            ->schema([
                                Forms\Components\Select::make('cartable_id')
                                    ->options(function($get){
                                        $options = BotOption::whereHas('bot.product', function($query) use($get) {
                                            $query->where('products.id', $get('../../product_id'));
                                        })->get()->pluck('duration', 'id');
                                        return $options;
                                    })
                                        ->getOptionLabelUsing(fn($record) => $record->duration . ' | '.$record)
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            ])
                            ->streamlined()
                            ->mutateRelationshipDataBeforeCreateUsing(function($data, $get) {
                                $data['cartable_type'] = ProductType::Bot;
                                return $data;
                            })
                            ->visible(fn($get) => $get('type') == ProductType::Bot),
                        Forms\Components\Repeater::make("cartProductItems")
                            ->relationship('cartProductItems')
                            ->schema([
                                Forms\Components\TextInput::make('extra.referral_target'),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                            ])
                                ->defaultItems(1)
                                ->maxItems(1)
                                ->mutateRelationshipDataBeforeCreateUsing(function($data, $get) {
                                    $data['cartable_type'] = ProductType::Referral;
                                    return $data;
                                })
                                ->visible(fn($get) => $get('type') == ProductType::Referral),
                        TableRepeaterCustom::make('cartProductItems')
                            ->label('Accounts')
                            ->relationship('cartProductItems')
                            ->headers([
                                Header::make('phone_number')
                            ])
                            ->defaultItems(1)
                            ->schema([
                                Forms\Components\Select::make('cartable_id')
                                    ->searchable()
                                    ->options(function($get, $state) {
                                        $options = Account::whereHas('product', function($query) use ($get, $state) {
                                            $query->where('products.id', $get('../../product_id'));
                                        })->get()->pluck('phone_number', 'id');
                                        return $options;
                                    })
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            ])
                            ->streamlined()
                            ->mutateRelationshipDataBeforeCreateUsing(function($data, $get) {
                                $data['cartable_type'] = ProductType::Account;
                                return $data;
                            })
                            ->visible(fn($get) => $get('type') == ProductType::Account)
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'cartProducts',
                'cartProducts.product',
                'cartProducts.cartProductItems',
                'cartProducts.cartProductItems.cartable',
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            // ->record($record)
            ->schema([
                Infolists\Components\TextEntry::make('owner.name'),
                TableRepeatableEntry::make('cartProducts')
                    ->schema([
                        Infolists\Components\TextEntry::make('product.name')
                            ->label('Product Name'),
                        Infolists\Components\TextEntry::make('product.type')
                            ->label('Product Type'),
                        TableRepeatableEntry::make('cartProductItems')
                            ->schema([
                                Infolists\Components\TextEntry::make('cartable.phone_number')
                                    ->label('Phone Number'),
                                Infolists\Components\TextEntry::make('cartable.country_code')
                                    ->label('Country Code'),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Price')
                                    ->money()
                                    ->alignEnd(),
                            ])
                            ->striped()
                            ->visible(fn($record) => $record->product->type == ProductType::Account),
                        TableRepeatableEntry::make('cartProductItems')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('extra.target')
                                    ->label('Referral Target'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Quantity'),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Price')
                                    ->money()
                                    ->alignEnd(),
                            ])
                            ->striped()
                            ->visible(fn($record) => $record->product->type == ProductType::Referral),
                        TableRepeatableEntry::make('cartProductItems')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('cartable.duration')
                                    ->label('Duration'),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Price')
                                    ->money()
                                    ->alignEnd(),
                            ])
                            ->visible(fn($record) => $record->product->type == ProductType::Bot),
                    ])->columnSpanFull(),
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('total')
                        ->default('')
                        ->inlineLabel()
                        ->formatStateUsing(function($record){
                            return Money::IDR($record->cartProductItems->sum(function($item) {
                                return $item->price * $item->quantity;
                            }), true);
                        })
                        ->columnSpanFull()
                        ->alignEnd(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query) {
                $query->with('cartProductItems');
            })
            ->columns([
                Tables\Columns\TextColumn::make('owner.name'),
                Tables\Columns\TextColumn::make('cart_product_items_count')
                    ->label('Cart Items')
                    ->counts('cartProductItems'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->getStateUsing(function ($record) {
                        // Calculate total by summing price * quantity for each item
                        return $record->cartProductItems->sum(function ($item) {
                            return $item->price * $item->quantity;
                        });
                    })
                    ->money()
                // Tables\Columns\TextColumn::make('cart_product_items_sum_cart_product_itemsprice')
                //     ->label('Total')
                //     ->default(0)
                //     ->sum('cartProductItems', 'cart_product_items.price')
                //     ->money()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('checkout')
                    ->action(function($record) {
                        $record->checkout();
                    })
                    ->hidden(fn($record) => $record->isEmpty()),
                Tables\Actions\Action::make('clear')
                    ->action(function($record) {
                        $record->clear();
                    })
                    ->hidden(fn($record) => $record->isEmpty()),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewCart::class,
            Pages\EditCart::class,
        ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'edit' => Pages\EditCart::route('/{record}/edit'),
            'view' => Pages\ViewCart::route('/{record}'),
        ];
    }
}
