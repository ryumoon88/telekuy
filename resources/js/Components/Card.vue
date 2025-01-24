<script setup>
const props = defineProps({
    title: {
        type: String,
        required: false,
    },
});
</script>

<template>
    <div class="bg-[#0E275E] flex flex-col text-white p-4 rounded-lg w-full gap-4">
        <div class="flex justify-between">
            <span class="text-lg font-bold uppercase">
                {{ title }}
            </span>
            <slot name="action" />
        </div>
        <slot/>
    </div>
</template>
