<script setup>
import { usePage, usePoll } from "@inertiajs/vue3";
import { Fieldset } from "primevue";
import { computed, ref } from "vue";

const page = usePage();

const balance = ref(page.props.auth.user?.balance || 0);

if (page.props.auth.user) {
    Echo.private(`user.${page.props.auth.user.id}`).listen(
        "Client\\UserBalanceUpdated",
        (event) => {
            console.log(event);
            balance.value = event.balance;
        }
    );
}

const formatCurrency = window.formatCurrency;
</script>

<template>
    <div class="">
        <Fieldset
            legend="Balance"
            class="flex flex-col items-center justify-center text-sm bg-transparent"
        >
            <span>{{ formatCurrency(balance) }}</span>
        </Fieldset>
    </div>
</template>
