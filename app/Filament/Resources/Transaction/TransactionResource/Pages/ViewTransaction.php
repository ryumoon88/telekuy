<?php

namespace App\Filament\Resources\Transaction\TransactionResource\Pages;

use App\Filament\Resources\Transaction\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
