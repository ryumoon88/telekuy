<?php
namespace App\Filament\Tables\Actions;

use App\Enums\AccountTransactionType;
use App\Enums\TransactionType;
use App\Models\Telegram\Account;
use App\Models\Telegram\Referral;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Collection;

class AddReferralBulkAction extends BulkAction {
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'add_referral_any';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label("Add Accounts Referrals");

        $this->modal();

        $this->modalHeading("Add Accounts Referrals");

        $this->modalSubmitActionLabel("Add Referrals");

        $this->successNotificationTitle("Account Referred");

        $this->color('success');

        $this->form([
            Forms\Components\Select::make('referral_id')
                ->relationship('transactions.referral', 'name', function($query, $livewire){
                    $selectedRecords = $livewire->selectedTableRecords;
                    $query->whereDoesntHave('accounts', function ($query) use ($selectedRecords) {
                        $query->whereIn('transactable_id', $selectedRecords);
                    });
                })
                ->preload()
                ->multiple()
                ->dehydrated(),
        ]);

        $this->action(function (Collection $records, array $data): void {
            $referral_id = $data['referral_id'];
            $referrals = Referral::whereIn('id', $referral_id)->get();
            foreach($records as $record) {
                foreach($referrals as $referral){
                    $record->transactions()->create([
                        'type' => TransactionType::AccountReferral,
                        'referral_id' => $referral->id,
                    ]);
                }
            }
            $this->success();
        });
    }
}
