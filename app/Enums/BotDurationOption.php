<?php

namespace App\Enums;

enum BotDurationOption: string
{
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
    case Day = 'day';
}
