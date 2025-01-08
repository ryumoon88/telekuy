<?php

namespace App\Observers;

use App\Enums\ProductType;
use App\Models\Shop\Product;
use Illuminate\Support\Str;

class ProductObserver
{

    /**
     * Handle the Product "creating" event.
     */
    public function creating(Product $product): void
    {
        if(!$product->code){
            $newstr = preg_replace('/[^a-zA-Z0-9\']/', '', $product->name);
            $newstr = str_replace("'", '', $newstr);
            $product->code = Str::upper($newstr);
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
