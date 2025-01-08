<?php

namespace App\Observers;

use App\Enums\AccountStatus;
use App\Enums\ProductType;
use App\Models\Shop\CartProductItem;
use App\Models\Telegram\Account;

class CartProductItemObserver
{
    /**
     * Handle the CartProductItem "creating" event.
     */
    public function creating(CartProductItem $cartProductItem): void
    {
        $cartable = $cartProductItem->cartable;
        if($cartProductItem->cartProduct->product->type == ProductType::Account){
            $cartProductItem->price = $cartable->selling_price;
            $cartProductItem->quantity = 0;
        }
    }

    /**
     * Handle the CartProductItem "created" event.
     */
    public function created(CartProductItem $cartProductItem): void
    {
        $cartable = $cartProductItem->cartable;
        if($cartProductItem->cartable_type == Account::class){
            $cartable->update(['status' => AccountStatus::Booked]);
        }
    }

    /**
     * Handle the CartProductItem "updated" event.
     */
    public function updated(CartProductItem $cartProductItem): void
    {
        //
    }

    /**
     * Handle the CartProductItem "deleted" event.
     */
    public function deleted(CartProductItem $cartProductItem): void
    {
        if(!$cartProductItem->cartable->orderable){
            $cartProductItem->cartable->update(['status' => AccountStatus::Available]);
        }
    }

    /**
     * Handle the CartProductItem "restored" event.
     */
    public function restored(CartProductItem $cartProductItem): void
    {
        //
    }

    /**
     * Handle the CartProductItem "force deleted" event.
     */
    public function forceDeleted(CartProductItem $cartProductItem): void
    {
        //
    }
}
