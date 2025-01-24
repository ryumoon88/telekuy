<script setup>
import ProductList from '@/Components/Products/ProductList.vue';
import Default from '@/Layouts/Default.vue';

defineProps({
    type: String,
})

</script>

<template>
    <Default>
        <ProductList :type="$props.type"/>
    </Default>
</template>
