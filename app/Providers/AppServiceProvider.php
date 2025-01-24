<?php

namespace App\Providers;

use Filament\Infolists\Infolist;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
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
        FilamentColor::register([
            'pending' => Color::Yellow,       // Pending actions (neutral but attention-grabbing)
            'accepted' => Color::Green,       // Accepted (positive)
            'processing' => Color::Blue,      // Processing (in progress, calming and neutral)
            'canceled' => Color::Red,         // Canceled (negative, alarming)
            'completed' => Color::Emerald,    // Completed (success with a polished tone)
            'edit' => Color::Teal,            // Edit (subtle, inviting change or adjustment)
            'delete' => Color::Rose,          // Delete (negative, but slightly softer than pure red)
        ]);

        Vite::prefetch(concurrency: 3);

        Number::useLocale('id');
        Number::useCurrency('idr');
        Table::$defaultCurrency = 'idr';
        Table::$defaultNumberLocale = 'id';
        Infolist::$defaultCurrency = 'idr';
        Infolist::$defaultNumberLocale = 'id';

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn (): string => Blade::render('@livewire(\'user-balance\')')
        );

        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            // Check if the model class is under the 'Telegram' namespace
            if (strpos($modelClass, 'App\Models\Telegram') === 0) {
                // Remove 'App\Models\Telegram' from the start of the model class and replace it with 'App\Policies\Telegram'
                $policyClass = 'App\Policies\Telegram\\'.class_basename($modelClass).'Policy';

                if (class_exists($policyClass)) {
                    return $policyClass;
                }
            }
            if (strpos($modelClass, 'App\Models') === 0) {
                // Remove 'App\Models\Telegram' from the start of the model class and replace it with 'App\Policies\Telegram'
                $policyClass = 'App\Policies\\'.class_basename($modelClass).'Policy';

                if (class_exists($policyClass)) {
                    return $policyClass;
                }
            }

            // Return null to let Laravel guess the default policy if necessary
            return null;
        });

    }
}
