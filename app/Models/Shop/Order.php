<?php

namespace App\Models\Shop;

use App\Enums\AccountStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Interface\HasTotal;
use App\Models\Telegram\Account;
use App\Models\Telegram\AccountTransaction;
use App\Models\Transaction\Transaction;
use App\Models\User;
use App\Observers\OrderObserver;
use Cknow\Money\Money;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

#[ObservedBy(OrderObserver::class)]
class Order extends Model implements HasTotal
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'buyer_id',
        'status',
        'extra',
    ];

    protected $casts = [
        'extra' => 'json',
        'status' => OrderStatus::class,
    ];

    public function getTotal(): int
    {
        return $this->orderProductItems()->sum('price');
    }

    public function pay(){
        $user = Auth::user();
        $amount = $this->getTotal();

        if($user->balance < $amount) {
            Notification::make()
                ->title('Insufficient Balance')
                ->body("You don't have enough balance.")
                ->danger()
                ->color('danger')
                ->send();
            return;
        }

        $user->balance -= $amount;
        $this->transactions()->create([
            'amount' => $amount,
            'type' => TransactionType::Payment,
        ]);

        $items = $this->orderProductItems->groupBy('orderable_type');
        foreach($items as $orderable_type => $orderables){
            $orderable_ids = $orderables->pluck('orderable_id');

            $updates = [];

            switch($orderable_type){
                case Account::class:
                    $updates['status'] = AccountStatus::Sold;
                    break;
            }

            $orderable_type::whereIn('id', $orderable_ids)->update($updates);
        }

        $this->update(['status' => OrderStatus::Completed]);
        $user->save();
    }

    // Relations

    public function buyer(){
        return $this->belongsTo(User::class);
    }

    public function orderProducts(){
        return $this->hasMany(OrderProduct::class);
    }

    public function orderProductItems(){
        return $this->hasManyThrough(OrderProductItem::class, OrderProduct::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
