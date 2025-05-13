<script setup>
import moment from "moment";

defineProps({
    message: Object,
});
</script>

<template>
    <div class="flex flex-col gap-3 text-sm" v-if="!$props.message.you">
        <div class="">Support</div>
        <div class="space-y-3 w-fit">
            <div class="p-3 text-black bg-white rounded-lg">
                <p>{{ $props.message.message }}</p>
            </div>
            <div class="text-end">
                {{ moment($props.message.sent_at).fromNow() }}
            </div>
        </div>
    </div>
    <div class="flex flex-col items-end gap-3 text-sm" v-else>
        <div class="">You</div>
        <div class="space-y-3 w-fit">
            <div class="p-3 text-black rounded-lg bg-primary-300">
                <p>{{ $props.message.message }}</p>
            </div>
            <div class="text-start">
                {{ moment($props.message.sent_at).fromNow() }}
            </div>
        </div>
    </div>
</template>
