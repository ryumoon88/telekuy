<?php

namespace App\Filament\Resources\Telegram\BundleResource\Pages;

use App\Filament\Resources\Telegram\BundleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBundle extends ViewRecord
{
    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            BundleResource\RelationManagers\AccountsRelationManager::class,
        ];
    }
}
