<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
    ];

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function orderProductItems(){
        return $this->hasMany(OrderProductItem::class);
    }
}
