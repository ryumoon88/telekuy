<?php

namespace App\Models\Shop;

use AjCastro\EagerLoadPivotRelations\EagerLoadPivotTrait;
use App\Enums\AccountStatus;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bundle;
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

    public function addItem(Account $model, int $quantity = 1) {
        $cartProduct = $this->cartProducts()->firstOrCreate([
            'product_id' => $model->product->id,
        ]);

        $productItem = $model->cartProductItem()->firstOrCreate([
            'cart_product_id' => $cartProduct->id,
        ]);
        $productItem->quantity += $quantity;
        $productItem->save();
    }

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
                ]);
            }
        }
        $this->clear();
    }

    public function clear() {
        $updates = $this->cartProductItems->groupBy('cartable_type');
        foreach ($updates as $cartableType => $items) {
            $cartableIds = $items->pluck('cartable_id');

            $cartableType::doesntHave('orderProductItem')->whereIn('id', $cartableIds)->update([
                'status' => AccountStatus::Available, // Example: Replace with actual fields and values
            ]);
        }
        $this->cartProductItems()->delete();
        $this->cartProducts()->delete();
    }

    public function isEmpty() {
        return $this->cartProductItems->isEmpty();
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
