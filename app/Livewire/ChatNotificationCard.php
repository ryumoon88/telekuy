<?php

namespace App\Livewire;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatNotificationCard extends Component
{
    public Chat $chat;

    public bool $buttonDisabled = false;

    public function render()
    {
        return view('livewire.chat-notification-card');
    }
}
