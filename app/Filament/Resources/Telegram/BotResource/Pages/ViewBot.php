<?php

namespace App\Filament\Resources\Telegram\BotResource\Pages;

use App\Filament\Resources\Telegram\BotResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBot extends ViewRecord
{
    protected static string $resource = BotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
