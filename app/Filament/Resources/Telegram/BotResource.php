<?php

namespace App\Filament\Resources\Telegram;

use App\Enums\BotDurationOption;
use App\Filament\Resources\Telegram\BotResource\Pages;
use App\Filament\Resources\Telegram\BotResource\RelationManagers;
use App\Forms\Components\TableRepeaterCustom;
use App\Models\Telegram\Bot;
use Awcodes\TableRepeater\Header;
use Cknow\Money\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BotResource extends Resource
{
    protected static ?string $model = Bot::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Telegram';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bot Information')
                    ->schema([
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\RichEditor::make('description'),
                    ])
                        ->columnSpan(3),
                Forms\Components\Section::make('Bot Detail')
                    ->schema([
                        Forms\Components\FileUpload::make('file')
                            ->hint('Bot file, in zip format.')
                            ->hintColor('warning'),
                        Forms\Components\Checkbox::make('licensed'),
                        Forms\Components\Checkbox::make('active')
                            ->default(true),
                    ])
                        ->columnSpan(1),
            ])
                ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function(Builder $query) {
                $query
                    ->withMin('options', 'price')
                    ->withMax('options', 'price')
                    ->withCount(['options', 'licenses' => function($query) {
                        $query->where('active', true);
                    }]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('licensed')
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options'),
                Tables\Columns\TextColumn::make('licenses_count')
                    ->label('Active Licenses'),
                Tables\Columns\TextColumn::make('price')
                    ->getStateUsing(function($record) {
                        $min = Money::IDR($record->options_min_price, true);
                        $max = Money::IDR($record->options_max_price, true);
                        return "$min ~ $max";
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
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
            RelationManagers\OptionsRelationManager::class,
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            BotResource\Pages\ViewBot::class,
            BotResource\Pages\EditBot::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBots::route('/'),
            'create' => Pages\CreateBot::route('/create'),
            'view' => Pages\ViewBot::route('/{record}'),
            'edit' => Pages\EditBot::route('/{record}/edit'),
        ];
    }
}
