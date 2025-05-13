<div>
    <style>
        .chat-card .fi-section-content {
            padding: 0;
        }
    </style>
    <div class="fixed bottom-0 right-0 flex items-end gap-3">
        @foreach ($activeChats as $chat)
            <livewire:chat-card :chat="$chat" :key="$chat->id"/>
        @endforeach
    </div>
</div>
