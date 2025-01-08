<?php

namespace App\Filament\Resources\Transaction\PaymentMethodResource\Pages;

use App\Filament\Resources\Transaction\PaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
