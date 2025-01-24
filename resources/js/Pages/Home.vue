<script setup>
import Default from "@/Layouts/Default.vue";
import Card from "@/Components/Card.vue";
import { Carousel, Slide, Pagination, Navigation } from "vue3-carousel";
import "vue3-carousel/dist/carousel.css";
import { Link } from "@inertiajs/vue3";
import ProductList from "@/Components/Products/ProductList.vue";

const config = {
    itemsToShow: 1.5,
};
</script>

<template>
    <Default>
        <div class="flex flex-col max-w-6xl gap-4">
            <Carousel v-bind="config">
                <Slide v-for="slide in 10" :key="slide">
                    <img src="https://dummyimage.com/1080x720/000/fff" alt="" />
                </Slide>

                <template #addons>
                    <Navigation />
                    <Pagination />
                </template>
            </Carousel>
            <ProductList />
            <!-- <Card title="Referral">
                <div class="flex gap-4 overflow-hidden flex-nowrap">
                    <a
                        :href="route('product', {product: item.id})"
                        class="flex flex-col w-[150px] rounded-lg bg-[#D9D9D9] overflow-hidden shrink-0"
                        v-for="(item, index) in props.referralProducts"
                    >
                        <div class="">
                            <img
                                src="https://dummyimage.com/150x150/000/fff"
                                alt=""
                            />
                        </div>
                        <div class="p-4">
                            <h1 class="text-sm text-black">
                                {{ item.name }}
                            </h1>
                        </div>
                    </a>
                </div>
            </Card>
            <Card title="Account">
                <div class="flex gap-4 overflow-hidden flex-nowrap">
                    <div
                        class="flex flex-col w-[150px] rounded-lg bg-[#D9D9D9] overflow-hidden shrink-0"
                        v-for="(item, index) in props.botProducts"
                    >
                        <div class="">
                            <img
                                src="https://dummyimage.com/150x150/000/fff"
                                alt=""
                            />
                        </div>
                        <div class="p-4">
                            <h1 class="text-sm text-black">
                                {{ item.name }}
                            </h1>
                        </div>
                    </div>
                </div>
            </Card>
            <Card title="Account">
                <div class="flex gap-4 overflow-hidden flex-nowrap">
                    <div
                        class="flex flex-col w-[150px] rounded-lg bg-[#D9D9D9] overflow-hidden shrink-0"
                        v-for="i in 10"
                    >
                        <div class="">
                            <img
                                src="https://dummyimage.com/150x150/000/fff"
                                alt=""
                            />
                        </div>
                        <div class="p-4">
                            <h1 class="text-sm text-black">
                                Telegram Account South Africa
                            </h1>
                        </div>
                    </div>
                </div>
            </Card> -->
        </div>
    </Default>
</template>
