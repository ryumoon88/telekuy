<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReferralType: string implements HasColor, HasLabel
{
    case OneTime = 'one time';
    case Active = 'active';

    public function getColor(): string|array|null
    {
        return match($this) {
            self::OneTime => 'info',
            self::Active => 'success',
        };
    }

    public function getLabel(): ?string
    {
        return match($this){
            self::OneTime => 'One Time',
            self::Active => 'Active',
        };
    }
}
