<?php
namespace App\Filament\Tables\Actions;

use App\Enums\AccountStatus;
use App\Enums\AccountTransactionType;
use App\Models\Telegram\Account;
use App\Models\Telegram\AccountTransaction;
use App\Models\Telegram\BundleHasAccount;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RetrieveBulkAction extends BulkAction {
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'retrieve_any';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label("Retrieve Accounts");

        $this->fetchSelectedRecords();

        $this->successNotificationTitle("Account Retrieved");
        $this->failureNotificationTitle("Retrieving Failed");

        $this->color('success');

        $this->action(function(Collection $records){
            $sold_account_ids = AccountTransaction::select(['account_id'])->whereIn('account_id', $records->pluck('id'))->where('type', AccountTransactionType::Selling)->get()->pluck('account_id');
            $bundled_account_ids = BundleHasAccount::select(['account_id'])->whereIn('account_id', $records->pluck('id'))->get()->pluck('account_id');
            $available_account_ids = array_diff($records->pluck('id')->toArray(), $sold_account_ids->toArray(), $bundled_account_ids->toArray());

            if(count($bundled_account_ids))
                Account::whereIn('id', $bundled_account_ids)->update(['status' => AccountStatus::Bundled, 'downloader_id' => null]);
            if(count($sold_account_ids))
                Account::whereIn('id', $sold_account_ids)->update(['status' => AccountStatus::Sold, 'downloader_id' => null]);
            if(count($available_account_ids))
                Account::whereIn('id', $available_account_ids)->update(['status' => AccountStatus::Available, 'downloader_id' => null]);
            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
