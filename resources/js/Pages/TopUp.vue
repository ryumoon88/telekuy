<script setup>
import Default from "@/Layouts/Default.vue";
import { Deferred, router } from "@inertiajs/vue3";
import axios from "axios";
import { Button, Card, Divider, Image } from "primevue";
import { computed, onMounted, ref } from "vue";
const props = defineProps({
    topUpOptions: Array,
    canCustomAmount: Boolean,
    paymentMethods: Array,
});

const formatCurrency = window.formatCurrency;
const selectedTopUpOption = ref(0);

function onTopUpOptionSelected(topUpOption) {
    selectedTopUpOption.value = topUpOption;
}

function getPriceCalculation(paymentMethod) {
    let price = selectedTopUpOption.value;
    let disabled = false;

    if (paymentMethod.fee_customer) {
        let flatFee = paymentMethod.fee_customer.flat;
        let percentFee = price * (paymentMethod.fee_customer.percent / 100);
        let minimumFee = parseInt(paymentMethod.minimum_fee);

        let totalFee = percentFee + flatFee;

        console.log(
            "Current: ",
            price,
            "Fee: ",
            totalFee,
            "Minimum Fee: ",
            minimumFee
        );
        price += totalFee < minimumFee ? minimumFee : totalFee;
        console.log("Final: ", price);
    }

    if (
        price < paymentMethod.minimum_amount ||
        selectedTopUpOption.value == 0
    ) {
        disabled = true;
    }

    return {
        disabled: disabled,
        price: disabled ? 0 : price,
    };
}

const paymentMethodDetails = computed(() => {
    const computed = props.paymentMethods.map((method) => ({
        ...method,
        calculation: getPriceCalculation(method),
    }));
    console.log(computed[0].calculation.price);
    return computed;
});

function onPaymentClicked(paymentMethod) {
    const data = {
        method: paymentMethod.code,
        amount: selectedTopUpOption.value,
    };
    axios.post(route("top-up.topup"), data).then((response) => {
        console.log("Top up success");
        if (response.status == 200) {
            window.open(response.data.checkout_url, "_blank");
        }
    });
}
</script>

<template>
    <Card class="w-full">
        <template #title>
            <div class="text-lg text-center">Instant TopUp</div>
        </template>
        <template #content>
            <div class="flex flex-col gap-3">
                <div class="">Top Up Amount</div>
                <Deferred data="topUpOptions">
                    <template #fallback>Loading</template>
                    <div class="grid grid-cols-3 gap-3">
                        <Button
                            class="w-full p-4 text-center text-black bg-white rounded-lg"
                            :class="
                                selectedTopUpOption == topUpOption
                                    ? 'bg-primary'
                                    : ''
                            "
                            v-for="topUpOption in topUpOptions"
                            @click="() => onTopUpOptionSelected(topUpOption)"
                        >
                            {{ formatCurrency(topUpOption, 0) }}
                        </Button>
                    </div>
                </Deferred>
                <Divider />
                <div class="">Payment Method</div>
                <Deferred data="paymentMethods">
                    <template #fallback>Loading</template>
                    <div class="flex flex-col gap-3">
                        <Button
                            class="flex justify-between p-4 text-black bg-white rounded-lg"
                            v-for="(
                                paymentMethod, index
                            ) in paymentMethodDetails"
                            :disabled="paymentMethod.calculation.disabled"
                            @click="() => onPaymentClicked(paymentMethod)"
                        >
                            <div
                                class="flex items-center justify-between w-full"
                            >
                                <Image
                                    :src="paymentMethod.icon_url"
                                    class="flex h-10 max-w-full max-h-full"
                                />
                                <span class="">
                                    {{
                                        formatCurrency(
                                            paymentMethod.calculation.price
                                        )
                                    }}
                                </span>
                            </div>
                        </Button>
                    </div>
                </Deferred>
            </div>
        </template>
    </Card>
</template>
