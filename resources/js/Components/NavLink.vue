<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

</script>

<template>
    <Link
        :href="href"
        :class="
            active
                ? 'bg-[var(--p-menubar-item-active-background)] rounded-lg'
                : ''
        "
    >
        <slot />
    </Link>
</template>
