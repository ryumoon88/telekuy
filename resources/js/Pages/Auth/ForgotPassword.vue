<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Default from "@/Layouts/Default.vue";
import { Button, Card, InputText, Message } from "primevue";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
    console.log(form.errors);
};
</script>

<template>
    <Default>
        <Head title="Forgot Password" />

        <Card class="max-w-xl m-auto">
            <template #content>
                <Message variant="simple" severity="contrast" class="mb-3">
                    Forgot your password? No problem. Just let us know your
                    email address and we will email you a password reset link
                    that will allow you to choose a new one.
                </Message>

                <div
                    v-if="status"
                    class="mb-4 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div>
                        <InputLabel for="email" value="Email" />

                        <InputText
                            id="email"
                            type="email"
                            class="block w-full mt-1"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <Message variant="simple" severity="error" class="mt-2">
                            {{ form.errors.email }}
                        </Message>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <Button
                            type="submit"
                            size="small"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Email Password Reset Link
                        </Button>
                    </div>
                </form>
            </template>
        </Card>
    </Default>
</template>
