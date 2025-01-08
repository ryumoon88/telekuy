<?php

namespace App\Filament\Resources\Shop\CartResource\Pages;

use App\Filament\Resources\Shop\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarts extends ListRecords
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
