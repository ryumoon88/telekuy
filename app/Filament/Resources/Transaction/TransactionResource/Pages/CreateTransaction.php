<?php

namespace App\Filament\Resources\Transaction\TransactionResource\Pages;

use App\Filament\Resources\Transaction\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
}
