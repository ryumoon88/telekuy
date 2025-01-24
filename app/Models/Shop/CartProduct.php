<?php

namespace App\Models\Shop;

use App\Enums\ProductType;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bundle;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CartProduct extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
    ];

    protected $with = [
        'cartProductItems'
    ];

    protected $appends = ['total'];

    public function getTotalAttribute(){
        return $this->cartProductItems->sum(fn($item) => $item->price * $item->quantity);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function cart(){
        return $this->belongsTo(Cart::class);
    }

    public function cartProductItems(){
        return $this->hasMany(CartProductItem::class);
    }
}
