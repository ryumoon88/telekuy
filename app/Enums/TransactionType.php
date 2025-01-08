<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasLabel, HasColor
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Payment = 'payment';
    case TopUp = 'top-up';

    public function getLabel(): ?string
    {
        return match($this){
            self::Deposit => 'Deposit',
            self::Withdrawal => 'Withdrawal',
            self::Payment => 'Payment',
            self::TopUp => 'Top Up',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::Deposit,
            self::Payment,
            self::TopUp, => 'success',
            self::Withdrawal, => 'danger',
        };
    }
}
