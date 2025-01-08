<?php

use App\Enums\AccountTransactionType;
use App\Models\Telegram\Account;
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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->foreignIdFor(Account::class);
            $table->enum('type', array_column(AccountTransactionType::cases(), 'value'));
            $table->integer('amount');
            $table->foreignIdFor(Referral::class)->nullable();
            $table->foreignIdFor(User::class, 'causer_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
