<?php

namespace App\Observers;

use App\Models\Telegram\BotOption;

class BotOptionObserver
{
    /**
     * Handle the BotOption "creating" event.
     */
    public function creating($botOption): void
    {

    }


    /**
     * Handle the BotOption "created" event.
     */
    public function created(BotOption $botOption): void
    {
        //
    }

    /**
     * Handle the BotOption "updated" event.
     */
    public function updated(BotOption $botOption): void
    {
        //
    }

    /**
     * Handle the BotOption "deleted" event.
     */
    public function deleted(BotOption $botOption): void
    {
        //
    }

    /**
     * Handle the BotOption "restored" event.
     */
    public function restored(BotOption $botOption): void
    {
        //
    }

    /**
     * Handle the BotOption "force deleted" event.
     */
    public function forceDeleted(BotOption $botOption): void
    {
        //
    }
}
