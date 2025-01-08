<?php
namespace App\Filament\Tables\Actions;

use App\Enums\AccountTransactionType;
use App\Enums\TransactionType;
use App\Models\Telegram\Account;
use App\Models\Telegram\Referral;
use App\Models\Transaction\Transaction;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class AddReferralAction extends Action {
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'addReferral';
    }

    protected function setUp(): void {
        parent::setUp();

        $this->model(Account::class);

        $this->authorize('addReferral');

        $this->label($this->getLabel());

        $this->form([
            Forms\Components\Select::make('referral_id')
                ->relationship('referral', 'name', function($query, $livewire) {
                    $ownerId = $livewire->ownerRecord->id;
                    $query->whereDoesntHave('accounts', function ($query) use ($ownerId) {
                        $query->where('transactable_id', $ownerId);
                    });
                })
                ->multiple()
                ->preload()
                ->dehydrated()
        ]);

        $this->action(function($data, $livewire) {
            $ownerRecord = $livewire->ownerRecord;
            $referral_id = $data['referral_id'];
            $referrals = Referral::whereIn('id', $referral_id)->get();
            $failed_refs = [];
            $success_refs = [];
            foreach($referrals as $referral){
                if($ownerRecord->transactions()->where('referral_id', $referral->id)->exists()){
                    $failed_refs[] = $referral->name;
                    continue;
                }
                $ownerRecord->transactions()->create([
                    'type' => TransactionType::AccountReferral,
                    'referral_id' => $referral->id,
                ]);
                $success_refs[] = $referral->name;
            }
            $this->success();
            if(count($failed_refs) > 0) {
                Notification::make()
                    ->title('Some refs failed to add')
                    ->body('Someone might already ref this account')
                    ->send();
            }
        });
    }
}
