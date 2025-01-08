<?php

namespace App\Filament\Resources\Telegram;

use App\Filament\Resources\Telegram\BundleResource\Pages;
use App\Filament\Resources\Telegram\BundleResource\RelationManagers;
use App\Filament\Tables\Actions;
use App\Models\Telegram\Bundle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BundleResource extends Resource
{
    protected static ?string $model = Bundle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = "Telegram";

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('selling_price')
                    ->numeric()
                    ->hiddenOn(['createOptionBundle']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('accounts_count')
                    ->label('Accounts')
                    ->counts('accounts'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Listed On'),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money()
                    ->sortable(),
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
            ])
            ->actions([
                Actions\DownloadAction::make(),
                Actions\RetrieveAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            BundleResource\Pages\ViewBundle::class,
            BundleResource\Pages\EditBundle::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBundles::route('/'),
            'create' => Pages\CreateBundle::route('/create'),
            'view' => Pages\ViewBundle::route('/{record}'),
            'edit' => Pages\EditBundle::route('/{record}/edit'),
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
