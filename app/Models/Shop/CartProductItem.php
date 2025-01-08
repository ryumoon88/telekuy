<?php

namespace App\Models\Shop;

use App\Observers\CartProductItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([CartProductItemObserver::class])]
class CartProductItem extends Model
{
    protected $fillable = [
        'cart_product_id',
        'cartable_type',
        'cartable_id',
        'quantity',
        'price',
        'extra',
    ];

    protected $casts = [
        'extra' => 'json'
    ];

    public function cartProduct(){
        return $this->belongsTo(CartProduct::class);
    }

    public function cartable(){
        return $this->morphTo('cartable');
    }
}
