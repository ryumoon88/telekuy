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
use Awcodes\TableRepeater\Header;
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
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->searchable()
                            ->live()
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->type->name." | ".$record->name)
                            ->preload(),
                        TableRepeaterCustom::make('cartProductItems')
                            ->label('Accounts')
                            ->relationship('cartProductItems')
                            ->headers([
                                Header::make('phone_number')
                            ])
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
                                $product = Product::find($get('product_id'));
                                $data['cartable_type'] = $product->type;
                                return $data;
                            })
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
                Infolists\Components\RepeatableEntry::make('cartProducts')
                    ->schema([
                        Infolists\Components\TextEntry::make('product.name')
                            ->label('Product Name'),
                        Infolists\Components\TextEntry::make('product.type')
                            ->label('Product Type'),
                        TableRepeatableEntry::make('cartProductItems')
                            ->label('Accounts in Cart')
                            ->schema([
                                Infolists\Components\TextEntry::make('cartable.phone_number')
                                    ->label('Phone Number'),
                                Infolists\Components\TextEntry::make('cartable.country_code')
                                    ->label('Country Code'),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Price')
                                    ->money(),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.name'),
                Tables\Columns\TextColumn::make('cart_product_items_count')
                    ->label('Cart Items')
                    ->counts('cartProductItems'),
                Tables\Columns\TextColumn::make('cart_product_items_sum_cart_product_itemsprice')
                    ->label('Total')
                    ->default(0)
                    ->sum('cartProductItems', 'cart_product_items.price')
                    ->money()
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
