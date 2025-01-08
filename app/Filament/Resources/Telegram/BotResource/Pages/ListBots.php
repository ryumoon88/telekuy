<?php

namespace App\Filament\Resources\Telegram\BotResource\Pages;

use App\Filament\Resources\Telegram\BotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBots extends ListRecords
{
    protected static string $resource = BotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
