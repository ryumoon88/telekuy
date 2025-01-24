<script setup>
import Default from "@/Layouts/Default.vue";
import { Deferred, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import fileDownload from "js-file-download";
import moment from "moment";

import {
    Accordion,
    AccordionContent,
    AccordionHeader,
    AccordionPanel,
    Button,
    Card,
    DataView,
    Divider,
    Fieldset,
    Panel,
    Skeleton,
    Tag,
} from "primevue";
import { onBeforeUnmount } from "vue";

const props = defineProps({
    order: Object,
    attachments: Object,
});

const page = usePage();
// Group constants for better organization
const constants = {
    statuses: {
        pending: "warn",
        completed: "success",
    },
    buttonLabels: {
        pending: { label: "Pay" },
        completed: { severity: "success", label: "Paid", disabled: true },
    },
    severities: {
        account: "info",
        referral: "primary",
        bot: "contrast",
    },
};

const download = {
    tdata: () => {
        axios
            .get(
                route("my-order.attachment.download", {
                    order: props.order.id,
                    target: "account",
                    type: "tdata",
                }),
                {
                    responseType: "blob",
                }
            )
            .then((response) => {
                const contentDisposition =
                    response.headers["content-disposition"];
                let filename = "downloaded-file.zip"; // Default filename if not found

                if (
                    contentDisposition &&
                    contentDisposition.includes("filename=")
                ) {
                    filename = contentDisposition
                        .split("filename=")[1]
                        .replace(/['"]/g, ""); // Remove quotes if present
                }

                fileDownload(response.data, filename);
            });
    },
};

// Function to handle payment
function payClicked(order) {
    if (order.status === "pending") {
        axios.post(route("my-order.pay", { order: order.id }));
    }
}

// Setup Echo listener
if (page.props.auth.user) {
    const channel = Echo.private(`user.${page.props.auth.user.id}`);

    channel.listen("Client\\OrderUpdated", (event) => {
        console.log("AA");
        router.reload({ only: ["order"] });
    });

    // Cleanup listener
    onBeforeUnmount(() => {
        channel.stopListening("Client\\OrderUpdated");
    });
}

function copyToClipboard(doi) {
    console.log(doi);
    navigator.clipboard.writeText(doi);
}

// Currency formatter
const formatCurrency = window.formatCurrency;
</script>

<style>
.parent > .p-fieldset-content-container {
    @apply w-full;
}
.parent > .p-fieldset-content-container > .p-fieldset-content {
    @apply flex flex-row gap-3 w-full;
}

.p-fieldset-legend-label {
    @apply text-nowrap;
}
</style>

<template>
    <Default>
        <div class="flex flex-col w-full gap-2 py-12">
            <Deferred data="order">
                <template #fallback>
                    <Card class="w-full">
                        <template #content>
                            <!-- Skeleton Order Details -->
                            <div class="flex justify-between w-full">
                                <div class="flex flex-col gap-3">
                                    <Skeleton width="24rem"> </Skeleton>
                                    <Skeleton style="width: 16rem"> </Skeleton>
                                </div>
                                <div
                                    class="flex flex-col items-end justify-between"
                                >
                                    <Skeleton width="5rem"> </Skeleton>
                                    <Skeleton width="4rem"> </Skeleton>
                                </div>
                            </div>
                        </template>
                    </Card>
                </template>
                <Card class="w-full">
                    <template #content>
                        <!-- Order Details -->
                        <Accordion class="flex flex-col w-full gap-3">
                            <div class="flex justify-between">
                                <div
                                    class="p-card-caption"
                                    data-pc-section="caption"
                                >
                                    <div
                                        class="p-card-title"
                                        data-pc-section="title"
                                    >
                                        {{ order.reference }}
                                    </div>
                                    <div
                                        class="p-card-subtitle"
                                        data-pc-section="subtitle"
                                    >
                                        {{ order.created_at }}
                                    </div>
                                </div>
                                <div
                                    class="flex flex-col justify-between gap-4"
                                >
                                    <Tag
                                        class="text-xs capitalize w-fit"
                                        :severity="
                                            constants.statuses[order.status]
                                        "
                                        :value="order.status"
                                    />
                                    <div class="flex justify-end">
                                        <Button
                                            size="small"
                                            icon="pi pi-wallet"
                                            v-bind="
                                                constants.buttonLabels[
                                                    order.status
                                                ]
                                            "
                                            class="text-xs"
                                            @click="payClicked(order)"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <DataView
                                class="w-full"
                                :value="order.order_products"
                            >
                                <template #header>Items</template>
                                <template #list="props">
                                    <AccordionPanel
                                        v-for="(item, index) in props.items"
                                        :value="index"
                                    >
                                        <AccordionHeader>
                                            <div
                                                class="flex items-center w-full"
                                            >
                                                <img
                                                    src="https://dummyimage.com/50x50/000/fff"
                                                    alt=""
                                                />
                                                <div class="flex flex-col ms-3">
                                                    <span
                                                        class="text-sm font-bold"
                                                        >{{
                                                            item.product.name
                                                        }}</span
                                                    >
                                                    <Tag
                                                        :value="
                                                            item.product.type
                                                        "
                                                        class="text-xs capitalize w-fit"
                                                        :severity="
                                                            constants
                                                                .severities[
                                                                item.product
                                                                    .type
                                                            ]
                                                        "
                                                    />
                                                </div>
                                                <span
                                                    class="grow text-end me-3"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            item.total
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </AccordionHeader>
                                        <AccordionContent>
                                            <div
                                                class="flex flex-col items-center w-full gap-1"
                                            >
                                                <div
                                                    v-for="(
                                                        product, idx
                                                    ) in item.order_product_items"
                                                    :key="idx"
                                                    class="flex items-center w-full"
                                                >
                                                    <img
                                                        src="https://dummyimage.com/50x50/000/fff"
                                                        alt=""
                                                    />
                                                    <div
                                                        class="flex flex-col justify-center ms-3 text-start"
                                                    >
                                                        <span
                                                            v-if="
                                                                item.product
                                                                    .type ===
                                                                'account'
                                                            "
                                                            class="text-sm"
                                                        >
                                                            {{
                                                                product
                                                                    .orderable
                                                                    .phone_number
                                                            }}
                                                        </span>
                                                        <template
                                                            v-if="
                                                                item.product
                                                                    .type ===
                                                                'referral'
                                                            "
                                                        >
                                                            <span
                                                                class="text-sm"
                                                                >{{
                                                                    product.quantity
                                                                }}x</span
                                                            >
                                                            <span
                                                                class="text-sm"
                                                            >
                                                                Referral:
                                                                {{
                                                                    product
                                                                        .extra
                                                                        ?.target
                                                                }}
                                                            </span>
                                                        </template>
                                                        <template
                                                            v-if="
                                                                item.product
                                                                    .type ===
                                                                'bot'
                                                            "
                                                        >
                                                            <span
                                                                class="text-sm"
                                                            >
                                                                {{
                                                                    product
                                                                        .orderable
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
                                                    </div>
                                                </div>
                                            </div>
                                        </AccordionContent>
                                    </AccordionPanel>
                                </template>
                            </DataView>
                        </Accordion>

                        <!-- Total Price -->
                        <div class="flex items-center justify-between p-3">
                            <span>Total</span>
                            <span>{{ formatCurrency(order.total || 0) }}</span>
                        </div>
                    </template>
                </Card>
                <Card v-if="order.status == 'completed'">
                    <template #title>Attachments</template>
                    <template #content>
                        <div
                            class="flex justify-center"
                            v-if="
                                order.order_products.filter(
                                    (order_product) =>
                                        order_product.product.type === 'bot'
                                ).length < 1 && !order.has_accounts
                            "
                        >
                            No attachments
                        </div>
                        <Panel toggleable v-if="order.has_accounts">
                            <template #header>
                                <div class="flex items-center w-full">
                                    <div class="flex flex-col ms-3">
                                        <span class="text-sm font-bold"
                                            >Accounts
                                            {{
                                                order.order_products
                                                    .filter(
                                                        (order_product) =>
                                                            order_product
                                                                .product.type ==
                                                            "account"
                                                    )
                                                    .reduce(
                                                        (
                                                            n,
                                                            {
                                                                order_product_items,
                                                            }
                                                        ) =>
                                                            n +
                                                            order_product_items.length,
                                                        0
                                                    )
                                            }}x</span
                                        >
                                        <!-- <Tag
                                            :value="item.product.type"
                                            class="text-xs capitalize w-fit"
                                            :severity="
                                                constants.severities[
                                                    item.product.type
                                                ]
                                            "
                                        /> -->
                                    </div>
                                </div>
                            </template>
                            <Divider />
                            <div class="flex flex-col items-center gap-3">
                                <span class="text-lg">Download as</span>
                                <div class="flex gap-3">
                                    <Button label="TData" size="small"></Button>
                                    <Button
                                        label="Telethon Session"
                                        size="small"
                                    ></Button>
                                    <Button
                                        label="Pyogram Session"
                                        size="small"
                                    ></Button>
                                    <Button label="JSON" size="small"></Button>
                                </div>
                                <Divider />
                                <span class="text-lg">Instruction</span>
                                <p>
                                    Lorem, ipsum dolor sit amet consectetur
                                    adipisicing elit. Obcaecati, consequuntur!
                                    Vel itaque expedita tempore, deserunt sunt
                                    consequuntur saepe rem assumenda, alias
                                    numquam fuga amet iusto nihil enim!
                                    Mollitia, animi sint.
                                </p>
                            </div>
                        </Panel>
                        <Panel
                            toggleable
                            collapsed
                            v-for="(item, index) in order.order_products.filter(
                                (order_product) =>
                                    order_product.product.type === 'bot'
                            )"
                        >
                            <template #header>
                                <div class="flex items-center w-full">
                                    <div class="flex flex-col ms-3">
                                        <span class="text-sm font-bold">{{
                                            item.product.name
                                        }}</span>
                                        <Tag
                                            :value="item.product.type"
                                            class="text-xs capitalize w-fit"
                                            :severity="
                                                constants.severities[
                                                    item.product.type
                                                ]
                                            "
                                        />
                                    </div>
                                    <Button
                                        size="small"
                                        class="ms-auto me-3 z-[999]"
                                        label="Download"
                                        @click="console.log('a')"
                                    >
                                    </Button>
                                </div>
                            </template>

                            <div
                                class="flex flex-col items-center w-full gap-1 mt-3"
                            >
                                <Fieldset
                                    v-for="(
                                        product, index
                                    ) in item.order_product_items"
                                    :legend="product.orderable.duration"
                                    class="flex flex-row w-full parent"
                                >
                                    <div class="flex flex-row w-full gap-3">
                                        <Fieldset
                                            legend="Status"
                                            class="justify-center w-fit"
                                        >
                                            <Tag
                                                :value="
                                                    product.license.active
                                                        ? 'Activated'
                                                        : 'Not Active'
                                                "
                                                :severity="
                                                    product.license.active
                                                        ? 'success'
                                                        : 'danger'
                                                "
                                                class="w-full text-xs text-nowrap"
                                            ></Tag>
                                        </Fieldset>
                                        <Fieldset
                                            legend="Expired at"
                                            class="justify-center w-fit text-nowrap"
                                        >
                                            <span>{{
                                                moment(
                                                    product.license.expired_at
                                                ).format("llll")
                                            }}</span>
                                        </Fieldset>
                                        <Fieldset
                                            legend="Expire"
                                            class="justify-center w-fit"
                                        >
                                            <span class="text-nowrap">{{
                                                moment(
                                                    product.license.expired_at
                                                ).fromNow()
                                            }}</span>
                                        </Fieldset>
                                        <Fieldset
                                            legend="License Key"
                                            class="justify-center w-full"
                                        >
                                            <span
                                                class="cursor-pointer"
                                                :class="
                                                    product.license
                                                        ? 'blur hover:blur-0'
                                                        : ''
                                                "
                                                @click="
                                                    () =>
                                                        copyToClipboard(
                                                            product.license
                                                                .license
                                                        )
                                                "
                                            >
                                                {{ product.license.license }}
                                            </span>
                                        </Fieldset>
                                    </div>
                                </Fieldset>
                            </div>
                            <Divider />
                            <div class="flex flex-col items-center">
                                <span>Instruction</span>
                                <Divider />
                                <p>
                                    Lorem ipsum dolor sit amet consectetur
                                    adipisicing elit. Accusantium deserunt quis
                                    nostrum quisquam error neque obcaecati
                                    excepturi voluptates sapiente impedit
                                    provident optio hic voluptatibus
                                    consequatur, quod maxime possimus.
                                    Distinctio, consequatur.
                                </p>
                            </div>
                        </Panel>
                    </template>
                </Card>
            </Deferred>
        </div>
    </Default>
</template>
