<?php

use App\Models\Shop\Order;
use App\Models\Shop\OrderProduct;
use App\Models\Shop\Product;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->foreignIdFor(User::class, 'buyer_id')->nullable();
            $table->enum('status', ['pending', 'accepted', 'canceled', 'completed'])->default('pending');
            $table->json('extra')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_products', function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class);
            $table->foreignIdFor(Product::class);
            $table->timestamps();
        });

        Schema::create('order_product_items', function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OrderProduct::class);
            $table->nullableUuidMorphs('orderable');
            $table->boolean('completed')->default(false);
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
        Schema::dropIfExists('orders');
    }
};
