
<div>
    <div class="flex justify-between px-3 py-2">
        <div class="flex flex-col">
            <span>Order {{ $chat->order_id }}</span>
            <span class="text-sm text-red-500">
                {{Str::title($chat->status)}}
            </span>
        </div>
        <div class="flex">
            <x-filament::link tag="button" wire:click="$parent.acceptChat({{$chat->id}})">
                <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5"/>
            </x-filament::link>
        </div>
    </div>
</div>
