<?php

namespace App\Enums;

use App\Models\Shop\Order;
use App\Models\Telegram\Account;
use Filament\Support\Contracts\HasLabel;

enum TransactableType: string implements HasLabel
{
    case Account = Account::class;
    case Order = Order::class;

    public function getLabel(): ?string
    {
        return match($this){
            self::Account => 'Account',
            self::Order => 'Order',
        };
    }
}
