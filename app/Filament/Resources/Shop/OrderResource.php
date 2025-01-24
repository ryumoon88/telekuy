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
use App\Models\Telegram\BotOption;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Cknow\Money\Money;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
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

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationGroup = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('buyer_id')
                    ->relationship('buyer', 'name'),
                Forms\Components\Repeater::make('orderProducts')
                    ->relationship('orderProducts')
                    ->defaultItems(1)
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function($set, $record, $state){
                                $product = Product::find($state);
                                $set('orderProductItems', []);
                                $set('type', $state ? $product->type : null);
                            })
                            ->afterStateHydrated(fn($set, $record) => $record ? $set('type', $record->product->type) : null)
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->type->name." | ".$record->name)
                            ->preload(),
                        Forms\Components\TextInput::make('type')
                            ->live()
                            ->hidden(),
                        TableRepeaterCustom::make('orderProductItems')
                            ->label('Durations')
                            ->relationship('orderProductItems')
                            ->headers([
                                Header::make('duration')
                            ])
                            ->schema([
                                Forms\Components\Select::make('orderable_id')
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
                                $data['orderable_type'] = ProductType::Bot;
                                return $data;
                            })
                            ->visible(fn($get) => $get('type') == ProductType::Bot),
                        Forms\Components\Repeater::make("orderProductItems")
                            ->relationship('orderProductItems')
                            ->schema([
                                Forms\Components\TextInput::make('extra.referral_target'),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                            ])
                                ->defaultItems(1)
                                ->maxItems(1)
                                ->mutateRelationshipDataBeforeCreateUsing(function($data, $get) {
                                    $data['orderable_type'] = ProductType::Referral;
                                    return $data;
                                })
                                ->visible(fn($get) => $get('type') == ProductType::Referral),
                        TableRepeaterCustom::make('orderProductItems')
                            ->label('Accounts')
                            ->relationship('orderProductItems')
                            ->headers([
                                Header::make('phone_number')
                            ])
                            ->defaultItems(1)
                            ->schema([
                                Forms\Components\Select::make('orderable_id')
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
                                $data['orderable_type'] = ProductType::Account;
                                return $data;
                            })
                            ->visible(fn($get) => $get('type') == ProductType::Account)
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('buyer.name'),
                TableRepeatableEntry::make('orderProducts')
                    ->schema([
                        Infolists\Components\TextEntry::make('product.name')
                            ->label('Product Name'),
                        Infolists\Components\TextEntry::make('product.type')
                            ->label('Product Type'),
                        TableRepeatableEntry::make('orderProductItems')
                            ->schema([
                                Infolists\Components\TextEntry::make('orderable.phone_number')
                                    ->label('Phone Number'),
                                Infolists\Components\TextEntry::make('orderable.country_code')
                                    ->label('Country Code'),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Price')
                                    ->money()
                                    ->alignEnd(),
                            ])
                            ->striped()
                            ->visible(fn($record) => $record->product->type == ProductType::Account),
                        TableRepeatableEntry::make('orderProductItems')
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
                                Infolists\Components\TextEntry::make('total')
                                    ->label('Total')
                                    ->default(fn($record) => $record->price * $record->quantity)
                                    ->money()
                                    ->alignEnd(),
                                Infolists\Components\TextEntry::make('action')
                                    ->suffixAction(
                                        Infolists\Components\Actions\Action::make('completed')
                                            ->icon('heroicon-o-check-circle')
                                            ->action(function($record) {
                                                dd($record);
                                            })
                                    )
                                    ->alignEnd()
                                    ->default('')
                            ])
                            ->striped()
                            ->visible(fn($record) => $record->product->type == ProductType::Referral),
                        TableRepeatableEntry::make('orderProductItems')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('orderable.duration')
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
                            return Money::IDR($record->orderProductItems->sum(function($item) {
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
            ->modifyQueryUsing(function($query){
                $query->with(['buyer']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('reference'),
                Tables\Columns\TextColumn::make('buyer.name')
                    ->getStateUsing(fn($record) => $record->buyer_id ? $record->buyer->name : $record->extra['buyer'])
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    // ->sum('orderProductItems', 'order_product_items.price')
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('pay')
                    ->action(function($record, $data, $livewire){
                        $record->pay();
                        $livewire->dispatch('userBalanceUpdated');
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

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewOrder::class,
            Pages\EditOrder::class,
        ]);
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
