<?php

use App\Models\Shop\Order;
use App\Models\Telegram\Referral;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['deposit', 'withdrawal', 'payment', 'top-up']);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->foreignIdFor(User::class, 'causer_id')->nullable();
            $table->foreignIdFor(Order::class);
            $table->integer('amount');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
