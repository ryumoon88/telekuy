<?php

namespace App\Filament\Widgets;

use App\Enums\AccountStatus;
use App\Models\Telegram\Account;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsAccountOverview extends BaseWidget
{
    // protected ?string $heading = 'Accounts Overview';

    // protected ?string $description = 'An overview of accounts statistic.';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Account', Account::all()->count())
                ->description('Total Accounts'),
            Stat::make('Available Account', Account::where('status', AccountStatus::Available)->count())
                ->description('Available Accounts')
                ->color('success'),
            Stat::make('Sold Account', Account::where('status', AccountStatus::Sold)->count())
                ->description('Sold Accounts')
                ->color('danger'),
        ];
    }
}
