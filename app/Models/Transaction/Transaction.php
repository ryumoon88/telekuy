<?php

namespace App\Models\Transaction;

use App\Enums\TransactableType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Shop\Order;
use App\Models\Telegram\Referral;
use App\Models\User;
use App\Observers\TransactionObserver;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nekoding\Tripay\TripayFacade;

#[ObservedBy([TransactionObserver::class])]
class Transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'type',
        'status',
        'causer_id',
        'order_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'type' => TransactionType::class,
    ];

    public static function TopUp(User $user, int $amount): static{
        $transaction = static::create([
            'causer_id' => $user->id,
            'amount' => abs($amount),
            'type' => TransactionType::TopUp,
            'method' => '',
            'reference' => '',
        ]);
        return $transaction;
    }

    public function accept(){
        return $this->update(['status' => TransactionStatus::Success]);
    }

    public function causer(){
        return $this->belongsTo(User::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }
}
