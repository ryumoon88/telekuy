<?php

namespace App\Filament\Resources\Telegram\BotResource\Pages;

use App\Filament\Resources\Telegram\BotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBot extends EditRecord
{
    protected static string $resource = BotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
