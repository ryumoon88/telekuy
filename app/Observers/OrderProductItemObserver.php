<?php

namespace App\Observers;

use App\Enums\AccountStatus;
use App\Enums\ProductType;
use App\Models\Shop\OrderProductItem;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bot;
use App\Models\Telegram\BotOption;
use App\Models\Telegram\Referral;

class OrderProductItemObserver
{
    /**
     * Handle the OrderProductItem "creating" event.
     */
    public function creating(OrderProductItem $orderProductItem): void
    {
        $orderProductItem->orderable_type = match($orderProductItem->orderable_type){
            ProductType::Account, Account::class => Account::class,
            ProductType::Bot, Bot::class, BotOption::class => BotOption::class,
            ProductType::Referral, Referral::class => Referral::class,
        };

        if($orderProductItem->orderable_type == Referral::class){
            $orderable_id = $orderProductItem->orderProduct->product->referral->id;
            $orderProductItem->orderable_id = $orderable_id;
        }

        $orderable = $orderProductItem->orderable;
        if($orderProductItem->orderProduct->product->type == ProductType::Account){
            $orderProductItem->price = $orderable->selling_price;
            $orderProductItem->quantity = 1;
        }elseif($orderProductItem->orderProduct->product->type == ProductType::Bot){
            $orderProductItem->price = $orderable->price;
            $orderProductItem->quantity = 1;
        }elseif($orderProductItem->orderProduct->product->type == ProductType::Referral){
            $orderProductItem->price = $orderable->price;
        }
    }

    /**
     * Handle the OrderProductItem "created" event.
     */
    public function created(OrderProductItem $orderProductItem): void
    {
        //
    }

    /**
     * Handle the OrderProductItem "updated" event.
     */
    public function updated(OrderProductItem $orderProductItem): void
    {
        //
    }

    /**
     * Handle the OrderProductItem "deleted" event.
     */
    public function deleted(OrderProductItem $orderProductItem): void
    {
        if(!$orderProductItem->orderable){
            $orderProductItem->orderable->update(['status' => AccountStatus::Available]);
        }
    }

    /**
     * Handle the OrderProductItem "restored" event.
     */
    public function restored(OrderProductItem $orderProductItem): void
    {
        //
    }

    /**
     * Handle the OrderProductItem "force deleted" event.
     */
    public function forceDeleted(OrderProductItem $orderProductItem): void
    {
        //
    }
}
