<?php

namespace App\Filament\Resources\Shop;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Enums\TransactionType;
use App\Filament\Resources\Shop\OrderResource\Pages;
use App\Filament\Resources\Shop\OrderResource\RelationManagers;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\StatsOrderOverview;
use App\Forms\Components\TableRepeaterCustom;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Models\Shop\ProductHasAccount;
use App\Models\Telegram\Account;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use LaraZeus\Quantity\Components\Quantity;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('buyer_id')
                    ->relationship('buyer', 'name'),
                Forms\Components\Repeater::make('orderProducts')
                    ->relationship('orderProducts')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->searchable()
                            ->live()
                            ->preload(),
                        TableRepeaterCustom::make('orderProductItems')
                            ->relationship('orderProductItems')
                            ->headers([
                                Header::make('phone_number')
                            ])
                            ->schema([
                                Forms\Components\Select::make('orderable_id')
                                    ->options(function($get, $state) {
                                        $options = Account::whereHas('product', function($query) use ($get, $state) {
                                            $query->where('products.id', $get('../../product_id'));
                                        })->get()->pluck('phone_number', 'id');
                                        return $options;
                                    })
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function($data, $get) {
                                $product = Product::find($get('product_id'));
                                $data['orderable_type'] = $product->type;
                                return $data;
                            })
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Order Information')
                ->schema([
                    Infolists\Components\TextEntry::make('buyer.name'),
                    Infolists\Components\TextEntry::make('status')
                ]),
            Infolists\Components\Section::make('Order Items')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('orderProducts')
                        ->hiddenLabel()
                        ->schema([
                            Infolists\Components\TextEntry::make('product.name')
                                ->label('Product Name'),
                            Infolists\Components\TextEntry::make('product.type')
                                ->label('Product Type'),
                            TableRepeatableEntry::make('orderProductItems')
                                ->label('Accounts in Cart')
                                ->schema([
                                    Infolists\Components\TextEntry::make('orderable.phone_number')
                                        ->label('Phone Number'),

                                    Infolists\Components\TextEntry::make('orderable.country_code')
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
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['buyer']))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('reference'),
                Tables\Columns\TextColumn::make('buyer.name')
                    ->getStateUsing(fn($record) => $record->buyer_id ? $record->buyer->name : $record->extra['buyer'])
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('order_product_items_sum_order_product_itemsprice')
                    ->label('Total')
                    ->sum('orderProductItems', 'order_product_items.price')
                    ->money()
                    ->sortable(),
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
            ])
            ->actions([
                Tables\Actions\Action::make('pay')
                    ->action(function($record, $data){
                        $record->pay();
                    })
                    ->hidden(fn($record) => $record->status != OrderStatus::Pending),
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
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            StatsOrderOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
