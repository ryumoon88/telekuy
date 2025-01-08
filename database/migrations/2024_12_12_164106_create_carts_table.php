<?php

use App\Models\Shop\Cart;
use App\Models\Shop\CartProduct;
use App\Models\Shop\Product;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bundle;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->nullable(); // Null for guest carts
            $table->string('session_id')->nullable();          // For guest carts
            $table->timestamps();
        });

        Schema::create('cart_products', function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cart::class);
            $table->foreignIdFor(Product::class);
            $table->timestamps();
        });

        Schema::create('cart_product_items', function(Blueprint $table) {
            $table->id('id');
            $table->foreignIdFor(CartProduct::class);
            $table->nullableUuidMorphs('cartable');
            $table->integer('quantity');
            $table->integer('price');
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_product_items');
        Schema::dropIfExists('cart_products');
        Schema::dropIfExists('carts');
    }
};
