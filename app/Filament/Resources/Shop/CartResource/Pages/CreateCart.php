<?php

namespace App\Filament\Resources\Shop\CartResource\Pages;

use App\Filament\Resources\Shop\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCart extends CreateRecord
{
    protected static string $resource = CartResource::class;
}
