<?php

namespace App\Livewire;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatsContainer extends Component
{
    public $activeChats = [];

    protected $listeners = [
        'refetch-chats' => 'loadChats',
        'chat-accepted' => 'loadChats',
    ];

    public function mount()
    {
        $this->loadChats();
    }

    public function loadChats()
    {
        $this->activeChats = Chat::where('admin_id', Auth::id())->where('status', 'accepted')->where('closed_at', null)->get();
    }

    public function render()
    {
        return view('livewire.chats-container');
    }
}
