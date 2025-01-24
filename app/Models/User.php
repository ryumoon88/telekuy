<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Shop\Cart;
use App\Models\Shop\Order;
use App\Models\Telegram\Referral;
use App\Models\Telegram\UserReferralFee;
use Cknow\Money\Casts\MoneyCast;
use Cknow\Money\Casts\MoneyIntegerCast;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // functions

    public function getCart(){
        return $this->cart()->firstOrCreate();
    }

    public function latestOrders(){
        return $this->orders()->orderBy('created_at', 'desc')->limit(5)->get();
    }

    // relations
    public function cart(){
        return $this->hasOne(Cart::class);
    }

    public function orders(){
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function referralFees(){
        return $this->belongsToMany(Referral::class, UserReferralFee::class)
            ->withPivot(['fee']);
    }
}
