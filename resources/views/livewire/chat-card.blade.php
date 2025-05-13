<div>
    <x-filament::section
        collapsible
        collapsed
        persist-collapsed
        class="chat-card"
        id="{{$chat->id}}"
    >
        <x-slot name="heading">
            <flex class="flex flex-col">
                <span>{{ $chat->user->name }}</span>
                <span class="text-xs font-normal">#{{$chat->order_id}}</span>
            </flex>
        </x-slot>

        <div class="flex flex-col justify-between">
            <div class="flex flex-col gap-2 p-6 h-[400px] overflow-y-auto">
                @forelse ($messages as $message)
                    <livewire:chat-bubble :message="$message" wire:key='{{$message->id}}' />
                @empty
                    No messages yet
                @endforelse
            </div>
            <div class="flex w-full p-3">
                <x-filament::input.wrapper class="w-full">
                    <x-filament::input
                        type="textarea"
                        wire:model="messageToSend"
                    />
                    <x-slot name="suffix">
                        <x-filament::button size="xs" wire:click="send">
                            <x-filament::icon icon="mdi-send" class="w-5 h-5"/>
                        </x-filament::button>
                    </x-slot>
                </x-filament::input.wrapper>
            </div>
        </div>
    </x-filament::section>
</div>
