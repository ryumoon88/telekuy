<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
    ];

    protected $appends = ['total', 'completed'];

    public function getTotalAttribute(){
        return $this->orderProductItems->sum(fn($item) => $item->price * $item->quantity);
    }

    public function getCompletedAttribute(): bool {
        return (bool) $this->orderProductItems->every(fn($item) => $item->completed);
    }

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
