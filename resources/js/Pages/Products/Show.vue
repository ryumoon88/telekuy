<script setup>
// import Card from "@/Components/Card.vue";
import Default from "@/Layouts/Default.vue";
import { router, useForm } from "@inertiajs/vue3";
import { Form } from "@primevue/forms";
import {
    Button,
    Card,
    DataView,
    Dialog,
    InputText,
    Message,
    Select,
} from "primevue";
import { computed, ref, Teleport } from "vue";

const props = defineProps({
    product: Object,
});

function addToCart(item) {
    let data = {};
    if (props.product.type == "referral") {
        data = {
            quantity: referral.value.quantity,
            target: referral.value.extra.target,
        };
    }
    console.log(props.product.type);
    router.post(
        route("my-cart.add", {
            cartable_id: item.id,
            type: props.product.type,
        }),
        data
    );

    referral.value = {
        quantity: null,
        extra: {
            target: null,
        },
    };

    modalShowed.value.referral = false;
}

const referral = ref({
    quantity: null,
    extra: {
        target: null,
    },
});

const bot = ref({
    botOption: null,
});

const modalShowed = ref({
    referral: false,
});

const totalReferralPrice = computed(() => {
    return formatCurrency(
        props.product.referral.price * referral.value.quantity || 0
    );
});

const totalBotPrice = computed(() => {
    return formatCurrency(bot.value.botOption?.price ?? 0);
});

const formatCurrency = window.formatCurrency;

console.log(props.product);
</script>

<template>
    <Default>
        <Card class="w-full">
            <template #title> {{ props.product.name }}</template>
            <template #content>
                <!-- account -->
                <div
                    class="flex flex-col gap-4"
                    v-if="props.product.type == 'account'"
                >
                    <DataView :value="props.product.accounts">
                        <template #list="prop">
                            <div class="flex flex-col gap-3">
                                <div
                                    class="flex gap-3"
                                    v-for="item in prop.items"
                                >
                                    <div class="w-[100px] h-[100px]">
                                        <img
                                            alt="flag"
                                            src="https://dummyimage.com/50x50/000/fff"
                                            :class="`flag flag-${item.isoCode} w-full`"
                                        />
                                    </div>
                                    <div
                                        class="flex justify-between w-full p-3 body"
                                    >
                                        <div class="header">
                                            <div class="flex flex-col">
                                                <span>{{
                                                    item.phone_number
                                                }}</span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex flex-col justify-between footer"
                                        >
                                            <span>{{
                                                formatCurrency(
                                                    item.selling_price
                                                )
                                            }}</span>
                                            <Button
                                                icon="pi pi-cart-plus"
                                                label="Add to cart"
                                                size="small"
                                                @click="() => addToCart(item)"
                                            ></Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </DataView>
                </div>

                <!-- referral -->
                <div
                    class="flex flex-col gap-4"
                    v-if="props.product.type == 'referral'"
                >
                    <div class="flex gap-4">
                        <img
                            src="https://dummyimage.com/150x150/000/fff"
                            alt=""
                        />
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-bold">
                                {{ product.name }}
                            </h1>
                            <div class="flex gap-4 text-xs">
                                <span>Updated 9 hours ago</span>
                                <span>6969</span> <span>9322</span>
                            </div>
                            <Form class="mt-3">
                                <div class="flex flex-col gap-1 mt-4">
                                    <label for="packages">Packages</label>
                                    <Select
                                        v-model="referral.quantity"
                                        :options="[10, 25, 50, 100]"
                                        size="small"
                                        placeholder="Select package"
                                    ></Select>
                                    <!-- <Message
                                        severity="error"
                                        size="small"
                                        variant="simple"
                                        >{{ referralForm.errors.botOption }}
                                    </Message> -->
                                    <div class="flex gap-3">
                                        <Button
                                            :label="totalReferralPrice"
                                            size="small"
                                        ></Button>
                                        <Button
                                            :disabled="!referral.quantity"
                                            icon="pi pi-cart-arrow-down"
                                            label="Add to cart"
                                            size="small"
                                            @click="modalShowed.referral = true"
                                        ></Button>
                                    </div>
                                </div>
                            </Form>
                        </div>
                    </div>
                    <div class="">
                        <h1 class="text-lg font-bold">Description</h1>
                        <p>
                            Lorem, ipsum dolor sit amet consectetur adipisicing
                            elit. Et praesentium architecto ea nobis sequi
                            quibusdam ullam asperiores quasi soluta eligendi
                            mollitia alias ipsum fugit delectus voluptate,
                            recusandae corporis inventore nesciunt.
                        </p>
                    </div>
                </div>

                <!-- bot -->
                <div
                    class="flex flex-col gap-4"
                    v-if="props.product.type == 'bot'"
                >
                    <div class="flex gap-4">
                        <img
                            src="https://dummyimage.com/150x150/000/fff"
                            alt=""
                        />
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-bold">
                                {{ product.name }}
                            </h1>
                            <div class="flex gap-4 text-xs">
                                <span>Updated 9 hours ago</span>
                                <span>6969</span> <span>9322</span>
                            </div>
                            <Form class="mt-3">
                                <div class="flex flex-col gap-1 mt-4">
                                    <label for="packages">Packages</label>
                                    <Select
                                        v-model="bot.botOption"
                                        :options="product.bot.options"
                                        option-label="duration"
                                        size="small"
                                        placeholder="Select package"
                                    ></Select>
                                    <!-- <Message
                                        severity="error"
                                        size="small"
                                        variant="simple"
                                        >{{ referralForm.errors.botOption }}
                                    </Message> -->
                                    <div class="flex gap-3">
                                        <Button
                                            :label="totalBotPrice"
                                            size="small"
                                        ></Button>
                                        <Button
                                            icon="pi pi-cart-arrow-down"
                                            label="Add to cart"
                                            size="small"
                                            @click="
                                                () => addToCart(bot.botOption)
                                            "
                                        ></Button>
                                    </div>
                                </div>
                            </Form>
                        </div>
                    </div>
                    <div class="">
                        <h1 class="text-lg font-bold">Description</h1>
                        <p>
                            Lorem, ipsum dolor sit amet consectetur adipisicing
                            elit. Et praesentium architecto ea nobis sequi
                            quibusdam ullam asperiores quasi soluta eligendi
                            mollitia alias ipsum fugit delectus voluptate,
                            recusandae corporis inventore nesciunt.
                        </p>
                    </div>
                </div>
            </template>
        </Card>

        <Teleport to="body">
            <Dialog
                v-model:visible="modalShowed.referral"
                modal
                :header="props.product.name"
                :style="{ width: '25rem' }"
            >
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col gap-3">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold">Product</span>
                            <span>{{ props.product.name }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold">Quantity</span>
                            <span>{{ referral.quantity }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold">Price</span>
                            <span>{{
                                formatCurrency(props.product.referral.price)
                            }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold">Price</span>
                            <span>{{ totalReferralPrice }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 mb-4">
                        <label
                            for="referral_url"
                            class="w-24 font-semibold text-nowrap"
                        >
                            Referral Target
                        </label>
                        <InputText
                            placeholder=""
                            v-model="referral.extra.target"
                            id="referral_url"
                            class="flex-auto"
                            autocomplete="off"
                        />
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button
                            type="button"
                            label="Cancel"
                            severity="secondary"
                            @click="modalShowed.referral = false"
                        ></Button>
                        <Button
                            :disabled="!referral.extra.target"
                            icon="pi pi-cart-arrow-down"
                            label="Add to cart"
                            size="small"
                            @click="() => addToCart(props.product.referral)"
                        ></Button>
                    </div>
                </div>
            </Dialog>
        </Teleport>
    </Default>
</template>
