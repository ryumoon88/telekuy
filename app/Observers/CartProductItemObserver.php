<?php

namespace App\Observers;

use App\Enums\AccountStatus;
use App\Enums\ProductType;
use App\Models\Shop\CartProductItem;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bot;
use App\Models\Telegram\BotOption;
use App\Models\Telegram\Referral;

class CartProductItemObserver
{
    /**
     * Handle the CartProductItem "creating" event.
     */
    public function creating(CartProductItem $cartProductItem): void
    {
        $cartProductItem->cartable_type = match($cartProductItem->cartable_type){
            ProductType::Account, Account::class => Account::class,
            ProductType::Bot, Bot::class, BotOption::class => BotOption::class,
            ProductType::Referral, Referral::class => Referral::class
        };

        if($cartProductItem->cartable_type == Referral::class){
            $cartable_id = $cartProductItem->cartProduct->product->referral->id;
            $cartProductItem->cartable_id = $cartable_id;
        }

        $cartable = $cartProductItem->cartable;

        if($cartProductItem->cartProduct->product->type == ProductType::Account){
            $cartProductItem->price = $cartable->selling_price;
            $cartProductItem->quantity = 1;
        }elseif($cartProductItem->cartProduct->product->type == ProductType::Bot){
            $cartProductItem->price = $cartable->price;
            $cartProductItem->quantity = 1;
        }elseif($cartProductItem->cartProduct->product->type == ProductType::Referral){
            $cartProductItem->price = $cartable->price;
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
        if($cartProductItem->cartable && $cartProductItem->cartable_type == Account::class){
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
