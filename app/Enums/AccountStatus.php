<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccountStatus: string implements HasColor, HasLabel
{
    case Available = 'available';
    case Bundled = 'bundled';
    case Sold = 'sold';
    case Invalid = 'invalid';
    case Used = 'used';
    case Booked = 'booked';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Bundled => 'Bundled',
            self::Sold => 'Sold',
            self::Invalid => 'Invalid',
            self::Used => 'Used',
            self::Booked => 'Booked',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Available => 'success',
            self::Bundled, self::Used => 'warning',
            self::Sold, self::Booked => 'gray',
            self::Invalid => 'danger',
            self::Invalid => 'danger',
        };
    }
}
