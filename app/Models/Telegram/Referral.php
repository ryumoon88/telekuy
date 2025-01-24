<?php

namespace App\Models\Telegram;

use App\Enums\ReferralType;
use App\Models\Shop\CartProductItem;
use App\Models\Shop\OrderProductItem;
use App\Models\Shop\Product;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Referral extends Model
{
    use HasUuids, SoftDeletes;


    protected $fillable = [
        'name',
        'price',
        'type',
        'fee',
    ];

    protected $casts = [
        'type' => ReferralType::class,
    ];

    public function myFee(){
        return $this->userFees()->where('user_id', Auth::id())->first();
    }

    public function userFees(){
        return $this->belongsToMany(User::class, UserReferralFee::class)
            ->withPivot(['fee']);
    }

    public function transactions(){
        return $this->hasMany(AccountTransaction::class);
    }

    public function accounts(){
        return $this->belongsToMany(Account::class, AccountTransaction::class,);
    }

    public function users(){
        return $this->belongsToMany(User::class, UserReferralFee::class)
            ->withPivot(['fee']);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function cartProductItem(){
        return $this->morphOne(CartProductItem::class, 'cartable');
    }

    public function orderProductItem(){
        return $this->morphOne(OrderProductItem::class, 'orderable');
    }
}
