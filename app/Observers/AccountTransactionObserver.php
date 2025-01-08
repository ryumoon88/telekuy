<?php

namespace App\Observers;

use App\Enums\AccountTransactionType;
use App\Models\Telegram\AccountTransaction;
use Illuminate\Support\Facades\Auth;

class AccountTransactionObserver
{
    public function getAmountModifier(AccountTransactionType $type, $amount){
        return abs($amount) * (in_array($type, [AccountTransactionType::Purchase, AccountTransactionType::ReferralFee]) ? -1 : 1);
    }

    public function creating(AccountTransaction $accountTransaction): void {
        if($accountTransaction->type == AccountTransactionType::Referral)
            $accountTransaction->amount = $this->getAmountModifier($accountTransaction->type, $accountTransaction->referral->price);
        else
            $accountTransaction->amount = $this->getAmountModifier($accountTransaction->type, $accountTransaction->amount);

        if(in_array($accountTransaction->type, [AccountTransactionType::Purchase, AccountTransactionType::ReferralFee, AccountTransactionType::Referral]) && !$accountTransaction->causer_id){
            $accountTransaction->causer_id = Auth::id();
        }
    }

    /**
     * Handle the AccountTransaction "created" event.
     */
    public function created(AccountTransaction $accountTransaction): void
    {
        if($accountTransaction->type == AccountTransactionType::Referral){
            $user_fee = $accountTransaction->referral->myFee();
            AccountTransaction::create([
                'account_id' => $accountTransaction->account_id,
                'type' => AccountTransactionType::ReferralFee,
                'amount' => $user_fee ? $user_fee->pivot->fee : $accountTransaction->referral->fee,
                'referral_id' => $accountTransaction->referral_id,
            ]);
        }
    }

    /**
     * Handle the AccountTransaction "updated" event.
     */
    public function updated(AccountTransaction $accountTransaction): void
    {
        //
    }

    /**
     * Handle the AccountTransaction "deleted" event.
     */
    public function deleted(AccountTransaction $accountTransaction): void
    {
        //
    }

    /**
     * Handle the AccountTransaction "restored" event.
     */
    public function restored(AccountTransaction $accountTransaction): void
    {
        //
    }

    /**
     * Handle the AccountTransaction "force deleted" event.
     */
    public function forceDeleted(AccountTransaction $accountTransaction): void
    {
        //
    }
}
