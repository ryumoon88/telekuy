<?php

namespace App\Models\Shop;

use AjCastro\EagerLoadPivotRelations\EagerLoadPivotTrait;
use App\Enums\AccountStatus;
use App\Models\Telegram\Account;
use App\Models\Telegram\BotOption;
use App\Models\Telegram\Bundle;
use App\Models\Telegram\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Cart extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
    ];

    protected $appends = ['total'];

    public function add($model, array $extra) {
        $cartProduct = $this->cartProducts()->firstOrCreate([
            'product_id' => $model->product->id,
        ]);

        $item['cart_product_id'] = $cartProduct->id;

        if($model instanceof Referral){
            $item['extra']['target'] = $extra['target'];
            $item['quantity'] = $extra['quantity'];
        }

        $model->cartProductItem()->firstOrCreate($item);
    }

    public function remove(CartProductItem $cartProductItem) {
        $cartProduct = $cartProductItem->cartProduct;
        $cartProductItem->delete();

        if($cartProduct->cartProductItems()->count() < 1){
            $cartProduct->delete();
        }
    }

    // public function addReferral(Referral $referral, int $quantity, array $extra) {
    //     $cart_product = $this->cartProducts()->firstOrCreate([
    //         'product_id' => $referral->product->id,
    //     ]);

    //     $this->cartProductItems()->firstOrCreate([
    //         'cart_product_id' => $cart_product->id,
    //     ]);
    // }

    // public function addBot(BotOption $botOption, int $quantity, array $extra) {
    //     $this->cartProducts()->firstOrCreate([
    //         // 'product_id' => $botOption->bot->
    //     ]);
    // }

    // public function addAccount(Account $account, int $quantity = 1){
    //     $cartProduct = $this->cartProducts()->firstOrCreate([
    //         'product_id' => $account->product->id,
    //     ]);

    //     $productItem = $account->cartProductItem()->firstOrCreate([
    //         'cart_product_id' => $cartProduct->id,
    //     ]);
    // }

    public function checkout(){
        $order = Order::create([
            'buyer_id' => Auth::user()->id,
        ]);

        foreach($this->cartProducts as $cartProduct) {
            $orderProduct = $order->orderProducts()->create([
                'order_id' => $order->id,
                'product_id' => $cartProduct->product_id,
            ]);
            foreach($cartProduct->cartProductItems as $cartProductItem) {
                $orderProductItem = $orderProduct->orderProductItems()->create([
                    'orderable_type' => $cartProductItem->cartable_type,
                    'orderable_id' => $cartProductItem->cartable_id,
                    'quantity' => $cartProductItem->quantity,
                    'price' => $cartProductItem->price,
                    'extra' => $cartProductItem->extra,
                ]);
            }
        }
        $this->clear();
        return $order;
    }

    public function clear() {
        $updates = $this->cartProductItems->groupBy('cartable_type');
        foreach ($updates as $cartableType => $items) {
            $cartableIds = $items->pluck('cartable_id');

            if($cartableType == Account::class){
                $cartableType::doesntHave('orderProductItem')->whereIn('id', $cartableIds)->update([
                    'status' => AccountStatus::Available, // Example: Replace with actual fields and values
                ]);
            }
        }
        $this->cartProductItems()->delete();
        $this->cartProducts()->delete();
    }

    public function isEmpty() {
        return $this->cartProductItems->isEmpty();
    }

    public function getTotalAttribute(){
        return $this->cartProducts->sum(fn($item) => $item->total);
    }

    // Relations

    public function owner(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cartProducts(){
        return $this->hasMany(CartProduct::class);
    }

    public function cartProductItems(){
        return $this->hasManyThrough(CartProductItem::class, CartProduct::class);
    }
}
