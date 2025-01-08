<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class OrderProductItem extends Model
{
    protected $fillable = [
        'order_product_id',
        'orderable_type',
        'orderable_id',
        'quantity',
        'price',
        'extra',
    ];

    protected $casts = [
        'extra' => 'json'
    ];

    public function orderProduct(){
        return $this->belongsTo(OrderProduct::class);
    }

    public function orderable(){
        return $this->morphTo('orderable');
    }
}
