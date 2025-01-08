<?php

namespace App\Models\Telegram;

use App\Models\Shop\CartProductItem;
use App\Models\Shop\OrderProductItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BotOption extends Model
{
    protected $fillable = [
        'bot_id',
        'duration',
        'price',
    ];

    public function getDurationNumAttribute(){
        $duration = explode(' ', $this->duration);
        return (int) $duration ? $duration[0] ?? 0 : 0;
    }

    public function getDurationModifierAttribute(){
        $duration = explode(' ', $this->duration);
        return $duration ? $duration[1] ?? '' : '';
    }

    public function bot(){
        return $this->belongsTo(Bot::class);
    }

    public function cartProductItem(){
        return $this->morphOne(CartProductItem::class, 'cartable');
    }

    public function orderProductItem(){
        return $this->morphOne(OrderProductItem::class, 'orderable');
    }
}
