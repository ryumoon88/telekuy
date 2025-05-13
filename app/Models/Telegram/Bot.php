<?php

namespace App\Models\Telegram;

use App\Models\Shop\CartProductItem;
use App\Models\Shop\OrderProductItem;
use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'description',
        'licensed',
        'active',
    ];

    public function cartProductItem(){
        return $this->morphOne(CartProductItem::class, 'cartable');
    }

    public function orderProductItem(){
        return $this->morphOne(OrderProductItem::class, 'orderable');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function options(){
        return $this->hasMany(BotOption::class);
    }

    public function licenses(){
        return $this->hasManyThrough(BotLicense::class, BotOption::class);
    }
}
