<script setup>
import Default from "@/Layouts/Default.vue";
import axios from "axios";
import { Button, Card, DataView, Skeleton, Tag } from "primevue";
import { onBeforeUnmount, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";

const page = usePage();
const orders = ref([]);
const isLoading = ref(true);

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

function payClicked(order) {
    if (order.status != "pending") return;
    router.post(route("my-order.pay", { order: order.id }));
}

axios.get(route("my-order.latest")).then((response) => {
    orders.value = response.data;
    isLoading.value = false;
});

function updateOrders(updatedOrder) {
    updatedOrder.status = updatedOrder.status || "pending";

    const index = orders.value.findIndex(
        (order) => order.id === updatedOrder.id
    );

    if (index !== -1) {
        // Update the existing order
        orders.value.splice(index, 1); // Remove the old instance
    }

    // Add the updated order
    orders.value.push(updatedOrder);

    // Sort by `created_at` in descending order
    orders.value.sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );
}

if (page.props.auth.user) {
    const channel = Echo.private(`user.${page.props.auth.user.id}`);

    channel.listen("Client\\OrderUpdated", (event) => {
        updateOrders(event.order);
    });

    onBeforeUnmount(() => {
        channel.stopListening("Client\\OrderUpdated");
    });
}

const formatCurrency = window.formatCurrency;
</script>

<style></style>

<template>
    <Default>
        <div class="w-full py-12">
            <Card class="w-full parent">
                <template #title>Order History</template>
                <template #content>
                    <DataView :value="orders" paginator :rows="5">
                        <template #empty>
                            <div class="flex flex-col items-center">
                                <div
                                    v-for="i in 6"
                                    :key="i"
                                    v-if="isLoading == true"
                                >
                                    <div
                                        class="flex flex-col gap-6 p-6 xl:flex-row xl:items-start"
                                        :class="{
                                            'border-t border-surface-200 dark:border-surface-700':
                                                i !== 0,
                                        }"
                                    >
                                        <Skeleton
                                            class="!w-9/12 sm:!w-64 xl:!w-40 !h-24 mx-auto"
                                        />
                                        <div
                                            class="flex flex-col items-center justify-between flex-1 gap-6 sm:flex-row xl:items-start"
                                        >
                                            <div
                                                class="flex flex-col items-center gap-4 sm:items-start"
                                            >
                                                <Skeleton
                                                    width="8rem"
                                                    height="2rem"
                                                />
                                                <Skeleton
                                                    width="6rem"
                                                    height="1rem"
                                                />

                                                <div
                                                    class="flex items-center gap-4"
                                                >
                                                    <Skeleton
                                                        width="6rem"
                                                        height="1rem"
                                                    />
                                                    <Skeleton
                                                        width="3rem"
                                                        height="1rem"
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center gap-4 sm:flex-col sm:items-end sm:gap-2"
                                            >
                                                <Skeleton
                                                    width="4rem"
                                                    height="2rem"
                                                />
                                                <Skeleton
                                                    size="3rem"
                                                    shape="circle"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <template v-else
                                    >There is no orders made yet</template
                                >
                            </div>
                        </template>
                        <template #list="prop">
                            <Card
                                class="w-full"
                                v-for="item in prop.items"
                                v-if="isLoading == false"
                            >
                                <template #content>
                                    <div class="flex justify-between">
                                        <div
                                            class="p-card-caption"
                                            data-pc-section="caption"
                                        >
                                            <div
                                                class="p-card-title"
                                                data-pc-section="title"
                                            >
                                                {{ item.reference }}
                                            </div>
                                            <div
                                                class="p-card-subtitle"
                                                data-pc-section="subtitle"
                                            >
                                                {{ item.created_at }}
                                            </div>
                                            <div class="p-card-content">
                                                {{ formatCurrency(item.total) }}
                                            </div>
                                        </div>

                                        <div
                                            class="flex flex-col items-end justify-between"
                                        >
                                            <Tag
                                                class="text-xs capitalize w-fit"
                                                :severity="
                                                    statuses[item.status]
                                                "
                                                :value="item.status"
                                            ></Tag>
                                            <div class="flex justify-end gap-3">
                                                <Button
                                                    @click="
                                                        () => payClicked(item)
                                                    "
                                                    size="small"
                                                    icon="pi pi-wallet"
                                                    v-bind="
                                                        buttonLabels[
                                                            item.status
                                                        ]
                                                    "
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
                                </template>
                            </Card>
                        </template>
                    </DataView>
                </template>
            </Card>
        </div>
    </Default>
</template>
