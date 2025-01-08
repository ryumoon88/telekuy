<?php

namespace App\Models\Telegram;

use App\Enums\AccountStatus;
use App\Models\Shop\Product;
use App\Models\Shop\ProductHasBundle;
use App\Models\User;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Znck\Eloquent\Traits\BelongsToThrough;

class Bundle extends Model
{
    use HasUuids, SoftDeletes, BelongsToThrough;


    protected $fillable = [
        'name',
        'selling_price',
    ];

    protected $casts = [
    ];

    public function attachAccount(Collection|Account $accounts){
        if(!$accounts instanceof Collection)
            $accounts = collect([$accounts]);
        $this->accounts()->attach($accounts);
        Account::whereIn('id', $accounts->pluck('id'))->update(['status' => AccountStatus::Bundled]);
    }

    public function detachAccount(Collection|Account $accounts){
        if(!$accounts instanceof Collection)
            $accounts = collect([$accounts]);
        $this->accounts()->detach($accounts);
        Account::whereIn('id', $accounts->pluck('id'))->update(['status' => AccountStatus::Available]);
    }

    public function downloader(){
        return $this->belongsTo(User::class);
    }

    public function accounts() {
        return $this->belongsToMany(Account::class, BundleHasAccount::class);
    }

    public function product(){
        return $this->belongsToThrough(
            Product::class,
            ProductHasBundle::class,
            localKey: 'id',
            foreignKeyLookup: [ProductHasBundle::class => 'id'],
            localKeyLookup: [ProductHasBundle::class => 'bundle_id'],
        );
    }
}
