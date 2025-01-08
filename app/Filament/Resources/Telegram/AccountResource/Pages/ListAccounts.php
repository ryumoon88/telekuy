<?php

namespace App\Filament\Resources\Telegram\AccountResource\Pages;

use App\Filament\Tables\Actions;
use App\Filament\Resources\Telegram\AccountResource;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\CanAuthorizeAccess;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
            Actions\ImportAccountsAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'my-download' => Tab::make()->query(fn ($query) => $query->where('downloader_id', Auth::user()->id))
                ->label('My Downloads'),
            // 'processing' => Tab::make()->query(fn ($query) => $query->where('status', 'processing')),
            // 'shipped' => Tab::make()->query(fn ($query) => $query->where('status', 'shipped')),
            // 'delivered' => Tab::make()->query(fn ($query) => $query->where('status', 'delivered')),
            // 'cancelled' => Tab::make()->query(fn ($query) => $query->where('status', 'cancelled')),
        ];
    }
}
