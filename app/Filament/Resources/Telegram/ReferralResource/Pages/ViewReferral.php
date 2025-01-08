<?php

namespace App\Filament\Resources\Telegram\ReferralResource\Pages;

use App\Filament\Resources\Telegram\ReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReferral extends ViewRecord
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
