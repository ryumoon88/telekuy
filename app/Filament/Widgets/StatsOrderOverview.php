<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOrderOverview extends BaseWidget
{
    // protected ?string $heading = 'Orders Overview';

    // protected ?string $description = 'An overview of orders statistic.';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Orders', Order::count()),
            Stat::make('Pending Orders', Order::where('status', OrderStatus::Pending)->count()),
            Stat::make('Completed Orders', Order::where('status', OrderStatus::Completed)->count()),
        ];
    }
}
