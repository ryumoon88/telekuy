@php
    $chatStatuses = [
        'pending' => [
            'class' => 'text-orange-500',
        ],
    ]
@endphp

<div>
    <x-filament::dropdown placement="bottom-end" width="lg">
        <x-slot name="trigger">
            <x-filament::link tag="button">
                <x-filament::icon icon="heroicon-s-chat-bubble-left-right" class="w-5 h-5"/>
            </x-filament::link>
        </x-slot>
        
        
        @forelse($unhandledChats as $unhandledChat) 
            <x-filament::dropdown.list wire:key="{{$unhandledChat->id}}">
                <livewire:chat-notification-card :chat="$unhandledChat" />
            </x-filament::dropdown.list>
        @empty
            <x-filament::dropdown.list>
                <div class="px-3 py-2">  
                    There is currently no support requested
                </div>
            </x-filament::dropdown.list>
        @endforelse
    </x-filament::dropdown>
</div>
