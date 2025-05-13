<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Canceled = 'canceled';
    case Completed = 'completed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Canceled => 'Canceled',
            self::Completed => 'Completed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processing => 'info',
            self::Canceled => 'danger',
            self::Completed => 'success',
        };
    }
}
