<?php

namespace App\Livewire;

use App\Events\Client\ChatAccepted;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatNotifications extends Component
{
    public $isOpen = false;

    public $unhandledChats = [];

    protected $listeners = [
        'echo-private:admin,Client\\SupportRequested' => 'loadUnhandledChats'
    ];

    public function mount(){
        $this->loadUnhandledChats();
    }

    public function loadUnhandledChats() {
        $this->unhandledChats = Chat::where('status', 'pending')->get();
    }

    public function acceptChat(Chat $chat) {
        $chat->update([
            'status' => 'accepted',
            'admin_id' => Auth::user()->id,
        ]);

        $this->dispatch('chat-accepted')->to(ChatsContainer::class);
        broadcast(new ChatAccepted($chat->user))->toOthers();

        $this->loadUnhandledChats();
    }

    public function toggleOpen(){
        $this->isOpen = !$this->isOpen;
    }


    public function render()
    {
        return view('livewire.chat-notifications');
    }
}
