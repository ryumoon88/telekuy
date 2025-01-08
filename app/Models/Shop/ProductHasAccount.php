<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class ProductHasAccount extends Model
{
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
