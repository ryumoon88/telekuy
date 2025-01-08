<?php

namespace App\Filament\Resources\Telegram\AccountResource\Pages;

use App\Filament\Resources\Telegram\AccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            AccountResource\RelationManagers\TransactionsRelationManager::class
        ];
    }

    public function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return true;
    }
}
