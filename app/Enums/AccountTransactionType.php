<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccountTransactionType: string implements HasColor, HasLabel
{
    case Purchase = 'purchase';
    case Referral = 'referral';
    case ReferralFee = 'referral_fee';
    case Selling = 'selling';

    public function getColor(): string|array|null
    {
        return match($this) {
            self::Purchase => 'danger',
            self::ReferralFee => 'danger',
            self::Referral => 'success',
            self::Selling => 'success',
        };
    }

    public function getLabel(): ?string
    {
        return match($this){
            self::Purchase => 'Purchase',
            self::ReferralFee => 'Referral Fee',
            self::Referral => 'Referral',
            self::Selling => 'Selling',
        };
    }
}
