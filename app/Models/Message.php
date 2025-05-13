<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
    ];

    public function sender(){
        return $this->belongsTo(User::class);
    }
    
    public function chat(){
        return $this->belongsTo(Chat::class);
    }

}
