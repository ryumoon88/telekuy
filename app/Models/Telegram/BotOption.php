<?php

namespace App\Models\Telegram;

use App\Models\Shop\CartProductItem;
use App\Models\Shop\Order;
use App\Models\Shop\OrderProductItem;
use App\Models\Shop\Product;
use App\Observers\BotOptionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Znck\Eloquent\Traits\BelongsToThrough;
use Illuminate\Database\Eloquent\Builder;


#[ObservedBy([BotOptionObserver::class])]
class BotOption extends Model
{
    use BelongsToThrough;

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

    // relations
    public function bot(){
        return $this->belongsTo(Bot::class);
    }

    public function licenses(){
        return $this->hasMany(BotLicense::class);
    }

    public function product(){
        return $this->belongsToThrough(Product::class, Bot::class);
    }

    public function cartProductItem(){
        return $this->morphOne(CartProductItem::class, 'cartable');
    }

    public function orderProductItem(){
        return $this->morphOne(OrderProductItem::class, 'orderable');
    }
}
