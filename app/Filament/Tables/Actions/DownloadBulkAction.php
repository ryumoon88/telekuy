<?php
namespace App\Filament\Tables\Actions;

use App\Enums\AccountStatus;
use App\Models\Telegram\Account;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class DownloadBulkAction extends BulkAction {
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'download_any';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label("Download Accounts");

        $this->fetchSelectedRecords();

        $this->successNotificationTitle("Account Downloaded");
        $this->failureNotificationTitle("Failed to download the accounts");

        $this->color('success');

        $this->action(function(Collection $records){
            if($records->first(fn($record) => $record->downloader_id != null) != null){
                Notification::make()
                    ->title('Download failed')
                    ->danger()
                    ->color('danger')
                    ->body('Some of the accounts selected has been downloaded by others')
                    ->send();
                $this->cancel();
                return;
            }
            Account::whereIn('id', $records->pluck('id'))->update(['status' => AccountStatus::Used, 'downloader_id' => Auth::id()]);
            $this->success();
            return Account::DownloadAccounts('accounts', $records);
        });

        $this->deselectRecordsAfterCompletion();
    }
}
