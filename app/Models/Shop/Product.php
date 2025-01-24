<?php

namespace App\Models\Shop;

use App\Enums\AccountStatus;
use App\Enums\ProductType;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bot;
use App\Models\Telegram\BotOption;
use App\Models\Telegram\Bundle;
use App\Models\Telegram\Referral;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'active',
        'price',
        'code',
    ];

    protected $casts = [
        'type' => ProductType::class
    ];

    public function attachAccount(Collection|Account $accounts){
        if(!$accounts instanceof Collection)
            $accounts = collect([$accounts]);
        $this->accounts()->attach($accounts);
    }

    public function bot(){
        return $this->hasOne(Bot::class);
    }

    public function botOptions(){
        return $this->hasManyThrough(BotOption::class, Bot::class);
    }

    public function accounts(){
        return $this->belongsToMany(Account::class, ProductHasAccount::class);
    }

    public function bundles(){
        return $this->belongsToMany(Bundle::class, ProductHasBundle::class);
    }

    public function referral(){
        return $this->hasOne(Referral::class);
    }
}
