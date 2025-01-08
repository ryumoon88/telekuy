<?php

use App\Models\Shop\Product;
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
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('country_code', 10);
            $table->string('phone_number', 20);
            $table->string('path');
            $table->enum('status', ['available', 'bundled', 'sold', 'invalid', 'used', 'booked'])->default('available');
            $table->integer('selling_price');
            $table->foreignIdFor(User::class, 'downloader_id')->nullable();
            // $table->foreignIdFor(Bundle::class) ->nullable();
            // $table->foreignIdFor(Product::class);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
