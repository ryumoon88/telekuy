@php
    $class = $message->sender_id == Auth::user()->id ? 'text-end' : '';
@endphp

<div>
    @if ($message->sender_id == Auth::user()->id)
    <div class="flex gap-2.5 justify-end">
        <div class="">
            <div class="grid mb-2">
                <h5 class="pb-1 text-sm font-semibold leading-snug text-right text-gray-900">You</h5>
                <div class="px-3 py-2 bg-orange-600 rounded">
                    <h2 class="text-sm font-normal leading-snug text-white">{{$message->message}}</h2>
                </div>
                <div class="inline-flex items-center justify-start">
                    <h3 class="py-1 text-xs font-normal leading-4 text-gray-500">05:14 PM</h3>
                </div>
            </div>
        </div>
        {{-- <img src="https://pagedone.io/asset/uploads/1704091591.png" alt="Hailey image" class="w-10 h-11"> --}}
    </div>
    @else
    <div class="flex gap-2.5 mb-4 w-[400px]">
        {{-- <img src="https://pagedone.io/asset/uploads/1710412177.png" alt="Shanay image" class="w-10 h-11"> --}}
        <div class="grid">
            <h5 class="pb-1 text-sm font-semibold leading-snug text-gray-900">{{$message->sender->name}}</h5>
            <div class="grid">
                <div class="px-3.5 py-2 bg-gray-100 rounded justify-start  items-center gap-3 inline-flex">
                    <p class="flex-wrap text-sm font-normal leading-snug text-gray-900">
                        {{$message->message}}
                    </p>
                </div>
                <div class="justify-end items-center inline-flex mb-2.5">
                    <h6 class="py-1 text-xs font-normal leading-4 text-gray-500">05:14 PM</h6>
                </div>
            </div>
        </div>
    </div>
    @endif
    {{-- <div class="flex items-start gap-2.5">
        <div class="flex flex-col gap-1 w-[320px]">
            <div class="flex flex-row-reverse items-center gap-3">
                <span class="text-sm font-semibold text-gray-900 dark:text-white {{$class}}">
                    {{ $message->sender->id == Auth::user()->id ? 'You': $message->sender->name }}
                </span>
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400 {{$class}}">{{$message->created_at}}</span>
            </div>
            <div class="flex flex-col leading-1.5 p-4 border-gray-200 bg-gray-100 rounded-e-xl rounded-es-xl dark:bg-gray-700">
                <p class="text-sm font-normal text-gray-900 dark:text-white {{$class}}">
                    {{$message->message}}
                </p>
            </div>
        </div>
    </div> --}}
</div>
