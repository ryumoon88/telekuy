<?php

namespace App\Filament\Tables\Actions;

use App\Enums\AccountStatus;
use App\Models\Telegram\Account;
use App\Models\Telegram\AccountTransaction;
use App\Models\Telegram\Bundle;
use App\Models\Telegram\BundleHasAccount;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RetrieveAction extends Action {
    public static function getDefaultName(): ?string
    {
        return 'retrieve';
    }

    protected function setUp(): void {
        parent::setUp();

        $this->label($this->getLabel());

        $this->hidden(fn($record) => !$record->downloader_id);

        $this->successNotificationTitle("Account Retrieved");
        $this->failureNotificationTitle("Retrieving Failed");

        $this->action(function($record) {
            $record->downloader()->dissociate();
            $record->push();
            $this->success();
        });
    }
}
