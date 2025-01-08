<?php

namespace App\Filament\Widgets;

use App\Models\Transaction\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsTransactionOverview extends BaseWidget
{
    // protected ?string $heading = 'Transactions Overview';

    // protected ?string $description = 'An overview of transactions statistic.';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Transactions', Transaction::all()->count()),
            Stat::make('Income', Number::currency(Transaction::all()->sum('amount')))
        ];
    }
}
