<?php
namespace App\Filament\Tables\Actions;

use App\Enums\AccountStatus;
use App\Filament\Resources\Telegram\BundleResource;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bundle;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Collection;

class BundleBulkAction extends BulkAction {
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'bundle';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label("Bundle Accounts");

        $this->modal();

        $this->modalHeading("Bundle Account");

        $this->modalSubmitActionLabel("Bundle");

        $this->successNotificationTitle("Account Bundled");

        $this->color('success');

        $this->form([
            Forms\Components\Select::make('bundle_id')
                ->options(Bundle::all()->pluck('name', 'id'))
                ->createOptionForm(fn(Forms\Form $form) => BundleResource::form($form)->operation('createOptionBundle'))
                ->createOptionUsing(fn($data) => Bundle::create(array_merge($data))->getKey())
                ->preload()
                ->dehydrated()
        ]);

        $this->before(function(Collection $records) {
            if($records->every(fn($record) => $record->type === AccountStatus::Available)){
                $this->failureNotification(Notification::make()->title('Failed to bundle account')->body('Some of the selected account has been bundled.')->danger()->color('danger'));
                $this->failure();
                $this->cancel();
            }
        });

        $this->action(function (Collection $records, array $data): void {
            $bundle = Bundle::find($data['bundle_id']);
            $bundle->attachAccount($records);
            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
