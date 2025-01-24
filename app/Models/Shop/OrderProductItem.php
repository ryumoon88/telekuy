<?php

namespace App\Models\Shop;

use App\Models\Telegram\BotLicense;
use App\Models\Telegram\BotOption;
use App\Observers\OrderProductItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[ObservedBy([OrderProductItemObserver::class])]
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

    public function createLicense($duration){
        return $this->license()->create([
            'duration' => $duration,
            'user_id' => Auth::user()->id,
            'order_product_item_id' => $this->id,
            'bot_option_id' => $this->orderable_id,
            'license' => Str::uuid7(),
        ]);
    }

    public function license(){
        return $this->hasOne(BotLicense::class);
    }

    public function orderProduct(){
        return $this->belongsTo(OrderProduct::class);
    }

    public function orderable(){
        return $this->morphTo('orderable');
    }

    // public function licenses(){
    //     return $this->hasManyThrough(BotLicense::class, BotOption::class)->where('orderable_type', BotOption::class);
    // }
}
