<?php

namespace App\Observers;

use App\Enums\AccountStatus;
use App\Enums\AccountTransactionType;
use App\Models\Telegram\Account;

class AccountObserver
{
    /**
     * Handle the Account "created" event.
     */
    public function created(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "updated" event.
     */
    public function updated(Account $account): void
    {
        if($account->status == AccountStatus::Sold){
            $account->transactions()->create([
                'causer_id' => $account->orderProductItem->orderProduct->order_id,
                'type' => AccountTransactionType::Selling,
                'amount' => $account->selling_price,
            ]);
        }
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "restored" event.
     */
    public function restored(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "force deleted" event.
     */
    public function forceDeleted(Account $account): void
    {
        //
    }
}
