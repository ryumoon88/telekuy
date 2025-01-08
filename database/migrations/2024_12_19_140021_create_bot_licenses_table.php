<?php

use App\Models\Telegram\Bot;
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
        Schema::create('bot_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Bot::class);
            $table->string('license');
            $table->boolean('active');
            $table->dateTime('expired_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_licenses');
    }
};
