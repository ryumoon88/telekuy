<?php

namespace App\Models\Shop;

use App\Models\Telegram\BotLicense;
use App\Models\Telegram\BotOption;
use App\Models\User;
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
        'completed',
        'quantity',
        'price',
        'extra',
    ];

    protected $casts = [
        'extra' => 'json'
    ];

    public function createOrUpdateLicense($license = null)
    {
        $duration = $this->orderable->duration;

        if ($this->license)
            $this->license->update([
                'license' => $license ?? Str::uuid7()
            ]);
        else
            $this->license()->create([
                'duration' => $duration,
                'user_id' => Auth::user()->id,
                'order_product_item_id' => $this->id,
                'bot_option_id' => $this->orderable_id,
                'license' => $license ?? Str::uuid7(),
            ]);
        $this->update(['completed' => true]);
        return $this->license;
    }

    public function license()
    {
        return $this->hasOne(BotLicense::class);
    }

    public function orderProduct()
    {
        return $this->belongsTo(OrderProduct::class);
    }

    public function orderable()
    {
        return $this->morphTo('orderable');
    }

    public function handler()
    {
        return $this->belongsTo(User::class);
    }

    // public function licenses(){
    //     return $this->hasManyThrough(BotLicense::class, BotOption::class)->where('orderable_type', BotOption::class);
    // }
}
