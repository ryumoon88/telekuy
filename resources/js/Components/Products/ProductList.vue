<script setup>
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import { computed, ref } from "vue";
// import Card from "../Card.vue";
import { Button, Card, DataView } from "primevue";

const props = defineProps({
    type: {
        type: String,
        required: false,
    },
});

const products = ref({});

axios.get(route("products.index", { format: "json", type: props.type })).then((response) => {
    if(props.type){

    }
    products.value = response.data ?? {};
});
</script>

<template>
    <Card v-for="(product, type) in products" class="w-full">
        <template #title>
            <span class="text-lg capitalize">{{ type }}</span>
        </template>
        <template #content>
            <DataView :value="product" layout="grid" class="scrollbar-thin">
                <template #grid="props">
                    <div class="flex gap-3">
                        <Link v-for="(item, index) in props.items" :href="route('products.show', {product: item.id})">
                            <Card class="w-[12rem] bg-slate-50 relative overflow-hidden">
                                <template #header>
                                    <img
                                        class="w-[12rem]"
                                        src="https://dummyimage.com/150x150/000/fff"
                                        alt=""
                                    />
                                </template>
                                <template #title>
                                    <span class="text-primary">{{item.name}}</span>
                                </template>
                                <template #footer>
                                    <Button class="w-full text-xs">View Items</Button>
                                </template>
                            </Card>
                        </Link>
                    </div>
                </template>
            </DataView>
        </template>
    </Card>
    <!-- <Card v-for="(product, index) in products" :title="index">
        <div class="flex gap-4 overflow-hidden flex-nowrap">
            <Link
                :href="route('products.show', { product: item.id })"
                class="flex flex-col w-[150px] rounded-lg bg-[#D9D9D9] overflow-hidden shrink-0"
                v-for="(item, index) in product"
                :key="index"
            >
                <div class="">
                    <img src="https://dummyimage.com/150x150/000/fff" alt="" />
                </div>
                <div class="p-4">
                    <h1 class="text-sm text-black">
                        {{ item.name }}
                    </h1>
                </div>
            </Link>
        </div>
    </Card> -->
</template>
