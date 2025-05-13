<script setup>
import { computed } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Default from "@/Layouts/Default.vue";
import { Button, Card, Message } from "primevue";

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <Head title="Email Verification" />

    <Card class="max-w-xl m-auto">
        <template #content>
            <div class="mb-4 text-sm">
                Thanks for signing up! Before getting started, could you verify
                your email address by clicking on the link we just emailed to
                you? If you didn't receive the email, we will gladly send you
                another.
            </div>

            <Message
                variant="simple"
                severity="success"
                v-if="verificationLinkSent"
            >
                A new verification link has been sent to the email address you
                provided during registration.
            </Message>

            <form @submit.prevent="submit">
                <div class="flex items-center justify-between mt-4">
                    <Button
                        severity="secondary"
                        size="small"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        type="submit"
                    >
                        Resend Verification Email
                    </Button>

                    <Button
                        variant="link"
                        :href="route('logout')"
                        method="post"
                        :as="Link"
                    >
                        Log Out
                    </Button>
                </div>
            </form>
        </template>
    </Card>
</template>
