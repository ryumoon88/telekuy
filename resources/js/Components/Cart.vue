<script setup>
import {
    Accordion,
    AccordionContent,
    AccordionHeader,
    AccordionPanel,
    Button,
    DataView,
    Popover,
    Tag,
} from "primevue";
import { computed, ref } from "vue";
import CartItem from "./CartItem.vue";
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";

const formatCurrency = window.formatCurrency;

const popover = ref();
const page = usePage();

function togglePopOver(event) {
    popover.value.toggle(event);
}

function checkout() {
    if (cart.cart_products < 1) return;
    router.post(route("my-cart.checkout"));
}

function remove(id) {
    router.delete(route("my-cart.remove", { cartProductItem: id }));
}

const cart = ref({});

const severities = {
    account: "info",
    referral: "primary",
    bot: "contrast",
};

axios.get(route("my-cart.index")).then((response) => {
    cart.value = response.data;
});

if (page.props.auth.user) {
    Echo.private(`user.${page.props.auth.user.id}`).listen(
        "Client\\MyCartUpdated",
        (event) => {
            console.log(event);
            cart.value = event.cart;
        }
    );
}
</script>

<template>
    <div class="flex items-center justify-center">
        <Button
            icon="pi pi-shopping-cart"
            variant="text"
            @click="togglePopOver"
        />
        <Popover ref="popover">
            <div class="flex flex-col w-[24rem]">
                <Accordion>
                    <DataView :value="cart.cart_products">
                        <template #header>
                            <span class="pi pi-shopping-cart me-2"></span>
                            Cart
                        </template>
                        <template #empty>
                            <div class="w-[24rem] py-3 text-center">
                                There is no items
                            </div>
                        </template>
                        <template #list="props">
                            <AccordionPanel
                                v-for="(item, index) in props.items"
                                :value="index"
                            >
                                <AccordionHeader>
                                    <div class="flex items-center w-full">
                                        <img
                                            src="https://dummyimage.com/50x50/000/fff"
                                            alt=""
                                        />
                                        <div class="flex flex-col ms-3">
                                            <span class="text-sm font-bold">
                                                {{ item.product.name }}
                                            </span>
                                            <Tag
                                                :value="item.product.type"
                                                class="text-xs capitalize w-fit"
                                                :severity="
                                                    severities[
                                                        item.product.type
                                                    ]
                                                "
                                            ></Tag>
                                        </div>
                                        <span class="grow text-end me-3">
                                            {{ formatCurrency(item.total) }}
                                        </span>
                                    </div>
                                </AccordionHeader>
                                <AccordionContent>
                                    <div
                                        class="flex flex-col items-center w-full gap-1"
                                    >
                                        <div
                                            class="flex items-center w-full"
                                            v-for="(
                                                product, index
                                            ) in item.cart_product_items"
                                        >
                                            <img
                                                src="https://dummyimage.com/50x50/000/fff"
                                                alt=""
                                            />
                                            <div
                                                class="flex flex-col justify-center ms-3 text-start"
                                            >
                                                <span
                                                    class="text-sm"
                                                    v-if="
                                                        item.product.type ==
                                                        'account'
                                                    "
                                                    >{{
                                                        product.cartable
                                                            .phone_number
                                                    }}</span
                                                >
                                                <template
                                                    v-if="
                                                        item.product.type ==
                                                        'referral'
                                                    "
                                                >
                                                    <span class="text-sm">
                                                        {{ product.quantity }}x
                                                    </span>
                                                    <span class="text-sm">
                                                        Referral:
                                                        {{
                                                            product.extra.target
                                                        }}
                                                    </span>
                                                </template>
                                                <template
                                                    v-if="
                                                        item.product.type ==
                                                        'bot'
                                                    "
                                                >
                                                    <span class="text-sm">
                                                        {{
                                                            product.cartable
                                                                .duration
                                                        }}
                                                    </span>
                                                </template>
                                            </div>
                                            <div
                                                class="flex items-center justify-center grow"
                                            >
                                                <span
                                                    class="grow text-end me-3"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            product.price
                                                        )
                                                    }}
                                                </span>
                                                <Button
                                                    icon="pi pi-trash"
                                                    severity="danger"
                                                    variant="text"
                                                    rounded
                                                    aria-label="Cancel"
                                                    @click="
                                                        () => remove(product.id)
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </AccordionContent>
                            </AccordionPanel>
                        </template>
                    </DataView>
                </Accordion>
                <div class="flex items-center justify-between p-3">
                    <span>Total</span>
                    <span>{{ formatCurrency(cart.total || 0) }}</span>
                </div>
                <Button
                    icon="pi pi-cart-arrow-down"
                    :disabled="cart.cart_products?.length < 1"
                    severity="warn"
                    label="Checkout"
                    @click="checkout"
                ></Button>
            </div>
        </Popover>
    </div>
</template>
