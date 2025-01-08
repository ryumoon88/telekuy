<?php

namespace App\Filament\Resources\Telegram\BundleResource\Pages;

use App\Filament\Resources\Telegram\BundleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBundles extends ListRecords
{
    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
