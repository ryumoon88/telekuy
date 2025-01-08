<?php

namespace App\Filament\Tables\Actions;

use App\Models\Telegram\Account;
use App\Models\Telegram\Bundle;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DownloadAction extends Action {
    public static function getDefaultName(): ?string
    {
        return 'download';
    }

    protected function setUp(): void {
        parent::setUp();

        $this->label($this->getLabel());

        $this->hidden(fn($record) => !!$record->downloader_id);

        $this->successNotificationTitle("Account Downloaded");
        $this->failureNotificationTitle("Failed to download the accounts");

        $this->before(function($record) {
            if($record->downloader_id) {
                $this->failureNotification(Notification::make()->title('Bundle already downloaded!')->body('This bundle already downloaded by others!')->danger()->color('danger'));
                $this->failure();
                $this->cancel();
            }

            if(is_a($record, Bundle::class) && $record->accounts->count() < 1){
                $this->failureNotification(Notification::make()->title('Nothing to download')->body('There is no account to download!')->danger()->color('danger'));
                $this->failure();
                $this->cancel();
            }
        });

        $this->action(function($record) {
            $accounts = new Collection();
            $name = '';
            if(is_a($record, Account::class)){
                $accounts = new Collection([$record]);
                $name = 'accounts';
            }elseif(is_a($record, Bundle::class)){
                $accounts = new Collection($record->accounts);
                $record->downloader_id = Auth::id();
                $record->save();
                $name = $record->name;
            }

            $record->downloader_id = Auth::id();
            $record->save();
            $this->success();
            return Account::DownloadAccounts($name, $accounts);
        });
    }
}
