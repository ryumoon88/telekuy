<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasColor, HasLabel
{
    case Account = 'account';
    case Referral = 'referral';
    // case Bundle = 'bundle';
    case Bot = 'bot';

    public function getLabel(): ?string
    {
        return match($this){
            self::Account => 'Account',
            self::Referral => 'Referral',
            // self::Bundle => 'Bundle',
            self::Bot => 'Bot',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::Account => 'info',
            self::Referral => 'warning',
            // self::Bundle => 'warning',
            self::Bot => 'gray',
        };
    }
}
