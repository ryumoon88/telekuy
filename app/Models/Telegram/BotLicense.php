<?php

namespace App\Models\Telegram;

use App\Models\Shop\Order;
use App\Models\Shop\OrderProductItem;
use App\Models\User;
use Carbon\Doctrine\CarbonType;
use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Traits\BelongsToThrough;
use Illuminate\Database\Eloquent\Builder;

class BotLicense extends Model
{
    use BelongsToThrough;

    protected $fillable = [
        'user_id',
        'order_product_item_id',
        'bot_option_id',
        'duration',
        'license',
        'active',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime'
    ];

    // Relations

    public function orderProductItem() {
        return $this->belongsTo(OrderProductItem::class);
    }

    public function botOption(){
        return $this->belongsTo(BotOption::class);
    }

    public function bot(){
        return $this->belongsToThrough(Bot::class, BotOption::class);
    }

    public function owner(){
        return $this->belongsTo(User::class);
    }
}
