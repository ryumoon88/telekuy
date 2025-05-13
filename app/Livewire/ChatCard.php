<?php

namespace App\Livewire;

use App\Events\Client\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class ChatCard extends Component
{
    public Chat $chat;

    public $messages = [];

    public string $messageToSend = '';

    public function mount()
    {
        $this->messages = Message::where('chat_id', $this->chat->id)->orderBy('created_at', 'asc')->get();
    }

    #[On('echo-private:chat.{chat.id},Client\\MessageSent')]
    public function messageReceived($data)
    {
        $message = Message::find($data['message']['id']);
        $this->pushMessage($message);
    }

    #[On('echo-private:chat.{chat.id},Client\\ChatClosed')]
    public function chatClosed()
    {
        $this->dispatch('refetch-chats');
    }

    public function send()
    {
        if (empty($this->messageToSend)) return;

        $message = $this->chat->messages()->create([
            'chat_id' => $this->chat->id,
            'sender_id' => Auth::user()->id,
            'message' => $this->messageToSend,
        ]);

        $this->pushMessage($message);
        $this->messageToSend = '';

        broadcast(new MessageSent($message))->toOthers();
    }

    public function pushMessage(Message $message)
    {
        $this->messages = [...$this->messages, $message];
    }

    public function render()
    {
        return view('livewire.chat-card');
    }
}
