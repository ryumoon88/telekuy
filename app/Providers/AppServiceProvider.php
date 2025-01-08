<?php

namespace App\Providers;

use App\Models\Telegram\Account;
use App\Policies\Telegram\AccountPolicy;
use Cknow\Money\Money;
use Filament\Infolists\Infolist;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Number::useLocale('id');
        Number::useCurrency('idr');
        Table::$defaultCurrency = 'idr';
        Table::$defaultNumberLocale = 'id';
        Infolist::$defaultCurrency = 'idr';
        Infolist::$defaultNumberLocale = 'id';

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn(): View => view('components.user-balance')
        );
        
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            // Check if the model class is under the 'Telegram' namespace
            if (strpos($modelClass, 'App\Models\Telegram') === 0) {
                // Remove 'App\Models\Telegram' from the start of the model class and replace it with 'App\Policies\Telegram'
                $policyClass = 'App\Policies\Telegram\\' . class_basename($modelClass) . 'Policy';

                if (class_exists($policyClass)) {
                    return $policyClass;
                }
            }
            if (strpos($modelClass, 'App\Models') === 0) {
                // Remove 'App\Models\Telegram' from the start of the model class and replace it with 'App\Policies\Telegram'
                $policyClass = 'App\Policies\\' . class_basename($modelClass) . 'Policy';

                if (class_exists($policyClass)) {
                    return $policyClass;
                }
            }

            // Return null to let Laravel guess the default policy if necessary
            return null;
        });

        // Gate::policy(Account::class, AccountPolicy::class);

    }
}
