<script setup>
import ProductList from "@/Components/Products/ProductList.vue";
import Default from "@/Layouts/Default.vue";

defineProps({
    type: String,
});
</script>

<template>
    <div class="w-full py-12">
        <ProductList :type="$props.type" />
    </div>
</template>
