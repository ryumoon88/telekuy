<?php

use App\Broadcasting\AdminChannel;
use App\Broadcasting\ChatChannel;
use App\Broadcasting\UserChannel;
use App\Events\UserBalanceUpdated;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', UserChannel::class, ['guards' => ['web', 'admin']]);
Broadcast::channel('chat.{chat}', ChatChannel::class, ['guards' => ['web', 'admin']]);
Broadcast::channel('admin', AdminChannel::class, ['guards' => ['web', 'admin']]);
