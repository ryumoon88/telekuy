<?php

namespace App\Models\Telegram;

use App\Enums\AccountStatus;
use App\Enums\AccountTransactionType;
use App\Enums\TransactionType;
use App\Models\Shop\Cart;
use App\Models\Shop\CartItem;
use App\Models\Shop\CartProductItem;
use App\Models\Shop\OrderProductItem;
use App\Models\Shop\Product;
use App\Models\Shop\ProductHasAccount;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Cknow\Money\Casts\MoneyIntegerCast;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Znck\Eloquent\Traits\BelongsToThrough;

class Account extends Model
{
    use HasFactory, HasUuids, BelongsToThrough, SoftDeletes;

    protected $fillable = [
        'country_code',
        'phone_number',
        'path',
        'status',
        'selling_price',
    ];

    protected $casts = [
        'status' => AccountStatus::class,
    ];

    public static function ImportAccounts($data): bool{
        $selling_price = $data['selling_price'];
        $purchase_price = $data['purchase_price'];
        $country_code = $data['country_code'];
        $accounts = explode(',', $data['accounts']);

        try {
            foreach($accounts as $account){
                $account = explode(':', $account);
                $phone_number = $account[0];
                $path = $account[1];

                $model = static::create([
                    'country_code' => $country_code,
                    'phone_number' => $phone_number,
                    'path' => $path,
                    'selling_price' => $selling_price,
                ]);

                $model->transactions()->create([
                    'type' => AccountTransactionType::Purchase,
                    'causer_id' => Auth::id(),
                    'amount' => $purchase_price,
                ]);
            }
            return true;
        } catch (\Throwable $th) {
            throw new Exception($th, 1, $th);
        }
    }

    public static function DownloadAccounts($name, object $accounts) {
        $accounts = is_array($accounts) ? $accounts : new Collection($accounts);
        if (!Storage::disk('local')->exists('zip'))
            Storage::disk('local')->makeDirectory('zip');
        $filename = $name.'-' . uniqid() . '.zip';
        $destination = Storage::disk('local')->path('zip/' . $filename);
        $folders = $accounts->map(fn($account) => Storage::disk('local')->path($account->path))->toArray();
        if (zipFolders($folders, $destination))
            return response()->download($destination, );
        return false;
    }

    public function sold(){
        $this->update(['status' => AccountStatus::Sold]);
    }

    // Relations
    public function cartProductItem(){
        return $this->morphOne(CartProductItem::class, 'cartable');
    }

    public function orderProductItem(){
        return $this->morphOne(OrderProductItem::class, 'orderable');
    }

    public function downloader(){
        return $this->belongsTo(User::class);
    }

    public function transactions(){
        return $this->hasMany(AccountTransaction::class);
    }

    public function bundle(){
        return $this->belongsToThrough(
            Bundle::class,
            BundleHasAccount::class,
            localKey: 'id',
            foreignKeyLookup: [BundleHasAccount::class => 'id'],
            localKeyLookup: [BundleHasAccount::class => 'account_id'],
        );
    }

    public function product(){
        return $this->belongsToThrough(
            Product::class,
            ProductHasAccount::class,
            localKey: 'id',
            foreignKeyLookup: [ProductHasAccount::class => 'id'],
            localKeyLookup: [ProductHasAccount::class => 'account_id'],
        );
    }
}
