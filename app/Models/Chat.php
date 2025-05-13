<?php

namespace App\Models;

use App\Models\Shop\Order;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{

    protected $fillable = [
        'order_id',
        'user_id',
        'admin_id',
        'status',
        'closed_at',
    ];


    public function getIsClosedAttribute(){
        return $this->isClosed();
    }

    public function isClosed(){
        return $this->closed_at;
    }

    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function admin(){
        return $this->belongsTo(User::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }
}
