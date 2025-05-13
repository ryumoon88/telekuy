<?php

namespace App\Models\Shop;

use App\Enums\AccountStatus;
use App\Enums\AccountTransactionType;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Events\Client\UserBalanceUpdated;
use App\Interface\HasTotal;
use App\Models\Chat;
use App\Models\Telegram\Account;
use App\Models\Telegram\AccountTransaction;
use App\Models\Telegram\Bot;
use App\Models\Telegram\BotLicense;
use App\Models\Telegram\BotOption;
use App\Models\Transaction\Transaction;
use App\Models\User;
use App\Observers\OrderObserver;
use Cknow\Money\Money;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasUuids, SoftDeletes;
    use HasRelationships;

    protected $fillable = [
        'buyer_id',
        'status',
        'payment_status',
        'extra',
        'handler',
    ];

    protected $casts = [
        'extra' => 'json',
        'status' => OrderStatus::class,
        'payment_status' => OrderPaymentStatus::class,
    ];

    protected $appends = ['total', 'has_accounts', 'has_chat'];

    public function getTotalAttribute(): int
    {
        return $this->orderProductItems->sum(fn($item) => $item->price * $item->quantity);
    }

    public function getHasAccountsAttribute()
    {
        return !$this->getAccounts()->isEmpty();
    }

    public function pay()
    {
        $user = Auth::user();
        $amount = $this->getTotalAttribute();

        if ($user->balance < $amount) {
            Notification::make()
                ->title('Insufficient Balance')
                ->body("You don't have enough balance.")
                ->danger()
                ->color('danger')
                ->send();

            return;
        }

        $orderProductItems = $this->orderProductItems->groupBy('orderable_type');
        foreach ($orderProductItems as $orderable_type => $orderProductItems) {
            $orderable_ids = collect($orderProductItems->pluck('orderable_id'));
            $orderProductItem_ids = collect($orderProductItems->pluck('id'));

            if ($orderable_type == BotOption::class) {
                $orderable_type::find($orderable_ids);
            }

            $updates = [];
            $accountTransactions = collect([]);

            if ($orderable_type == Account::class) {
                $updates['status'] = AccountStatus::Sold;
                foreach ($orderProductItems as $orderProductItem) {
                    $accountTransactions->add([
                        'id' => Str::orderedUuid()->toString(),
                        'account_id' => $orderProductItem->orderable_id,
                        'type' => AccountTransactionType::Selling,
                        'causer_id' => $user->id,
                        'amount' => $orderProductItem->orderable->selling_price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                AccountTransaction::insert($accountTransactions->toArray());
                OrderProductItem::whereIn('id', $orderProductItem_ids)->update(['completed' => true]);
            } else if ($orderable_type == BotOption::class) {
            }

            $orderable_type::whereIn('id', $orderable_ids)->update($updates);
        }

        $user->balance -= $amount;

        $this->transactions()->create([
            'amount' => $amount,
            'type' => TransactionType::Payment,
        ]);

        UserBalanceUpdated::dispatch($user, $user->balance);

        $this->update([
            'status' => OrderStatus::Pending,
            'payment_status' => OrderPaymentStatus::Paid,
        ]);

        $user->save();
    }

    public function getAccounts(): Collection
    {
        return $this->orderProductItems()
            ->with('orderable')
            ->where('orderable_type', Account::class)
            ->get()
            ->map(fn($item) => $item->orderable);
    }

    public static function GetAttachment(Order $order)
    {
        $accounts = $order->getAccounts();
        $bots = $order->licenses()->get();

        $output = [];
        if (!$accounts->isEmpty())
            $output['accounts'] = $accounts;
        if (!$bots->isEmpty())
            $output['bots'] = $bots;

        return $output;
    }

    public function getHasChatAttribute()
    {
        return !!$this->chat;
    }

    // Relations
    public function licenses()
    {
        return $this->hasManyDeep(
            BotLicense::class,
            [OrderProduct::class, OrderProductItem::class],
        );
    }

    public function chat()
    {
        return $this->hasOne(Chat::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function orderProductItems()
    {
        return $this->hasManyThrough(OrderProductItem::class, OrderProduct::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
