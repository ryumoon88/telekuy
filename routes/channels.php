<?php

use App\Broadcasting\UserChannel;
use App\Events\UserBalanceUpdated;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', UserChannel::class);
