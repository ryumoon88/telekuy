<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { Button, Card, DataView, Fieldset, Popover, Tag } from "primevue";
import { ref } from "vue";

const formatCurrency = window.formatCurrency;

const popover = ref();

const page = usePage();

const statuses = {
    pending: "warn",
    completed: "success",
};

const buttonLabels = {
    pending: {
        label: "Pay",
    },
    completed: {
        severity: "success",
        label: "Paid",
        disabled: true,
    },
};

function togglePopOver(event) {
    popover.value.toggle(event);
}

function payClicked(order) {
    if (order.status != "pending") return;

    router.post(route("my-order.pay", { order: order.id }));
}

const latestOrders = ref([]);

axios.get(route("my-order.latest", { limit: 5 })).then((response) => {
    latestOrders.value = response.data;
});

function updateOrders(updatedOrder) {
    updatedOrder.status = updatedOrder.status || "pending";

    const index = latestOrders.value.findIndex(
        (order) => order.id === updatedOrder.id
    );

    if (index !== -1) {
        // Update the existing order
        latestOrders.value.splice(index, 1); // Remove the old instance
    }

    // Add the updated order
    latestOrders.value.push(updatedOrder);

    // Sort by `created_at` in descending order
    latestOrders.value.sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );

    // Limit the array to 5 items
    if (latestOrders.value.length > 5) {
        latestOrders.value = latestOrders.value.slice(0, 5);
    }
}

if (page.props.auth.user) {
    Echo.private(`user.${page.props.auth.user.id}`).listen(
        "Client\\OrderUpdated",
        (event) => {
            updateOrders(event.order);
        }
    );
}
</script>

<style>
.p-card-title {
    @apply text-base;
}
.p-card-subtitle {
    @apply text-sm;
}
</style>

<template>
    <div class="flex items-center justify-center">
        <Button icon="pi pi-book" variant="text" @click="togglePopOver" />
        <Popover ref="popover">
            <DataView :value="latestOrders">
                <template #header>
                    <span class="pi pi-book me-2"></span>
                    Orders
                </template>
                <template #empty>
                    <div class="w-[24rem] py-3 text-center">
                        There is no orders
                    </div>
                </template>
                <template #list="props">
                    <div
                        class="flex flex-col items-center justify-center"
                        v-for="(item, index) in props.items"
                    >
                        <div
                            class="flex flex-row gap-4 px-6 py-3 w-[26rem] sm:flex-row sm:items-center justify-between"
                            :class="{
                                'border-t border-surface-200 dark:border-surface-700':
                                    index !== 0,
                            }"
                        >
                            <div class="flex flex-col gap-3">
                                <div class="mt-2 text-xs font-medium">
                                    {{ item.reference }}
                                </div>
                                <Tag
                                    class="text-xs capitalize w-fit"
                                    :severity="statuses[item.status]"
                                    :value="item.status"
                                ></Tag>
                            </div>
                            <div class="flex flex-col items-end gap-3">
                                <span>{{ formatCurrency(item.total) }}</span>
                                <div class="flex gap-2">
                                    <Button
                                        @click="() => payClicked(item)"
                                        size="small"
                                        icon="pi pi-wallet"
                                        v-bind="buttonLabels[item.status]"
                                        class="text-xs"
                                    ></Button>
                                    <Button
                                        :as="Link"
                                        :href="
                                            route('my-order.show', {
                                                order: item.id,
                                            })
                                        "
                                        size="small"
                                        label="Detail"
                                        class="text-xs"
                                    >
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </DataView>
            <Button
                :as="Link"
                :href="route('my-order.index')"
                label="View Order History"
                size="small"
                class="w-full"
            ></Button>
        </Popover>
    </div>
</template>
