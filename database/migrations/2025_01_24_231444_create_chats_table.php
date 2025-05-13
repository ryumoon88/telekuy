<?php

use App\Models\Chat;
use App\Models\Shop\Order;
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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->nullable();
            $table->foreignIdFor(User::class, 'user_id');
            $table->foreignIdFor(User::class, 'admin_id')->nullable();
            $table->enum('status', ['pending', 'accepted', 'denied'])->default('pending');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function(Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Chat::class);
            $table->foreignIdFor(User::class, 'sender_id');
            $table->text('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
