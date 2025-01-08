<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class BotLicense extends Model
{
    protected $fillable = [
        'bot_id',
        'license',
        'active',
        'expired_at'
    ];
}
