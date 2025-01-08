<?php

namespace App\Observers;

use App\Enums\TransactionType;
use App\Models\Transaction\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    protected $debit = [
        TransactionType::Deposit,
        TransactionType::Payment,
        TransactionType::TopUp,
    ];

    protected $credit = [
        TransactionType::Withdrawal,
    ];

    /**
     * Handle the Transaction "creating" event.
     */
    public function creating(Transaction $transaction): void
    {
        if(in_array($transaction->type, $this->debit))
            $transaction->amount = abs($transaction->amount);
        if(in_array($transaction->type, $this->credit))
            $transaction->amount = -abs($transaction->amount);

        if(!$transaction->causer_id)
            $transaction->causer_id = Auth::id();
    }


    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        $items = $transaction->order->orderProductItems;
        $items->each(fn($item) => $item->sold());
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
