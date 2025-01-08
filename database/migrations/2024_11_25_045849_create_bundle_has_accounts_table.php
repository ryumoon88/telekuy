<?php

use App\Models\Telegram\Account;
use App\Models\Telegram\Bundle;
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
        Schema::create('bundle_has_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Bundle::class);
            $table->foreignIdFor(Account::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_has_accounts');
    }
};
