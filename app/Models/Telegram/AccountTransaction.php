<?php

namespace App\Models\Telegram;

use App\Enums\AccountTransactionType;
use App\Models\User;
use App\Observers\AccountTransactionObserver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy([AccountTransactionObserver::class])]
class AccountTransaction extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'referral_id',
        'causer_id',
        'amount',
    ];

    protected $casts = [
        'type' => AccountTransactionType::class
    ];

    // Relations

    public function account(){
        return $this->belongsTo(Account::class);
    }

    public function referral(){
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function causer() {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
