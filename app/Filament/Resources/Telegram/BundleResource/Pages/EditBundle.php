<?php

namespace App\Filament\Resources\Telegram\BundleResource\Pages;

use App\Filament\Resources\Telegram\BundleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBundle extends EditRecord
{
    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            BundleResource\RelationManagers\AccountsRelationManager::class,
        ];
    }
}
